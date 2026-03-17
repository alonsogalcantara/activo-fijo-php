<?php
namespace Controllers;

use Models\Document;
use Models\User;

require_once __DIR__ . '/../../config/Config.php';

class DocumentsController {
    
    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
            // Verify session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['user_id'])) {
                header('Location: /login');
                exit;
            }

            $entity_type = $_POST['entity_type'] ?? '';
            $entity_id   = (int)($_POST['entity_id'] ?? 0);
            $redirect_url = $_POST['redirect_url'] ?? '/dashboard';

            // Validate entity type
            $allowed_types = ['asset', 'account', 'user'];
            if (!in_array($entity_type, $allowed_types)) {
                $_SESSION['flash_message'] = "Tipo de entidad inválido.";
                header("Location: $redirect_url");
                exit;
            }

            $file = $_FILES['document'];

            // Delegate upload + DB save to Model
            $documentModel = new Document();
            $result = $documentModel->uploadFile($file, $entity_type, $entity_id, $_SESSION['user_id']);

            $_SESSION['flash_message'] = $result['success']
                ? "Documento subido correctamente."
                : "Error al subir: " . $result['error'];

            header("Location: $redirect_url");
            exit;
        }
    }

    public function delete($id) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $documentModel = new Document();
        $doc = $documentModel->getById($id);

        if ($doc) {
            // Delete physical file
            $file_path = \Config::uploadsPath() . $doc['filename'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete DB record
            $documentModel->delete($id);
            $_SESSION['flash_message'] = "Documento eliminado correctamente.";
            
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
            $_SESSION['flash_message'] = "Documento no encontrado.";
            header("Location: /dashboard");
            exit;
        }
    }
}
