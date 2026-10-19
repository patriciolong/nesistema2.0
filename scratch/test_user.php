<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Plantilla;
use App\Http\Controllers\PlantillaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// 1. Get or create a sample plantilla
$plantilla = Plantilla::first();
if (!$plantilla) {
    $plantilla = Plantilla::create([
        'nombre_plantilla' => 'Test Plantilla',
        'contenido_html' => '<p>Test</p>',
        'activo' => true
    ]);
}

// 2. Setup user with permissions excluding 'plantillas.edit'
$user = User::where('role', 'Empleado')->first() ?? User::find(2);
// Give user only plantillas.view, but NOT plantillas.edit
$user->permissions = ['plantillas.view', 'dashboard.view'];
$user->save();
Auth::login($user);

echo "User: " . $user->name . " (Role: " . $user->role . ")\n";
echo "Effective permissions: " . json_encode($user->getEffectivePermissions()) . "\n";
echo "hasPermission('plantillas.view'): " . ($user->hasPermission('plantillas.view') ? 'YES' : 'NO') . "\n";
echo "hasPermission('plantillas.edit'): " . ($user->hasPermission('plantillas.edit') ? 'YES' : 'NO') . "\n";

$controller = new PlantillaController();

// Test index (should be allowed)
try {
    $request = Request::create(route('plantillas.index'), 'GET');
    $view = $controller->index($request);
    echo "Access to plantillas.index: ALLOWED (200 OK)\n";
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    echo "Access to plantillas.index: BLOCKED (" . $e->getStatusCode() . ")\n";
}

// Test edit (should be BLOCKED with 403)
try {
    $view = $controller->edit($plantilla);
    echo "Access to plantillas.edit: ALLOWED (FAILURE - should be blocked!)\n";
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    echo "Access to plantillas.edit: BLOCKED (" . $e->getStatusCode() . " - " . $e->getMessage() . ")\n";
}

// Test update (should be BLOCKED with 403)
try {
    $reqUpdate = Request::create(route('plantillas.update', $plantilla), 'PUT', [
        'nombre_plantilla' => 'Updated Test',
        'contenido_html' => '<p>Updated</p>'
    ]);
    $controller->update($reqUpdate, $plantilla);
    echo "Access to plantillas.update: ALLOWED (FAILURE - should be blocked!)\n";
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    echo "Access to plantillas.update: BLOCKED (" . $e->getStatusCode() . " - " . $e->getMessage() . ")\n";
}

// 3. Now grant 'plantillas.edit' to user and re-test
$user->permissions = ['plantillas.view', 'plantillas.edit', 'dashboard.view'];
$user->save();
$freshUser = User::find($user->id);
Auth::login($freshUser);

echo "\n--- AFTER GRANTING plantillas.edit ---\n";
echo "hasPermission('plantillas.edit'): " . ($freshUser->hasPermission('plantillas.edit') ? 'YES' : 'NO') . "\n";

try {
    $view = $controller->edit($plantilla);
    echo "Access to plantillas.edit: ALLOWED (SUCCESS!)\n";
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    echo "Access to plantillas.edit: BLOCKED (" . $e->getStatusCode() . ")\n";
}
