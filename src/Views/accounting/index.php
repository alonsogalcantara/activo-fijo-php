<?php ob_start(); ?>

<!-- Variables del sistema -->
<?php 
// $assets is passed from controller as $paged_assets
// $pagination is passed from controller
// $kpis is passed from controller
$current_sort = $_GET['sort_by'] ?? 'id';
$current_order = $_GET['order'] ?? 'desc';
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Contabilidad y Depreciación</h1>
        <p class="text-gray-500 text-sm">Edición en lote y ordenamiento. <span class="text-blue-600 font-bold">
            <?= 'Página ' . $pagination['page'] . ' de ' . $pagination['total_pages'] ?>
        </span></p>
    </div>

    <div class="flex flex-wrap items-center gap-3 bg-white p-2 rounded-xl shadow-sm border border-gray-100">
        <!-- FILTRO AÑO -->
        <div class="flex items-center bg-blue-50 rounded-lg px-2 border border-blue-100">
            <i class="fas fa-calendar-alt text-blue-600 ml-2"></i>
            <select id="fiscalYearFilter" onchange="applyServerFilters()"
                class="bg-transparent border-none text-blue-800 text-sm font-bold focus:ring-0 cursor-pointer py-2 pl-2 pr-8 outline-none">
                <option value="all">Histórico (Todos)</option>
                <option value="2025">Año 2025</option>
                <option value="2024">Año 2024</option>
                <option value="2023">Año 2023</option>
                <option value="2022">Año 2022</option>
                <option value="custom">Personalizado...</option>
            </select>
        </div>
        <!-- RANGO CUSTOM -->
        <div id="customDateContainer" class="hidden flex items-center gap-2 animate-fade-in">
            <input type="date" id="dateStart" onchange="applyServerFilters()"
                class="text-xs border border-gray-300 rounded-lg p-1.5 outline-none text-gray-600">
            <span class="text-gray-400 font-bold">-</span>
            <input type="date" id="dateEnd" onchange="applyServerFilters()"
                class="text-xs border border-gray-300 rounded-lg p-1.5 outline-none text-gray-600">
        </div>
        <div class="h-6 w-px bg-gray-200 mx-1"></div>
        <button onclick="downloadPdf()"
            class="text-red-500 hover:text-red-700 hover:bg-red-50 px-3 py-1 rounded-lg text-sm font-bold transition flex items-center gap-2 border border-transparent hover:border-red-100">
            <i class="fas fa-file-pdf"></i> PDF
        </button>
        <button onclick="downloadExcel()"
            class="text-green-600 hover:text-green-800 hover:bg-green-50 px-3 py-1 rounded-lg text-sm font-bold transition flex items-center gap-2 border border-transparent hover:border-green-100">
            <i class="fas fa-file-excel"></i> Excel
        </button>
        <div class="h-6 w-px bg-gray-200 mx-1"></div>
        <button onclick="showForecast()"
            class="text-purple-600 hover:text-purple-800 hover:bg-purple-50 px-3 py-1 rounded-lg text-sm font-bold transition flex items-center gap-2 border border-transparent hover:border-purple-100">
            <i class="fas fa-chart-line"></i> Proyección
        </button>
    </div>
</div>

