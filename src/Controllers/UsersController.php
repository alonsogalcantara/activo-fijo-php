<?php
namespace Controllers;

require_once __DIR__ . '/../Models/User.php';


require_once __DIR__ . '/../Models/Asset.php';
require_once __DIR__ . '/../Models/Account.php';

use Models\User;
use Models\Asset;
use Models\Account;

class UsersController {
    
    public function index() {
        $userModel = new User();
        // Use getAllFull if available, or just getAll
        // Based on previous file read, getAll returns id,name. getAllFull returns *.
        // Index view needs full data generally? The previous index code worked, let's stick to what works or use getAllFull if I saw it.
        // I saw getAllFull in step 509.
        $users = $userModel->getAllFull(); 
        require_once __DIR__ . '/../Views/users/index.php';
    }

    public function show($id) {
        $userModel = new User();
        $user = $userModel->getUserById($id);
        
        if (!$user) {
            header('Location: /users');
            exit();
        }

        $assetModel = new Asset();
        $assigned_assets = $assetModel->getByUser($id);
        
        $accountModel = new Account();
        $assigned_accounts = $accountModel->getByUser($id);

        // Available assets for assignment modal
        // We need assets with status 'Disponible'
        // Asset::getAll() returns all. We can filter in PHP or add getAvailable() to model.
        // For now, filter in PHP to avoid changing model structure too much.
        $all_assets = $assetModel->getAll();
        $available_assets = array_filter($all_assets, function($a) {
            return $a['status'] === 'Disponible';
        });

        require_once __DIR__ . '/../Views/users/detail.php';
    }

    public function create() {
        require_once __DIR__ . '/../Views/users/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             $data = [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'role' => $_POST['role'],
                'status' => $_POST['status'] ?? 'Activo',
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                // Add other fields as necessary per schema
             ];

             $userModel = new User();
             $newId = $userModel->create($data);
             if ($newId) {
                 header('Location: /users/detail/' . $newId);
             } else {
                 $error = "Failed to create user";
                 require_once __DIR__ . '/../Views/users/create.php';
             }
        }
    }

    public function edit($id) {
        $userModel = new User();
        $user = $userModel->getUserById($id);
        
        if (!$user) {
            header('Location: /users');
            exit();
        }
        
        require_once __DIR__ . '/../Views/users/edit.php';
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             $data = [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'role' => $_POST['role'],
                'status' => $_POST['status'],
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
             ];
             
             // Handle Password only if provided
             if (!empty($_POST['password'])) {
                 $data['password'] = $_POST['password'];
             }

             $userModel = new User();
             if ($userModel->update($id, $data)) {
                 header('Location: /users');
             } else {
                 $error = "Failed to update user";
                 $user = $userModel->getUserById($id);
                 require_once __DIR__ . '/../Views/users/edit.php';
             }
        }
    }

    public function delete($id) {
        $userModel = new User();
        $userModel->delete($id);
        header('Location: /users');
    }

    public function admin() {
        $userModel = new User();
        $allUsers = $userModel->getAllFull();
        
        // Filter those who have system access
        $systemUsers = array_filter($allUsers, function($u) {
            return !empty($u['system_role']);
        });
        
        // Filter those who DO NOT have system access
        $employees = array_filter($allUsers, function($u) {
            return empty($u['system_role']);
        });

        require_once __DIR__ . '/../Views/users/admin.php';
    }

    public function grantAccess() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'];
            $password = $_POST['password'];
            $role = $_POST['role']; // This comes from the form as 'normal' or 'admin'

            $userModel = new User();
            $user = $userModel->getUserById($userId);
            
            if ($user) {
                // We keep their current job title 'role', but update 'system_role'
                $data = [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'], // Keep existing job title
                    'system_role' => $role,  // Update system permission
                    'status' => $user['status'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'password' => $password
                ];

                if ($userModel->update($userId, $data)) {
                    // Log audit
                    $audit = new \Models\AuditLog();
                    $audit->log($_SESSION['user_name'] ?? 'System', 'UPDATE', 'users', $userId, null, "Granted access as $role");
                    header('Location: /admin/users');
                } else {
                    header('Location: /admin/users?error=failed');
                }
            }
        }
    }

    public function revokeAccess($id) {
        $userModel = new User();
        $user = $userModel->getUserById($id);
        
        if ($user) {
            $data = [
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'], // Keep job title
                'system_role' => null,   // Revoke system access
                'status' => $user['status'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name']
                // no password update, keeps old hash or could clear it if desired
            ];
            
            if ($userModel->update($id, $data)) {
               $audit = new \Models\AuditLog();
               $audit->log($_SESSION['user_name'] ?? 'System', 'UPDATE', 'users', $id, 'system_role=' . $user['system_role'], "Revoked access");
               header('Location: /admin/users');
            }
        }
    }
}
