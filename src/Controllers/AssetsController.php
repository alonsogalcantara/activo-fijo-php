<?php
namespace Controllers;

require_once __DIR__ . '/../Models/Asset.php';
require_once __DIR__ . '/../Models/User.php'; // For assigned_to dropdown

use Models\Asset;
use Models\User;
use Models\Document;
use Models\Incident;

class AssetsController {
    
    public function index() {
        $assetModel = new Asset();
        $assets = $assetModel->getAll(); 
        
        require_once __DIR__ . '/../Views/assets/index.php';
    }

    public function show($id) {
        // Handle Document Upload
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_document') {
            require_once __DIR__ . '/DocumentsController.php';
            $docController = new \Controllers\DocumentsController();
            $docController->upload();
            exit();
        }

        $assetModel = new Asset();
        $asset = $assetModel->getById($id);
        
        if (!$asset) {
            header('Location: /assets');
            exit();
        }

        // Fetch user name for display if assigned (already in getById via join if updated, let's check model)
        // Model getById does NOT have join in current state? Let's check.
        // I need to update getById in Asset Model to Include JOIN or do it here.
        // Actually Asset::getAll has JOIN. Asset::getById DOES NOT. 
        // I should update Asset::getById to include user name to match getAll logic.
        // But for now, let's check if I can get user name separately or if I should update model.
        // Updating model is better.
        
        // Fetch available users for assignment dropdown
        $userModel = new User();
        $available_users = $userModel->getAll(); // In real app, filter by Active status

        // Mock incidents/documents
        $incidentModel = new \Models\Incident();
        $asset['incidents'] = $incidentModel->getByAssetId($id);

        $documentModel = new \Models\Document();
        $asset['documents'] = $documentModel->getByEntity('asset', $id);

        // If assigned, get User Name. 
        // The view expects $asset['assigned_to_name']. 
        if ($asset['assigned_to']) {
            $user = $userModel->getUserById($asset['assigned_to']);
            $asset['assigned_to_name'] = $user['name'] ?? 'Usuario Desconocido';
            $asset['user_dept'] = $user['department'] ?? '';
            // $asset['assigned_at'] is already fetched from DB if it exists in schema
        } else {
            $asset['assigned_to_name'] = null;
        }

        require_once __DIR__ . '/../Views/assets/detail.php';
    }

    public function create() {
        // Need users for assignment
        $userModel = new User();
        $users = $userModel->getAll();
        
        require_once __DIR__ . '/../Views/assets/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             $data = [
                'name' => $_POST['name'],
                'category' => $_POST['category'],
                'brand' => $_POST['brand'],
                'model' => $_POST['model'],
                'description' => $_POST['description'],
                'purchase_date' => $_POST['purchase_date'],
                'purchase_cost' => $_POST['purchase_cost'],
                'status' => $_POST['status'],
                'assigned_to' => !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null,
                'quantity' => $_POST['quantity'] ?? 1,
                'batch_number' => $_POST['batch_number'] ?? '',
                'serial_number' => $_POST['serial_number'] ?? '',
                'acquisition_type' => $_POST['acquisition_type'],
                'leasing_company' => $_POST['leasing_company'] ?? '',
                'cost_center' => $_POST['cost_center'] ?? '',
                'photo_filename' => '' // Handle upload later
             ];

             $assetModel = new Asset();
             $newId = $assetModel->create($data);
             if ($newId) {
                 // Log audit
                 $audit = new \Models\AuditLog();
                 $audit->log($_SESSION['user_name'] ?? 'System', 'CREATE', 'assets', $newId, null, "Created asset: {$data['name']}");
                 
                 header('Location: /assets/detail/' . $newId);
             } else {
                 $error = "Failed to create asset";
                 require_once __DIR__ . '/../Views/assets/create.php';
             }
        }
    }

    public function edit($id) {
        $assetModel = new Asset();
        $asset = $assetModel->getById($id);
        
        if (!$asset) {
            header('Location: /assets');
            exit();
        }
        
        $userModel = new User();
        $users = $userModel->getAll();

        require_once __DIR__ . '/../Views/assets/edit.php';
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             $data = [
                'name' => $_POST['name'],
                'category' => $_POST['category'],
                'brand' => $_POST['brand'],
                'model' => $_POST['model'],
                'description' => $_POST['description'],
                'purchase_date' => $_POST['purchase_date'],
                'purchase_cost' => $_POST['purchase_cost'],
                'status' => $_POST['status'],
                'assigned_to' => !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null,
                'quantity' => $_POST['quantity'],
                'batch_number' => $_POST['batch_number'],
                'serial_number' => $_POST['serial_number'],
                'acquisition_type' => $_POST['acquisition_type'],
                'leasing_company' => $_POST['leasing_company'],
                'cost_center' => $_POST['cost_center']
             ];

             $assetModel = new Asset();
             if ($assetModel->update($id, $data)) {
                 // Log audit
                 $audit = new \Models\AuditLog();
                 $audit->log($_SESSION['user_name'] ?? 'System', 'UPDATE', 'assets', $id, null, "Updated asset: {$data['name']}");
                 
                 header('Location: /assets/detail/' . $id);
             } else {
                 $error = "Failed to update asset";
                 $asset = $assetModel->getById($id); // Reload for view
                 require_once __DIR__ . '/../Views/assets/edit.php';
             }
        }
    }

    public function delete($id) {
        $assetModel = new Asset();
        $asset = $assetModel->getById($id);
        
        // Log audit before deletion
        if ($asset) {
            $audit = new \Models\AuditLog();
            $audit->log($_SESSION['user_name'] ?? 'System', 'DELETE', 'assets', $id, "Deleted asset: {$asset['name']}", null);
        }
        
        $assetModel->delete($id);
        header('Location: /assets');
    }
}