<!-- MODAL FORECAST -->
<div id="forecastModal"
    class="fixed inset-0 bg-gray-900/60 hidden items-center justify-center z-50 backdrop-blur-sm transition-opacity">
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl p-0 overflow-hidden transform scale-100 transition-transform flex flex-col max-h-[90vh]">
        <div class="bg-purple-600 px-6 py-4 border-b border-purple-700 flex justify-between items-center text-white">
            <h2 class="text-lg font-bold"><i class="fas fa-chart-line mr-2"></i> Proyección de Depreciación (Próximos 12
                Meses)</h2>
            <button onclick="closeForecastModal()" class="text-purple-200 hover:text-white"><i
                    class="fas fa-times"></i></button>
        </div>
        <div class="p-6 overflow-y-auto">
            <div id="forecastLoading" class="hidden text-center py-10">
                <i class="fas fa-circle-notch fa-spin text-4xl text-purple-200 mb-3"></i>
                <p class="text-gray-500 font-bold">Calculando proyecciones...</p>
            </div>
            <div id="forecastContent" class="hidden">
                <!-- Gráfico de barras simple CSS -->
                <div class="flex items-end h-64 gap-2 mb-8 border-b border-gray-200 pb-2 custom-chart">
                    <!-- Se llena con JS -->
                </div>

                <!-- Tabla de datos -->
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 font-bold uppercase text-xs">
                        <tr>
                            <th class="p-3 text-left">Mes</th>
                            <th class="p-3 text-right">Depreciación Proyectada</th>
                            <th class="p-3 text-right">Acumulado Anual</th>
                        </tr>
                    </thead>
                    <tbody id="forecastTableBody" class="divide-y divide-gray-100">
                        <!-- Se llena con JS -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 text-right">
            <button onclick="closeForecastModal()"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded-lg">Cerrar</button>
        </div>
    </div>
</div>

<!-- KPIs -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div
        class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition">
        <div class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Inversión (Total Filtro)</div>
        <div class="text-2xl font-bold text-gray-800" id="kpi-gross-investment">$<?= number_format($kpis['gross_investment'], 2) ?></div>
    </div>
    <div
        class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition">
        <div class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Depreciación Acum.</div>
        <div class="text-2xl font-bold text-red-500" id="kpi-accumulated-depreciation">-$<?= number_format($kpis['accumulated_depreciation'], 2) ?></div>
    </div>
    <div
        class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between border-l-4 border-l-emerald-400 hover:shadow-md transition">
        <div class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Valor Neto</div>
        <div class="text-2xl font-bold text-emerald-600" id="kpi-net-book-value">$<?= number_format($kpis['net_book_value'], 2) ?></div>
    </div>
    <div
        class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition">
        <div class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Desgaste Global</div>
        <?php 
            $global_percent = ($kpis['gross_investment'] > 0) ? ($kpis['accumulated_depreciation'] / $kpis['gross_investment']) * 100 : 0;
        ?>
        <div class="flex items-end">
            <div class="text-2xl font-bold text-purple-600" id="kpi-global-wear-percent"><?= number_format($global_percent, 1) ?>%</div>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2">
            <div class="bg-purple-500 h-1.5 rounded-full" id="kpi-global-wear-bar" style="width: <?= $global_percent ?>%"></div>
        </div>
    </div>
</div>

<!-- FILTROS -->
<div class="bg-white p-3 rounded-xl shadow-sm mb-6 border border-gray-100 flex flex-col md:flex-row gap-3 items-center">
    <div class="relative flex-1 w-full">
        <i class="fas fa-search absolute left-4 top-3 text-gray-400"></i>
        <input type="text" id="accountingSearch" placeholder="Buscar en esta página..."
            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 focus:bg-white transition text-sm"
            onkeyup="filterVisualRows()">
    </div>
    <select id="filterCategory" onchange="applyServerFilters()"
        class="w-full md:w-48 p-2 border border-gray-200 rounded-lg bg-gray-50 text-sm focus:ring-2 focus:ring-blue-500 cursor-pointer">
        <option value="">Todas las Categorías</option>
        <option value="Computadora">Computadora</option>
        <option value="Vehículo">Vehículo</option>
        <option value="Mobiliario">Mobiliario</option>
        <option value="Servidor">Servidor</option>
        <option value="Otro">Otro</option>
    </select>
</div>

