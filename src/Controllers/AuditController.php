<?php
namespace Controllers;

require_once __DIR__ . '/../Models/AuditLog.php';

use Models\AuditLog;

class AuditController {
    
    public function index() {
        $auditModel = new AuditLog();
        try {
            $logs = $auditModel->getAll();
        } catch (\PDOException $e) {
            // Table might not exist
            $logs = []; 
            $error = "Could not fetch logs. Error: " . $e->getMessage();
        }
        
        // Transform logs for view if needed
        foreach ($logs as &$log) {
            // Example transformation or formatting
            $log['timestamp'] = date('d/m/Y H:i', strtotime($log['created_at']));
        }

        require_once __DIR__ . '/../Views/audit/index.php';
    }
}
