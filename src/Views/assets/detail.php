<?php ob_start(); ?>

<div class="mb-6">
    <a href="/assets" class="text-gray-500 hover:text-gray-800 font-medium transition flex items-center group">
        <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center mr-2 group-hover:bg-gray-100 transition">
            <i class="fas fa-arrow-left"></i>
        </div>
        Volver al inventario
    </a>
</div>

<!-- TOP ACTIONS -->
<div class="flex justify-end gap-2 mb-6">
    <?php if ($asset['status'] == 'En Mantenimiento'): ?>
    <button onclick="endMaintenance(<?= $asset['id'] ?>)" class="bg-emerald-600 text-white px-4 py-2 rounded-lg shadow hover:bg-emerald-700 transition font-bold flex items-center">
        <i class="fas fa-check-circle mr-2"></i> Terminar Mantenimiento
    </button>
    <?php endif; ?>

    <button onclick="openIncidentModal()" class="bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-lg shadow-sm hover:bg-gray-50 transition font-bold flex items-center">
        <i class="fas fa-exclamation-triangle mr-2"></i> Reportar
    </button>

    <?php if ($asset['status'] != 'De Baja'): ?>
    <button onclick="openDisposalModal()" class="bg-white text-red-600 border border-red-200 px-4 py-2 rounded-lg shadow-sm hover:bg-red-50 transition font-bold flex items-center">
        <i class="fas fa-trash-alt mr-2"></i> Dar de Baja
    </button>
    <?php endif; ?>

    <div class="relative group">
        <button class="bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-lg shadow-sm hover:bg-gray-50 font-medium flex items-center">
            <i class="fas fa-print mr-2"></i> Imprimir <i class="fas fa-chevron-down ml-2 text-xs"></i>
        </button>
        <!-- Dropdown -->
        <div class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-100 hidden group-hover:block z-50">
            <button onclick="downloadPdf(<?= $asset['id'] ?>)" class="block w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 border-b border-gray-50">
                <i class="fas fa-file-pdf text-red-500 mr-2"></i> Carta Responsiva
            </button>
            <button onclick="downloadHistory(<?= $asset['id'] ?>)" class="block w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                <i class="fas fa-history text-blue-500 mr-2"></i> Historial Completo
            </button>
            <button onclick="printLabel(<?= $asset['id'] ?>)" class="block w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 border-t border-gray-50">
                <i class="fas fa-qrcode text-gray-800 mr-2"></i> Etiqueta QR
            </button>
        </div>
    </div>

    <a href="/assets/edit/<?= $asset['id'] ?>" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 shadow-md font-bold transition flex items-center">
        <i class="fas fa-pen mr-2"></i> Editar
    </a>
</div>

