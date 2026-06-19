<?php
    namespace Features\WorkTracker;

    use Core\Router;
    use Features\WorkTracker\TaskManagerController;
    use Features\WorkTracker\WorkLogController;
    use Features\WorkTracker\CommentController;

    class WorkTrackerRouter {
        public static function registerRoutes(Router $router) {
            // Task Manager endpoints
            $router->addRoute('POST', '/work-tracker/task-manager', [TaskManagerController::class, 'create'], 'user');
            $router->addRoute('GET', '/work-tracker/task-manager', [TaskManagerController::class, 'getAll'], 'user');
            $router->addRoute('GET', '/work-tracker/task-manager/{id}', [TaskManagerController::class, 'getById'], 'user');
            $router->addRoute('PATCH', '/work-tracker/task-manager/{id}', [TaskManagerController::class, 'update'], 'user');
            $router->addRoute('DELETE', '/work-tracker/task-manager/{id}', [TaskManagerController::class, 'delete'], 'user');

            // Task Comments endpoints
            $router->addRoute('POST', '/work-tracker/task-manager/{taskId}/comments', [CommentController::class, 'create'], 'user');
            $router->addRoute('GET', '/work-tracker/task-manager/{taskId}/comments', [CommentController::class, 'getAll'], 'user');
            $router->addRoute('GET', '/work-tracker/task-manager/{taskId}/comments/count', [CommentController::class, 'getCount'], 'user');
            $router->addRoute('PATCH', '/work-tracker/task-manager/{taskId}/comments/{id}', [CommentController::class, 'update'], 'user');
            $router->addRoute('DELETE', '/work-tracker/task-manager/{taskId}/comments/{id}', [CommentController::class, 'delete'], 'user');

            // Work Log endpoints
            $router->addRoute('POST', '/work-tracker/work-log', [WorkLogController::class, 'create'], 'user');
            $router->addRoute('GET', '/work-tracker/work-log', [WorkLogController::class, 'getAll'], 'user');
            $router->addRoute('GET', '/work-tracker/work-log/{id}', [WorkLogController::class, 'getById'], 'user');
            $router->addRoute('PATCH', '/work-tracker/work-log/{id}', [WorkLogController::class, 'update'], 'user');
            $router->addRoute('DELETE', '/work-tracker/work-log/{id}', [WorkLogController::class, 'delete'], 'user');
        }
    }
