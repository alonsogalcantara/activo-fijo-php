<?php
namespace Models;

require_once __DIR__ . '/../../config/db.php';

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
}
