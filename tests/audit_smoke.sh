#!/usr/bin/env bash
# Audit smoke test — login as admin, then GET every admin route, asserting HTTP 200.
# Reports any non-200 responses.
#
# Usage: bash tests/audit_smoke.sh (with dev server on http://127.0.0.1:8000)
set -u
BASE="${BASE:-http://127.0.0.1:8000}"
COOKIE_JAR="$(mktemp)"
ROUTES_FILE="$(mktemp --suffix=.json)"
trap 'rm -f "$COOKIE_JAR" "$ROUTES_FILE"' EXIT

# Generate the route list fresh from the running app.
php artisan route:list --json > "$ROUTES_FILE"

echo ">> 1. Login as admin"
# Fetch login page to get CSRF token
LOGIN_HTML=$(curl -s -c "$COOKIE_JAR" -b "$COOKIE_JAR" "$BASE/login")
CSRF=$(echo "$LOGIN_HTML" | grep -oP 'name="_token"\s+value="\K[^"]+' | head -1)
if [ -z "$CSRF" ]; then
    echo "   FAIL: could not get CSRF token from login page"
    exit 1
fi

# Submit login
LOGIN_RC=$(curl -s -o /dev/null -w "%{http_code}" \
    -c "$COOKIE_JAR" -b "$COOKIE_JAR" \
    -X POST "$BASE/login" \
    -L --max-redirs 0 \
    -d "_token=$CSRF&login=admin&password=password")
echo "   login POST returned: $LOGIN_RC (expect 302)"

# Verify dashboard
DASH_RC=$(curl -s -o /dev/null -w "%{http_code}" -c "$COOKIE_JAR" -b "$COOKIE_JAR" "$BASE/admin")
echo "   dashboard GET returned: $DASH_RC (expect 200)"
if [ "$DASH_RC" != "200" ]; then
    echo "   FAIL: not authenticated after login"
    exit 1
fi

echo ""
echo ">> 2. Smoke-test every admin GET route"
echo ""

# Generate the list of URLs to test (substituting numeric IDs into {param} placeholders).
URLS=$(php -r "
\$routes = json_decode(file_get_contents('$ROUTES_FILE'), true);
foreach (\$routes as \$r) {
    \$method = \$r['method'] ?? '';
    if (strpos(\$method, 'GET') === false) continue;
    \$uri = \$r['uri'] ?? '';
    if (\$uri !== 'admin' && strpos(\$uri, 'admin/') !== 0) continue;
    \$url = '/' . preg_replace('/\\{[^}]+\\}/', '1', \$uri);
    echo \$url . PHP_EOL;
}
")

PASS=0
FAIL=0
WARN=0
declare -a FAILURES=()

while IFS= read -r URL; do
    [ -z "$URL" ] && continue
    RC=$(curl -s -o /dev/null -w "%{http_code}" -b "$COOKIE_JAR" "$BASE$URL")
    if [ "$RC" = "200" ]; then
        PASS=$((PASS+1))
        printf "  PASS  %s %s\n" "$RC" "$URL"
    elif [ "$RC" = "302" ]; then
        WARN=$((WARN+1))
        printf "  WARN  %s %s (redirect — likely auth/perm)\n" "$RC" "$URL"
    else
        FAIL=$((FAIL+1))
        FAILURES+=("$RC $URL")
        printf "  FAIL  %s %s\n" "$RC" "$URL"
    fi
done <<< "$URLS"

echo ""
echo "===================="
echo "Pass:    $PASS"
echo "Warn:    $WARN (redirects)"
echo "Fail:    $FAIL"
echo "===================="

if [ "$FAIL" -gt 0 ]; then
    echo ""
    echo "Failures:"
    for f in "${FAILURES[@]}"; do echo "  $f"; done
    exit 2
fi
exit 0
