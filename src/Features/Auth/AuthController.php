<?php
    namespace Features\Auth;

    use Core\BaseController;
    use Core\ApiException;

    class AuthController extends BaseController {
        private $authService;

        public function __construct() {
            parent::__construct();
            $this->authService = new AuthService();
        }

        public function register() {
            try {
                $data = $this->getJsonBody(true);
                $appId = $this->getAppId();
                $result = $this->authService->register($data, $appId);

                header('Content-Type: application/json');
                if ($result['success']) {
                    http_response_code(201);
                } else {
                    http_response_code(400);
                }
                echo json_encode($result);
                
            } catch (ApiException $e) {
                $this->handleException($e);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }

        public function login() {
            try {
                $data = $this->getJsonBody(true);
                $appId = $this->getAppId();
                $result = $this->authService->login($data, $appId);

                header('Content-Type: application/json');
                if ($result['success']) {
                    http_response_code(200);
                } else {
                    http_response_code(401);
                }
                echo json_encode($result);
                
            } catch (ApiException $e) {
                $this->handleException($e);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }
        
        public function apiCheck() {
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode(['message' => 'API is working']);
        }
    }

// Made with Bob
