<?php
    require __DIR__ . '/vendor/autoload.php';

    use Core\Router;
    use Features\Admin\AdminQueryController;
    use Features\Auth\AuthController;
    use Features\Crud\CrudController;
    use Features\Users\UsersController;

    $router = new Router();

    $router->addRoute('POST', '/register', [AuthController::class, 'register'], 'public');
    $router->addRoute('POST', '/login', [AuthController::class, 'login'], 'public');
    $router->addRoute('POST', '/users', [UsersController::class, 'createUser'], 'admin');
    $router->addRoute('GET', '/users', [UsersController::class, 'getUsers'], 'user');
    $router->addRoute('GET', '/users/{id}', [UsersController::class, 'getUser'], 'user');
    $router->addRoute('PUT', '/users/{id}', [UsersController::class, 'updateUser'], 'admin');
    $router->addRoute('DELETE', '/users/{id}', [UsersController::class, 'deleteUser'], 'admin');
    $router->addRoute('POST', '/admin/query', [AdminQueryController::class, 'execute'], 'admin');

    $router->addRoute('GET', '/{appId}/{featureName}/all', [CrudController::class, 'list'], 'user');
    $router->addRoute('GET', '/{appId}/{featureName}/{id}', [CrudController::class, 'get'], 'user');
    $router->addRoute('POST', '/{appId}/{featureName}', [CrudController::class, 'create'], 'user');
    $router->addRoute('POST', '/{appId}/{featureName}/all', [CrudController::class, 'createMany'], 'user');
    $router->addRoute('PUT', '/{appId}/{featureName}/{id}', [CrudController::class, 'update'], 'user');
    $router->addRoute('PATCH', '/{appId}/{featureName}/{id}', [CrudController::class, 'update'], 'user');
    $router->addRoute('DELETE', '/{appId}/{featureName}/{id}', [CrudController::class, 'delete'], 'user');

    $router->handleRequest();
