<?php
namespace Controllers;

require_once __DIR__ . '/../Models/User.php';


require_once __DIR__ . '/../Models/Asset.php';
require_once __DIR__ . '/../Models/Account.php';

use Models\User;
use Models\Asset;
use Models\Account;
use Models\Document;

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
        // Handle Document Upload
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_document') {
            require_once __DIR__ . '/DocumentsController.php';
            $docController = new \Controllers\DocumentsController();
            $docController->upload();
            exit();
        }

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

        // Fetch related documents
        $documentModel = new \Models\Document();
        $user['documents'] = $documentModel->getByEntity('user', $id);

        require_once __DIR__ . '/../Views/users/detail.php';
    }

    public function create() {
        require_once __DIR__ . '/../Views/users/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             $data = [
                'name' => $_POST['name'],
                'first_name' => $_POST['first_name'] ?? '',
                'middle_name' => $_POST['middle_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'second_last_name' => $_POST['second_last_name'] ?? '',
                'email' => $_POST['email'],
                'phone' => $_POST['phone'] ?? '',
                'company' => $_POST['company'] ?? '',
                'department' => $_POST['department'] ?? '',
                'role' => $_POST['role'] ?? '',
                'entry_date' => !empty($_POST['entry_date']) ? $_POST['entry_date'] : null,
                'gender' => $_POST['gender'] ?? '',
                'password' => $_POST['password'],
                'status' => $_POST['status'] ?? 'Activo',
             ];

             $userModel = new User();
             $newId = $userModel->create($data);
             if ($newId) {
                 $this->handleDocumentUpload('user', $newId);
                 // Log audit
                 $audit = new \Models\AuditLog();
                 $audit->log($_SESSION['user_name'] ?? 'System', 'CREATE', 'users', $newId, null, "Created user: {$data['name']}");
                 
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
                'first_name' => $_POST['first_name'] ?? '',
                'middle_name' => $_POST['middle_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'second_last_name' => $_POST['second_last_name'] ?? '',
                'email' => $_POST['email'],
                'phone' => $_POST['phone'] ?? '',
                'company' => $_POST['company'] ?? '',
                'department' => $_POST['department'] ?? '',
                'role' => $_POST['role'] ?? '',
                'entry_date' => !empty($_POST['entry_date']) ? $_POST['entry_date'] : null,
                'gender' => $_POST['gender'] ?? '',
                'status' => $_POST['status'],
             ];
             
             // Handle Password only if provided
             if (!empty($_POST['password'])) {
                 $data['password'] = $_POST['password'];
             }

             $userModel = new User();
             if ($userModel->update($id, $data)) {
                 $this->handleDocumentUpload('user', $id);
                 // Log audit
                 $audit = new \Models\AuditLog();
                 $audit->log($_SESSION['user_name'] ?? 'System', 'UPDATE', 'users', $id, null, "Updated user: {$data['name']}");
                 
                 header('Location: /users/detail/' . $id);
             } else {
                 $error = "Failed to update user";
                 $user = $userModel->getUserById($id);
                 require_once __DIR__ . '/../Views/users/edit.php';
             }
        }
    }

    public function delete($id) {
        $userModel = new User();
        $user = $userModel->getUserById($id);
        
        // Log audit before deletion
        if ($user) {
            $audit = new \Models\AuditLog();
            $audit->log($_SESSION['user_name'] ?? 'System', 'DELETE', 'users', $id, "Deleted user: {$user['name']}", null);
        }
        
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
                    'first_name' => $user['first_name'] ?? '',
                    'middle_name' => $user['middle_name'] ?? '',
                    'last_name' => $user['last_name'] ?? '',
                    'second_last_name' => $user['second_last_name'] ?? '',
                    'email' => $user['email'],
                    'phone' => $user['phone'] ?? '',
                    'company' => $user['company'] ?? '',
                    'department' => $user['department'] ?? '',
                    'role' => $user['role'] ?? '', // Keep existing job title
                    'entry_date' => $user['entry_date'] ?? null,
                    'gender' => $user['gender'] ?? '',
                    'system_role' => $role,  // Update system permission
                    'status' => $user['status'],
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
                'first_name' => $user['first_name'] ?? '',
                'middle_name' => $user['middle_name'] ?? '',
                'last_name' => $user['last_name'] ?? '',
                'second_last_name' => $user['second_last_name'] ?? '',
                'email' => $user['email'],
                'phone' => $user['phone'] ?? '',
                'company' => $user['company'] ?? '',
                'department' => $user['department'] ?? '',
                'role' => $user['role'] ?? '', // Keep job title
                'entry_date' => $user['entry_date'] ?? null,
                'gender' => $user['gender'] ?? '',
                'system_role' => null,   // Revoke system access
                'status' => $user['status']
                // no password update, keeps old hash or could clear it if desired
            ];
            
            if ($userModel->update($id, $data)) {
               $audit = new \Models\AuditLog();
               $audit->log($_SESSION['user_name'] ?? 'System', 'UPDATE', 'users', $id, 'system_role=' . $user['system_role'], "Revoked access");
               header('Location: /admin/users');
            }
        }
    }

    private function handleDocumentUpload($entity_type, $entity_id) {
        if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['document'];
            $max_size = 10 * 1024 * 1024;
            if ($file['size'] > $max_size) return;

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $clean_name = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
            $final_filename = $clean_name . '_' . time() . '.' . $ext;
            
            $upload_dir = __DIR__ . '/../../public/uploads/';
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

            if (move_uploaded_file($file['tmp_name'], $upload_dir . $final_filename)) {
                require_once __DIR__ . '/../Models/Document.php';
                $documentModel = new \Models\Document();
                $documentModel->create([
                    'entity_id' => $entity_id,
                    'entity_type' => $entity_type,
                    'filename' => $final_filename,
                    'file_type' => $ext,
                    'file_size' => $file['size'],
                    'uploaded_by' => $_SESSION['user_id'] ?? 0
                ]);
            }
        }
    }
}
