<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\RobotController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ScoreboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CompetitionController;
use App\Http\Controllers\Admin\CompetitionEventController;
use App\Http\Controllers\Admin\ScoreController;
use App\Http\Controllers\Admin\RegistrationManagementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\ImagenPruebaController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BracketController;
use App\Http\Controllers\HomologacionController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\CompetitorController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminCompetitionController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\CompeticionController;
use App\Http\Controllers\Admin\EventoController;
use App\Http\Controllers\Admin\InscripcionController;
use App\Http\Controllers\Admin\SiteConfigController;
use App\Http\Controllers\Admin\GestionCompetenciasController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\CountdownConfigController;
use Filament\Facades\Filament;
use App\Filament\Judge\Pages\BracketListView;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::post('/inscripcion', [WelcomeController::class, 'store'])->name('inscripcion.store');

Route::get('/dashboard', function () {
    // Redirigir a los usuarios al panel de administración Filament
    return redirect('/admin');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas de usuario
Route::middleware('auth')->group(function () {
    // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Equipos
    Route::resource('teams', TeamController::class);
    
    // Nuevos equipos (copa 2025)
    Route::resource('equipos', \App\Http\Controllers\EquipoController::class);
    
    // Nuevos robots (copa 2025)
    Route::resource('robots', \App\Http\Controllers\RobotController::class);
    
    // Robots (anidados dentro de equipos)
    Route::resource('teams.robots', RobotController::class)->shallow();
    
    // Inscripciones
    Route::resource('registrations', RegistrationController::class);
    
    // Tabla de posiciones
    Route::get('/scoreboard', [ScoreboardController::class, 'index'])->name('scoreboard.index');
    Route::get('/scoreboard/category/{category}', [ScoreboardController::class, 'byCategory'])->name('scoreboard.category');
});

// =========== RUTAS ADMIN ===========
// Usamos middleware auth y la clase completa de AdminMiddleware

// Redirección para el dashboard de administrador
Route::get('/admin/dashboard', function () {
    return redirect('/admin');
})->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->name('admin.dashboard');

// Redirección a Filament para gestión de competencias
Route::get('/admin/gestion-competencias', function() {
    return redirect('/admin/gestion-competencia');
})->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->name('admin.gestion-competencia.index');

// Rutas para brackets/torneos
Route::get('/brackets/{llave}', function($llave) {
    return redirect("/admin/brackets/{$llave}");
})->name('brackets.show');

Route::get('/admin/brackets/{llave}', function($llave) {
    return redirect("/admin/bracket-admin/{$llave}");
})->name('admin.brackets.admin');

Route::post('/admin/brackets/{llave}/tipo', [\App\Http\Controllers\Admin\BracketManagerController::class, 'configurarTipo'])
    ->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->name('admin.brackets.configurar_tipo');

Route::post('/admin/brackets/{llave}/generar', [\App\Http\Controllers\Admin\BracketManagerController::class, 'generarBracket'])
    ->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->name('admin.brackets.generar');

Route::post('/admin/brackets/{llave}/iniciar', [\App\Http\Controllers\Admin\BracketManagerController::class, 'iniciar'])
    ->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->name('admin.brackets.iniciar');

Route::post('/admin/brackets/{llave}/finalizar', [\App\Http\Controllers\Admin\BracketManagerController::class, 'finalizar'])
    ->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->name('admin.brackets.finalizar');

Route::post('/admin/brackets/{llave}/reiniciar', [\App\Http\Controllers\Admin\BracketManagerController::class, 'reiniciar'])
    ->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->name('admin.brackets.reiniciar');

// Rutas para recursos de administración
Route::resource('admin/categories', CategoryController::class)
    ->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->names([
        'index' => 'admin.categories.index',
        'create' => 'admin.categories.create',
        'store' => 'admin.categories.store',
        'show' => 'admin.categories.show',
        'edit' => 'admin.categories.edit',
        'update' => 'admin.categories.update',
        'destroy' => 'admin.categories.destroy',
    ]);

Route::resource('admin/competitions', CompetitionController::class)
    ->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->names([
        'index' => 'admin.competitions.index',
        'create' => 'admin.competitions.create',
        'store' => 'admin.competitions.store',
        'show' => 'admin.competitions.show',
        'edit' => 'admin.competitions.edit',
        'update' => 'admin.competitions.update',
        'destroy' => 'admin.competitions.destroy',
    ]);

Route::resource('admin/events', CompetitionEventController::class)
    ->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->names([
        'index' => 'admin.events.index',
        'create' => 'admin.events.create',
        'store' => 'admin.events.store',
        'show' => 'admin.events.show',
        'edit' => 'admin.events.edit',
        'update' => 'admin.events.update',
        'destroy' => 'admin.events.destroy',
    ]);

Route::resource('admin/registrations', RegistrationManagementController::class)
    ->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->names([
        'index' => 'admin.registrations.index',
        'create' => 'admin.registrations.create',
        'store' => 'admin.registrations.store',
        'show' => 'admin.registrations.show',
        'edit' => 'admin.registrations.edit',
        'update' => 'admin.registrations.update',
        'destroy' => 'admin.registrations.destroy',
    ]);

// Acciones para inscripciones
Route::post('/admin/registrations/{registration}/approve', [RegistrationManagementController::class, 'approve'])
    ->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->name('admin.registrations.approve');

Route::post('/admin/registrations/{registration}/reject', [RegistrationManagementController::class, 'reject'])
    ->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->name('admin.registrations.reject');

// Recursos para puntajes
Route::resource('admin/scores', ScoreController::class)
    ->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->names([
        'index' => 'admin.scores.index',
        'create' => 'admin.scores.create',
        'store' => 'admin.scores.store',
        'show' => 'admin.scores.show',
        'edit' => 'admin.scores.edit',
        'update' => 'admin.scores.update',
        'destroy' => 'admin.scores.destroy',
    ]);

// Comentado para evitar conflicto con resource
// Route::get('/admin/scores/create/{registration}', [ScoreController::class, 'createForRegistration'])
//     ->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
//     ->name('admin.scores.create');

// Crear llave para una categoría
Route::post('/admin/llave/crear/{categoriaEvento}', function($categoriaEventoId) {
    $categoriaEvento = \App\Models\CategoriaEvento::findOrFail($categoriaEventoId);
    
    // Verificar si ya existe una llave
    if($categoriaEvento->llave) {
        return redirect("/admin/bracket-admin/{$categoriaEvento->llave->id}");
    }
    
    // Crear nueva llave
    $llave = new \App\Models\Llave([
        'categoria_evento_id' => $categoriaEvento->id,
        'tipo_fixture' => \App\Models\Llave::TIPO_ELIMINACION_DIRECTA,
        'estructura' => [
            'total_equipos' => 0,
            'total_rondas' => 0,
            'tamano_llave' => 0,
            'total_enfrentamientos' => 0,
        ],
        'finalizado' => false,
        'estado_torneo' => \App\Models\Llave::ESTADO_PENDIENTE,
    ]);
    
    $llave->save();
    
    return redirect("/admin/bracket-admin/{$llave->id}")
        ->with('success', 'Llave creada correctamente');
})->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->name('admin.llave.crear');

// Gestión de competencia (ruta original)
Route::get('/admin/gestion-competencia', [\App\Http\Controllers\Admin\GestionCompetenciaController::class, 'index'])
    ->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->name('admin.gestion-competencia.index');

Route::post('/admin/gestion-competencia/{categoriaEvento}/cambiar-estado', [\App\Http\Controllers\Admin\GestionCompetenciaController::class, 'cambiarEstado'])
    ->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->name('admin.gestion-competencia.cambiar-estado');

Route::post('/admin/gestion-competencia/{categoriaEvento}/crear-llave', [\App\Http\Controllers\Admin\GestionCompetenciaController::class, 'crearLlave'])
    ->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->name('admin.gestion-competencia.crear-llave');

// Rutas para homologaciones
Route::put('/admin/homologaciones/{homologacion}', [HomologacionController::class, 'update'])
    ->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->name('admin.homologaciones.update');

// Rutas de configuración del sitio
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/site-config', [App\Http\Controllers\Admin\SiteConfigController::class, 'index'])->name('admin.site-config.index');
    Route::put('/admin/site-config', [App\Http\Controllers\Admin\SiteConfigController::class, 'update'])->name('admin.site-config.update');
});

