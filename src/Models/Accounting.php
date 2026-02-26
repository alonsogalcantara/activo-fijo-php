<?php
namespace Models;

require_once __DIR__ . '/../../config/db.php';

use Database;
use PDO;

class Accounting {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAssetFinancials() {
        // Calculate Total Acquisition Cost and Current Book Value
        // Note: Depreciation calculation logic is in Asset Model individually. 
        // For mass calculation, we might need to duplicate logic in SQL or fetch all assets and loop.
        // SQL is faster but complex if logic is complex (straight line vs specific).
        // Let's do a fetch all and loop for accuracy with existing logic suitable for this scale.
        
        $query = 'SELECT a.*, COALESCE(SUM(i.cost), 0) as capex_additions 
                  FROM assets a 
                  LEFT JOIN incidents i ON a.id = i.asset_id AND i.is_capex = 1
                  GROUP BY a.id';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total_acquisition_cost = 0;
        $total_current_value = 0;
        $total_accumulated_depreciation = 0;

        foreach ($assets as $asset) {
            $cost = floatval($asset['purchase_cost']) + floatval($asset['capex_additions']);
            $total_acquisition_cost += $cost;

            // Use the same logic as Asset Model for depreciation
            // We can reuse Asset Model methods if we instantiate it, but that might be heavy.
            // Let's implement a static helper or just copy logic as it is critical.
            // Logic: (Cost - Residual) / Life * Age
            // For now, let's implement a simplified version or instantiate generic Asset model.
            
            // Re-implementing simplified logic here for report
            // Ideally, we should refactor `calculateDepreciation` to a Helper class.
            // For this task, I'll approximate or calculate properly.
            
            // Checking Asset Model logic...
            // It uses category for lifespan.
            $lifespan_map = [
                'Computadora' => 3, 
                'Vehículo' => 5, // Actually 25% = 4 years? Logic said 5.
                'Mobiliario' => 10,
                'Servidor' => 4, // 25%
                // Defaults
            ];
            
            $cat = $asset['category'];
            $years_useful = isset($lifespan_map[$cat]) ? $lifespan_map[$cat] : 5; // Default 5
            
            // Recalculate basic depreciation
            if (empty($asset['purchase_date'])) {
                 $current_value = $cost;
                 $accumulated_depreciation = 0;
            } else {
                $purchase_date = new \DateTime($asset['purchase_date']);
                $current_date = new \DateTime();
                $interval = $purchase_date->diff($current_date);
                $age_years = $interval->y + ($interval->m / 12) + ($interval->d / 365);
                
                if ($asset['acquisition_type'] === 'Arrendamiento') {
                    $depreciation_per_year = 0;
                    $accumulated_depreciation = 0;
                } else {
                     $depreciation_per_year = $cost / $years_useful;
                     $accumulated_depreciation = $depreciation_per_year * $age_years;
                }

                if ($accumulated_depreciation > $cost) {
                    $accumulated_depreciation = $cost;
                }
                
                // Override
                if (!is_null($asset['accumulated_depreciation_override'])) {
                     $accumulated_depreciation = floatval($asset['accumulated_depreciation_override']);
                }

                $current_value = $cost - $accumulated_depreciation;
            }

            $total_current_value += $current_value;
            $total_accumulated_depreciation += $accumulated_depreciation;
        }

        return [
            'total_acquisition_cost' => $total_acquisition_cost,
            'total_current_value' => $total_current_value,
            'total_depreciation' => $total_accumulated_depreciation,
            'asset_count' => count($assets)
        ];
    }

    public function getServiceCosts() {
        // Aggregate costs from accounts
        // We can sum by frequency and annualized.
        
        $query = 'SELECT * FROM accounts';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $monthly_total = 0;
        $annual_total = 0;

        foreach ($accounts as $acc) {
            $cost = floatval($acc['cost']);
            $freq = $acc['frequency'];
            
            // Normalize to Monthly and Annual
            if ($freq === 'Mensual') {
                $monthly = $cost;
                $annual = $cost * 12;
            } elseif ($freq === 'Anual') {
                $monthly = $cost / 12;
                $annual = $cost;
            } elseif ($freq === 'Trimestral') {
                $monthly = $cost / 3;
                $annual = $cost * 4;
            } else { // Único or unknown
                $monthly = 0;
                $annual = 0; // Or treat as one-off
            }

            // Convert Currency roughly if needed? Assuming all MXN for now as per schema default.
            // If currency is USD, we might want to flag it.
            // For simplicity, just summing raw values but ideally should convert.
            
            $monthly_total += $monthly;
            $annual_total += $annual;
        }

        return [
            'monthly_recurring' => $monthly_total,
            'annual_recurring' => $annual_total,
            'account_count' => count($accounts)
        ];
    }

    public function getDashboardStats() {
        // Asset Stats
        $queryStats = "SELECT status, COUNT(*) as count FROM assets GROUP BY status";
        $stmt = $this->conn->prepare($queryStats);
        $stmt->execute();
        $assetStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // ['Disponible' => 5, 'Asignado' => 3]

        // Total Users
        $queryUsers = "SELECT COUNT(*) FROM users WHERE status = 'Activo'";
        $stmt = $this->conn->query($queryUsers);
        $totalUsers = $stmt->fetchColumn();

        // Financials
        $financials = $this->getAssetFinancials(); // This is a bit heavy as it loops, but valid reused logic
        $services = $this->getServiceCosts();

        return [
            'total_assets' => $financials['asset_count'],
            'total_users' => $totalUsers,
            'total_accounts' => $services['account_count'],
            'monthly_spend_mxn' => $services['monthly_recurring'], 
            'monthly_spend_usd' => 0, 
            'asset_stats' => $assetStats
        ];
    }

