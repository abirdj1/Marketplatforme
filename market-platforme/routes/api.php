<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;

use App\Models\Report;
use App\Models\AuditLog;
use App\Models\SearchHistory;
use App\Models\ReportView;
use App\Models\DownloadHistory;

use App\Services\AccessService;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail']);
    });

    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed'])
        ->name('verification.verify');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function (Request $request) {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'admin.dashboard_view',
            'entity_type' => 'admin',
            'entity_id' => null,
            'metadata' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        ]);

        return response()->json([
            'message' => 'Admin dashboard',
        ]);
    });

    Route::get('/users', function () {
        return response()->json([
            'message' => 'Manage users',
        ]);
    });

    Route::post('/reports', function (Request $request) {
        return response()->json([
            'message' => 'Create report',
        ]);
    });

    Route::post('/plans', function (Request $request) {
        return response()->json([
            'message' => 'Create plan',
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| Search Route + Search History
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->get('/search', function (Request $request) {
    SearchHistory::create([
        'user_id' => $request->user()->id,
        'query' => $request->query('q'),
        'filters' => [
            'sector' => $request->query('sector'),
            'country' => $request->query('country'),
            'period' => $request->query('period'),
        ],
    ]);

    return response()->json([
        'message' => 'Recherche enregistrée',
        'query' => $request->query('q'),
        'filters' => [
            'sector' => $request->query('sector'),
            'country' => $request->query('country'),
            'period' => $request->query('period'),
        ],
    ]);
});

/*
|--------------------------------------------------------------------------
| Report Preview
|--------------------------------------------------------------------------
*/

Route::get('/reports/{report}/preview', function (Report $report) {
    return response()->json([
        'message' => 'Aperçu gratuit',
        'report' => $report->only(['id', 'title', 'summary']),
        'preview_pages' => [1, 2, 3],
    ]);
});

/*
|--------------------------------------------------------------------------
| Report Access + View History + Audit
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->get('/reports/{report}/access', function (
    Request $request,
    Report $report,
    AccessService $accessService
) {
    $user = $request->user();

    if (! $accessService->canViewReport($user, $report)) {
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'report.view_denied',
            'entity_type' => 'report',
            'entity_id' => $report->id,
            'metadata' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'reason' => 'no_access',
                'access_type' => 'preview_only',
            ],
        ]);

        return response()->json([
            'message' => 'Accès refusé',
            'access' => 'preview_only',
        ], 403);
    }

    ReportView::create([
        'user_id' => $user->id,
        'report_id' => $report->id,
        'viewed_at' => now(),
    ]);

    AuditLog::create([
        'user_id' => $user->id,
        'action' => 'report.view',
        'entity_type' => 'report',
        'entity_id' => $report->id,
        'metadata' => [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'access_type' => 'full',
        ],
    ]);

    return response()->json([
        'message' => 'Accès complet autorisé',
        'access' => 'full',
    ]);
});

/*
|--------------------------------------------------------------------------
| Report Download + Download History + Audit
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->get('/reports/{report}/download', function (
    Request $request,
    Report $report,
    AccessService $accessService
) {
    $user = $request->user();

    if (! $accessService->canDownloadReport($user, $report)) {
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'report.download_denied',
            'entity_type' => 'report',
            'entity_id' => $report->id,
            'metadata' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'reason' => 'plan_not_allowed',
            ],
        ]);

        return response()->json([
            'message' => 'Téléchargement non autorisé pour votre plan.',
        ], 403);
    }

    DownloadHistory::create([
        'user_id' => $user->id,
        'report_id' => $report->id,
        'downloaded_at' => now(),
    ]);

    AuditLog::create([
        'user_id' => $user->id,
        'action' => 'report.download',
        'entity_type' => 'report',
        'entity_id' => $report->id,
        'metadata' => [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ],
    ]);

    return response()->json([
        'message' => 'Téléchargement autorisé.',
    ]);
});

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/me', [ProfileController::class, 'show']);
    Route::patch('/me', [ProfileController::class, 'update']);

    Route::get('/me/preferences', [ProfileController::class, 'preferences']);
    Route::patch('/me/preferences', [ProfileController::class, 'updatePreferences']);

    Route::get('/me/history/searches', [ProfileController::class, 'searchHistory']);
    Route::get('/me/history/views', [ProfileController::class, 'viewHistory']);
    Route::get('/me/history/downloads', [ProfileController::class, 'downloadHistory']);
});