// Countdown config routes
Route::get('/admin/countdown-config', [CountdownConfigController::class, 'index'])->name('admin.countdown-config.index');
Route::put('/admin/countdown-config', [CountdownConfigController::class, 'update'])->name('admin.countdown-config.update');

// Ruta específica para BracketListView
// Route::get('/judge/llaves-competencia', function() {
//     return Filament::getTenantPanel('judge')->renderPage(
//         \App\Filament\Judge\Pages\BracketListView::class
//     );
// })->middleware(['auth', \App\Http\Middleware\JudgeMiddleware::class])
// ->name('filament.judge.pages.llaves-competencia');

// =========== RUTAS PÚBLICAS ===========

// Ruta para probar la carga de imágenes
Route::get('/prueba-imagen', [ImagenPruebaController::class, 'test']);

// Ruta para servir imágenes directamente (sin CORS)
Route::get('/get-image/{path}', function ($path) {
    $path = 'public/' . $path;
    if (Storage::exists($path)) {
        $file = Storage::get($path);
        $type = Storage::mimeType($path);
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }
    Log::error('Imagen no encontrada: ' . $path);
    return response()->json(['error' => 'Imagen no encontrada', 'path' => $path], 404);
})->where('path', '.*');

// Rutas para brackets/torneos
Route::get('/api/brackets/{llave}', [BracketController::class, 'datos'])->name('api.brackets.datos');
Route::post('/api/enfrentamientos/{enfrentamiento}/resultado', [BracketController::class, 'registrarResultado'])->name('api.enfrentamientos.resultado');

