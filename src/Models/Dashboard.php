<?php
namespace Models;

require_once __DIR__ . '/../../config/db.php';

use Database;
use PDO;

class Dashboard {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getStats() {
        $stats = [];

        // Total Assets
        $query = 'SELECT COUNT(*) as total FROM assets';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_assets'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total Value (Purchase Cost) - Simple sum
        $query = 'SELECT SUM(purchase_cost) as total_value FROM assets';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_purchase_value'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_value'] ?? 0;

        // Assets by Status
        $query = 'SELECT status, COUNT(*) as count FROM assets GROUP BY status';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Assets by Category
        $query = 'SELECT category, COUNT(*) as count FROM assets GROUP BY category';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate Current Total Value (Depreciated)
        // This is expensive as it requires PHP processing for each asset
        // For dashboard, maybe we can accept an approximation or we calculate it.
        // Let's reuse Asset Model logic efficiently or just sum purchase cost for now if performance is key.
        // But the requirement says "Port these SQL queries... Ensure decimal handling".
        // Since I don't have the original complex SQL, I will iterate (slow) or try to implement depreciation in SQL (hard for different categories).
        // I'll iterate for now as dataset size is unknown but likely manageable for a migration starting point.
        
        $query = 'SELECT * FROM assets';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $allAssets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $totalCurrentValue = 0;
        $assetModel = new \Models\Asset(); // Use Asset Model to access calculateDepreciation but it is private.
        // I need to make calculateDepreciation public or duplicate logic.
        // I will make a helper method in Dashboard or just duplicate logic for simplicity here since Asset property is private.
        // Actually best is to instantiate Asset and use a public method if available. 
        // I'll quickly add a public method to Asset or just replicate logic here. 
        // Replicating for speed in this file.
        
        foreach ($allAssets as $asset) {
             $dep = $this->calculateDepreciation($asset);
             if (is_numeric($dep['current_value'])) {
                $totalCurrentValue += $dep['current_value'];
             }
        }
        $stats['total_current_value'] = $totalCurrentValue;

        return $stats;
    }

    private function calculateDepreciation($asset) {
        $lifespan_map = [
            'Computadora' => 3,
            'Laptop' => 3,
            'Servidor' => 3,
            'Vehículo' => 4,
            'Automóvil' => 4,
            'Camioneta' => 4,
            'Mobiliario' => 10,
            'Silla' => 10,
            'Escritorio' => 10,
            'Celular' => 3,
            'Impresora' => 3,
        ];
        
        $category = $asset['category'];
        $years_useful_life = isset($lifespan_map[$category]) ? $lifespan_map[$category] : 10;
        
        $purchase_cost = floatval($asset['purchase_cost']);
        
        if (empty($asset['purchase_date'])) {
             // Handle missing purchase date (e.g. assume new or ignore)
             // For depreciation, if no date, maybe 0 depreciation or assumes acquisition method
             // Let's assume current date to avoid error and result in 0 age.
             $purchase_date = new \DateTime(); 
        } else {
             $purchase_date = new \DateTime($asset['purchase_date']);
        }

        $current_date = new \DateTime();
        
        $interval = $purchase_date->diff($current_date);
        $age_years = $interval->y + ($interval->m / 12) + ($interval->d / 365);
        
        if ($asset['acquisition_type'] === 'Arrendamiento') {
             return ['current_value' => 0]; 
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

        $current_value = $purchase_cost - $accumulated_depreciation;
        if ($current_value <= 0) $current_value = 0;

        return ['current_value' => $current_value];
    }
}
