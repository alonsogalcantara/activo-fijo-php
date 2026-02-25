<?php
namespace Controllers;

require_once __DIR__ . '/../Models/Account.php';
require_once __DIR__ . '/../Models/User.php';

use Models\Account;
use Models\User;
use Models\Document;

class AccountsController {
    
    public function index() {
        $accountModel = new Account();
        $accounts = $accountModel->getAll();
        
        require_once __DIR__ . '/../Views/accounts/index.php';
    }

    public function show($id) {
        // Handle Document Upload
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_document') {
            require_once __DIR__ . '/DocumentsController.php';
            $docController = new \Controllers\DocumentsController();
            $docController->upload();
            exit();
        }

        $accountModel = new Account();
        $account = $accountModel->getById($id);
        
        if (!$account) {
            header('Location: /accounts');
            exit();
        }

        // Fetch related documents
        $documentModel = new \Models\Document();
        $account['documents'] = $documentModel->getByEntity('account', $id);
        
        require_once __DIR__ . '/../Views/accounts/detail.php';
    }

    public function create() {
        $userModel = new User();
        $users = $userModel->getAll();

        require_once __DIR__ . '/../Views/accounts/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             $data = [
                'service_name' => $_POST['service_name'],
                'username' => $_POST['username'] ?? '',
                'password' => $_POST['password'] ?? '',
                'provider' => $_POST['provider'] ?? '',
                'contract_ref' => $_POST['contract_ref'] ?? '',
                'renewal_date' => $_POST['renewal_date'] ?? null,
                'birth_date' => $_POST['birth_date'] ?? null,
                'cost' => $_POST['cost'] ?? 0.00,
                'currency' => $_POST['currency'] ?? 'MXN',
                'frequency' => $_POST['frequency'] ?? 'Mensual',
                'account_type' => $_POST['account_type'] ?? 'Individual',
                'assigned_to' => !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null,
                'max_licenses' => $_POST['max_licenses'] ?? 1,
                'observations' => $_POST['observations'] ?? ''
             ];

             
             //Handle date empty strings
             if (empty($data['renewal_date'])) {
                 $data['renewal_date'] = null;
             }
             if (empty($data['birth_date'])) {
                 $data['birth_date'] = null;
             }

             $accountModel = new Account();
             $newId = $accountModel->create($data);
             if ($newId) {
                 $this->handleDocumentUpload('account', $newId);
                 // Log audit
                 $audit = new \Models\AuditLog();
                 $audit->log($_SESSION['user_name'] ?? 'System', 'CREATE', 'accounts', $newId, null, "Created account: {$data['service_name']}");
                 
                 header('Location: /accounts/detail/' . $newId);
             } else {
                 $error = "Failed to create account";
                 // Fetch users again for view
                 $userModel = new User();
                 $users = $userModel->getAll();
                 require_once __DIR__ . '/../Views/accounts/create.php';
             }
        }
    }

    public function edit($id) {
        $accountModel = new Account();
        $account = $accountModel->getById($id);
        
        if (!$account) {
            header('Location: /accounts');
            exit();
        }

        $userModel = new User();
        $users = $userModel->getAll();
        
        require_once __DIR__ . '/../Views/accounts/edit.php';
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             $data = [
                'service_name' => $_POST['service_name'],
                'username' => $_POST['username'],
                'password' => $_POST['password'],
                'provider' => $_POST['provider'],
                'contract_ref' => $_POST['contract_ref'],
                'renewal_date' => $_POST['renewal_date'],
                'birth_date' => $_POST['birth_date'] ?? null,
                'cost' => $_POST['cost'],
                'currency' => $_POST['currency'],
                'frequency' => $_POST['frequency'],
                'account_type' => $_POST['account_type'],
                'assigned_to' => !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null,
                'max_licenses' => $_POST['max_licenses'],
                'observations' => $_POST['observations']
             ];


              // Handle date empty strings
             if (empty($data['renewal_date'])) {
                 $data['renewal_date'] = null;
             }
             if (empty($data['birth_date'])) {
                 $data['birth_date'] = null;
             }

             $accountModel = new Account();
             if ($accountModel->update($id, $data)) {
                 $this->handleDocumentUpload('account', $id);
                 // Log audit
                 $audit = new \Models\AuditLog();
                 $audit->log($_SESSION['user_name'] ?? 'System', 'UPDATE', 'accounts', $id, null, "Updated account: {$data['service_name']}");
                 
                 header('Location: /accounts/detail/' . $id);
             } else {
                 $error = "Failed to update account";
                 $account = $accountModel->getById($id);
                 $userModel = new User();
                 $users = $userModel->getAll();
                 require_once __DIR__ . '/../Views/accounts/edit.php';
             }
        }
    }

    public function delete($id) {
        $accountModel = new Account();
        $account = $accountModel->getById($id);
        
        // Log audit before deletion
        if ($account) {
            $audit = new \Models\AuditLog();
            $audit->log($_SESSION['user_name'] ?? 'System', 'DELETE', 'accounts', $id, "Deleted account: {$account['service_name']}", null);
        }
        
        $accountModel->delete($id);
        header('Location: /accounts');
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