// Rutas para el panel de jueces
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    \App\Http\Middleware\JudgeMiddleware::class,
])->group(function () {
    
    // Ruta para el listado de brackets
    Route::get('/judge/brackets', function() {
        return redirect()->route('filament.judge.pages.llaves-competencia');
    });
    
    // Ruta para el dashboard del panel de jueces
    Route::get('/judge/dashboard', function() {
        return view('filament.judge.pages.dashboard', [
            'title' => 'Dashboard',
        ]);
    })->name('filament.judge.pages.dashboard');
    
    // Ruta para cargar la página de listado de brackets a través de Filament
    // Route::get('/judge/llaves-competencia', [\App\Http\Controllers\Judge\LlavesCompetenciaController::class, 'index'])
    //     ->name('filament.judge.pages.llaves-competencia');
    
    // Simplificamos la ruta para evitar problemas de parámetros
    Route::get('/judge/bracket-admin/{id}', function($id) {
        return redirect()->route('filament.judge.pages.dashboard')->with([
            'llave_id' => $id
        ])->with('bracketRedirect', $id);
    });
    
    // Ruta principal de Bracket Admin sin parámetros para Filament
    Route::get('/judge/bracket-admin', function() {
        $id = session('bracketRedirect');
        if (!$id) {
            return redirect()->route('filament.judge.pages.llaves-competencia');
        }
        return \Filament\Facades\Filament::renderPage('filament.judge.pages.bracket-admin');
    })->name('filament.judge.pages.bracket-admin');
    
    // Restauramos la ruta de vista admin para mantener compatibilidad
    Route::get('/judge/bracket-admin-view/{id}', [\App\Http\Controllers\Judge\BracketViewerController::class, 'adminView']);
    
    // RUTA PARA LA VISTA PÚBLICA DE BRACKETS
    Route::get('/judge/bracket-public-view/{id}', function($id) {
        return app(\App\Http\Controllers\Judge\BracketViewerController::class)->publicView($id);
    })->name('filament.judge.pages.bracket-public-view');
    
    // Rutas estándar de brackets (mantenemos las rutas originales para otras funcionalidades)
    Route::get('/judge/brackets/{id}/admin', [\App\Http\Controllers\Judge\BracketAdminController::class, 'show'])
        ->name('judge.brackets.admin');
        
    Route::post('/judge/brackets/{id}/cambiar-estado', [\App\Http\Controllers\Judge\BracketAdminController::class, 'cambiarEstado'])
        ->name('judge.brackets.cambiar-estado');
        
    Route::post('/judge/brackets/{id}/guardar-resultado/{enfrentamiento}', [\App\Http\Controllers\Judge\BracketAdminController::class, 'guardarResultado'])
        ->name('judge.brackets.guardar-resultado');
        
    Route::post('/judge/brackets/{id}/reiniciar-resultado/{enfrentamiento}', [\App\Http\Controllers\Judge\BracketAdminController::class, 'reiniciarResultado'])
        ->name('judge.brackets.reiniciar-resultado');
        
    Route::get('/judge/brackets/{id}/filtrar', [\App\Http\Controllers\Judge\BracketAdminController::class, 'filtrar'])
        ->name('judge.brackets.filtrar');
        
    Route::get('/judge/brackets/{id}/public', [\App\Http\Controllers\Judge\BracketPublicController::class, 'show'])
        ->name('judge.brackets.public');
});

