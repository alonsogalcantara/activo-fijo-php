<?php
namespace Controllers;

require_once __DIR__ . '/../Models/Accounting.php';

use Models\Accounting;

class DashboardController {
    
    public function index() {
        $accountingModel = new Accounting();
        
        $data = $accountingModel->getDashboardStats();
        $data['renewals'] = $accountingModel->getUpcomingRenewals(30);
        
        require_once __DIR__ . '/../Models/AuditLog.php';
        $auditModel = new \Models\AuditLog();
        $logs = $auditModel->getAll(6);
        
        // Format for dashboard
        $formatted_activity = [];
        foreach ($logs as $log) {
            $actor = $log['actor_username'] ?? 'Sistema';
            $details = "Modificó un registro en " . $log['table_name'];
            if ($log['action'] == 'CREATE') $details = "Creó un nuevo registro en " . $log['table_name'];
            if ($log['action'] == 'DELETE') $details = "Eliminó un registro de " . $log['table_name'];
            
            $formatted_activity[] = [
                'action' => $actor,
                'details' => $details,
                'created_at' => date('d/m/Y H:i', strtotime($log['created_at'] ?? 'now'))
            ];
        }
        $data['recent_activity'] = $formatted_activity;

        require_once __DIR__ . '/../Views/dashboard.php';
    }
}