<!-- TABLA PRINCIPAL CON HEADERS ORDENABLES -->
<div class="bg-white rounded-xl shadow overflow-hidden w-full border border-gray-200 flex flex-col mb-20">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead
                class="bg-gray-50 text-gray-500 uppercase text-[10px] font-bold tracking-wider border-b border-gray-200">
                <tr>
                    <!-- ACTIVO (NAME) -->
                    <th class="p-3 w-1/4 cursor-pointer hover:bg-gray-100 transition select-none group"
                        onclick="toggleSort('name')">
                        Activo / Descripción
                        <span class="ml-1 text-gray-300 group-hover:text-gray-400">
                            <?php if ($current_sort == 'name'): ?>
                            <i class="fas fa-sort-<?= $current_order == 'asc' ? 'alpha-down' : 'alpha-up' ?> text-blue-500"></i>
                            <?php else: ?>
                            <i class="fas fa-sort"></i>
                            <?php endif; ?>
                        </span>
                    </th>

                    <!-- FECHA (DATE) -->
                    <th class="p-3 text-left w-32 cursor-pointer hover:bg-gray-100 transition select-none group"
                        onclick="toggleSort('date')">
                        F. Adquisición
                        <span class="ml-1 text-gray-300 group-hover:text-gray-400">
                            <?php if ($current_sort == 'date'): ?>
                            <i class="fas fa-sort-<?= $current_order == 'asc' ? 'numeric-down' : 'numeric-up' ?> text-blue-500"></i>
                            <?php else: ?>
                            <i class="fas fa-sort"></i>
                            <?php endif; ?>
                        </span>
                    </th>

                    <!-- COSTO (COST) -->
                    <th class="p-3 text-right cursor-pointer hover:bg-gray-100 transition select-none group"
                        onclick="toggleSort('cost')">
                        Costo (MOI)
                        <span class="ml-1 text-gray-300 group-hover:text-gray-400">
                             <?php if ($current_sort == 'cost'): ?>
                            <i class="fas fa-sort-<?= $current_order == 'asc' ? 'amount-down' : 'amount-up' ?> text-blue-500"></i>
                            <?php else: ?>
                            <i class="fas fa-sort"></i>
                            <?php endif; ?>
                        </span>
                    </th>

                    <th class="p-3 text-center">Vida Útil</th>
                    <th class="p-3 text-right w-32">Depr. Acum ($)</th>
                    <th class="p-3 text-center w-24">% Depr.</th>
                    <th class="p-3 text-right">Valor Libros</th>

                    <!-- ESTATUS -->
                    <th class="p-3 text-center cursor-pointer hover:bg-gray-100 transition select-none group"
                        onclick="toggleSort('status')">
                        Estatus
                        <span class="ml-1 text-gray-300 group-hover:text-gray-400">
                             <?php if ($current_sort == 'status'): ?>
                            <i class="fas fa-sort-<?= $current_order == 'asc' ? 'alpha-down' : 'alpha-up' ?> text-blue-500"></i>
                            <?php else: ?>
                            <i class="fas fa-sort"></i>
                            <?php endif; ?>
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white text-sm" id="accountingBody">
                <?php foreach ($paged_assets as $a): ?>
                <?php $acc = $a['accounting']; ?>
                <tr class="hover:bg-blue-50 transition group acc-row" id="row-<?= $a['id'] ?>" data-id="<?= $a['id'] ?>"
                    data-search="<?= htmlspecialchars(strtolower($a['name'] . ' ' . $a['category'] . ' ' . $acc['status'])) ?>"
                    data-cost="<?= $a['purchase_cost'] ?: 0 ?>"
                    data-depreciation="<?= $acc['accumulated_depreciation'] ?: 0 ?>"
                    data-current-value="<?= $acc['current_value'] ?: 0 ?>">

                    <td class="p-3">
                        <div class="font-bold text-gray-800 truncate max-w-[250px]" title="<?= htmlspecialchars($a['name']) ?>"><?= htmlspecialchars($a['name']) ?>
                        </div>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-[10px] bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded border border-gray-200">
                                <?= htmlspecialchars($a['category']) ?>
                            </span>
                            <span class="text-[10px] text-gray-400">ID: <?= $a['id'] ?></span>
                        </div>
                    </td>

                    <td class="p-3 text-sm text-gray-600 border-l border-gray-100 cursor-pointer hover:bg-yellow-50 cell-editable"
                        ondblclick="makeEditable(this, 'date', '<?= $a['id'] ?>')">
                        <span class="value-display"><?= htmlspecialchars($a['purchase_date'] ? date('d/m/Y', strtotime($a['purchase_date'])) : '-') ?></span>
                    </td>

                    <td class="p-3 text-right font-mono text-gray-700 border-r border-gray-100 cursor-pointer hover:bg-yellow-50 cell-editable"
                        ondblclick="makeEditable(this, 'cost', '<?= $a['id'] ?>')">
                        <span class="value-display">$<?= number_format($a['purchase_cost'] ?: 0, 2) ?></span>
                    </td>

                    <td class="p-3 text-center">
                        <span class="bg-gray-100 text-gray-500 py-0.5 px-2 rounded text-xs font-medium">
                            <?= $acc['useful_life'] ?>a
                        </span>
                    </td>

                    <td class="p-3 text-right font-mono text-red-600 border-l border-gray-100 cursor-pointer hover:bg-yellow-50 relative cell-editable"
                        ondblclick="makeEditable(this, 'amount', '<?= $a['id'] ?>', <?= $a['purchase_cost'] ?: 0 ?>)">
                        <span class="value-display">-$<?= number_format($acc['accumulated_depreciation'] ?: 0, 2) ?></span>
                        <?php if ($acc['is_manual']): ?>
                        <div class="absolute top-1 right-1 text-[8px] text-orange-400 manual-indicator"><i
                                class="fas fa-circle"></i></div>
                        <?php endif; ?>
                    </td>

                    <td class="p-3 text-center font-mono text-purple-600 border-r border-gray-100 cursor-pointer hover:bg-yellow-50 cell-editable"
                        ondblclick="makeEditable(this, 'percent', '<?= $a['id'] ?>', <?= $a['purchase_cost'] ?: 0 ?>)">
                        <span class="value-display"><?= $acc['percentage_depreciated'] ?>%</span>
                    </td>

                    <td class="p-3 text-right font-mono font-bold text-emerald-700 bg-emerald-50/30 cell-net-value">
                        $<?= number_format($acc['current_value'] ?: 0, 2) ?>
                    </td>

                    <!-- Estatus -->
                    <td class="p-3 text-center">
                        <?php if ($acc['status'] == 'Totalmente Depreciado'): ?>
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Depreciado</span>
                        <?php elseif ($a['acquisition_type'] == 'Arrendamiento'): ?>
                        <span class="text-[10px] font-bold text-purple-500 uppercase">Renta</span>
                        <?php else: ?>
                        <span class="text-[10px] font-bold text-emerald-500 uppercase">Vigente</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($paged_assets)): ?>
                <tr>
                    <td colspan="9" class="p-12 text-center text-gray-400 italic">No hay registros para este filtro.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINACIÓN -->
    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 flex items-center justify-between sm:px-6">
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Mostrando <span class="font-medium"><?= ($pagination['page'] - 1) * $pagination['per_page'] + 1 ?></span> a
                    <span class="font-medium"><?= min([$pagination['page'] * $pagination['per_page'], $pagination['total']]) ?></span> de <span class="font-medium"><?= $pagination['total'] ?></span> resultados
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <?php 
                    function page_url($p) {
                        $params = $_GET;
                        $params['page'] = $p;
                        return '?' . http_build_query($params);
                    }
                    ?>
                    <?php if ($pagination['page'] > 1): ?>
                    <a href="<?= page_url($pagination['page'] - 1) ?>"
                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"><i
                            class="fas fa-chevron-left"></i></a>
                    <?php endif; ?>

                    <span
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">Página
                        <?= $pagination['page'] ?> de <?= $pagination['total_pages'] ?></span>

                    <?php if ($pagination['page'] < $pagination['total_pages']): ?> <a href="<?= page_url($pagination['page'] + 1) ?>"
                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div id="saveChangesContainer" class="fixed bottom-6 right-6 z-50 hidden transition-all duration-300">
    <button onclick="commitChanges()"
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-full shadow-2xl flex items-center gap-3 transform hover:scale-105 border-4 border-white">
        <i class="fas fa-save text-xl"></i>
        <span class="text-lg">Guardar Cambios (<span id="changesCount">0</span>)</span>
    </button>
