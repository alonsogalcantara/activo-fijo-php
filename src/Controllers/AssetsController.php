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
                'batch_number' => !empty($_POST['batch_number']) ? $_POST['batch_number'] : null,
                'serial_number' => !empty($_POST['serial_number']) ? $_POST['serial_number'] : null,
                'acquisition_type' => $_POST['acquisition_type'],
                'leasing_company' => $_POST['leasing_company'] ?? '',
                'cost_center' => $_POST['cost_center'] ?? '',
                'photo_filename' => $this->handlePhotoUpload()
             ];

             $assetModel = new Asset();
             $newId = $assetModel->create($data);
             if ($newId) {
                 $this->handleDocumentUpload('asset', $newId);
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
                'batch_number' => !empty($_POST['batch_number']) ? $_POST['batch_number'] : null,
                'serial_number' => !empty($_POST['serial_number']) ? $_POST['serial_number'] : null,
                'acquisition_type' => $_POST['acquisition_type'],
                'leasing_company' => $_POST['leasing_company'],
                'cost_center' => $_POST['cost_center']
             ];
             
             $photo = $this->handlePhotoUpload();
             if ($photo !== '') {
                 // In Asset.php update method, we should make sure photo_filename acts properly.
                 // Let's modify the query in Asset.php separately or just update it via a specific call, 
                 // but wait, Asset update doesn't include photo_filename right now! We will need to update the model.
                 $data['photo_filename'] = $photo;
             }

             $assetModel = new Asset();
             if ($assetModel->update($id, $data)) {
                 $this->handleDocumentUpload('asset', $id);
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

    public function dispose($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $assetModel = new Asset();
            $asset = $assetModel->getById($id);
            
            if (!$asset) {
                header('Location: /assets');
                exit();
            }

            $disposal_date = $_POST['disposal_date'] ?? date('Y-m-d');
            $disposal_reason = $_POST['disposal_reason'] ?? '';
            $disposal_price = !empty($_POST['disposal_price']) ? floatval($_POST['disposal_price']) : 0.00;

            // Calculate accumulated depreciation up to the disposal date
            // We use the same generic lifespans as calculateDepreciation
            $lifespan_map = [
                'Computadora' => 3, 'Laptop' => 3, 'Servidor' => 3, 'Celular' => 3, 'Impresora' => 3,
                'Vehículo' => 4, 'Automóvil' => 4, 'Camioneta' => 4,
                'Mobiliario' => 10, 'Silla' => 10, 'Escritorio' => 10
            ];
            $category = $asset['category'];
            $years_useful_life = isset($lifespan_map[$category]) ? $lifespan_map[$category] : 10;
            
            $purchase_cost = floatval($asset['purchase_cost']);
            $purchase_date = !empty($asset['purchase_date']) ? new \DateTime($asset['purchase_date']) : new \DateTime();
            $dispose_date_obj = new \DateTime($disposal_date);
            
            $accumulated_depreciation = 0;
            $book_value_at_disposal = $purchase_cost;

            if ($asset['acquisition_type'] !== 'Arrendamiento') {
                 // Calculate interval up to disposal date
                 $interval = $purchase_date->diff($dispose_date_obj);
                 $age_years = $interval->y + ($interval->m / 12) + ($interval->d / 365);
                 
                 // If disposal date is before purchase, age is negative -> 0
                 if ($dispose_date_obj < $purchase_date) {
                     $age_years = 0;
                 }
                 
                 if ($asset['accumulated_depreciation_override'] !== null) {
                      $accumulated_depreciation = floatval($asset['accumulated_depreciation_override']);
                 } else {
                      $annual_depreciation = $purchase_cost / $years_useful_life;
                      $accumulated_depreciation = $annual_depreciation * $age_years;
                 }

                 if ($accumulated_depreciation > $purchase_cost) {
                     $accumulated_depreciation = $purchase_cost;
                 }
                 $book_value_at_disposal = $purchase_cost - $accumulated_depreciation;
            }

            $data = [
                'disposal_date' => $disposal_date,
                'disposal_reason' => $disposal_reason,
                'disposal_price' => $disposal_price,
                'book_value_at_disposal' => $book_value_at_disposal,
                'accumulated_depreciation_override' => $accumulated_depreciation
            ];

            if ($assetModel->dispose($id, $data)) {
                // Unassign from user implicitly handled by model, maybe add history event
                $audit = new \Models\AuditLog();
                $audit->log($_SESSION['user_name'] ?? 'System', 'DISPOSE', 'assets', $id, null, "Dar de baja activo: {$asset['name']} por $disposal_reason");
            }
            
            header('Location: /assets/detail/' . $id);
            exit();
        }
    }

    public function storeIncident($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $incidentModel = new \Models\Incident();
            
            $data = [
                'asset_id' => $id,
                'incident_date' => $_POST['incident_date'] ?? date('Y-m-d'),
                'description' => $_POST['description'] ?? '',
                'resolution_type' => $_POST['resolution_type'] ?? 'Pendiente',
                'resolution_notes' => $_POST['resolution_notes'] ?? '',
                'cost' => !empty($_POST['cost']) ? floatval($_POST['cost']) : 0.00,
                'is_capex' => isset($_POST['is_capex']) && $_POST['is_capex'] == '1' ? 1 : 0
            ];
            
            if ($incidentModel->create($data)) {
                
                // Update asset status to 'En Mantenimiento'
                $assetModel = new Asset();
                $assetModel->updateStatus($id, 'En Mantenimiento');

                // Log audit
                require_once __DIR__ . '/../Models/AuditLog.php';
                $audit = new \Models\AuditLog();
                $audit->log($_SESSION['user_name'] ?? 'System', 'INCIDENT', 'assets', $id, null, "Reported incident on asset ID $id. Status changed to En Mantenimiento.");
            }
            
            header('Location: /assets/detail/' . $id);
            exit();
        }
    }

    public function endMaintenance($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $assetModel = new Asset();
            $asset = $assetModel->getById($id);
            if ($asset && $asset['status'] === 'En Mantenimiento') {
                $status = !empty($asset['assigned_to']) ? 'Asignado' : 'Disponible';
                $assetModel->updateStatus($id, $status);
                
                require_once __DIR__ . '/../Models/AuditLog.php';
                $audit = new \Models\AuditLog();
                $audit->log($_SESSION['user_name'] ?? 'System', 'UPDATE', 'assets', $id, null, "Mantenimiento terminado. Estado actualizado a $status.");
            }
            header('Location: /assets/detail/' . $id);
            exit();
        }
    }

    private function handlePhotoUpload() {
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['photo'];
            $max_size = 10 * 1024 * 1024;
            if ($file['size'] > $max_size) return '';
            
            $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($file['type'], $allowed_mimes) || in_array($ext, $allowed_exts)) {
                $upload_dir = __DIR__ . '/../../public/uploads/';
                if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
                
                $filename = uniqid() . '_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                    return $filename;
                }
            }
        }
        return '';
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
