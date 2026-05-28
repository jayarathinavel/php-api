<?php
    namespace Core;

    class Router {
        private $routes = [];

        public function addRoute($method, $path, $handler, $auth) {
            $this->routes[$method][] = [
                'path'        => $path,
                'handler'     => $handler,
                'middlewares' => $this->getMiddlewares($auth),
                'specificity' => $this->computeSpecificity($path),
            ];
        }
        
        private function getMiddlewares($auth){
            return ($auth != 'public')  ? [AuthMiddleware::class, [RoleMiddleware::class, [$auth]]] : [];
        }

        public function handleRequest() {
            $method = $_SERVER['REQUEST_METHOD'];
            $path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

            if (!isset($this->routes[$method])) {
                $this->sendNotFound();
                return;
            }

            // Sort routes by specificity (more static segments first), then by path length
            usort($this->routes[$method], function($a, $b) {
                if ($a['specificity'] === $b['specificity']) {
                    return strlen($b['path']) - strlen($a['path']);
                }
                return $b['specificity'] - $a['specificity'];
            });

            foreach ($this->routes[$method] as $route) {
                $pattern = $this->convertRouteToRegex($route['path']);
                if (preg_match($pattern, $path, $matches)) {
                    foreach ($route['middlewares'] as $middleware) {
                        if (is_array($middleware)) {
                            $middlewareClass = $middleware[0];
                            $middlewareParams = $middleware[1];
                            $middlewareInstance = new $middlewareClass(...$middlewareParams);
                        } else {
                            $middlewareInstance = new $middleware();
                        }
                        if (!$middlewareInstance->handle()) {
                            return;
                        }
                    }

                    $handler = $route['handler'];
                    array_shift($matches);

                    $controller = new $handler[0]();
                    $action = $handler[1];

                    call_user_func_array([$controller, $action], $matches);
                    return;
                }
            }

            $this->sendNotFound();
        }

        private function convertRouteToRegex($route) {
            $route = preg_replace('/\//', '\/', $route);
            $route = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^\/]+)', $route);
            return '/^' . $route . '$/';
        }

        private function computeSpecificity($path) {
            $segments = array_values(array_filter(explode('/', trim($path, '/')),
                fn($s) => $s !== ''
            ));
            $score = 0;
            foreach ($segments as $seg) {
                // static segment (no parameter) counts as 1
                if (strpos($seg, '{') === false && strpos($seg, '}') === false) {
                    $score += 1;
                }
            }
            return $score;
        }

        private function sendNotFound() {
            header("HTTP/1.0 404 Not Found");
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not Found']);
        }
    }