</div>

<style>
    .cell-modified {
        background-color: #fef9c3 !important;
        position: relative;
    }

    .cell-modified::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 0;
        height: 0;
        border-top: 6px solid #eab308;
        border-left: 6px solid transparent;
    }

    .hidden {
        display: none !important;
    }
</style>

<script>
    // const CURRENT_TOKEN = "{{ session.get('jwt_token', '') }}"; // Not using JWT in vanilla PHP session
    // const API_BASE = "http://localhost:5000/api"; // Internal routing now
    // API_BASE will be relative
    
    const moneyFmt = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 });
    let pendingChanges = {};

    document.addEventListener('DOMContentLoaded', () => { restoreFilters(); }); // calcKPIs is done by PHP mostly but we update it on visual filter

    function restoreFilters() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('year')) {
            const y = params.get('year');
            const el = document.getElementById('fiscalYearFilter');
            if (el) { el.value = y; if (y === 'custom') { document.getElementById('customDateContainer').classList.remove('hidden'); document.getElementById('customDateContainer').classList.add('flex'); if (params.has('start_date')) document.getElementById('dateStart').value = params.get('start_date'); if (params.has('end_date')) document.getElementById('dateEnd').value = params.get('end_date'); } }
        }
        if (params.has('category')) { const el = document.getElementById('filterCategory'); if (el) el.value = params.get('category'); }
    }

    // --- FUNCIÓN DE ORDENAMIENTO (NUEVA) ---
    function toggleSort(column) {
        if (Object.keys(pendingChanges).length > 0) {
            if (!confirm("Tienes cambios sin guardar. Al reordenar se recargará la página. ¿Deseas continuar y perder cambios?")) return;
        }
        const params = new URLSearchParams(window.location.search);
        const currentSort = params.get('sort_by') || 'id';
        const currentOrder = params.get('order') || 'desc';
        let newOrder = 'asc';
        if (currentSort === column) {
            newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
        }
        params.set('sort_by', column);
        params.set('order', newOrder);
        params.set('page', 1); // Reset page on sort
        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }

    function applyServerFilters() {
        if (Object.keys(pendingChanges).length > 0) {
            if (!confirm("Tienes cambios sin guardar. Si filtras ahora, perderás tus ediciones. ¿Deseas continuar?")) { restoreFilters(); return; }
        }
        const year = document.getElementById('fiscalYearFilter').value;
        const cat = document.getElementById('filterCategory').value;
        const customContainer = document.getElementById('customDateContainer');

        if (year === 'custom') {
            customContainer.classList.remove('hidden'); customContainer.classList.add('flex');
            const dStart = document.getElementById('dateStart').value; const dEnd = document.getElementById('dateEnd').value;
            if (!dStart || !dEnd) return;
        } else { customContainer.classList.add('hidden'); customContainer.classList.remove('flex'); }

        const params = new URLSearchParams(window.location.search);
        params.set('page', 1); params.set('year', year);
        if (cat) params.set('category', cat); else params.delete('category');
        if (year === 'custom') { params.set('start_date', document.getElementById('dateStart').value); params.set('end_date', document.getElementById('dateEnd').value); }
        else { params.delete('start_date'); params.delete('end_date'); }
        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }

    function filterVisualRows() {
        const search = document.getElementById('accountingSearch').value.toLowerCase();
        const rows = document.querySelectorAll('.acc-row');
        rows.forEach(row => { row.style.display = row.getAttribute('data-search').toLowerCase().includes(search) ? "" : "none"; });
        // calcKPIs(); // Not implemented locally for visual filter to speed up, using PHP totals
    }

    function makeEditable(td, type, assetId) {
        if (td.querySelector('input')) return;
        let currentVal = td.querySelector('.value-display') ? td.querySelector('.value-display').innerText : td.innerText;
        currentVal = currentVal.replace(/[$,%\-]/g, '').trim();
        let inputType = 'number';
        if (type === 'date') { 
            inputType = 'date'; 
            // format dd/mm/yyyy to yyyy-mm-dd if needed
            const parts = currentVal.split('/'); 
            if (parts.length === 3) currentVal = `${parts[2]}-${parts[1]}-${parts[0]}`; 
        }
        td.innerHTML = `<input type="${inputType}" class="w-full p-1 border-2 border-blue-400 rounded text-sm font-bold text-gray-700 text-center shadow-lg" value="${currentVal}" step="0.01" onblur="handleLocalChange(this, '${type}', '${assetId}')" onkeydown="if(event.key === 'Enter') this.blur()">`;
        td.querySelector('input').focus();
    }

    function handleLocalChange(input, type, assetId) {
        try {
            let val = input.value;
            const row = document.getElementById(`row-${assetId}`);
            if (!pendingChanges[assetId]) pendingChanges[assetId] = {};
            if (type === 'cost') pendingChanges[assetId].purchase_cost = parseFloat(val);
            else if (type === 'date') pendingChanges[assetId].purchase_date = val;
            else if (type === 'amount' || type === 'percent') {
                const currentCost = pendingChanges[assetId].purchase_cost !== undefined ? pendingChanges[assetId].purchase_cost : parseFloat(row.getAttribute('data-cost'));
                let finalAmount = 0;
                if (type === 'percent') finalAmount = (parseFloat(val) / 100) * currentCost;
                else finalAmount = parseFloat(val);
                pendingChanges[assetId].accumulated_depreciation = finalAmount;
                val = finalAmount;
            }
            updateRowVisuals(row, assetId, type, val);
            let selector = '';
            if (type === 'cost') selector = '[onclick*="\'cost\'"]'; else if (type === 'date') selector = '[onclick*="\'date\'"]'; else if (type === 'amount') selector = '[onclick*="\'amount\'"]'; else if (type === 'percent') selector = '[onclick*="\'percent\'"]';
            const td = row.querySelector(selector);
            if (td) td.classList.add('cell-modified');
            updateSaveButton(); 
            // calcKPIs();
        } catch (e) { console.error(e); location.reload(); }
    }

    function updateRowVisuals(row, assetId, type, val) {
        if (type === 'cost') {
            row.setAttribute('data-cost', val);
            const td = row.querySelector('[onclick*="\'cost\'"]');
            if (td) td.innerHTML = `<span class="value-display">${moneyFmt.format(val)}</span>`;
            recalcRowNetValue(row);
        }
        else if (type === 'date') {
            row.setAttribute('data-date', val);
            const parts = val.split('-');
            const displayDate = parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : val;
            const td = row.querySelector('[onclick*="\'date\'"]');
            if (td) td.innerHTML = `<span class="value-display">${displayDate}</span>`;
        }
        else if (type === 'amount' || type === 'percent') {
            row.setAttribute('data-depreciation', val);
            const tdAmount = row.querySelector('[onclick*="\'amount\'"]');
            if (tdAmount) {
                let html = `<span class="value-display">-${moneyFmt.format(val)}</span>`;
                if (tdAmount.querySelector('.manual-indicator')) html += tdAmount.querySelector('.manual-indicator').outerHTML;
                tdAmount.innerHTML = html;
            }
            const cost = parseFloat(row.getAttribute('data-cost'));
            const pct = cost > 0 ? ((val / cost) * 100).toFixed(1) : 0;
            const tdPct = row.querySelector('[onclick*="\'percent\'"]');
            if (tdPct) tdPct.innerHTML = `<span class="value-display">${pct}%</span>`;
            recalcRowNetValue(row);
        }
    }

    function recalcRowNetValue(row) {
        const cost = parseFloat(row.getAttribute('data-cost')) || 0;
        const depr = parseFloat(row.getAttribute('data-depreciation')) || 0;
        const net = cost - depr;
        row.setAttribute('data-current-value', net);
        row.querySelector('.cell-net-value').innerText = moneyFmt.format(net);
    }

    function updateSaveButton() {
        const count = Object.keys(pendingChanges).length;
        const btn = document.getElementById('saveChangesContainer');
        document.getElementById('changesCount').innerText = count;
        if (count > 0) btn.classList.remove('hidden'); else btn.classList.add('hidden');
    }

    async function commitChanges() {
        const btn = document.querySelector('#saveChangesContainer button');
        const originalText = btn.innerHTML;
        btn.innerHTML = `<i class=\"fas fa-circle-notch fa-spin text-xl\"></i> Guardando...`;
        btn.disabled = true;
        try {
            const promises = [];
            for (const [assetId, changes] of Object.entries(pendingChanges)) {
                
                // Construct query params or send multiple reqs.
                // Our PHP controller expects payload for one ID, but receives no ID in body.
                // Route: /accounting/update_ajax?id=X
                
                if (changes.purchase_cost !== undefined || changes.purchase_date !== undefined) {
                    const payload = {};
                    if (changes.purchase_cost !== undefined) payload.purchase_cost = changes.purchase_cost;
                    if (changes.purchase_date !== undefined) payload.purchase_date = changes.purchase_date;
                    promises.push(fetch(`/accounting/update_ajax?id=${assetId}`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) }));
                }
                if (changes.accumulated_depreciation !== undefined) {
                    promises.push(fetch(`/accounting/update_ajax?id=${assetId}`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ accumulated_depreciation: changes.accumulated_depreciation }) }));
                }
            }
            await Promise.all(promises);
            alert("¡Cambios guardados correctamente!");
            window.location.reload();
        } catch (e) {
            console.error(e);
            alert("Error al guardar.");
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
    function downloadPdf() {
        const params = new URLSearchParams(window.location.search);
        window.open(`/reports/accounting_pdf?${params.toString()}`, '_blank');
    }

    function downloadExcel() {
        const params = new URLSearchParams(window.location.search);
        window.open(`/reports/accounting_excel?${params.toString()}`, '_blank');
    }

    // --- FORECAST LOGIC ---
    function showForecast() {
        document.getElementById('forecastModal').classList.remove('hidden');
        document.getElementById('forecastModal').classList.add('flex');
        fetchForecastData();
    }

    function closeForecastModal() {
        document.getElementById('forecastModal').classList.add('hidden');
        document.getElementById('forecastModal').classList.remove('flex');
    }

    async function fetchForecastData() {
        document.getElementById('forecastLoading').classList.remove('hidden');
        document.getElementById('forecastContent').classList.add('hidden');

        try {
            const res = await fetch(`/accounting/forecast_ajax`);
            if (res.ok) {
                const data = await res.json();
                renderForecast(data);
            } else {
                alert("Error cargando proyección.");
                closeForecastModal();
            }
        } catch (e) {
            console.error(e);
            alert("Error de conexión");
            closeForecastModal();
        } finally {
            document.getElementById('forecastLoading').classList.add('hidden');
        }
    }

    function renderForecast(data) {
        document.getElementById('forecastContent').classList.remove('hidden');
        const months = data.months;
        const totals = data.totals;

        // Render Tabla
        let htmlTable = '';
        let accum = 0;
        totals.forEach((val, i) => {
            accum += val;
            htmlTable += `
            <tr class="hover:bg-purple-50 transition">
                <td class="p-3 font-bold text-gray-700">${months[i]}</td>
                <td class="p-3 text-right font-mono text-gray-800">$${moneyFmt.format(val).replace('$', '')}</td>
                <td class="p-3 text-right font-mono text-purple-600 font-bold">$${moneyFmt.format(accum).replace('$', '')}</td>
            </tr>`;
        });
        document.getElementById('forecastTableBody').innerHTML = htmlTable;

        // Render Chart (Barra simple)
        const maxVal = Math.max(...totals) * 1.1 || 1;
        let htmlChart = '';
        totals.forEach((val, i) => {
            const height = (val / maxVal) * 100;
            htmlChart += `
            <div class="flex-1 flex flex-col justify-end items-center group relative h-full">
                <div class="w-full bg-purple-200 rounded-t-sm hover:bg-purple-400 transition-all relative" style="height: ${height}%">
                    <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded pointer-events-none transition z-10 whitespace-nowrap">
                        $${moneyFmt.format(val).replace('$', '')}
                    </div>
                </div>
                <div class="text-[10px] text-gray-400 mt-1 rotate-45 transform origin-left translate-x-1">${months[i].split(' ')[0]}</div>
            </div>`;
        });
        document.querySelector('.custom-chart').innerHTML = htmlChart;
    }
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