<!-- MAIN CARD -->
<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-8 p-8">
    
    <!-- HEADER SECTION -->
    <div class="mb-8 pb-6 border-b border-gray-200">
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-xs font-bold uppercase tracking-wide bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg"><?= htmlspecialchars($asset['category']) ?></span>
                    <?php 
                    $statusColors = [
                        'Disponible' => 'bg-emerald-100 text-emerald-700 border-emerald-300',
                        'Asignado' => 'bg-blue-100 text-blue-700 border-blue-300',
                        'En Mantenimiento' => 'bg-amber-100 text-amber-700 border-amber-300',
                        'De Baja' => 'bg-red-100 text-red-700 border-red-300'
                    ];
                    $statusClass = $statusColors[$asset['status']] ?? 'bg-gray-100 text-gray-700 border-gray-300';
                    ?>
                    <span class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide border <?= $statusClass ?>">
                        <?= htmlspecialchars($asset['status']) ?>
                    </span>
                    <?php if ($asset['acquisition_type'] == 'Arrendamiento'): ?>
                    <span class="text-xs font-bold uppercase tracking-wide bg-purple-100 text-purple-700 px-3 py-1.5 rounded-lg border border-purple-300">
                        <i class="fas fa-file-contract mr-1"></i>Arrendamiento
                    </span>
                    <?php endif; ?>
                </div>
                <h1 class="text-4xl font-extrabold text-gray-900 mb-2"><?= htmlspecialchars($asset['name']) ?></h1>
                <div class="flex items-center gap-4 text-sm text-gray-500">
                    <span class="font-mono bg-gray-50 px-3 py-1.5 rounded border border-gray-200">
                        <i class="fas fa-barcode mr-2 text-gray-400"></i><?= htmlspecialchars($asset['serial_number'] ?? 'Sin Identificador') ?>
                    </span>
                    <span class="text-xs text-gray-400">ID: <?= $asset['id'] ?></span>
                </div>
            </div>
        </div>
        
        <?php if (!empty($asset['description'])): ?>
        <div class="mt-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
            <p class="text-sm text-gray-700"><i class="fas fa-align-left mr-2 text-gray-400"></i><?= htmlspecialchars($asset['description']) ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- SECTION 1: INFORMACIÓN GENERAL -->
    <div class="mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center border-b pb-2">
            <i class="fas fa-info-circle mr-2 text-blue-500"></i> Información General
        </h3>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-6 text-sm mb-6 pb-6 border-b border-gray-100">
                <div><span class="block text-gray-400 text-xs font-bold uppercase">Marca</span><span class="font-bold text-gray-800"><?= htmlspecialchars($asset['brand'] ?? '-') ?></span></div>
                <div><span class="block text-gray-400 text-xs font-bold uppercase">Modelo</span><span class="font-bold text-gray-800"><?= htmlspecialchars($asset['model'] ?? '-') ?></span></div>
                <div><span class="block text-gray-400 text-xs font-bold uppercase">Categoría</span><span class="font-bold text-blue-600"><?= htmlspecialchars($asset['category'] ?? '-') ?></span></div>
            </div>

            <!-- CATEGORY-SPECIFIC FIELDS -->
            <?php 
            $hasSpecificFields = false;
            $specificFields = [];
            
            // Tech specs (Laptops, Celulares, Tablets, etc.)
            if (!empty($asset['processor']) || !empty($asset['ram']) || !empty($asset['storage']) || !empty($asset['operating_system'])) {
                $hasSpecificFields = true;
                if (!empty($asset['processor'])) $specificFields['Procesador'] = $asset['processor'];
                if (!empty($asset['ram'])) $specificFields['RAM'] = $asset['ram'];
                if (!empty($asset['storage'])) $specificFields['Almacenamiento'] = $asset['storage'];
                if (!empty($asset['operating_system'])) $specificFields['Sistema Operativo'] = $asset['operating_system'];
            }
            
            // Vehicle specs
            if (!empty($asset['license_plate']) || !empty($asset['vin']) || !empty($asset['vehicle_year']) || !empty($asset['mileage'])) {
                $hasSpecificFields = true;
                if (!empty($asset['license_plate'])) $specificFields['Placas'] = $asset['license_plate'];
                if (!empty($asset['vin'])) $specificFields['VIN'] = $asset['vin'];
                if (!empty($asset['vehicle_year'])) $specificFields['Año'] = $asset['vehicle_year'];
                if (!empty($asset['mileage']) && $asset['mileage'] > 0) $specificFields['Kilometraje'] = number_format($asset['mileage']) . ' km';
            }
            
            // Uniform/Clothing specs
            if (!empty($asset['size']) || !empty($asset['gender_cut']) || !empty($asset['color']) || !empty($asset['material'])) {
                $hasSpecificFields = true;
                if (!empty($asset['size'])) $specificFields['Talla'] = $asset['size'];
                if (!empty($asset['gender_cut'])) $specificFields['Corte/Género'] = $asset['gender_cut'];
                if (!empty($asset['color'])) $specificFields['Color'] = $asset['color'];
                if (!empty($asset['material'])) $specificFields['Material'] = $asset['material'];
            }
            
            // Furniture/general dimensions
            if (!empty($asset['dimensions'])) {
                $hasSpecificFields = true;
                $specificFields['Dimensiones'] = $asset['dimensions'];
            }
            
            // Stock/Quantity info
            if (!empty($asset['quantity']) && $asset['quantity'] > 1) {
                $hasSpecificFields = true;
                $specificFields['Cantidad'] = $asset['quantity'];
                if (!empty($asset['batch_number'])) $specificFields['Lote/Batch'] = $asset['batch_number'];
                if (!empty($asset['min_stock'])) $specificFields['Stock Mínimo'] = $asset['min_stock'];
            }
            
            if ($hasSpecificFields): ?>
            <div class="mb-6 pb-6 border-b border-gray-100">
                <h4 class="text-xs font-bold text-gray-400 uppercase mb-3 flex items-center">
                    <i class="fas fa-cogs mr-2"></i> Especificaciones Técnicas
                </h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php foreach ($specificFields as $label => $value): ?>
                    <div>
                        <span class="block text-xs text-gray-500 mb-1"><?= htmlspecialchars($label) ?></span>
                        <span class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($value) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- DEVICE CREDENTIALS (if exist) -->
            <?php if (!empty($asset['device_user']) || !empty($asset['device_password'])): ?>
            <div class="mb-6 pb-6 border-b border-gray-100">
                <h4 class="text-xs font-bold text-gray-400 uppercase mb-3 flex items-center">
                    <i class="fas fa-lock mr-2"></i> Credenciales del Dispositivo
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                    <div>
                        <span class="block text-xs text-gray-500 mb-1">Usuario</span>
                        <span class="font-mono text-gray-800 text-sm"><?= htmlspecialchars($asset['device_user'] ?? '-') ?></span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 mb-1">Contraseña</span>
                        <span class="font-mono text-gray-800 text-sm"><?= !empty($asset['device_password']) ? '••••••••' : '-' ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- DISPOSAL INFO (if asset is disposed) -->
            <?php if (!empty($asset['disposal_date'])): ?>
            <div class="mb-6 pb-6 border-b border-gray-100">
                <h4 class="text-xs font-bold text-gray-400 uppercase mb-3 flex items-center">
                    <i class="fas fa-ban mr-2 text-red-500"></i> Información de Baja
                </h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-red-50 p-4 rounded-lg border border-red-200">
                    <div>
                        <span class="block text-xs text-gray-500 mb-1">Fecha de Baja</span>
                        <span class="font-bold text-red-700 text-sm"><?= date('d/m/Y', strtotime($asset['disposal_date'])) ?></span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 mb-1">Motivo</span>
                        <span class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($asset['disposal_reason'] ?? 'No especificado') ?></span>
                    </div>
                    <?php if (!empty($asset['disposal_price']) && $asset['disposal_price'] > 0): ?>
                    <div>
                        <span class="block text-xs text-gray-500 mb-1">Precio de Venta</span>
                        <span class="font-mono text-green-700 text-sm">$<?= number_format($asset['disposal_price'], 2) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($asset['book_value_at_disposal'])): ?>
                    <div>
                        <span class="block text-xs text-gray-500 mb-1">Valor en Libros</span>
                        <span class="font-mono text-gray-700 text-sm">$<?= number_format($asset['book_value_at_disposal'], 2) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- TIMESTAMPS -->
            <div class="mb-6">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-xs text-gray-500">
                    <div>
                        <span class="block text-gray-400 mb-1">Creado</span>
                        <span class="font-mono"><?= !empty($asset['created_at']) ? date('d/m/Y H:i', strtotime($asset['created_at'])) : '-' ?></span>
                    </div>
                    <?php if (!empty($asset['assigned_at'])): ?>
                    <div>
                        <span class="block text-gray-400 mb-1">Asignado el</span>
                        <span class="font-mono"><?= date('d/m/Y H:i', strtotime($asset['assigned_at'])) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
    </div>

    <!-- SECTION 2: ESPECIFICACIONES TÉCNICAS -->
    <?php if ($hasSpecificFields): ?>
    <div class="mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center border-b pb-2">
            <i class="fas fa-cogs mr-2 text-green-500"></i> Especificaciones Técnicas
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach ($specificFields as $label => $value): ?>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold"><?= htmlspecialchars($label) ?></span>
                <span class="font-bold text-gray-900"><?= htmlspecialchars($value) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- SECTION 3: INFORMACIÓN ADMINISTRATIVA -->
    <div class="mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center border-b pb-2">
            <i class="fas fa-file-invoice-dollar mr-2 text-purple-500"></i> Información Administrativa
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Tipo Adquisición</span>
                <span class="font-bold text-gray-900"><?= htmlspecialchars($asset['acquisition_type'] ?? 'Compra') ?></span>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Centro de Costos</span>
                <span class="font-bold text-gray-900"><?= htmlspecialchars($asset['cost_center'] ?? 'No asignado') ?></span>
            </div>
            <?php if ($asset['acquisition_type'] == 'Arrendamiento' && !empty($asset['leasing_company'])): ?>
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200 col-span-2">
                <span class="block text-xs text-purple-600 mb-1 uppercase font-bold">Empresa Arrendadora</span>
                <span class="font-bold text-purple-900"><i class="fas fa-building mr-1"></i><?= htmlspecialchars($asset['leasing_company']) ?></span>
            </div>
            <?php endif; ?>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Fecha Adquisición</span>
                <span class="font-mono text-gray-900"><?= htmlspecialchars($asset['purchase_date'] ?? '-') ?></span>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Costo Original</span>
                <span class="font-mono text-gray-900">$<?= number_format($asset['purchase_cost'], 2) ?></span>
            </div>
            <?php if ($asset['acquisition_type'] != 'Arrendamiento' && isset($asset['current_value'])): ?>
            <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-200">
                <span class="block text-xs text-emerald-600 mb-1 uppercase font-bold">Valor en Libros</span>
                <span class="font-bold text-emerald-900">$<?= number_format($asset['current_value'] ?? 0, 2) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- SECTION 4: CREDENCIALES DEL DISPOSITIVO -->
    <?php if (!empty($asset['device_user']) || !empty($asset['device_password'])): ?>
    <div class="mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center border-b pb-2">
            <i class="fas fa-lock mr-2 text-amber-500"></i> Credenciales del Dispositivo
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Usuario</span>
                <span class="font-mono text-gray-900"><?= htmlspecialchars($asset['device_user'] ?? '-') ?></span>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Contraseña</span>
                <span class="font-mono text-gray-900"><?= !empty($asset['device_password']) ? '••••••••' : '-' ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- SECTION 5: INFORMACIÓN DE BAJA -->
    <?php if (!empty($asset['disposal_date'])): ?>
    <div class="mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center border-b pb-2">
            <i class="fas fa-ban mr-2 text-red-500"></i> Información de Baja
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                <span class="block text-xs text-red-600 mb-1 uppercase font-bold">Fecha de Baja</span>
                <span class="font-bold text-red-900"><?= date('d/m/Y', strtotime($asset['disposal_date'])) ?></span>
            </div>
            <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                <span class="block text-xs text-red-600 mb-1 uppercase font-bold">Motivo</span>
                <span class="font-bold text-red-900"><?= htmlspecialchars($asset['disposal_reason'] ?? 'No especificado') ?></span>
            </div>
            <?php if (!empty($asset['disposal_price']) && $asset['disposal_price'] > 0): ?>
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <span class="block text-xs text-green-600 mb-1 uppercase font-bold">Precio de Venta</span>
                <span class="font-mono font-bold text-green-900">$<?= number_format($asset['disposal_price'], 2) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($asset['book_value_at_disposal'])): ?>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Valor en Libros</span>
                <span class="font-mono text-gray-900">$<?= number_format($asset['book_value_at_disposal'], 2) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- TIMESTAMPS -->
    <div class="pt-6 border-t border-gray-200">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-xs text-gray-500">
            <div>
                <span class="block text-gray-400 mb-1 uppercase font-bold">Creado</span>
                <span class="font-mono"><?= !empty($asset['created_at']) ? date('d/m/Y H:i', strtotime($asset['created_at'])) : '-' ?></span>
            </div>
            <?php if (!empty($asset['assigned_at'])): ?>
            <div>
                <span class="block text-gray-400 mb-1 uppercase font-bold">Asignado el</span>
                <span class="font-mono"><?= date('d/m/Y H:i', strtotime($asset['assigned_at'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ASSIGNMENT / STOCK -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
    <!-- Assignment Card -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 h-fit">
        <?php if (!empty($asset['assigned_to_name']) && $asset['quantity'] <= 1): ?>
        <h3 class="font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-user-tag text-blue-500 mr-2"></i> Asignación Actual
        </h3>
        <div class="flex items-center justify-between bg-blue-50 p-4 rounded-xl border border-blue-100">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-white text-blue-600 rounded-full flex items-center justify-center font-bold text-lg mr-3 shadow-sm border border-blue-200">
                    <?= strtoupper(substr($asset['assigned_to_name'], 0, 1)) ?>
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-lg"><?= htmlspecialchars($asset['assigned_to_name']) ?></p>
                    <p class="text-xs text-gray-500 uppercase">Usuario Asignado</p>
                </div>
            </div>
            <div class="text-right">
                <form action="/assets/unassign/<?= $asset['id'] ?>" method="POST" onsubmit="return confirm('¿Liberar activo? Pasará a estar disponible.')">
                    <button type="submit" class="text-xs bg-white text-red-500 border border-red-200 px-2 py-1 rounded hover:bg-red-50 font-bold transition">
                        <i class="fas fa-unlink mr-1"></i> Liberar
                    </button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <h3 class="font-bold text-gray-800 mb-4 flex items-center justify-between">
            <span><i class="fas fa-cubes text-blue-500 mr-2"></i> Control de Inventario</span>
            <?php if (!empty($asset['batch_number'])): ?>
            <span class="text-xs bg-gray-100 px-2 py-1 rounded border border-gray-200 font-mono text-gray-500">Lote: <?= htmlspecialchars($asset['batch_number']) ?></span>
            <?php endif; ?>
        </h3>

        <div class="flex items-center justify-center mb-6 py-4 bg-slate-50 rounded-xl border border-slate-100">
            <div class="text-center">
                <span class="text-5xl font-extrabold text-gray-800 tracking-tight"><?= $asset['quantity'] ?></span>
                <span class="text-xs text-gray-400 block uppercase font-bold mt-1">Unidades Disponibles</span>
            </div>
        </div>
        
        <!-- Simplified Assignment Form for Stock -->
        <div class="bg-blue-50 p-5 rounded-xl border border-blue-100">
             <form action="/assets/assign/<?= $asset['id'] ?>" method="POST">
                <label class="block text-xs font-bold text-blue-800 uppercase mb-3">Asignar Unidad</label>
                <div class="flex gap-3">
                    <select name="assigned_to" class="w-full p-2.5 border border-blue-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">-- Seleccionar Usuario --</option>
                        <?php foreach ($available_users as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="bg-blue-600 text-white font-bold py-2.5 px-4 rounded-lg shadow hover:bg-blue-700 transition">
                        Asignar
                    </button>
                </div>
             </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- DOCUMENTS PARTIAL -->
    <?php 
    $documents = $asset['documents'] ?? [];
    $entityType = 'asset';
    $entityId = $asset['id'];
    include __DIR__ . '/../partials/documents_list.php'; 
    ?>
</div>

<!-- INCIDENTS (Placeholder) -->
<div class="bg-white p-6 rounded-xl shadow mb-12 border-t-4 border-amber-500">
    <div class="flex justify-between items-center mb-6">
        <h3 class="font-bold text-gray-800 text-lg flex items-center"><i class="fas fa-history text-amber-500 mr-2"></i> Historial de Eventos</h3>
    </div>
    <div class="pl-8 text-gray-400 italic">Sin historial de incidencias (Módulo pendiente).</div>
</div>

<!-- MODALS (Placeholder Structure) -->
<div id="incidentModal" class="fixed inset-0 bg-gray-900/60 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg text-center">
        <h2 class="text-lg font-bold mb-4">Reportar Incidencia</h2>
        <p class="text-gray-600 mb-4">Esta funcionalidad estará disponible cuando se implemente el módulo de incidencias en el backend.</p>
        <button onclick="document.getElementById('incidentModal').classList.add('hidden'); document.getElementById('incidentModal').classList.remove('flex');" class="bg-gray-200 px-4 py-2 rounded text-gray-800 font-bold">Cerrar</button>
    </div>
</div>

<div id="disposalModal" class="fixed inset-0 bg-gray-900/60 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg text-center">
        <h2 class="text-lg font-bold mb-4">Procesar Baja</h2>
        <p class="text-gray-600 mb-4">Esta funcionalidad requiere lógica de backend adicional (stop depreciation, calc P&L).</p>
        <button onclick="document.getElementById('disposalModal').classList.add('hidden'); document.getElementById('disposalModal').classList.remove('flex');" class="bg-gray-200 px-4 py-2 rounded text-gray-800 font-bold">Cerrar</button>
    </div>
</div>

<script>
    function openIncidentModal() {
        document.getElementById('incidentModal').classList.remove('hidden');
        document.getElementById('incidentModal').classList.add('flex');
    }
    function openDisposalModal() {
        document.getElementById('disposalModal').classList.remove('hidden');
        document.getElementById('disposalModal').classList.add('flex');
    }
    
    // PDF Download Functions
    function endMaintenance(id) { alert('Funcionalidad pendiente.'); }
    function downloadPdf(id) { 
        window.open('/reports/responsive_letter/' + id, '_blank'); 
    }
    function downloadHistory(id) { 
        window.open('/reports/history/' + id, '_blank'); 
    }
    function printLabel(id) { alert('Impresión de etiqueta pendiente.'); }
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
