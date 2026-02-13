<?php
namespace Controllers;

require_once __DIR__ . '/../Lib/fpdf.php';
require_once __DIR__ . '/../Models/Accounting.php';
require_once __DIR__ . '/../Models/Asset.php';
require_once __DIR__ . '/../Models/User.php';

use Models\Accounting;
use Models\Asset;
use Models\User;
use FPDF;

/**
 * Helper Functions
 */
function clean_text($text) {
    if ($text === null) return "";
    $converted = @iconv('UTF-8', 'windows-1252//TRANSLIT', $text);
    return $converted === false ? $text : $converted;
}

function get_asset_details_string($asset) {
    $cat = $asset['category'] ?? '';
    $details = [];
    
    $name = $asset['name'] ?? $asset['asset_name'] ?? null;
    if ($name) {
        $details[] = $name;
    } else {
        $brand = $asset['brand'] ?? '';
        $model =  $asset['model'] ?? '';
        if ($brand || $model) {
            $details[] = trim("$brand $model");
        } else {
            $details[] = "Activo Sin Nombre";
        }
    }
    
    // Category-specific details
    if ($cat === 'Vehículo') {
        if (!empty($asset['brand'])) $details[] = $asset['brand'];
        if (!empty($asset['model'])) $details[] = $asset['model'];
        if (!empty($asset['vehicle_year'])) $details[] = "Año: " . $asset['vehicle_year'];
        if (!empty($asset['license_plate'])) $details[] = "Placa: " . $asset['license_plate'];
        if (!empty($asset['mileage'])) $details[] = "KM: " . $asset['mileage'];
    } elseif (in_array($cat, ['Computadora', 'Laptop', 'Celular'])) {
        if (!empty($asset['brand'])) $details[] = $asset['brand'];
        if (!empty($asset['model'])) $details[] = $asset['model'];
        if (!empty($asset['processor'])) $details[] = "Proc: " . $asset['processor'];
        if (!empty($asset['ram'])) $details[] = "RAM: " . $asset['ram'];
        if (!empty($asset['storage'])) $details[] = "HDD: " . $asset['storage'];
    } elseif ($cat === 'Uniforme') {
        if (!empty($asset['size'])) $details[] = "Talla: " . $asset['size'];
        if (!empty($asset['gender_cut'])) $details[] = "Corte: " . $asset['gender_cut'];
        if (!empty($asset['color'])) $details[] = "Color: " . $asset['color'];
    } elseif ($cat === 'Mobiliario') {
        if (!empty($asset['dimensions'])) $details[] = "Dim: " . $asset['dimensions'];
        if (!empty($asset['material'])) $details[] = "Mat: " . $asset['material'];
    } else {
        if (!empty($asset['brand'])) $details[] = $asset['brand'];
        if (!empty($asset['model'])) $details[] = $asset['model'];
        if (!empty($asset['description'])) $details[] = substr($asset['description'], 0, 30);
    }
    
    return clean_text(implode(" | ", $details));
}

/**
 * PDFReport Base Class
 */
class PDFReport extends FPDF {
    protected $title_text;
    protected $folio_prefix;
    protected $collaborator_name;
    protected $fiscal_year_info;
    public $folio;  // Public so it can be accessed for filename generation
    protected $print_time;

    public function __construct($title_text, $folio_prefix="DOC", $collaborator_name=null, $fiscal_year_info=null) {
        parent::__construct();
        $this->title_text = clean_text($title_text);
        $this->folio_prefix = $folio_prefix;
        $this->collaborator_name = clean_text($collaborator_name);
        $this->fiscal_year_info = clean_text($fiscal_year_info);
        
        $now = new \DateTime();
        $timestamp = time();
        $this->folio = sprintf("%s-%s-%05d", $folio_prefix, $now->format('ym'), $timestamp % 100000);
        $this->print_time = $now->format('d/m/Y H:i');
    }

    function Header() {
        // Print time
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, clean_text("Impreso el: " . $this->print_time), 0, 1, 'L');

        // Fiscal year info (optional)
        $title_y_pos = 15;
        if ($this->fiscal_year_info) {
            $this->SetFont('Arial', 'B', 8);
            $this->SetTextColor(0, 50, 100);
            $this->Cell(0, 5, $this->fiscal_year_info, 0, 1, 'L');
            $title_y_pos = 22;
        }

