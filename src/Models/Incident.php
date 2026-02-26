<?php
namespace Models;

use Database;
use PDO;

class Incident {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getByAssetId($assetId) {
        $sql = "SELECT i.*, u.name as reported_by_name 
                FROM incidents i 
                LEFT JOIN users u ON i.created_by = u.id 
                WHERE i.asset_id = :asset_id 
                ORDER BY i.incident_date DESC";
        
        // Note: 'created_by' column might not exist in schema based on previous read. 
        // Let's check schema again or just do simple select for now to fix error.
        // Schema line 220: id, asset_id, incident_date, description, resolution_type, resolution_notes, cost, created_at, is_capex
        // No 'created_by' in schema!
        
        $sql = "SELECT * FROM incidents WHERE asset_id = :asset_id ORDER BY incident_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':asset_id' => $assetId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO incidents (asset_id, incident_date, description, resolution_type, resolution_notes, cost, is_capex) 
                VALUES (:asset_id, :incident_date, :description, :resolution_type, :resolution_notes, :cost, :is_capex)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':asset_id' => $data['asset_id'],
            ':incident_date' => $data['incident_date'],
            ':description' => $data['description'],
            ':resolution_type' => $data['resolution_type'] ?? 'Pendiente',
            ':resolution_notes' => $data['resolution_notes'] ?? '',
            ':cost' => $data['cost'] ?? 0.00,
            ':is_capex' => $data['is_capex'] ?? 0
        ]);
    }
}
