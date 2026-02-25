<?php
namespace Controllers;

require_once __DIR__ . '/../Models/User.php';

use Models\User;

class AuthController {

    public function login() {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit();
        }

        // Show login view
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function authenticate() {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit();
        }

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
