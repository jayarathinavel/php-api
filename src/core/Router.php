<?php
    namespace Core;

    class Router {
        private $routes = [];

        public function addRoute($method, $path, $handler, $middlewares = []) {
            $this->routes[$method][] = [
                'path'        => $path,
                'handler'     => $handler,
                'middlewares' => $middlewares
            ];
        }

        /**
         * Handle the incoming HTTP request.
         */
        public function handleRequest() {
            $method = $_SERVER['REQUEST_METHOD'];
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

            if (!isset($this->routes[$method])) {
                $this->sendNotFound();
                return;
            }

            foreach ($this->routes[$method] as $route) {
                $pattern = $this->convertRouteToRegex($route['path']);
                if (preg_match($pattern, $path, $matches)) {
                    // Execute all middlewares
                    foreach ($route['middlewares'] as $middleware) {
                        if (is_array($middleware)) {
                            $middlewareClass = $middleware[0];
                            $middlewareParams = $middleware[1];
                            $middlewareInstance = new $middlewareClass(...$middlewareParams);
                        } else {
                            $middlewareInstance = new $middleware();
                        }

                        if (!$middlewareInstance->handle()) {
                            // Middleware handles the response and exits
                            return;
                        }
                    }

                    $handler = $route['handler'];
                    // Remove the full match
                    array_shift($matches);

                    $controller = new $handler[0]();
                    $action = $handler[1];

                    call_user_func_array([$controller, $action], $matches);
                    return;
                }
            }

            // If no route matches
            $this->sendNotFound();
        }

        private function convertRouteToRegex($route) {
            // Escape slashes
            $route = preg_replace('/\//', '\/', $route);
            // Convert {param} to regex capture groups
            $route = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^\/]+)', $route);
            return '/^' . $route . '$/';
        }

        /**
         * Send a 404 Not Found response.
         */
        private function sendNotFound() {
            header("HTTP/1.0 404 Not Found");
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not Found']);
        }
    }