// Ruta de redirección para las homologaciones antiguas
Route::middleware(['auth'])->get('/admin/homologaciones/{categoriaEvento}', function($categoriaEvento) {
    // Redireccionar a la página de Filament
    return redirect()->route('filament.admin.pages.gestion-homologaciones', ['id' => $categoriaEvento]);
});

// Rutas del panel de competidores - Redirección a Filament
Route::middleware(['auth'])->prefix('competitor')->group(function () {
    // Redirigir al panel Filament
    Route::get('/', function () {
        return redirect()->route('filament.competitor.pages.dashboard');
    });
});

// Rutas para el Panel de Jueces
Route::name('judge.')->prefix('judge')->middleware(['auth', 'judge'])->group(function () {
    // Bracket routes
    Route::get('/brackets/public/{id}', [App\Http\Controllers\Judge\BracketController::class, 'viewPublic'])->name('brackets.public');
    
    // Administración de brackets - estas operaciones se harán a través de Filament
    // pero mantenemos estas rutas para compatibilidad con el resto del sistema
    Route::get('/brackets', function() {
        return redirect()->route('filament.judge.pages.bracket-list-view');
    })->name('brackets');
});

// Rutas del instalador
Route::group(['prefix' => 'install', 'middleware' => 'web', 'as' => 'installer.'], function () {
    Route::get('/', [App\Http\Controllers\InstallerController::class, 'welcome'])->name('welcome');
    Route::get('/requirements', [App\Http\Controllers\InstallerController::class, 'requirements'])->name('requirements');
    Route::get('/database', [App\Http\Controllers\InstallerController::class, 'database'])->name('database');
    Route::post('/database', [App\Http\Controllers\InstallerController::class, 'databaseSave'])->name('database.save');
    Route::get('/app', [App\Http\Controllers\InstallerController::class, 'app'])->name('app');
    Route::post('/app', [App\Http\Controllers\InstallerController::class, 'appSave'])->name('app.save');
    Route::get('/finished', [App\Http\Controllers\InstallerController::class, 'finished'])->name('finished');
});

// Middleware para verificar si la aplicación está instalada
Route::middleware(['check.installed'])->group(function () {
    // ... existing code ...
});

require __DIR__.'/auth.php';
