<?php ob_start(); ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Usuarios y Personal</h1>
    <a href="/users/create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow flex items-center">
        <i class="fas fa-plus mr-2"></i> Nuevo Usuario
    </a>
</div>

<!-- BARRA DE HERRAMIENTAS -->
<div class="bg-white p-4 rounded-xl shadow mb-6 border border-gray-200 flex flex-col md:flex-row gap-4">
    <!-- Buscador -->
    <div class="relative flex-1">
        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        <input type="text" id="userSearchInput" placeholder="Buscar por nombre, correo, empresa..." 
               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
               onkeyup="filterUsers()">
    </div>

    <!-- Filtro Estado -->
    <div class="w-full md:w-64">
        <div class="relative">
            <i class="fas fa-filter absolute left-3 top-3 text-gray-400"></i>
            <select id="userStatusFilter" onchange="filterUsers()" class="w-full pl-10 pr-8 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-700 appearance-none">
                <option value="">Todos los Estados</option>
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo / Baja</option>
                <option value="Vacaciones">Vacaciones</option>
                <option value="Incapacidad">Incapacidad</option>
            </select>
            <i class="fas fa-chevron-down absolute right-3 top-3 text-gray-400 pointer-events-none"></i>
        </div>
    </div>
</div>

<?php
$headers = [
    'date' => ['label' => 'Fecha Registro', 'sortType' => 'date', 'align' => 'center'],
    'name' => 'Nombre / Contacto',
    'org' => 'Organización',
    'role' => 'Rol',
    'status' => 'Estado'
];

$tableData = [];
if (!empty($users)) {
    foreach ($users as $u) {
        $nameHtml = '<div class="font-bold text-gray-800 capitalize"><a href="/users/detail/' . htmlspecialchars($u['id']) . '" class="hover:text-blue-600 transition">';
        if (!empty($u['first_name']) && !empty($u['last_name'])) {
            $nameHtml .= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']);
        } else {
            $nameHtml .= htmlspecialchars($u['name'] ?? '');
        }
        $nameHtml .= '</a></div><div class="text-xs text-gray-500">' . htmlspecialchars($u['email'] ?? '') . '</div>';
        if (!empty($u['phone'])) {
            $nameHtml .= '<div class="text-xs text-gray-400 mt-0.5"><i class="fas fa-phone mr-1"></i>' . htmlspecialchars($u['phone']) . '</div>';
        }
        
        $orgHtml = '<div class="text-sm font-medium text-gray-700">' . htmlspecialchars($u['company'] ?: '-') . '</div><div class="text-xs text-gray-500">' . htmlspecialchars($u['department'] ?: '-') . '</div>';
        
        $roleHtml = '<span class="px-2 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">' . htmlspecialchars($u['system_role'] ?: 'N/A') . '</span>';
        
        $status = $u['status'];
        $st_class = 'bg-gray-100';
        if ($status == 'Activo') $st_class = 'bg-green-100 text-green-700';
        elseif ($status == 'Inactivo') $st_class = 'bg-red-100 text-red-700';
        elseif ($status == 'Vacaciones') $st_class = 'bg-yellow-100 text-yellow-700';
        
        $statusHtml = '<span class="px-3 py-1 rounded-full text-xs font-bold inline-flex items-center ' . $st_class . '">' . htmlspecialchars($status) . '</span>';

        $tableData[] = [
            'id' => $u['id'],
            '_rowClass' => 'user-row',
            '_rowAttrs' => 'data-status="' . htmlspecialchars($u['status']) . '"',
            
            '_tdAttrs_date' => 'data-raw="' . htmlspecialchars($u['created_at'] ?? '') . '"',
            'date' => '<div class="text-xs text-gray-500">' . htmlspecialchars(date('d/m/Y', strtotime($u['created_at'] ?? 'now'))) . '</div>',
            
            'name' => $nameHtml,
            'org' => $orgHtml,
            'role' => $roleHtml,
            'status' => $statusHtml
        ];
    }
}

$actions = [
    [
        'url' => '/users/detail/{id}',
        'icon' => 'fas fa-eye',
        'colorClass' => 'text-blue-600',
        'bgHover' => 'hover:bg-blue-50',
        'border' => 'border-blue-200',
        'title' => 'Ver Detalle'
    ],
    [
        'url' => '/users/edit/{id}',
        'icon' => 'fas fa-pen',
        'colorClass' => 'text-yellow-600',
        'bgHover' => 'hover:bg-yellow-50',
        'border' => 'border-yellow-200',
        'title' => 'Editar'
    ],
    [
        'url' => '/users/delete/{id}',
        'icon' => 'fas fa-trash-alt',
        'colorClass' => 'text-red-600',
        'bgHover' => 'hover:bg-red-50',
        'border' => 'border-red-200',
        'title' => 'Eliminar',
        'confirm' => '¿Estás seguro?'
    ]
];

echo Utils::generateTable($headers, $tableData, "No se encontraron usuarios.", $actions, 'usersTable');
?>

<script src="/assets/js/table-sort.js"></script>
<script>
    // --- LÓGICA DE FILTRADO ---
    function filterUsers() {
        const textFilter = document.getElementById('userSearchInput').value.toUpperCase();
        const statusFilter = document.getElementById('userStatusFilter').value;
        const rows = document.querySelectorAll('.user-row');

        rows.forEach(row => {
            const status = row.getAttribute('data-status');
            let textMatch = row.innerText.toUpperCase().includes(textFilter);
            let statusMatch = (statusFilter === "" || status === statusFilter);
            row.style.display = (textMatch && statusMatch) ? "" : "none";
        });
    }
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
