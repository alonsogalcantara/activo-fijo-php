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

<div class="bg-white rounded-xl shadow overflow-hidden w-full">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse" id="accountsTable">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="p-4 text-sm font-semibold tracking-wide text-center cursor-pointer hover:bg-gray-700 transition" onclick="sortTable('accountsTable', 0, 'date')">Fecha Registro</th>
                    <th class="p-4 text-sm font-semibold tracking-wide cursor-pointer hover:bg-gray-700 transition" onclick="sortTable('accountsTable', 1)">Servicio</th>
                    <th class="p-4 text-sm font-semibold tracking-wide cursor-pointer hover:bg-gray-700 transition" onclick="sortTable('accountsTable', 2)">Proveedor / Contrato</th>
                    <th class="p-4 text-sm font-semibold tracking-wide cursor-pointer hover:bg-gray-700 transition" onclick="sortTable('accountsTable', 3)">Asignado A</th>
                    <th class="p-4 text-sm font-semibold tracking-wide text-center cursor-pointer hover:bg-gray-700 transition" onclick="sortTable('accountsTable', 4)">Tipo</th>
                    <th class="p-4 text-sm font-semibold tracking-wide text-center cursor-pointer hover:bg-gray-700 transition" onclick="sortTable('accountsTable', 5, 'number')">Costo</th>
                    <th class="p-4 text-sm font-semibold tracking-wide text-center cursor-pointer hover:bg-gray-700 transition" onclick="sortTable('accountsTable', 6, 'date')">Renovación</th>
                    <th class="p-4 text-sm font-semibold tracking-wide text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($accounts)): ?>
                <?php foreach ($accounts as $acc): ?>
                <tr class="hover:bg-gray-50 transition duration-150 account-row" data-type="<?= htmlspecialchars($acc['account_type']) ?>">
                    <td class="p-4 text-center text-sm text-gray-600" data-raw="<?= htmlspecialchars($acc['created_at'] ?? '') ?>">
                         <?= htmlspecialchars(date('d/m/Y', strtotime($acc['created_at'] ?? 'now'))) ?>
                    </td>
                    <td class="p-4">
                        <div class="font-bold text-gray-800">
                            <a href="/accounts/detail/<?= $acc['id'] ?>" class="hover:text-blue-600 transition">
                                <?= htmlspecialchars($acc['service_name']) ?>
                            </a>
                        </div>
                        <div class="text-xs text-gray-500"><?= htmlspecialchars($acc['username'] ?: 'Sin usuario') ?></div>
                    </td>
                    <td class="p-4">
                        <div class="text-sm text-gray-700"><?= htmlspecialchars($acc['provider'] ?: '-') ?></div>
                        <div class="text-xs text-gray-400 font-mono"><?= htmlspecialchars($acc['contract_ref'] ?: '') ?></div>
                    </td>
                    <td class="p-4">
                        <?php if (!empty($acc['assigned_user_name'])): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-user mr-1"></i> <?= htmlspecialchars($acc['assigned_user_name']) ?>
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <i class="fas fa-warehouse mr-1"></i> Stock / Admin
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4 text-center">
                        <?php if ($acc['account_type'] == 'Individual'): ?>
                            <span class="text-xs font-bold text-gray-600 bg-gray-200 px-2 py-1 rounded"><i class="fas fa-user"></i> Indiv.</span>
                        <?php else: ?>
                            <span class="text-xs font-bold text-purple-600 bg-purple-100 px-2 py-1 rounded" title="Licencias">
                                <i class="fas fa-users"></i> <?= $acc['max_licenses'] ?> (Max)
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4 text-center">
                        <div class="font-bold text-gray-700">$<?= number_format($acc['cost'], 2) ?> <?= htmlspecialchars($acc['currency'] ?? 'MXN') ?></div>
                        <div class="text-[10px] uppercase text-gray-400"><?= htmlspecialchars($acc['frequency'] ?? '') ?></div>
                    </td>
                    <td class="p-4 text-center text-sm text-gray-600" data-raw="<?= htmlspecialchars($acc['renewal_date'] ?? '') ?>">
                        <?= htmlspecialchars($acc['renewal_date'] ?? '') ?>
                    </td>
                    <td class="p-4 text-center">
                        <div class="flex justify-center gap-2">
                            <a href="/accounts/detail/<?= $acc['id'] ?>" class="text-blue-600 hover:bg-blue-50 border border-blue-200 p-2 rounded transition" title="Ver Detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="/accounts/edit/<?= $acc['id'] ?>" class="text-blue-600 hover:bg-blue-50 border border-blue-200 p-2 rounded transition" title="Editar">
                                <i class="fas fa-pen"></i>
                            </a>
                            <a href="/accounts/delete/<?= $acc['id'] ?>" onclick="return confirm('¿Estás seguro?')" class="text-red-600 hover:bg-red-50 border border-red-200 p-2 rounded transition" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="7" class="p-8 text-center text-gray-500 italic bg-gray-50 rounded-b-xl">
                        No hay servicios registrados. ¡Agrega el primero!
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

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
<script src="/js/table-sort.js"></script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
