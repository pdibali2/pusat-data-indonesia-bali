<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\AccountSecurityController;
use Illuminate\Http\Request;
use App\Models\User;

$token = 'lRsQAkmUcbIXzOPXf9TNTndAhUaqQD8xvmA04JUmQtz9tsCajGx9nwNXx8cmJ7sw';
$request = Request::create('/unlock-account/' . $token, 'GET');
$request->setLaravelSession(app('session.store'));

$controller = new AccountSecurityController();
$response = $controller->unlock($request, $token);

$user = User::where('unlock_token', $token)->first();
if ($user) {
    echo 'after unlock locked_at=' . ($user->locked_at ? $user->locked_at->toDateTimeString() : 'null') . "\n";
    echo 'unlock_token=' . ($user->unlock_token ?? 'null') . "\n";
    echo 'unlock_token_expires_at=' . ($user->unlock_token_expires_at ? $user->unlock_token_expires_at->toDateTimeString() : 'null') . "\n";
} else {
    echo 'user gone?\n';
}

if ($response instanceof \Illuminate\Http\RedirectResponse) {
    echo 'redirect:' . $response->getTargetUrl() . '\n';
    echo 'session success=' . $response->getSession()->get('success') . '\n';
}
