<?php
    namespace Features\Users;

    use Core\BaseController;
    use Core\ApiException;

    class UsersController extends BaseController {
        private $usersService;

        public function __construct() {
            parent::__construct();
            $this->usersService = new UsersService();
        }

        public function createUser() {
            try {
                $data = $this->getJsonBody(true);
                $appId = $this->getAppId();
                $result = $this->usersService->createUser($data, $appId);

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

        public function getUsers() {
            try {
                $appId = $this->getAppId();
                $users = $this->usersService->getUsers($appId);

                header('Content-Type: application/json');
                echo json_encode($users);
                
            } catch (ApiException $e) {
                $this->handleException($e);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }

        public function getUser($id) {
            try {
                $appId = $this->getAppId();
                $user = $this->usersService->getUser($id, $appId);

                header('Content-Type: application/json');
                if ($user) {
                    echo json_encode($user);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'error' => 'User not found',
                        'errorType' => 'USER_NOT_FOUND'
                    ]);
                }
                
            } catch (ApiException $e) {
                $this->handleException($e);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }

        public function updateUser($id) {
            try {
                $data = $this->getJsonBody(true);
                $appId = $this->getAppId();
                $result = $this->usersService->updateUser($id, $data, $appId);

                header('Content-Type: application/json');
                if ($result['success']) {
                    echo json_encode($result);
                } else {
                    http_response_code(400);
                    echo json_encode($result);
                }
                
            } catch (ApiException $e) {
                $this->handleException($e);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }

        public function deleteUser($id) {
            try {
                $appId = $this->getAppId();
                $result = $this->usersService->deleteUser($id, $appId);

                header('Content-Type: application/json');
                if ($result['success']) {
                    http_response_code(204);
                } else {
                    http_response_code(404);
                    echo json_encode($result);
                }
                
            } catch (ApiException $e) {
                $this->handleException($e);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }
    }

// Made with Bob
