<?php
namespace Models;

require_once __DIR__ . '/../../config/db.php';

use Database;
use PDO;

class Asset {
    private $conn;
    private $table = 'assets';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll($limit = 1000, $offset = 0) {
        $query = 'SELECT a.*, CONCAT(u.first_name, " ", u.last_name) as assigned_to_name 
                  FROM ' . $this->table . ' a 
                  LEFT JOIN users u ON a.assigned_to = u.id 
                  LIMIT :limit OFFSET :offset';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate encryption for each asset
        foreach ($assets as &$asset) {
            $depreciation = $this->calculateDepreciation($asset);
            $asset['current_value'] = $depreciation['current_value'];
            $asset['accumulated_depreciation'] = $depreciation['accumulated_depreciation'];
            $asset['status_depreciation'] = $depreciation['status'];
        }
        
        return $assets;
    }

    public function getById($id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = :id LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $asset = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($asset) {
            $depreciation = $this->calculateDepreciation($asset);
            $asset['current_value'] = $depreciation['current_value'];
            $asset['accumulated_depreciation'] = $depreciation['accumulated_depreciation'];
            $asset['status_depreciation'] = $depreciation['status'];
        }

        return $asset;
    }

    public function create($data) {
        $query = 'INSERT INTO ' . $this->table . ' (name, category, brand, model, description, purchase_date, purchase_cost, status, assigned_to, quantity, batch_number, serial_number, acquisition_type, leasing_company, cost_center, photo_filename) VALUES (:name, :category, :brand, :model, :description, :purchase_date, :purchase_cost, :status, :assigned_to, :quantity, :batch_number, :serial_number, :acquisition_type, :leasing_company, :cost_center, :photo_filename)';
        
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':category', $data['category']);
        $stmt->bindParam(':brand', $data['brand']);
        $stmt->bindParam(':model', $data['model']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':purchase_date', $data['purchase_date']);
        $stmt->bindParam(':purchase_cost', $data['purchase_cost']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':assigned_to', $data['assigned_to']);
        $stmt->bindParam(':quantity', $data['quantity']);
        $stmt->bindParam(':batch_number', $data['batch_number']);
        $stmt->bindParam(':serial_number', $data['serial_number']);
        $stmt->bindParam(':acquisition_type', $data['acquisition_type']);
        $stmt->bindParam(':leasing_company', $data['leasing_company']);
        $stmt->bindParam(':cost_center', $data['cost_center']);
        $stmt->bindParam(':photo_filename', $data['photo_filename']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($id, $data) {
         $query = 'UPDATE ' . $this->table . ' SET name = :name, category = :category, brand = :brand, model = :model, description = :description, purchase_date = :purchase_date, purchase_cost = :purchase_cost, status = :status, assigned_to = :assigned_to, quantity = :quantity, batch_number = :batch_number, serial_number = :serial_number, acquisition_type = :acquisition_type, leasing_company = :leasing_company, cost_center = :cost_center';
         
         if (isset($data['photo_filename'])) {
             $query .= ', photo_filename = :photo_filename';
         }
         
         $query .= ' WHERE id = :id';
         
         $stmt = $this->conn->prepare($query);

         $stmt->bindParam(':id', $id);
         $stmt->bindParam(':name', $data['name']);
         $stmt->bindParam(':category', $data['category']);
         $stmt->bindParam(':brand', $data['brand']);
         $stmt->bindParam(':model', $data['model']);
         $stmt->bindParam(':description', $data['description']);
         $stmt->bindParam(':purchase_date', $data['purchase_date']);
         $stmt->bindParam(':purchase_cost', $data['purchase_cost']);
         // Note: Status logic might need more complexity if handling specific transitions
         $stmt->bindParam(':status', $data['status']);
         $stmt->bindParam(':assigned_to', $data['assigned_to']);
         $stmt->bindParam(':quantity', $data['quantity']);
         $stmt->bindParam(':batch_number', $data['batch_number']);
         $stmt->bindParam(':serial_number', $data['serial_number']);
         $stmt->bindParam(':acquisition_type', $data['acquisition_type']);
         $stmt->bindParam(':leasing_company', $data['leasing_company']);
         $stmt->bindParam(':cost_center', $data['cost_center']);

         if (isset($data['photo_filename'])) {
             $stmt->bindParam(':photo_filename', $data['photo_filename']);
         }

         return $stmt->execute();
    }

    public function delete($id) {
         $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
         $stmt = $this->conn->prepare($query);
         $stmt->bindParam(':id', $id);
         return $stmt->execute();
    }

    private function calculateDepreciation($asset) {
        $lifespan_map = [
            'Computadora' => 3,	// 30% approx in Mexico is 3.33, often rounded to 3 or 4. Using 3 based on description.
            'Laptop' => 3,
            'Servidor' => 3,
            'Vehículo' => 4,    // 25%
            'Automóvil' => 4,
            'Camioneta' => 4,
            'Mobiliario' => 10, // 10%
            'Silla' => 10,
            'Escritorio' => 10,
            'Celular' => 3,
            'Impresora' => 3,
            // Add more as needed
        ];
        
        $category = $asset['category'];
        $years_useful_life = isset($lifespan_map[$category]) ? $lifespan_map[$category] : 10; // Default 10 years
        
        $purchase_cost = floatval($asset['purchase_cost']);
        
        if (empty($asset['purchase_date'])) {
             $purchase_date = new \DateTime(); 
        } else {
             $purchase_date = new \DateTime($asset['purchase_date']);
        }

        $current_date = new \DateTime();
        
        $interval = $purchase_date->diff($current_date);
        $age_years = $interval->y + ($interval->m / 12) + ($interval->d / 365);
        
        if ($asset['acquisition_type'] === 'Arrendamiento') {
             return [
                'current_value' => 'N/A (Arrendamiento)',
                'accumulated_depreciation' => 0,
                'status' => 'Arrendamiento'
            ];
        }

        if ($asset['accumulated_depreciation_override'] !== null) {
             $accumulated_depreciation = floatval($asset['accumulated_depreciation_override']);
        } else {
             // Straight line: (Cost / Life) * Age
             // Or (Cost - Residual) ... assuming residual is 0 for now as per common practice unless specified
             $annual_depreciation = $purchase_cost / $years_useful_life;
             $accumulated_depreciation = $annual_depreciation * $age_years;
        }

        if ($accumulated_depreciation > $purchase_cost) {
            $accumulated_depreciation = $purchase_cost;
        }

        $current_value = $purchase_cost - $accumulated_depreciation;
        
        $status = 'Vigente';
        if ($current_value <= 0) {
            $current_value = 0;
            $status = 'Totalmente Depreciado';
        }

        return [
            'current_value' => $current_value,
            'accumulated_depreciation' => $accumulated_depreciation,
            'status' => $status
        ];
    }
    public function getByUser($userId) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE assigned_to = :user_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
