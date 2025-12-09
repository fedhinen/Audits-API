<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Supervisor\DashboardController;
use App\Http\Controllers\Web\Supervisor\LineWebController;
use App\Http\Controllers\Web\Supervisor\ToolWebController;
use App\Http\Controllers\Web\Supervisor\EmployeeWebController;
use App\Http\Controllers\Web\Supervisor\AssignmentWebController;
use App\Http\Controllers\Web\Supervisor\AuditWebController;
use App\Http\Controllers\Web\Supervisor\LoginWebController;
use App\Http\Controllers\Web\Supervisor\TechnicianWebController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Health check endpoint - no requiere sesión ni nada
Route::get('/health', function () {
    try {
        // Verificar conexión a BD
        \DB::connection()->getPdo();
        $dbStatus = 'connected';
    } catch (\Exception $e) {
        $dbStatus = 'error: ' . $e->getMessage();
    }
    
    return response()->json([
        'status' => 'ok',
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'environment' => config('app.env'),
        'debug' => config('app.debug'),
        'database' => $dbStatus,
        'storage_writable' => is_writable(storage_path()),
        'cache_writable' => is_writable(storage_path('framework/cache')),
        'session_driver' => config('session.driver'),
        'cache_driver' => config('cache.default'),
    ]);
});

Route::get('/', function () {
    return view('welcome');
});


Route::get('/login', [LoginWebController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginWebController::class, 'login']);
Route::post('/logout', [LoginWebController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');


Route::get('/lines', [LineWebController::class, 'index'])->name('lines.index');
Route::get('/lines/create', [LineWebController::class, 'create'])->name('lines.create');
Route::post('/lines', [LineWebController::class, 'store'])->name('lines.store');
Route::get('/lines/{line}/edit', [LineWebController::class, 'edit'])->name('lines.edit');
Route::put('/lines/{line}', [LineWebController::class, 'update'])->name('lines.update');
Route::delete('/lines/{line}', [LineWebController::class, 'destroy'])->name('lines.destroy');


Route::get('/tools', [ToolWebController::class, 'index'])->name('tools.index');
Route::get('/tools/create', [ToolWebController::class, 'create'])->name('tools.create');
Route::post('/tools', [ToolWebController::class, 'store'])->name('tools.store');
Route::get('/tools/{tool}/edit', [ToolWebController::class, 'edit'])->name('tools.edit');
Route::put('/tools/{tool}', [ToolWebController::class, 'update'])->name('tools.update');
Route::delete('/tools/{tool}', [ToolWebController::class, 'destroy'])->name('tools.destroy');


Route::get('/technicians', [TechnicianWebController::class, 'index'])->name('technicians.index');
Route::get('/technicians/create', [TechnicianWebController::class, 'create'])->name('technicians.create');
Route::post('/technicians', [TechnicianWebController::class, 'store'])->name('technicians.store');
Route::get('/technicians/{technician}/edit', [TechnicianWebController::class, 'edit'])->name('technicians.edit');
Route::put('/technicians/{technician}', [TechnicianWebController::class, 'update'])->name('technicians.update');
Route::delete('/technicians/{technician}', [TechnicianWebController::class, 'destroy'])->name('technicians.destroy');


Route::get('/assignments', [AssignmentWebController::class, 'index'])->name('assignments.index');
Route::get('/assignments/create', [AssignmentWebController::class, 'create'])->name('assignments.create');
Route::post('/assignments', [AssignmentWebController::class, 'store'])->name('assignments.store');
Route::get('/assignments/{assignment}/edit', [AssignmentWebController::class, 'edit'])->name('assignments.edit');
Route::put('/assignments/{assignment}', [AssignmentWebController::class, 'update'])->name('assignments.update');
Route::delete('/assignments/{assignment}', [AssignmentWebController::class, 'destroy'])->name('assignments.destroy');


Route::get('/audits', [AuditWebController::class, 'index'])->name('audits.index');
Route::get('/audits/{audit}', [AuditWebController::class, 'show'])->name('audits.show');
Route::post('/audits/{audit}/review', [AuditWebController::class, 'review'])->name('audits.review');
Route::post('/audits/{audit}/reopen', [AuditWebController::class, 'reopen'])
    ->name('audits.reopen');   // ✔ AQUI LO CORRECTO

});