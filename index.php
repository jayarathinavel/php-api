<?php
    // Suppress PHP warnings/notices/errors from leaking into JSON responses
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);

    // HTTPS Enforcement for production
    if (getenv('APP_ENV') === 'production') {
        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            exit;
        }
    }

    // Catch fatal errors and return a clean JSON error instead of HTML
    register_shutdown_function(function () {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            if (!headers_sent()) {
                header('Content-Type: application/json');
                http_response_code(500);
            }
            // Flush any partial output that may have been written before the fatal
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Log detailed error server-side
            error_log(sprintf(
                "Fatal Error: %s in %s on line %d",
                $error['message'],
                $error['file'],
                $error['line']
            ));
            
            // Return generic error to client (no details in production)
            $response = ['error' => 'An internal server error occurred'];
            if (getenv('APP_ENV') !== 'production') {
                $response['debug'] = [
                    'message' => $error['message'],
                    'file' => $error['file'],
                    'line' => $error['line']
                ];
            }
            echo json_encode($response);
        }
    });

    // Buffer output so we can discard partial content on fatal errors
    ob_start();

    date_default_timezone_set('Asia/Kolkata');

    // Security Headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Content-Security-Policy: default-src \'self\'');
    if (getenv('APP_ENV') === 'production') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    // CORS Headers - Secure configuration
    $allowedOrigins = getenv('ALLOWED_ORIGINS') ?: '*';
    
    if ($allowedOrigins === '*') {
        // Allow all origins (not recommended for production)
        header('Access-Control-Allow-Origin: *');
    } else {
        // Validate origin against whitelist
        $allowedOriginsList = array_map('trim', explode(',', $allowedOrigins));
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array($origin, $allowedOriginsList, true)) {
            header("Access-Control-Allow-Origin: $origin");
            header('Access-Control-Allow-Credentials: true');
        }
    }
    
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-App-Id');
    header('Access-Control-Max-Age: 3600');

    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    require __DIR__ . '/vendor/autoload.php';

    use Core\Router;
    use Features\Auth\AuthController;
    use Features\Crud\CrudController;
    use Features\Users\UsersController;
    use Features\WorkTracker\WorkTrackerRouter;
    use Features\Ytdb\YtdbRouter;

    // Handle base path for subdirectory deployments (e.g., serv00)
    $basePath = getenv('APP_BASE_PATH') ?: '';
    if ($basePath) {
        $basePath = '/' . trim($basePath, '/');
    }

    $router = new Router($basePath);

    // Register existing routes
    $router->addRoute('GET', '/', [AuthController::class, 'apiCheck'], 'public');
    $router->addRoute('POST', '/register', [AuthController::class, 'register'], 'public');
    $router->addRoute('POST', '/login', [AuthController::class, 'login'], 'public');
    $router->addRoute('POST', '/users', [UsersController::class, 'createUser'], 'admin');
    $router->addRoute('GET', '/users', [UsersController::class, 'getUsers'], 'user');
    $router->addRoute('GET', '/users/{id}', [UsersController::class, 'getUser'], 'user');
    $router->addRoute('PUT', '/users/{id}', [UsersController::class, 'updateUser'], 'admin');
    $router->addRoute('DELETE', '/users/{id}', [UsersController::class, 'deleteUser'], 'admin');

    $router->addRoute('GET', '/{appId}/{featureName}/all', [CrudController::class, 'list'], 'user');
    $router->addRoute('GET', '/{appId}/{featureName}/{id}', [CrudController::class, 'get'], 'user');
    $router->addRoute('POST', '/{appId}/{featureName}', [CrudController::class, 'create'], 'user');
    $router->addRoute('POST', '/{appId}/{featureName}/all', [CrudController::class, 'createMany'], 'user');
    $router->addRoute('PUT', '/{appId}/{featureName}/{id}', [CrudController::class, 'update'], 'user');
    $router->addRoute('PATCH', '/{appId}/{featureName}/{id}', [CrudController::class, 'update'], 'user');
    $router->addRoute('DELETE', '/{appId}/{featureName}/{id}', [CrudController::class, 'delete'], 'user');

    // Register work_tracker app routes
    WorkTrackerRouter::registerRoutes($router);

    // Register ytdb app routes
    YtdbRouter::registerRoutes($router);

    $router->handleRequest();
