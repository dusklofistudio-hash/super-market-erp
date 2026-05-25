#!/usr/bin/env bash
# Verify every Yajra DataTable AJAX endpoint returns valid {data, recordsTotal, recordsFiltered} JSON.
#
# Usage: bash tests/audit_datatables.sh (with dev server on http://127.0.0.1:8000)
set -u
BASE="${BASE:-http://127.0.0.1:8000}"
COOKIE_JAR="$(mktemp)"
ROUTES_FILE="$(mktemp --suffix=.json)"
trap 'rm -f "$COOKIE_JAR" "$ROUTES_FILE"' EXIT

php artisan route:list --json > "$ROUTES_FILE"

LOGIN_HTML=$(curl -s -c "$COOKIE_JAR" -b "$COOKIE_JAR" "$BASE/login")
CSRF=$(echo "$LOGIN_HTML" | grep -oP 'name="_token"\s+value="\K[^"]+' | head -1)
curl -s -o /dev/null -c "$COOKIE_JAR" -b "$COOKIE_JAR" \
    -X POST "$BASE/login" -L --max-redirs 0 \
    -d "_token=$CSRF&login=admin&password=password"

DATA_URLS=$(php -r "
\$routes = json_decode(file_get_contents('$ROUTES_FILE'), true);
foreach (\$routes as \$r) {
    \$uri = \$r['uri'] ?? '';
    if (preg_match('#^admin/[^/]+/data\$#', \$uri) && strpos(\$r['method'] ?? '', 'GET') !== false) {
        echo '/'.\$uri.PHP_EOL;
    }
}
")

PASS=0
FAIL=0
declare -a FAILURES=()

while IFS= read -r URL; do
    [ -z "$URL" ] && continue
    RESP=$(curl -s -b "$COOKIE_JAR" "$BASE$URL?draw=1&start=0&length=10")
    OK=$(echo "$RESP" | python3 -c "
import json, sys
try:
    d = json.loads(sys.stdin.read())
    if all(k in d for k in ('draw', 'recordsTotal', 'recordsFiltered', 'data')):
        print('ok', d.get('recordsTotal'), len(d.get('data', [])))
    else:
        print('badshape', list(d.keys())[:5])
except Exception as e:
    print('badjson', str(e)[:80])
" 2>&1)
    if [[ "$OK" == ok* ]]; then
        PASS=$((PASS+1))
        printf "  PASS  %s %s\n" "$URL" "$OK"
    else
        FAIL=$((FAIL+1))
        FAILURES+=("$URL $OK")
        printf "  FAIL  %s %s\n" "$URL" "$OK"
    fi
done <<< "$DATA_URLS"

echo ""
echo "Pass: $PASS  Fail: $FAIL"
[ "$FAIL" -gt 0 ] && exit 2 || exit 0
