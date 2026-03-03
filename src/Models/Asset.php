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
        $columns = array_keys($data);
        $placeholders = array_map(function($col) { return ':' . $col; }, $columns);
        
        $query = 'INSERT INTO ' . $this->table . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
        
        $stmt = $this->conn->prepare($query);

        if ($stmt->execute($data)) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($id, $data) {
         $setStr = '';
         foreach ($data as $key => $value) {
             $setStr .= $key . ' = :' . $key . ', ';
         }
         $setStr = rtrim($setStr, ', ');
         
         $query = 'UPDATE ' . $this->table . ' SET ' . $setStr . ' WHERE id = :id';
         $stmt = $this->conn->prepare($query);

         $data['id'] = $id;
         return $stmt->execute($data);
    }

    public function updateStatus($id, $status) {
         $query = 'UPDATE ' . $this->table . ' SET status = :status WHERE id = :id';
         $stmt = $this->conn->prepare($query);
         $stmt->bindParam(':id', $id);
         $stmt->bindParam(':status', $status);
         return $stmt->execute();
    }

    public function delete($id) {
         $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
         $stmt = $this->conn->prepare($query);
         $stmt->bindParam(':id', $id);
         return $stmt->execute();
    }

    public function dispose($id, $data) {
         $query = 'UPDATE ' . $this->table . ' SET 
                    status = :status, 
                    assigned_to = NULL, 
                    disposal_date = :disposal_date, 
                    disposal_reason = :disposal_reason, 
                    disposal_price = :disposal_price, 
                    book_value_at_disposal = :book_value_at_disposal, 
                    accumulated_depreciation_override = :accumulated_depreciation_override 
                  WHERE id = :id';
         
         $stmt = $this->conn->prepare($query);
         
         $status = 'De Baja';
         $stmt->bindParam(':id', $id);
         $stmt->bindParam(':status', $status);
         $stmt->bindParam(':disposal_date', $data['disposal_date']);
         $stmt->bindParam(':disposal_reason', $data['disposal_reason']);
         $stmt->bindParam(':disposal_price', $data['disposal_price']);
         $stmt->bindParam(':book_value_at_disposal', $data['book_value_at_disposal']);
         $stmt->bindParam(':accumulated_depreciation_override', $data['accumulated_depreciation_override']);

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
