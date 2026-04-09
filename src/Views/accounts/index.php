<?php ob_start(); ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Servicios y Suscripciones</h1>
    <a href="/accounts/create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow flex items-center">
        <i class="fas fa-plus mr-2"></i> Nuevo Servicio
    </a>
</div>

<!-- BARRA DE HERRAMIENTAS DE BÚSQUEDA Y FILTRO -->
<div class="bg-white p-4 rounded-xl shadow mb-6 border border-gray-200 flex flex-col md:flex-row gap-4">
    <!-- Buscador de Texto -->
    <div class="relative flex-1">
        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        <input type="text" id="accountSearchInput" placeholder="Buscar por servicio, proveedor, contrato..." 
               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
               onkeyup="filterAccounts()">
    </div>
    
    <!-- Filtro por Tipo -->
    <div class="w-full md:w-64">
        <div class="relative">
            <i class="fas fa-filter absolute left-3 top-3 text-gray-400"></i>
            <select id="accountTypeFilter" onchange="filterAccounts()" 
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
                <option value="">Todos los Tipos</option>
                <option value="Individual">Individual</option>
                <option value="Familiar">Familiar / Grupal</option>
                <option value="Empresarial">Empresarial</option>
            </select>
            <i class="fas fa-chevron-down absolute right-3 top-3 text-gray-400 pointer-events-none"></i>
        </div>
    </div>
</div>

<?php
$headers = [
    'date' => ['label' => 'Fecha Registro', 'sortType' => 'date', 'align' => 'center'],
    'service' => 'Servicio',
    'provider' => 'Proveedor / Contrato',
    'assigned' => 'Asignado A',
    'type' => ['label' => 'Tipo', 'align' => 'center'],
    'cost' => ['label' => 'Costo', 'sortType' => 'number', 'align' => 'center'],
    'renewal' => ['label' => 'Renovación', 'sortType' => 'date', 'align' => 'center']
];

$tableData = [];
if (!empty($accounts)) {
    foreach ($accounts as $acc) {
        $assignedHtml = '';
        if (!empty($acc['assigned_user_name'])) {
            $assignedHtml = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><i class="fas fa-user mr-1"></i> ' . htmlspecialchars($acc['assigned_user_name']) . '</span>';
        } else {
            $assignedHtml = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"><i class="fas fa-warehouse mr-1"></i> Stock / Admin</span>';
        }

        $typeHtml = '';
        if ($acc['account_type'] == 'Individual') {
            $typeHtml = '<span class="text-xs font-bold text-gray-600 bg-gray-200 px-2 py-1 rounded"><i class="fas fa-user"></i> Indiv.</span>';
        } else {
            $typeHtml = '<span class="text-xs font-bold text-purple-600 bg-purple-100 px-2 py-1 rounded" title="Licencias"><i class="fas fa-users"></i> ' . htmlspecialchars($acc['max_licenses']) . ' (Max)</span>';
        }

        $tableData[] = [
            'id' => $acc['id'],
            '_rowClass' => 'account-row',
            '_rowAttrs' => 'data-type="' . htmlspecialchars($acc['account_type']) . '"',
            
            '_tdClass_date' => 'text-sm text-gray-600',
            '_tdAttrs_date' => 'data-raw="' . htmlspecialchars($acc['created_at'] ?? '') . '"',
            'date' => htmlspecialchars(date('d/m/Y', strtotime($acc['created_at'] ?? 'now'))),
            
            'service' => '<div class="font-bold text-gray-800"><a href="/accounts/detail/' . htmlspecialchars($acc['id']) . '" class="hover:text-blue-600 transition">' . htmlspecialchars($acc['service_name']) . '</a></div><div class="text-xs text-gray-500">' . htmlspecialchars($acc['username'] ?: 'Sin usuario') . '</div>',
            
            'provider' => '<div class="text-sm text-gray-700">' . htmlspecialchars($acc['provider'] ?: '-') . '</div><div class="text-xs text-gray-400 font-mono">' . htmlspecialchars($acc['contract_ref'] ?: '') . '</div>',
            
            'assigned' => $assignedHtml,
            
            'type' => $typeHtml,
            
            'cost' => '<div class="font-bold text-gray-700">$' . number_format($acc['cost'], 2) . ' ' . htmlspecialchars($acc['currency'] ?? 'MXN') . '</div><div class="text-[10px] uppercase text-gray-400">' . htmlspecialchars($acc['frequency'] ?? '') . '</div>',
            
            '_tdClass_renewal' => 'text-sm text-gray-600',
            '_tdAttrs_renewal' => 'data-raw="' . htmlspecialchars($acc['renewal_date'] ?? '') . '"',
            'renewal' => htmlspecialchars($acc['renewal_date'] ?? '')
        ];
    }
}

$actions = [
    [
        'url' => '/accounts/detail/{id}',
        'icon' => 'fas fa-eye',
        'colorClass' => 'text-blue-600',
        'bgHover' => 'hover:bg-blue-50',
        'border' => 'border-blue-200',
        'title' => 'Ver Detalle'
    ],
    [
        'url' => '/accounts/edit/{id}',
        'icon' => 'fas fa-pen',
        'colorClass' => 'text-blue-600',
        'bgHover' => 'hover:bg-blue-50',
        'border' => 'border-blue-200',
        'title' => 'Editar'
    ],
    [
        'url' => '/accounts/delete/{id}',
        'icon' => 'fas fa-trash-alt',
        'colorClass' => 'text-red-600',
        'bgHover' => 'hover:bg-red-50',
        'border' => 'border-red-200',
        'title' => 'Eliminar',
        'confirm' => '¿Estás seguro?'
    ]
];

echo Utils::generateTable($headers, $tableData, "No hay servicios registrados. ¡Agrega el primero!", $actions, 'accountsTable');
?>

<script>
    // --- LÓGICA DE FILTRADO ---
    function filterAccounts() {
        const textInput = document.getElementById('accountSearchInput');
        const typeSelect = document.getElementById('accountTypeFilter');
        const rows = document.querySelectorAll('.account-row');

        const textFilter = textInput.value.toUpperCase();
        const typeFilter = typeSelect.value;

        rows.forEach(row => {
            const rowType = row.getAttribute('data-type');
            let textMatch = false;
            
            // Simple text match on whole row content
            if (row.innerText.toUpperCase().indexOf(textFilter) > -1) {
                textMatch = true;
            }

            let typeMatch = (typeFilter === "" || rowType === typeFilter);

            if (textMatch && typeMatch) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }
</script>
<script src="/assets/js/table-sort.js"></script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
