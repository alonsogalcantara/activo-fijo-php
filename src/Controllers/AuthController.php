<?php
namespace Controllers;

require_once __DIR__ . '/../Models/User.php';

use Models\User;

class AuthController {

    public function login() {
        // Show login view
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $userModel = new User();
            $user = $userModel->login($email, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['system_role'] = $user['system_role'];
                
                header('Location: /dashboard');
                exit();
            } else {
                $error = "Invalid credentials";
                require_once __DIR__ . '/../Views/auth/login.php';
            }
        }
    }

    public function logout() {
        session_destroy();
        header('Location: /login');
        exit();
    }
}
