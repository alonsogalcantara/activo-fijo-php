<?php
namespace Models;

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/Config.php';

use Database;
use PDO;

class Document {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function create($data) {
        $sql = "INSERT INTO documents (entity_id, entity_type, filename, file_type, file_size, uploaded_by, uploaded_at) 
                VALUES (:entity_id, :entity_type, :filename, :file_type, :file_size, :uploaded_by, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':entity_id' => $data['entity_id'],
            ':entity_type' => $data['entity_type'],
            ':filename' => $data['filename'],
            ':file_type' => $data['file_type'],
            ':file_size' => $data['file_size'],
            ':uploaded_by' => $data['uploaded_by']
        ]);
        
        return $this->db->lastInsertId();
    }

    public function getByEntity($type, $id) {
        $sql = "SELECT d.*, u.name as uploader_name 
                FROM documents d 
                LEFT JOIN users u ON d.uploaded_by = u.id 
                WHERE d.entity_type = :type AND d.entity_id = :id 
                ORDER BY d.uploaded_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':type' => $type, ':id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT * FROM documents WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $sql = "DELETE FROM documents WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function uploadFile($file, $entity_type, $entity_id, $user_id) {
        // --- Validate upload error code ---
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $codes = [
                UPLOAD_ERR_INI_SIZE   => 'El archivo supera upload_max_filesize en php.ini',
                UPLOAD_ERR_FORM_SIZE  => 'El archivo supera MAX_FILE_SIZE del formulario',
                UPLOAD_ERR_PARTIAL    => 'El archivo se subió parcialmente',
                UPLOAD_ERR_NO_FILE    => 'No se subió ningún archivo',
                UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal de PHP',
                UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en disco',
                UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP detuvo la subida',
            ];
            $msg = $codes[$file['error']] ?? 'Error desconocido (código ' . ($file['error'] ?? '?') . ')';
            return ['success' => false, 'error' => $msg];
        }

        // --- Validate file size (max 10 MB) ---
        $max_size = 10 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            return ['success' => false, 'error' => 'El archivo excede el tamaño máximo permitido (10 MB)'];
        }

        // --- Validate extension ---
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt', 'zip', 'csv'];
        if (!in_array($ext, $allowed_exts)) {
            return ['success' => false, 'error' => 'Tipo de archivo no permitido: .' . $ext];
        }

        // --- Resolve upload directory (from .yml paths.uploads) ---
        $upload_dir = \Config::uploadsPath();
        
        // Create directory if missing
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                return ['success' => false, 'error' => 'No se pudo crear el directorio de subidas: ' . realpath(__DIR__ . '/../../public')];
            }
        }

        // Fix permissions if not writable
        if (!is_writable($upload_dir)) {
            @chmod($upload_dir, 0755);
            if (!is_writable($upload_dir)) {
                return ['success' => false, 'error' => 'El directorio de subidas no tiene permisos de escritura: ' . realpath($upload_dir)];
            }
        }

        // --- Build safe filename ---
        $clean_name    = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $final_filename = $clean_name . '_' . time() . '.' . $ext;
        $destination    = $upload_dir . $final_filename;

        // --- Move uploaded file (vanilla PHP) ---
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $err = error_get_last();
            return ['success' => false, 'error' => ($err ? $err['message'] : 'move_uploaded_file falló sin razón reportada')];
        }

        // --- Save record to DB ---
        $this->create([
            'entity_id'   => $entity_id,
            'entity_type' => $entity_type,
            'filename'    => $final_filename,
            'file_type'   => $ext,
            'file_size'   => $file['size'],
            'uploaded_by' => $user_id,
        ]);

        return ['success' => true, 'filename' => $final_filename];
    }
}
