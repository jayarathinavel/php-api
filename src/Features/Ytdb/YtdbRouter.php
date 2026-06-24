<?php

    namespace Features\Ytdb;

    use Core\Router;
    use Features\Ytdb\Lists\YtdbListsController;
    use Features\Ytdb\Videos\YtdbVideosController;

    class YtdbRouter {
        public static function registerRoutes(Router $router) {
            // Lists routes
            $router->addRoute('POST', '/ytdb/lists', [YtdbListsController::class, 'createList'], 'user');
            $router->addRoute('GET', '/ytdb/lists', [YtdbListsController::class, 'getLists'], 'user');
            $router->addRoute('GET', '/ytdb/lists/all', [YtdbListsController::class, 'getAllLists'], 'user');
            $router->addRoute('GET', '/ytdb/lists/{id}', [YtdbListsController::class, 'getListById'], 'user');
            $router->addRoute('PUT', '/ytdb/lists/{id}', [YtdbListsController::class, 'updateList'], 'user');
            $router->addRoute('DELETE', '/ytdb/lists/{id}', [YtdbListsController::class, 'deleteList'], 'user');
    
            // Videos routes
            $router->addRoute('POST', '/ytdb/videos', [YtdbVideosController::class, 'createVideo'], 'user');
            $router->addRoute('GET', '/ytdb/my-videos', [YtdbVideosController::class, 'getMyVideos'], 'user');
            $router->addRoute('GET', '/ytdb/all-videos', [YtdbVideosController::class, 'getAllVideos'], 'user');
            $router->addRoute('GET', '/ytdb/list-videos/{listId}', [YtdbVideosController::class, 'getAllVideosByListId'], 'user');
            $router->addRoute('GET', '/ytdb/video/{id}', [YtdbVideosController::class, 'getVideoById'], 'user');
            $router->addRoute('PUT', '/ytdb/videos/{id}', [YtdbVideosController::class, 'updateVideo'], 'user');
            $router->addRoute('DELETE', '/ytdb/videos/{id}', [YtdbVideosController::class, 'deleteVideo'], 'user');

            // Community users route
            $router->addRoute('GET', '/ytdb/community-users', [YtdbListsController::class, 'getCommunityUsers'], 'user');
        }
    }
