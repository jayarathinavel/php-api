<?php
    namespace Core;

    class Router {
        private $routes = [];

        public function addRoute($method, $path, $handler) {
            $this->routes[$method][] = [
                'path' => $path,
                'handler' => $handler
            ];
        }

        public function handleRequest() {
            $method = $_SERVER['REQUEST_METHOD'];
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

            foreach ($this->routes[$method] as $route) {
                $pattern = $this->convertRouteToRegex($route['path']);
                if (preg_match($pattern, $path, $matches)) {
                    $handler = $route['handler'];
                    $params = array_slice($matches, 1);
                    
                    $controller = new $handler[0]();
                    $action = $handler[1];
                    
                    call_user_func_array([$controller, $action], $params);
                    return;
                }
            }

            // If no route matches
            header("HTTP/1.0 404 Not Found");
            echo json_encode(['error' => 'Not Found']);
        }

        private function convertRouteToRegex($route) {
            return '#^' . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route) . '$#';
        }
    }
