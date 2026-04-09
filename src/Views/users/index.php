<?php ob_start(); ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Usuarios y Personal</h1>
    <a href="/users/create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow flex items-center">
        <i class="fas fa-plus mr-2"></i> Nuevo Usuario
    </a>
</div>

<!-- BARRA DE BÚSQUEDA Y ORDENAMIENTO -->
<?php 
renderSearchBar([
    'table_id' => 'usersTable', 
    'placeholder' => 'Buscar por nombre, correo, empresa...'
]); 
?>

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



<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
