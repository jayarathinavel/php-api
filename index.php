<?php
    require __DIR__ . '/vendor/autoload.php';

    use Core\Router;
    use Features\Auth\AuthController;
    use Features\Users\UsersController;
    use Core\AuthMiddleware;

    // Initialize the router
    $router = new Router();

    // Define Authentication Routes (Public)
    $router->addRoute('POST', '/register', [AuthController::class, 'register']);
    $router->addRoute('POST', '/login', [AuthController::class, 'login']);

    // Define User Routes (Protected by AuthMiddleware)
    $router->addRoute('POST', '/users', [UsersController::class, 'createUser'], AuthMiddleware::class);
    $router->addRoute('GET', '/users', [UsersController::class, 'getUsers'], AuthMiddleware::class);
    $router->addRoute('GET', '/users/{id}', [UsersController::class, 'getUser'], AuthMiddleware::class);
    $router->addRoute('PUT', '/users/{id}', [UsersController::class, 'updateUser'], AuthMiddleware::class);
    $router->addRoute('DELETE', '/users/{id}', [UsersController::class, 'deleteUser'], AuthMiddleware::class);

    // Handle the incoming request
    $router->handleRequest();
