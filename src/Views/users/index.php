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
            <select id="userStatusFilter" onchange="filterUsers()" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-700">
                <option value="">Todos los Estados</option>
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo / Baja</option>
                <option value="Vacaciones">Vacaciones</option>
                <option value="Incapacidad">Incapacidad</option>
            </select>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse" id="usersTable">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="p-4 text-sm font-semibold tracking-wide text-center cursor-pointer hover:bg-gray-700 transition" onclick="sortTable('usersTable', 0, 'date')">Fecha Registro</th>
                    <th class="p-4 text-sm font-semibold tracking-wide cursor-pointer hover:bg-gray-700 transition" onclick="sortTable('usersTable', 1)">Nombre / Contacto</th>
                    <th class="p-4 text-sm font-semibold tracking-wide cursor-pointer hover:bg-gray-700 transition" onclick="sortTable('usersTable', 2)">Organización</th>
                    <th class="p-4 text-sm font-semibold tracking-wide cursor-pointer hover:bg-gray-700 transition" onclick="sortTable('usersTable', 3)">Rol</th>
                    <th class="p-4 text-sm font-semibold tracking-wide cursor-pointer hover:bg-gray-700 transition" onclick="sortTable('usersTable', 4)">Estado</th>
                    <th class="p-4 text-sm font-semibold tracking-wide text-center">Acciones</th>
                </tr>
            </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if (!empty($users)): ?>
            <?php foreach ($users as $u): ?>
            <tr class="hover:bg-gray-50 transition user-row" data-status="<?= htmlspecialchars($u['status']) ?>">
                
                <td class="p-4" data-raw="<?= htmlspecialchars($u['created_at'] ?? '') ?>">
                   <div class="text-xs text-gray-500">
                       <?= htmlspecialchars(date('d/m/Y', strtotime($u['created_at'] ?? 'now'))) ?>
                   </div>
                </td>

                <td class="p-4">
                    <!-- NOMBRE FORMATEADO: Primer Nombre + Apellido Paterno -->
                    <div class="font-bold text-gray-800 capitalize">
                        <a href="/users/detail/<?= $u['id'] ?>" class="hover:text-blue-600 transition">
                            <?php if (!empty($u['first_name']) && !empty($u['last_name'])): ?>
                                <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?>
                            <?php else: ?>
                                <?= htmlspecialchars($u['name']) ?> <!-- Fallback para legacy -->
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="text-xs text-gray-500"><?= htmlspecialchars($u['email']) ?></div>
                    <?php if (!empty($u['phone'])): ?>
                    <div class="text-xs text-gray-400 mt-0.5"><i class="fas fa-phone mr-1"></i><?= htmlspecialchars($u['phone']) ?></div>
                    <?php endif; ?>
                </td>

                <td class="p-4">
                    <div class="text-sm font-medium text-gray-700"><?= htmlspecialchars($u['company'] ?: '-') ?></div>
                    <div class="text-xs text-gray-500"><?= htmlspecialchars($u['department'] ?: '-') ?></div>
                </td>

                <td class="p-4">
                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                        <?= htmlspecialchars($u['system_role'] ?: 'N/A') ?>
                    </span>
                </td>

                <td class="p-4">
                    <?php
                    $status = $u['status'];
                    $st_class = 'bg-gray-100';
                    if ($status == 'Activo') $st_class = 'bg-green-100 text-green-700';
                    elseif ($status == 'Inactivo') $st_class = 'bg-red-100 text-red-700';
                    elseif ($status == 'Vacaciones') $st_class = 'bg-yellow-100 text-yellow-700';
                    ?>
                    <span class="px-3 py-1 rounded-full text-xs font-bold inline-flex items-center <?= $st_class ?>">
                        <?= htmlspecialchars($status) ?>
                    </span>
                </td>

                <td class="p-4 text-center">
                    <div class="flex justify-center gap-2">
                        <a href="/users/detail/<?= $u['id'] ?>" class="text-blue-500 hover:bg-blue-50 p-2 rounded" title="Ver Detalle"><i class="fas fa-eye"></i></a>
                        <a href="/users/edit/<?= $u['id'] ?>" class="text-yellow-600 hover:bg-yellow-50 p-2 rounded" title="Editar"><i class="fas fa-pen"></i></a>
                        <a href="/users/delete/<?= $u['id'] ?>" onclick="return confirm('¿Estás seguro?')" class="text-red-600 hover:bg-red-50 p-2 rounded" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr><td colspan="5" class="p-6 text-center text-gray-500 italic">No se encontraron usuarios.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<script src="/js/table-sort.js"></script>
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
