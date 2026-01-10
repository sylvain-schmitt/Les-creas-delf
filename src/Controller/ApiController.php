<?php

namespace App\Controller;

use Ogan\Controller\AbstractController;
use Ogan\Router\Attributes\Route;
use Ogan\Http\Response;
use Ogan\Database\Database;

/**
 * API Controller pour les endpoints système
 */
class ApiController extends AbstractController
{
    #[Route(path: '/api/health', methods: ['GET'], name: 'api.health')]
    public function health(): Response
    {
        $checks = [
            'status' => 'ok',
            'timestamp' => date('c'),
            'checks' => []
        ];

        // Check 1: Application running
        $checks['checks']['app'] = [
            'status' => 'ok',
            'message' => 'Application is running'
        ];

        // Check 2: Database connection
        try {
            $pdo = Database::getConnection();
            $pdo->query('SELECT 1');
            $checks['checks']['database'] = [
                'status' => 'ok',
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            $checks['checks']['database'] = [
                'status' => 'error',
                'message' => 'Database connection failed'
            ];
            $checks['status'] = 'degraded';
        }

        // Check 3: Cache directory writable
        $cacheDir = dirname(__DIR__, 2) . '/var/cache';
        if (is_writable($cacheDir)) {
            $checks['checks']['cache'] = [
                'status' => 'ok',
                'message' => 'Cache directory is writable'
            ];
        } else {
            $checks['checks']['cache'] = [
                'status' => 'warning',
                'message' => 'Cache directory is not writable'
            ];
            if ($checks['status'] === 'ok') {
                $checks['status'] = 'warning';
            }
        }

        // Check 4: Uploads directory writable
        $uploadsDir = dirname(__DIR__, 2) . '/public/assets/uploads';
        if (is_writable($uploadsDir)) {
            $checks['checks']['uploads'] = [
                'status' => 'ok',
                'message' => 'Uploads directory is writable'
            ];
        } else {
            $checks['checks']['uploads'] = [
                'status' => 'warning',
                'message' => 'Uploads directory is not writable'
            ];
            if ($checks['status'] === 'ok') {
                $checks['status'] = 'warning';
            }
        }

        // HTTP status code based on overall status
        $httpStatus = match ($checks['status']) {
            'ok' => 200,
            'warning' => 200,
            'degraded' => 503,
            default => 500
        };

        $response = new Response(json_encode($checks, JSON_PRETTY_PRINT), $httpStatus);
        $response->setHeader('Content-Type', 'application/json; charset=utf-8');
        $response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');

        return $response;
    }
}
