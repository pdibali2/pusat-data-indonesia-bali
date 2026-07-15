<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$locked = User::whereNotNull('locked_at')->get();
if ($locked->isEmpty()) {
    echo "no locked users\n";
    exit(0);
}
foreach ($locked as $user) {
    echo $user->username . ': locked_at=' . $user->locked_at->toDateTimeString() . ', unlock_token=' . ($user->unlock_token ?? 'null') . ', expires=' . ($user->unlock_token_expires_at ? $user->unlock_token_expires_at->toDateTimeString() : 'null') . "\n";
}