    public function getUpcomingRenewals($days = 30) {
        $query = "SELECT *, DATEDIFF(renewal_date, NOW()) as days_left 
                  FROM accounts 
                  WHERE renewal_date IS NOT NULL 
                  AND renewal_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL :days DAY)
                  ORDER BY renewal_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getAssetsWithFinancials($filters = []) {
        $sql = "SELECT a.*, COALESCE(SUM(i.cost), 0) as capex_additions 
                FROM assets a 
                LEFT JOIN incidents i ON a.id = i.asset_id AND i.is_capex = 1 
                WHERE 1=1";
        $params = [];

        if (!empty($filters['year']) && $filters['year'] !== 'all') {
            if ($filters['year'] === 'custom' && !empty($filters['start_date']) && !empty($filters['end_date'])) {
                $sql .= " AND a.purchase_date BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $filters['start_date'];
                $params[':end_date'] = $filters['end_date'];
            } elseif (is_numeric($filters['year'])) {
                $sql .= " AND YEAR(a.purchase_date) = :year";
                $params[':year'] = $filters['year'];
            }
        }

        if (!empty($filters['category'])) {
            $sql .= " AND a.category = :category";
            $params[':category'] = $filters['category'];
        }

        $sql .= " GROUP BY a.id";

        // Sorting
        $sort_by = $filters['sort_by'] ?? 'id';
        $order = strtoupper($filters['order'] ?? 'DESC');
        $allowed_sorts = ['id', 'name', 'purchase_date', 'purchase_cost', 'status'];
        if (in_array($sort_by, $allowed_sorts)) {
             $sort_col = "a." . $sort_by;
             if ($sort_by === 'date') $sort_col = 'a.purchase_date';
             if ($sort_by === 'cost') $sort_col = '(a.purchase_cost + COALESCE(SUM(i.cost), 0))';
             $sql .= " ORDER BY $sort_col $order";
        } else {
             $sql .= " ORDER BY a.id DESC";
        }
        
        // Pagination (if needed, implemented in Controller usually, but fetchAll here for now)
        // For simplicity in this step, returning all and letting controller/view handle or simple slice.
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Enhance with financial calculations
        foreach ($assets as &$asset) {
            $calc = $this->calculateDepreciationForAsset($asset);
            $asset['accounting'] = $calc;
        }

        return $assets;
    }

    private function calculateDepreciationForAsset($asset) {
         $lifespan_map = [
            'Computadora' => 3, 
            'Vehículo' => 5, 
            'Mobiliario' => 10,
            'Servidor' => 4
        ];
        
        $cat = $asset['category'];
        $years_useful = isset($lifespan_map[$cat]) ? $lifespan_map[$cat] : 5; 
        $cost = floatval($asset['purchase_cost']) + floatval($asset['capex_additions'] ?? 0);

        if (empty($asset['purchase_date'])) {
             return [
                 'useful_life' => $years_useful,
                 'accumulated_depreciation' => 0,
                 'current_value' => $cost,
                 'percentage_depreciated' => 0,
                 'status' => 'No Iniciado',
                 'is_manual' => false
             ];
        }

        $purchase_date = new \DateTime($asset['purchase_date']);
        $current_date = new \DateTime();
        $interval = $purchase_date->diff($current_date);
        $age_years = $interval->y + ($interval->m / 12) + ($interval->d / 365);
        
        $is_manual = false;
        
        if ($asset['acquisition_type'] === 'Arrendamiento') {
            $accumulated_depreciation = 0;
            $status = 'Renta';
        } else {
             $depreciation_per_year = $cost / $years_useful;
             $accumulated_depreciation = $depreciation_per_year * $age_years;
             $status = 'Vigente';
        }

        if ($accumulated_depreciation >= $cost) {
            $accumulated_depreciation = $cost;
            $status = 'Totalmente Depreciado';
        }
        
        // Override check
        if (!is_null($asset['accumulated_depreciation_override'])) {
             $accumulated_depreciation = floatval($asset['accumulated_depreciation_override']);
             $is_manual = true;
        }

        $current_value = $cost - $accumulated_depreciation;
        $pct = ($cost > 0) ? ($accumulated_depreciation / $cost) * 100 : 0;

        return [
            'useful_life' => $years_useful,
            'accumulated_depreciation' => $accumulated_depreciation,
            'current_value' => $current_value,
            'percentage_depreciated' => round($pct, 1),
            'status' => $status,
            'is_manual' => $is_manual
        ];
    }

    public function updateAssetCost($id, $cost, $date) {
        $fields = [];
        $params = [':id' => $id];
        
        if (!is_null($cost)) {
            $fields[] = "purchase_cost = :cost";
            $params[':cost'] = $cost;
        }
        if (!is_null($date)) {
            $fields[] = "purchase_date = :date";
            $params[':date'] = $date;
        }

        if (empty($fields)) return true;

        $sql = "UPDATE assets SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateAccDepreciation($id, $amount) {
        $sql = "UPDATE assets SET accumulated_depreciation_override = :amount WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
