<?php
    require __DIR__ . '/vendor/autoload.php';

    use Core\Router;
    use Features\Auth\AuthController;
    use Features\Users\UsersController;

    $router = new Router();

    $router->addRoute('POST', '/register', [AuthController::class, 'register'], 'public');
    $router->addRoute('POST', '/login', [AuthController::class, 'login'], 'public');
    $router->addRoute('POST', '/users', [UsersController::class, 'createUser'], 'admin');
    $router->addRoute('GET', '/users', [UsersController::class, 'getUsers'], 'user');
    $router->addRoute('GET', '/users/{id}', [UsersController::class, 'getUser'], 'user');
    $router->addRoute('PUT', '/users/{id}', [UsersController::class, 'updateUser'], 'admin');
    $router->addRoute('DELETE', '/users/{id}', [UsersController::class, 'deleteUser'], 'admin');

    $router->handleRequest();
