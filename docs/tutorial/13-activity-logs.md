# Chapter 13 — Activity logs (`ActivityLogger`)

Activity logs are the append-only audit trail of "who did what when".
The user explicitly asked for an `activity_logs` table; this chapter
shows the tiny logging service that writes to it, the listener wiring
that captures logins/logouts automatically, and the admin viewer.

## 1. The table (from the consolidated migration)

```php
Schema::create('activity_logs', function (Blueprint $t) {
    $t->id();
    $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $t->string('action', 64);
    $t->string('subject_type', 191)->nullable();
    $t->unsignedBigInteger('subject_id')->nullable();
    $t->string('ip_address', 45)->nullable();
    $t->string('user_agent', 1000)->nullable();
    $t->json('payload')->nullable();
    $t->timestamps();
});
```

The shape is generic: `(action, subject_type, subject_id, payload)` lets
you log anything. The viewer renders rows polymorphically.

## 2. The `ActivityLog` model

```php
class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id',
                          'ip_address', 'user_agent', 'payload'];
    protected $casts    = ['payload' => 'array'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
```

`payload` is cast to an array so controllers can store arbitrary
attributes without serializing manually.

## 3. The `ActivityLogger` service

```php
class ActivityLogger
{
    public function __construct(protected Request $request) {}

    public function log(string $action, ?Model $subject = null, array $payload = []): ActivityLog
    {
        return ActivityLog::create([
            'user_id'      => $this->request->user()?->id,
            'action'       => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id'   => $subject?->getKey(),
            'ip_address'   => $this->request->ip(),
            'user_agent'   => substr((string) $this->request->userAgent(), 0, 1000),
            'payload'      => $payload,
        ]);
    }
}
```

The service auto-injects current user, IP, and user-agent so callers
only need to provide the semantic event:

```php
$logger->log('sale.completed', $sale, ['ref_no' => $sale->ref_no, 'total' => $sale->total]);
```

## 4. Wiring into authentication events

Laravel fires `Illuminate\Auth\Events\Login` and `Logout` automatically.
Wire them in `app/Providers/EventServiceProvider.php` (or via attribute
listeners on Laravel 11/12):

```php
use Illuminate\Auth\Events\{Login, Logout, PasswordReset};

protected $listen = [
    Login::class         => [LogLogin::class],
    Logout::class        => [LogLogout::class],
    PasswordReset::class => [LogPasswordReset::class],
];
```

The listeners themselves are one-liners:

```php
class LogLogin
{
    public function __construct(protected ActivityLogger $logger) {}
    public function handle(Login $event): void
    {
        $this->logger->log('auth.login', $event->user, ['via' => 'username']);
    }
}
```

## 5. Wiring into write controllers

Every controller that mutates business data calls the logger explicitly
inside (or just after) the transaction:

```php
$logger->log('sale.completed',  $sale,    ['ref_no' => $sale->ref_no, 'total' => $sale->total]);
$logger->log('purchase.received',$purchase,['ref_no' => $purchase->ref_no]);
$logger->log('transfer.sent',   $transfer, ['ref_no' => $transfer->ref_no]);
$logger->log('transfer.received',$transfer,['ref_no' => $transfer->ref_no]);
$logger->log('stock.adjusted',  $adj,     ['reason' => $adj->reason, 'ref_no' => $adj->ref_no]);
$logger->log('expense.recorded',$expense, ['amount' => (float) $expense->amount]);
```

Conventions for the `action` slug:

- Lowercase, dot-namespaced: `<noun>.<verb>` (e.g. `sale.completed`).
- Past tense: prefer `completed` over `complete`.
- Names match the React i18n keys so the viewer can render localized
  labels.

## 6. The viewer (`ActivityLogController`)

Standard Yajra DataTable pattern from Chapter 06:

```php
public function index() {
    return Inertia::render('ActivityLogs/Index');
}

public function data() {
    $q = ActivityLog::query()->with('user:id,name,username')->select('activity_logs.*');

    return DataTables::eloquent($q)
        ->addColumn('user_name', fn ($r) => $r->user?->name ?? '—')
        ->addColumn('subject',   fn ($r) => $r->subject_type
            ? class_basename($r->subject_type) . '#' . $r->subject_id
            : '—')
        ->addColumn('payload_str', fn ($r) => json_encode($r->payload, JSON_UNESCAPED_UNICODE))
        ->editColumn('created_at', fn ($r) => $r->created_at->format('Y-m-d H:i:s'))
        ->toJson();
}
```

The React page renders columns: Time, Actor, Action, Subject, IP,
Payload (with truncation). No edit/delete — logs are append-only.

## 7. RBAC

Only Super Admin and Branch Manager roles should see logs (cashiers
should not be able to audit themselves). Apply the permission:

```php
Route::middleware(['auth', 'permission:activity_logs.view'])->group(function () {
    Route::get('/admin/activity-logs',      [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
    Route::get('/admin/activity-logs/data', [ActivityLogController::class, 'data']) ->name('admin.activity-logs.data');
});
```

## 8. Retention

For production, logs grow forever. Set up a daily prune job:

```php
// routes/console.php
Schedule::command('activity-logs:prune --keep-days=180')->daily();
```

Where the command is a thin Artisan command:

```php
$cutoff = now()->subDays((int) $this->option('keep-days'));
ActivityLog::where('created_at', '<', $cutoff)->delete();
```

## Verify

```bash
php artisan tinker --execute='
use App\Models\ActivityLog;
echo ActivityLog::count() . " rows\n";
$last = ActivityLog::with("user:id,name")->latest("id")->first();
echo "Latest: action={$last->action} user={$last->user?->name} subject={$last->subject_type}#{$last->subject_id}\n";
echo "Payload: " . json_encode($last->payload) . "\n";
'
```

After a fresh login + a POS sale, expected output:

```text
N rows  (where N = seeded count + 2 from the new login + sale)
Latest: action=sale.completed user=Super Admin subject=App\Models\Sale#X
Payload: {"ref_no":"SAL-…","total":2.0,"paid":2.0}
```

In the browser, `/admin/activity-logs` should show the same row at the
top of the table within seconds of the action.