        // Title
        $this->SetY($title_y_pos);
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, $this->title_text, 0, 1, 'C');

        // Folio
        $this->SetY(10);
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(180, 0, 0);
        $this->Cell(0, 10, "Folio: " . $this->folio, 0, 1, 'R');

        // Separator line
        $line_y = $title_y_pos + 12;
        $this->SetDrawColor(200, 200, 200);
        $page_width = $this->GetPageWidth() - 20;
        $this->Line(10, $line_y, 10 + $page_width, $line_y);
        $this->SetY($line_y + 5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, clean_text('Página ' . $this->PageNo() . '/{nb}'), 0, 0, 'R');
    }

    function try_load_image($width=6) {
        $candidates = [
            __DIR__ . '/../Lib/svg/jupiter-symbol.png',
            __DIR__ . '/../Lib/svg/jupiter-symbol.svg',
            __DIR__ . '/../../public/images/logo.png'
        ];

        $page_width = $this->GetPageWidth();
        $x_pos = ($page_width - $width) / 2;

        foreach ($candidates as $ruta) {
            if (file_exists($ruta)) {
                try {
                    $this->Image($ruta, $x_pos, $this->GetY(), $width);
                    return true;
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        
        $this->SetFont("Arial", 'B', 12);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, "<< JUPITER >>", 0, 1, 'C');
    }

    function add_signatures() {
        $footer_margin = 55;
        if ($this->GetY() > ($this->GetPageHeight() - 60)) {
            $this->AddPage();
        }

        $this->SetY(-$footer_margin);
        $this->SetFont("Arial", '', 10);
        $this->SetTextColor(0, 0, 0);

        $page_width = $this->GetPageWidth();
        $center = $page_width / 2;
        $col_width = 70;
        $gap = 20;

        $x1 = $center - $col_width - ($gap/2);
        $x2 = $center + ($gap/2);

        $y_lines = $this->GetY();
        $this->Line($x1, $y_lines, $x1 + $col_width, $y_lines);
        $this->Line($x2, $y_lines, $x2 + $col_width, $y_lines);

        $this->Ln(2);
        $this->SetFont("Arial", 'B', 9);

        // Logic for labels based on Folio Prefix
        $label_left = "Firma de Revisión";
        $label_right = "Responsable Contable";

        if (strpos($this->folio, "RES-") !== false) {
            $label_left = "Recibí de Conformidad";
            $label_right = "Autorizó / Entregó";
        } elseif (strpos($this->folio, "HIS-") !== false) {
            $label_left = "Usuario Asignado";
            $label_right = "Validación Inventario";
        }

        $current_y = $this->GetY();
        $this->SetXY($x1, $current_y);
        $this->Cell($col_width, 5, clean_text($label_left), 0, 0, 'C');

        $this->SetXY($x2, $current_y);
        $this->Cell($col_width, 5, clean_text($label_right), 0, 1, 'C');

        $this->SetFont("Arial", '', 8);
        $collab_display = $this->collaborator_name ? $this->collaborator_name : "Administración";

        $this->SetXY($x1, $this->GetY());
        $collab_display = $this->collaborator_name ? $this->collaborator_name : "Administración";
        $this->Cell($col_width, 5, substr($collab_display, 0, 35), 0, 0, 'C');

        $this->SetXY($x2, $this->GetY());
        $this->Cell($col_width, 5, clean_text("Administración / Finanzas"), 0, 1, 'C');

        $this->Ln(5);
        $this->SetFont("Arial", 'I', 7);
        $this->SetTextColor(100, 100, 100);
        $msg = "Este documento es oficial y valida la asignación o estado del activo. Cualquier inconsistencia debe reportarse.";
        $this->MultiCell(0, 4, clean_text($msg), 0, 'C');

        $this->SetY(-25);
        $this->try_load_image(10);
    }
}

/**
 * Main ReportsController Class
 */
class ReportsController {

    /**
     * Generate Accounting Depreciation PDF Report
     */
    public function accounting_pdf() {
        $accountingModel = new Accounting();
        
        $filters = [
            'year' => $_GET['year'] ?? 'all',
            'category' => $_GET['category'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
            'sort_by' => $_GET['sort_by'] ?? 'id',
            'order' => $_GET['order'] ?? 'desc'
        ];
        
        $assets = $accountingModel->getAssetsWithFinancials($filters);
        $fiscal_year_label = $filters['year'] !== 'all' ? "Filtro: " . $filters['year'] : null;

        $pdf = new PDFReport(
            "Reporte de Depreciación de Activos Fijos", 
            "ACC", 
            "Gerencia Administrativa",
            $fiscal_year_label
        );
        
        $pdf->AliasNbPages();
        $pdf->AddPage('L'); // Landscape
        $pdf->SetAutoPageBreak(true, 20);

        // A4 Landscape width ~297mm. Margins ~20mm total. Usable ~277mm.
        // Total: 90+25+22+30+12+30+12+30+25 = 276mm (Perfect fit)
        $w_name = 90;
        $w_cc = 25;
        $w_date = 22;
        $w_cost = 30;
        $w_life = 12;
        $w_depr = 30;
        $w_perc = 12;
        $w_curr = 30;
        $w_stat = 25;

        $headers = [
            ["Activo / Descripción", $w_name, 'L'],
            ["C. Costos", $w_cc, 'C'],
            ["F. Adq.", $w_date, 'C'],
            ["MOI (Costo)", $w_cost, 'R'],
            ["Vida", $w_life, 'C'],
            ["Depr. Acum.", $w_depr, 'R'],
            ["%", $w_perc, 'C'],
            ["V. Libros", $w_curr, 'R'],
            ["Estado", $w_stat, 'C']
        ];

        // Header row
        $pdf->SetFont("Arial", 'B', 9);
        $pdf->SetFillColor(220, 230, 241);
        $pdf->SetTextColor(0, 0, 0);

        foreach ($headers as $h) {
            $pdf->Cell($h[1], 8, clean_text($h[0]), 1, 0, $h[2], true);
        }
        $pdf->Ln();

        // Data rows
        $pdf->SetFont("Arial", '', 8);
        $total_cost = 0;
        $total_depr = 0;
        $total_value = 0;

        foreach ($assets as $a) {
            $acc = $a['accounting'];
            
            $cost = floatval($a['purchase_cost'] ?: 0);
            $depr = floatval($acc['accumulated_depreciation'] ?: 0);
            $curr = floatval($acc['current_value'] ?: 0);

            $total_cost += $cost;
            $total_depr += $depr;
            $total_value += $curr;

            $name_txt = substr($a['name'] ?: "Sin Nombre", 0, 55);
            $date_txt = substr($a['purchase_date'] ?: '-', 0, 10);
            $cost_txt = number_format($cost, 2);
            $depr_txt = number_format($depr, 2);
            $curr_txt = number_format($curr, 2);
            $perc_txt = $acc['percentage_depreciated'] . '%';
            $stat_txt = $acc['status'];
            $cc_txt = substr($a['cost_center'] ?: '-', 0, 15);

            $pdf->Cell($w_name, 7, clean_text($name_txt), 1);
            $pdf->Cell($w_cc, 7, clean_text($cc_txt), 1, 0, 'C');
            $pdf->Cell($w_date, 7, $date_txt, 1, 0, 'C');
            $pdf->Cell($w_cost, 7, $cost_txt, 1, 0, 'R');
            $pdf->Cell($w_life, 7, $acc['useful_life'], 1, 0, 'C');
            
            $pdf->SetTextColor(180, 0, 0);
            $pdf->Cell($w_depr, 7, $depr_txt, 1, 0, 'R');
            $pdf->SetTextColor(0, 0, 0);

            $pdf->Cell($w_perc, 7, $perc_txt, 1, 0, 'C');
            $pdf->Cell($w_curr, 7, $curr_txt, 1, 0, 'R');

            $pdf->SetFont("Arial", '', 7);
            $pdf->Cell($w_stat, 7, clean_text($stat_txt), 1, 1, 'C');
            $pdf->SetFont("Arial", '', 8);
        }

        // Totals row
        $pdf->Ln(2);
        $pdf->SetFont("Arial", 'B', 9);
        $pdf->SetFillColor(240, 240, 240);

        $pdf->Cell($w_name + $w_cc + $w_date, 8, "TOTALES GENERALES", 1, 0, 'R', true);
        $pdf->Cell($w_cost, 8, number_format($total_cost, 2), 1, 0, 'R', true);
        $pdf->Cell($w_life, 8, "", 1, 0, 'C', true);
        
        $pdf->SetTextColor(180, 0, 0);
        $pdf->Cell($w_depr, 8, number_format($total_depr, 2), 1, 0, 'R', true);
        $pdf->SetTextColor(0, 0, 0);
        
        $pdf->Cell($w_perc, 8, "", 1, 0, 'C', true);
        
        $pdf->SetTextColor(0, 100, 0);
        $pdf->Cell($w_curr, 8, number_format($total_value, 2), 1, 0, 'R', true);
        $pdf->SetTextColor(0, 0, 0);
        
        $pdf->Cell($w_stat, 8, "", 1, 1, 'C', true);

        // Add signatures
        $pdf->Ln(10);
        $pdf->add_signatures();

        $pdf->Output('I', "Contabilidad_Activos_{$pdf->folio}.pdf");
    }

    /**
     * Generate Accounting Depreciation Excel Report
     */
    public function accounting_excel() {
        $accountingModel = new Accounting();
        $filters = [
            'year' => $_GET['year'] ?? 'all',
            'category' => $_GET['category'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
            'sort_by' => $_GET['sort_by'] ?? 'id',
            'order' => $_GET['order'] ?? 'desc'
        ];
        $assets = $accountingModel->getAssetsWithFinancials($filters);

        $now_str = date('Ymd_His');
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=Reporte_Contable_{$now_str}.xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Simple HTML Table for Excel
        echo "<table border='1'>";
        echo "<thead><tr style='background-color:#DCE6F1; font-weight:bold;'>";
        echo "<th>ID</th><th>Activo</th><th>Categoría</th><th>Centro Costos</th><th>F. Adquisición</th>";
        echo "<th>Costo (MOI)</th><th>Vida Útil</th><th>Depr. Acumulada</th><th>% Depr</th><th>Valor Libros</th><th>Estado</th>";
        echo "</tr></thead><tbody>";

        foreach ($assets as $a) {
            $acc = $a['accounting'];
            echo "<tr>";
            echo "<td>{$a['id']}</td>";
            echo "<td>" . htmlspecialchars($a['name'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($a['category'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($a['cost_center'] ?? '') . "</td>";
            echo "<td>{$a['purchase_date']}</td>";
            echo "<td>" . number_format($a['purchase_cost'], 2) . "</td>";
            echo "<td>{$acc['useful_life']}</td>";
            echo "<td style='color:red'>" . number_format($acc['accumulated_depreciation'], 2) . "</td>";
            echo "<td>{$acc['percentage_depreciated']}%</td>";
            echo "<td style='color:green'>" . number_format($acc['current_value'], 2) . "</td>";
            echo "<td>{$acc['status']}</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    }

    /**
     * Generate Asset Responsibility Letter PDF
     */
    public function responsive_letter_pdf($id) {
        $assetModel = new Asset();
        $userModel = new User();
        
        $asset = $assetModel->getById($id);
        if (!$asset) die("Activo no encontrado");
        
        $assigned_name = "Sin Asignar";
        if ($asset['assigned_to']) {
            $user = $userModel->getUserById($asset['assigned_to']);
            $assigned_name = $user['name'] ?? "Usuario Desconocido";
        }

        $pdf = new PDFReport("Carta Responsiva de Activo", "RES", $assigned_name);
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 20);

        // Configuration Layout (A4 = 210mm width)
        $margin = 10;
        $printable_width = 190;
        $col_gap = 6;
        $col_width = ($printable_width - $col_gap) / 2;
        $label_w = 40;
        $val_w = $col_width - $label_w;
        $line_height = 6;

        // Grid rendering helper
        $render_grid = function($data_list) use ($pdf, $margin, $col_width, $col_gap, $label_w, $val_w, $line_height) {
            for ($i = 0; $i < count($data_list); $i += 2) {
                // Column 1
                if ($i < count($data_list)) {
                    $item = $data_list[$i];
                    $pdf->SetX($margin);
                    $pdf->SetFont("Arial", 'B', 9);
                    $pdf->Cell($label_w, $line_height, clean_text($item[0]), 1, 0, 'L');
                    $pdf->SetFont("Arial", '', 9);
                    $pdf->Cell($val_w, $line_height, substr(clean_text($item[1]), 0, 45), 1, 0, 'L');
                }
                // Column 2
                if ($i + 1 < count($data_list)) {
                    $item = $data_list[$i+1];
                    $pdf->SetX($margin + $col_width + $col_gap);
                    $pdf->SetFont("Arial", 'B', 9);
                    $pdf->Cell($label_w, $line_height, clean_text($item[0]), 1, 0, 'L');
                    $pdf->SetFont("Arial", '', 9);
                    $pdf->Cell($val_w, $line_height, substr(clean_text($item[1]), 0, 45), 1, 0, 'L');
                }
                $pdf->Ln($line_height);
            }
        };

        // 1. GENERAL INFORMATION
        $pdf->SetFont("Arial", 'B', 12);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, clean_text("1. Información General"), 1, 1, 'L', true);
        $pdf->Ln(2);

        $data_general = [
            ["ID Sistema", $asset['id']],
            ["Categoría", $asset['category']],
            ["Marca", $asset['brand'] ?? '-'],
            ["Modelo", $asset['model'] ?? '-'],
            ["Serie / ID", $asset['serial_number'] ?? ($asset['license_plate'] ?? '-')],
            ["Nombre", $asset['name'] ?? "Sin Nombre"],
            ["Centro Costos", $asset['cost_center'] ?? 'No Asignado'],
            ["Ubicación", $asset['location'] ?? 'General']
        ];
        $render_grid($data_general);
        $pdf->Ln(4);

        // RESPONSIBILITY DISCLAIMER
        $pdf->Ln(5);
        $pdf->SetFont("Arial", 'B', 10);
        $pdf->Cell(0, 6, clean_text("DECLARACIÓN DE RESPONSABILIDAD:"), 0, 1, 'L');
        
        $pdf->SetFont("Arial", '', 9);
        $msg = "Por medio de la presente, el colaborador recibe de conformidad el activo descrito, asumiendo la total responsabilidad de su buen uso, custodia y cuidado.\n\nEl activo es propiedad exclusiva de la empresa y deberá ser devuelto en las mismas condiciones al término de la relación laboral.";
        $pdf->MultiCell(0, 5, clean_text($msg), 0, 'J');

        $pdf->Ln(8);
        $pdf->add_signatures();
        $pdf->Output('I', "Carta_Responsiva_{$asset['id']}_{$pdf->folio}.pdf");
    }

    /**
     * Generate User Kardex PDF Report
     */
    public function kardex_pdf($id) {
        $userModel = new User();
        $assetModel = new Asset();

        $user = $userModel->getUserById($id);
        if (!$user) die("Usuario no encontrado");

        $assets = $assetModel->getByUser($id);

        // Pass user name to PDF with fallback
        $user_name = $user['name'];
        if (empty($user_name)) {
            $user_name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        }
        $user_name = !empty($user_name) ? $user_name : ($user['email'] ?? 'Usuario');

        $pdf = new PDFReport("Kardex de Resguardo de Activos", "KDX", $user_name);
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 20);

        // 1. USER DATA
        $pdf->SetFont("Arial", 'B', 12);
        $pdf->SetFillColor(220, 230, 241);
        $pdf->Cell(0, 8, clean_text("1. Datos del Colaborador"), 1, 1, 'L', true);
        
        $pdf->SetFont("Arial", '', 10);
        $pdf->Cell(40, 7, "Nombre:", 1);
        $pdf->Cell(0, 7, clean_text($user_name), 1, 1);
        
        $pdf->Cell(40, 7, "Email:", 1);
        $pdf->Cell(0, 7, clean_text($user['email']), 1, 1);
        $pdf->Ln(5);

        // 2. ASSIGNED ASSETS
        $pdf->SetFont("Arial", 'B', 12);
        $pdf->Cell(0, 8, clean_text("2. Activos Actualmente Asignados"), 1, 1, 'L', true);
        $pdf->Ln(2);

        if (!empty($assets)) {
            $pdf->SetFont("Arial", 'B', 9);
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(20, 7, "ID", 1, 0, 'C', true);
            $pdf->Cell(25, 7, "Categ.", 1, 0, 'C', true);
            $pdf->Cell(80, 7, clean_text("Descripción / Marca / Modelo"), 1, 0, 'L', true);
            $pdf->Cell(35, 7, "Serie / Placa", 1, 0, 'C', true);
            $pdf->Cell(30, 7, "F. Asig.", 1, 1, 'C', true);

            $pdf->SetFont("Arial", '', 8);
            foreach ($assets as $a) {
                $desc = $a['name'] . ' ' . $a['brand'] . ' ' . $a['model'];
                $serial = $a['serial_number'] ?? '-';
                $date = substr($a['assigned_at'] ?? '-', 0, 10);

                $pdf->Cell(20, 7, $a['id'], 1, 0, 'C');
                $pdf->Cell(25, 7, substr($a['category'], 0, 12), 1, 0, 'C');
                $pdf->Cell(80, 7, substr(clean_text($desc), 0, 50), 1, 0, 'L');
                $pdf->Cell(35, 7, substr($serial, 0, 18), 1, 0, 'C');
                $pdf->Cell(30, 7, $date, 1, 1, 'C');
            }
        } else {
            $pdf->SetFont("Arial", 'I', 10);
            $pdf->Cell(0, 10, "El usuario no tiene activos asignados actualmente.", 1, 1, 'C');
        }

        $pdf->Ln(10);
        $pdf->add_signatures();
        $pdf->Output('I', "Kardex_{$id}_{$pdf->folio}.pdf");
    }

    /**
     * Generate Asset History PDF Report  
     */
    public function history_pdf($id) {
        $assetModel = new Asset();
        $asset = $assetModel->getById($id);
        
        if (!$asset) die("Activo no encontrado");
        
        $assigned_name = "Sin Asignar";
        // Get user if assigned
        
        $pdf = new PDFReport("Historial Completo del Activo", "HIS", $assigned_name);
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 20);

        // Configuration Layout
        $margin = 10;
        $printable_width = 190;
        $col_gap = 6;
        $col_width = ($printable_width - $col_gap) / 2;
        $label_w = 40;
        $val_w = $col_width - $label_w;
        $line_height = 6;

        // Grid rendering helper
        $render_grid = function($data_list) use ($pdf, $margin, $col_width, $col_gap, $label_w, $val_w, $line_height) {
            for ($i = 0; $i < count($data_list); $i += 2) {
                if ($i < count($data_list)) {
                    $item = $data_list[$i];
                    $pdf->SetX($margin);
                    $pdf->SetFont("Arial", 'B', 9);
                    $pdf->Cell($label_w, $line_height, clean_text($item[0]), 1, 0, 'L');
                    $pdf->SetFont("Arial", '', 9);
                    $pdf->Cell($val_w, $line_height, substr(clean_text($item[1]), 0, 45), 1, 0, 'L');
                }
                if ($i + 1 < count($data_list)) {
                    $item = $data_list[$i+1];
                    $pdf->SetX($margin + $col_width + $col_gap);
                    $pdf->SetFont("Arial", 'B', 9);
                    $pdf->Cell($label_w, $line_height, clean_text($item[0]), 1, 0, 'L');
                    $pdf->SetFont("Arial", '', 9);
                    $pdf->Cell($val_w, $line_height, substr(clean_text($item[1]), 0, 45), 1, 0, 'L');
                }
                $pdf->Ln($line_height);
            }
        };

        // 1. GENERAL INFORMATION
        $pdf->SetFont("Arial", 'B', 12);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, clean_text("1. Información General"), 1, 1, 'L', true);
        $pdf->Ln(2);

        $data_general = [
            ["ID Sistema", $asset['id']],
            ["Categoría", $asset['category']],
            ["Marca", $asset['brand'] ?? '-'],
            ["Modelo", $asset['model'] ?? '-'],
            ["Serie / ID", $asset['serial_number'] ?? ($asset['license_plate'] ?? '-')],
            ["Nombre", $asset['name'] ?? "Sin Nombre"],
            ["Centro Costos", $asset['cost_center'] ?? 'No Asignado'],
            ["Ubicación", $asset['location'] ?? 'General']
        ];
        $render_grid($data_general);
        $pdf->Ln(4);

        // 2. FINANCIAL STATUS
        $pdf->SetFont("Arial", 'B', 12);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(0, 8, clean_text("2. Estado Financiero"), 1, 1, 'L', true);
        $pdf->Ln(2);

        $data_financial = [
            ["Costo Original", number_format($asset['purchase_cost'] ?? 0, 2)],
            ["Fecha Adquisición", $asset['purchase_date'] ?? '-'],
            ["Estado", $asset['status'] ?? '-'],
        ];
        $render_grid($data_financial);
        $pdf->Ln(4);

        // Future: Add history table from database
        $pdf->SetFont("Arial", 'I', 9);
        $pdf->Cell(0, 8, clean_text("Historial de movimientos pendiente de implementación."), 0, 1, 'C');

        $pdf->Ln(10);
        $pdf->add_signatures();
        $pdf->Output('I', "Historial_{$id}_{$pdf->folio}.pdf");
    }
}
