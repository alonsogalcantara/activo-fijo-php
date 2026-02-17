<?php ob_start(); ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Inventario General</h1>
    <a href="/assets/create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow flex items-center">
        <i class="fas fa-plus mr-2"></i> Nuevo Activo
    </a>
</div>

<!-- BARRA DE BÚSQUEDA Y FILTROS MEJORADA -->
<div class="bg-white p-4 rounded-xl shadow mb-6 border border-gray-200 flex flex-col md:flex-row gap-4">
    <div class="relative flex-1">
        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        <input type="text" id="assetSearchInput" placeholder="Buscar por nombre, serie, usuario, placa..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            onkeyup="filterAssets()">
    </div>

    <div class="w-full md:w-40">
        <select id="filterCategory" onchange="filterAssets()"
            class="w-full pl-3 pr-8 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer text-gray-700">
            <option value="">Categorías</option>
            <option value="Computadora">Computadora</option>
            <option value="Celular">Celular / Tablet</option>
            <option value="Vehículo">Vehículo</option>
            <option value="Uniforme">Uniforme</option>
            <option value="Mobiliario">Mobiliario</option>
            <option value="Herramienta">Herramienta</option>
            <option value="Otro">Otro</option>
        </select>
    </div>

    <!-- NUEVO FILTRO TIPO DE ADQUISICIÓN -->
    <div class="w-full md:w-40">
        <select id="filterAcquisition" onchange="filterAssets()"
            class="w-full pl-3 pr-8 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer text-gray-700">
            <option value="">Adquisición</option>
            <option value="Compra">Compras</option>
            <option value="Arrendamiento">Leasing / Renta</option>
        </select>
    </div>

    <div class="w-full md:w-40">
        <select id="filterStatus" onchange="filterAssets()"
            class="w-full pl-3 pr-8 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer text-gray-700">
            <option value="">Estados</option>
            <option value="Disponible">Disponible</option>
            <option value="Asignado">Asignado</option>
            <option value="En Mantenimiento">En Mantenimiento</option>
            <option value="De Baja">De Baja</option>
        </select>
    </div>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden w-full">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse" id="assetsTable">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="p-4 text-sm font-semibold tracking-wide text-center cursor-pointer hover:bg-gray-700 transition" onclick="sortTable('assetsTable', 0, 'date')">Fecha Registro</th>
                    <th class="p-4 text-sm font-semibold tracking-wide text-center">Foto</th>
                    <th class="p-4 text-sm font-semibold tracking-wide cursor-pointer hover:bg-gray-700 transition" onclick="sortTable('assetsTable', 2)">Descripción</th>
                    <th class="p-4 text-sm font-semibold tracking-wide cursor-pointer hover:bg-gray-700 transition" onclick="sortTable('assetsTable', 3)">Adquisición</th>
                    <th class="p-4 text-sm font-semibold tracking-wide cursor-pointer hover:bg-gray-700 transition" onclick="sortTable('assetsTable', 4)">Identificador</th>
                    <th class="p-4 text-sm font-semibold tracking-wide cursor-pointer hover:bg-gray-700 transition" onclick="sortTable('assetsTable', 5)">Ubicación / Asignación</th>
                    <th class="p-4 text-sm font-semibold tracking-wide text-center cursor-pointer hover:bg-gray-700 transition" onclick="sortTable('assetsTable', 6)">Estado</th>
                    <th class="p-4 text-sm font-semibold tracking-wide text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" id="assetsTableBody">
                <?php if (!empty($assets)): ?>
                <?php foreach ($assets as $a): ?>
                <tr class="hover:bg-gray-50 transition group asset-row" data-category="<?= htmlspecialchars($a['category']) ?>"
                    data-status="<?= htmlspecialchars($a['status']) ?>" data-acquisition="<?= htmlspecialchars($a['acquisition_type'] ?: 'Compra') ?>"
                    data-search="<?= htmlspecialchars($a['name'] . ' ' . ($a['serial_number'] ?? '') . ' ' . ($a['assigned_to_name'] ?? '') . ' ' . ($a['leasing_company'] ?? '')) ?>">
                    
                    <td class="p-4" data-raw="<?= htmlspecialchars($a['created_at'] ?? '') ?>">
                        <div class="text-sm text-gray-600">
                            <?= htmlspecialchars(date('d/m/Y', strtotime($a['created_at'] ?? 'now'))) ?>
                        </div>
                    </td>

                    <td class="p-3 text-center">
                        <div class="relative inline-block">
                            <?php if (!empty($a['photo_filename'])): ?>
                            <img src="/uploads/<?= htmlspecialchars($a['photo_filename']) ?>"
                                class="w-12 h-12 object-cover rounded-lg border border-gray-200 bg-white shadow-sm">
                            <?php else: ?>
                            <!-- Icono Fallback según categoría -->
                            <div
                                class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 text-xl">
                                <?php if ($a['category'] == 'Vehículo'): ?><i class="fas fa-car"></i>
                                <?php elseif ($a['category'] == 'Computadora' || $a['category'] == 'Laptop'): ?><i
                                    class="fas fa-laptop"></i>
                                <?php elseif ($a['category'] == 'Celular'): ?><i class="fas fa-mobile-alt"></i>
                                <?php elseif ($a['category'] == 'Uniforme'): ?><i class="fas fa-tshirt"></i>
                                <?php elseif ($a['category'] == 'Herramienta'): ?><i class="fas fa-tools"></i>
                                <?php else: ?><i class="fas fa-box"></i><?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </td>

                    <td class="p-4">
                        <div>
                            <a href="/assets/detail/<?= $a['id'] ?>" class="font-bold text-gray-800 text-base group-hover:text-blue-600 transition">
                                <?= htmlspecialchars($a['name']) ?>
                            </a>
                        </div>
                        <div class="text-xs text-gray-500 font-semibold bg-gray-100 px-2 py-0.5 rounded w-fit mt-1">
                            <?= htmlspecialchars($a['category']) ?>
                        </div>
                    </td>

                    <!-- CELDA NUEVA ADQUISICIÓN -->
                    <td class="p-4">
                        <?php if ($a['acquisition_type'] == 'Arrendamiento'): ?>
                        <span
                            class="inline-flex items-center text-xs font-bold text-purple-600 bg-purple-50 px-2 py-1 rounded border border-purple-100">
                            <i class="fas fa-file-contract mr-1"></i> Leasing
                        </span>
                        <?php if (!empty($a['leasing_company'])): ?>
                        <div class="text-[10px] text-gray-400 mt-1 truncate max-w-[100px]"
                            title="<?= htmlspecialchars($a['leasing_company']) ?>">
                            <?= htmlspecialchars($a['leasing_company']) ?>
                        </div>
                        <?php endif; ?>
                        <?php else: ?>
                        <span
                            class="inline-flex items-center text-xs font-bold text-gray-500 bg-gray-50 px-2 py-1 rounded border border-gray-100">
                            Compra
                        </span>
                        <?php endif; ?>
                    </td>

                    <td class="p-4">
                        <div class="text-sm font-mono text-gray-600"><?= htmlspecialchars($a['serial_number'] ?: '-') ?></div>
                        <div class="text-xs text-gray-400"><?= htmlspecialchars($a['brand']) ?> <?= htmlspecialchars($a['model']) ?></div>
                    </td>

                    <td class="p-4">
                        <?php if (!empty($a['assigned_to_name'])): ?>
                        <div class="flex items-center">
                            <div
                                class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold mr-2">
                                <?= strtoupper(substr($a['assigned_to_name'], 0, 1)) ?>
                            </div>
                            <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars($a['assigned_to_name']) ?></span>
                        </div>
                        <?php else: ?>
                        <span class="text-gray-400 italic text-sm flex items-center"><i
                                class="fas fa-warehouse mr-1.5 text-gray-300"></i> En Stock</span>
                        <?php endif; ?>
                    </td>

                    <td class="p-4 text-center">
                        <?php 
                        $status = $a['status'];
                        $st_class = 'bg-gray-100';
                        if ($status == 'Disponible') $st_class = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                        elseif ($status == 'Asignado') $st_class = 'bg-blue-100 text-blue-700 border-blue-200';
                        elseif ($status == 'En Mantenimiento' || $status == 'En Reparación') $st_class = 'bg-amber-100 text-amber-700 border-amber-200';
                        elseif ($status == 'De Baja') $st_class = 'bg-red-100 text-red-700 border-red-200';
                        ?>
                        <span class="px-3 py-1 rounded-full text-xs font-bold border <?= $st_class ?> shadow-sm">
                            <?= htmlspecialchars($status) ?>
                        </span>
                    </td>

                    <td class="p-4 text-center">
                        <div class="flex justify-center gap-2">
                            <a href="/assets/detail/<?= $a['id'] ?>" class="text-blue-600 hover:bg-blue-50 border border-blue-200 p-2 rounded transition" title="Ver Detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="/assets/edit/<?= $a['id'] ?>" class="text-yellow-600 hover:bg-yellow-50 border border-yellow-200 p-2 rounded transition" title="Editar">
                                <i class="fas fa-pen"></i>
                            </a>
                            <a href="/assets/delete/<?= $a['id'] ?>" onclick="return confirm('¿Estás seguro?')" class="text-red-600 hover:bg-red-50 border border-red-200 p-2 rounded transition" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="7" class="p-12 text-center text-gray-400 italic">No hay activos registrados. Comienza
                        agregando uno.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div id="noResults" class="hidden p-12 text-center text-gray-400 italic bg-white">
            No se encontraron activos con los filtros seleccionados.
        </div>
    </div>
</div>

<script src="/js/table-sort.js"></script>
<script>
    // --- LÓGICA DE FILTRADO ---
    function filterAssets() {
        const search = document.getElementById('assetSearchInput').value.toLowerCase();
        const cat = document.getElementById('filterCategory').value;
        const stat = document.getElementById('filterStatus').value;
        const acq = document.getElementById('filterAcquisition').value;

        const rows = document.querySelectorAll('.asset-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowCat = row.getAttribute('data-category');
            const rowStat = row.getAttribute('data-status');
            const rowAcq = row.getAttribute('data-acquisition');
            const rowText = row.getAttribute('data-search').toLowerCase();

            const matchSearch = rowText.includes(search);
            const matchCat = cat === "" || rowCat === cat;
            const matchStat = stat === "" || rowStat === stat;
            const matchAcq = acq === "" || rowAcq === acq;

            if (matchSearch && matchCat && matchStat && matchAcq) {
                row.style.display = "";
                visibleCount++;
            } else {
                row.style.display = "none";
            }
        });

        const noRes = document.getElementById('noResults');
        if (visibleCount === 0) noRes.classList.remove('hidden');
        else noRes.classList.add('hidden');
    }
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
