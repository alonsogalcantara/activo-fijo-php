<?php
namespace Controllers;

use Models\Document;
use Models\User;

class DocumentsController {
    
    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
            // Verify session
            session_start();
            if (!isset($_SESSION['user_id'])) {
                header('Location: /login');
                exit;
            }

            $entity_type = $_POST['entity_type'] ?? '';
            $entity_id = $_POST['entity_id'] ?? 0;
            $redirect_url = $_POST['redirect_url'] ?? '/dashboard';
            
            // Validate entity type
            $allowed_types = ['asset', 'account', 'user'];
            if (!in_array($entity_type, $allowed_types)) {
                $_SESSION['error'] = "Tipo de entidad inválido.";
                header("Location: $redirect_url");
                exit;
            }

            $file = $_FILES['document'];
            
            // Validate upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = "Error al subir el archivo. Código: " . $file['error'];
                header("Location: $redirect_url");
                exit;
            }

            // Validate file size (e.g., max 10MB)
            $max_size = 10 * 1024 * 1024;
            if ($file['size'] > $max_size) {
                $_SESSION['error'] = "El archivo excede el tamaño máximo permitido (10MB).";
                header("Location: $redirect_url");
                exit;
            }

            // Validate file type (allow common docs and images)
            $allowed_mimes = [
                'application/pdf', 
                'application/msword', 
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'image/jpeg', 
                'image/png', 
                'text/plain',
                'application/zip'
            ];
            
            if (!in_array($file['type'], $allowed_mimes) && !in_array(mime_content_type($file['tmp_name']), $allowed_mimes)) {
                // Determine extension as fallback check
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed_exts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt', 'zip', 'csv'];
                
                if (!in_array($ext, $allowed_exts)) {
                    $_SESSION['error'] = "Tipo de archivo no permitido.";
                    header("Location: $redirect_url");
                    exit;
                }
            }

            // Generate unique filename
            $upload_dir = __DIR__ . '/../../public/uploads/documents/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $ext;
            $destination = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Save to DB
                $documentModel = new Document();
                $data = [
                    'entity_id' => $entity_id,
                    'entity_type' => $entity_type,
                    'filename' => $file['name'], // Original name for display
                    'file_type' => $ext,
                    'file_size' => $file['size'],
                    'uploaded_by' => $_SESSION['user_id']
                ];
                
                // Store the physical filename in a separate field or reuse 'filename' if schema matches. 
                // Based on schema, 'filename' is varchar(255). Let's store the generated name there
                // BUT we also want to keep the original name. 
                // The schema has 'filename'. Let's use 'filename' for the physical file 
                // and maybe add 'original_name' later. For now, let's store "PhysicalName|OriginalName" 
                // or just the physical name and lose the original name? 
                // Better: Store generated name. Layout will show generated name.
                // Wait, user wants to see "Contract.pdf" not "65d4...pdf".
                // Let's modify schema to have original_name? 
                // Constraint: User didn't ask for schema change for original_name.
                // Compromise: Save as "OriginalName_Timestamp.ext" to preserve readability.
                $clean_name = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
                $final_filename = $clean_name . '_' . time() . '.' . $ext;
                $destination = $upload_dir . $final_filename;
                
                // Move file again (or just rename the var above)
                // Let's redo the move logic
                if (rename($upload_dir . $filename, $destination)) {
                     $data['filename'] = $final_filename;
                     $documentModel->create($data);
                     $_SESSION['success'] = "Documento subido correctamente.";
                } else {
                     // Fallback if rename fails
                     $data['filename'] = $filename;
                     $documentModel->create($data);
                     $_SESSION['success'] = "Documento subido correctamente.";
                }
            } else {
                $_SESSION['error'] = "Error al mover el archivo al directorio de destino.";
            }

            header("Location: $redirect_url");
            exit;
        }
    }

    public function delete($id) {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $documentModel = new Document();
        $doc = $documentModel->getById($id);

        if ($doc) {
            // Delete physical file
            $file_path = __DIR__ . '/../../public/uploads/documents/' . $doc['filename'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete DB record
            $documentModel->delete($id);
            $_SESSION['success'] = "Documento eliminado correctamente.";
            
            // Redirect back
            $redirect_base = match($doc['entity_type']) {
                'asset' => '/assets/detail/',
                'account' => '/accounts/detail/',
                'user' => '/users/detail/',
                default => '/dashboard'
            };
            
            header("Location: " . $redirect_base . $doc['entity_id']);
            exit;
        } else {
            $_SESSION['error'] = "Documento no encontrado.";
            header("Location: /dashboard");
            exit;
        }
    }
}
