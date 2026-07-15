<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$user = User::where('username', 'admin')->first();
if (! $user) {
    echo "no user\n";
    exit(0);
}

echo 'user_id=' . $user->user_id . "\n";
echo 'locked_at=' . ($user->locked_at ? $user->locked_at->toDateTimeString() : 'null') . "\n";
echo 'unlock_token=' . ($user->unlock_token ?? 'null') . "\n";
echo 'unlock_token_expires_at=' . ($user->unlock_token_expires_at ? $user->unlock_token_expires_at->toDateTimeString() : 'null') . "\n";
