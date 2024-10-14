<?php
    require __DIR__ . '/vendor/autoload.php';

    use Core\Router;
    use Features\Users\UsersController;

    $router = new Router();

    // Define routes
    $router->addRoute('POST', '/users', [UsersController::class, 'createUser']);
    $router->addRoute('GET', '/users', [UsersController::class, 'getUsers']);
    $router->addRoute('GET', '/users/(\d+)', [UsersController::class, 'getUser']);
    $router->addRoute('PUT', '/users/(\d+)', [UsersController::class, 'updateUser']);
    $router->addRoute('DELETE', '/users/(\d+)', [UsersController::class, 'deleteUser']);

    // Handle the request
    $router->handleRequest();