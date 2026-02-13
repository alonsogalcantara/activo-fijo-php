<?php
namespace Controllers;

require_once __DIR__ . '/../Models/Accounting.php';

use Models\Accounting;

class DashboardController {
    
    public function index() {
        $accountingModel = new Accounting();
        
        $data = $accountingModel->getDashboardStats();
        $data['renewals'] = $accountingModel->getUpcomingRenewals(30);
        $data['recent_activity'] = []; // Placeholder as we don't have Audit Log yet

        require_once __DIR__ . '/../Views/dashboard.php';
    }
}
