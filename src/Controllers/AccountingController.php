<?php
namespace Controllers;

require_once __DIR__ . '/../Models/Accounting.php';

use Models\Accounting;

class AccountingController {
    
    public function index() {
        $accountingModel = new Accounting();
        
        // Handle Filters
        $filters = [
            'year' => $_GET['year'] ?? 'all',
            'category' => $_GET['category'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
            'sort_by' => $_GET['sort_by'] ?? 'id',
            'order' => $_GET['order'] ?? 'desc'
        ];

        // Assets List
        $assets = $accountingModel->getAssetsWithFinancials($filters);
        
        // Paginator (Simple implementation)
        $per_page = 20;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $total = count($assets);
        $total_pages = ceil($total / $per_page);
        $offset = ($page - 1) * $per_page;
        $paged_assets = array_slice($assets, $offset, $per_page);
        
        $pagination = [
            'page' => $page,
            'per_page' => $per_page,
            'total' => $total,
            'total_pages' => $total_pages
        ];
        
        // KPIs (Recalculate based on visible assets?) 
        // For the view's top KPI cards, usually we want the TOTAL of the current filter, not just the page.
        // We can pass the full stats or let the view calculate via JS (as done in the example).
        // But for "Net Book Value" global, we should pass server side data.
        
        // Let's reuse getDashboardStats for global context or calculate from filtered $assets.
        $total_cost = 0;
        $total_depr = 0;
        $total_net = 0;
        foreach ($assets as $a) {
             $total_cost += floatval($a['purchase_cost']);
             $total_depr += floatval($a['accounting']['accumulated_depreciation']);
             $total_net += floatval($a['accounting']['current_value']);
        }
        
        $kpis = [
            'gross_investment' => $total_cost,
            'accumulated_depreciation' => $total_depr,
            'net_book_value' => $total_net
        ];

        require_once __DIR__ . '/../Views/accounting/index.php';
    }

    public function update_ajax() {
        header('Content-Type: application/json');
        
        // Auth check (simple session check)
        // if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            exit;
        }

        $accountingModel = new Accounting();
        
        // The example JS logic sends individual requests:
        // PUT /api/assets/{id} -> update cost/date
        // PUT /api/assets/{id}/accounting -> update depreciation
        // Here we need to parse the URI or handle a unified endpoint.
        // Since we are not using a router that supports RESTful parameters deeply in this simple index.php,
        // we might need to rely on GET param or input body for ID.
        // Let's assume the router in public/index.php sends requests here.
        
        // Check how routing handles this. We might need to update public/index.php to route /accounting/update_ajax.
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
             http_response_code(400);
             echo json_encode(['error' => 'Missing ID from query params']);
             exit;
        }

        if (isset($input['purchase_cost']) || isset($input['purchase_date'])) {
            $cost = $input['purchase_cost'] ?? null;
            $date = $input['purchase_date'] ?? null;
            $accountingModel->updateAssetCost($id, $cost, $date);
        }

        if (isset($input['accumulated_depreciation'])) {
            $accountingModel->updateAccDepreciation($id, $input['accumulated_depreciation']);
        }

        echo json_encode(['success' => true]);
        exit;
    }

    public function forecast_ajax() {
        header('Content-Type: application/json');
        
        // Simple linear forecast for demo
        $months = [];
        $totals = [];
        $current_date = new \DateTime();
        
        for ($i = 0; $i < 12; $i++) {
            $months[] = $current_date->format('M Y');
            $totals[] = rand(1000, 5000); // Placeholder data as real forecast is complex
            $current_date->modify('+1 month');
        }

        echo json_encode([
            'months' => $months,
            'totals' => $totals
        ]);
        exit;
    }
}
