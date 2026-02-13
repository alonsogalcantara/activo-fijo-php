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
<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-8">
    <div class="md:flex">
        <!-- PHOTO -->
        <div class="md:w-1/3 relative bg-gray-50 border-r border-gray-100 min-h-[300px] flex items-center justify-center overflow-hidden group">
            <?php if (!empty($asset['photo_filename'])): ?>
            <img src="/uploads/<?= htmlspecialchars($asset['photo_filename']) ?>" class="w-full h-full object-cover absolute inset-0 transition duration-500 group-hover:scale-105">
            <?php else: ?>
            <div class="text-6xl text-gray-300">
                <?php 
                $icon = 'fa-box';
                if ($asset['category'] == 'Vehículo') $icon = 'fa-car';
                elseif (strpos($asset['category'], 'Computadora') !== false) $icon = 'fa-laptop';
                elseif ($asset['category'] == 'Uniforme') $icon = 'fa-tshirt';
                elseif ($asset['category'] == 'Herramienta') $icon = 'fa-tools';
                ?>
                <i class="fas <?= $icon ?>"></i>
            </div>
            <?php endif; ?>

            <div class="absolute top-4 left-4">
                <?php 
                $statusColors = [
                    'Disponible' => 'bg-emerald-500',
                    'Asignado' => 'bg-blue-600',
                    'En Mantenimiento' => 'bg-amber-500',
                    'De Baja' => 'bg-red-600'
                ];
                $statusColor = $statusColors[$asset['status']] ?? 'bg-gray-500';
                ?>
                <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg text-white <?= $statusColor ?>">
                    <?= htmlspecialchars($asset['status']) ?>
                </span>
            </div>
        </div>

        <!-- DETAILS -->
        <div class="md:w-2/3 p-8">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-bold uppercase tracking-wide bg-gray-100 text-gray-500 px-2 py-0.5 rounded"><?= htmlspecialchars($asset['category']) ?></span>
                        <span class="text-xs font-mono text-gray-400">ID: <?= $asset['id'] ?></span>
                        
                        <?php if ($asset['acquisition_type'] == 'Arrendamiento'): ?>
                        <span class="text-xs font-bold uppercase tracking-wide bg-purple-100 text-purple-600 px-2 py-0.5 rounded border border-purple-200">
                            <i class="fas fa-file-contract mr-1"></i>Arrendamiento
                        </span>
                        <?php endif; ?>
                    </div>
                    <h1 class="text-4xl font-extrabold text-gray-900 mb-2"><?= htmlspecialchars($asset['name']) ?></h1>
                    <p class="text-gray-500 font-mono text-sm bg-gray-50 inline-block px-2 py-1 rounded border border-gray-200">
                        <i class="fas fa-barcode mr-2 text-gray-400"></i> <?= htmlspecialchars($asset['serial_number'] ?? 'Sin Identificador') ?>
                    </p>
                </div>
            </div>

            <!-- DYNAMIC INFO -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-6 text-sm mb-6 pb-6 border-b border-gray-100">
                <div><span class="block text-gray-400 text-xs font-bold uppercase">Marca</span><span class="font-bold text-gray-800"><?= htmlspecialchars($asset['brand'] ?? '-') ?></span></div>
                <div><span class="block text-gray-400 text-xs font-bold uppercase">Modelo</span><span class="font-bold text-gray-800"><?= htmlspecialchars($asset['model'] ?? '-') ?></span></div>
                <!-- Additional fields can be added here based on category if stored in generic fields or json -->
            </div>

            <!-- ADMIN INFO -->
            <div class="mb-6">
                <h4 class="text-xs font-bold text-gray-400 uppercase mb-3 flex items-center">
                    <i class="fas fa-file-invoice-dollar mr-2"></i> Información Administrativa
                </h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <div>
                        <span class="block text-xs text-gray-500 mb-1">Tipo Adquisición</span>
                        <span class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($asset['acquisition_type'] ?? 'Compra') ?></span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 mb-1">Centro de Costos</span>
                        <span class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($asset['cost_center'] ?? 'No asignado') ?></span>
                    </div>
                    <?php if ($asset['acquisition_type'] == 'Arrendamiento'): ?>
                    <div class="col-span-2">
                        <span class="block text-xs text-gray-500 mb-1">Empresa Arrendadora</span>
                        <span class="font-bold text-purple-700 text-sm"><i class="fas fa-building mr-1"></i> <?= htmlspecialchars($asset['leasing_company'] ?? 'No especificada') ?></span>
                    </div>
                    <?php endif; ?>
                    <div>
                        <span class="block text-xs text-gray-500 mb-1">Fecha Adquisición</span>
                        <span class="font-mono text-gray-800 text-sm"><?= htmlspecialchars($asset['purchase_date'] ?? '') ?></span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 mb-1">Costo Original</span>
                        <span class="font-mono text-gray-800 text-sm">$<?= number_format($asset['purchase_cost'], 2) ?></span>
                    </div>
                    
                    <!-- Depreciation Warning -->
                     <?php if ($asset['acquisition_type'] != 'Arrendamiento'): ?>
                     <div class="col-span-2">
                        <span class="block text-xs text-gray-500 mb-1">Valor en Libros Actual</span>
                        <span class="font-bold text-gray-800 text-sm">$<?= number_format($asset['current_value'] ?? 0, 2) ?></span>
                     </div>
                     <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($asset['description'])): ?>
            <div>
                <p class="text-sm text-gray-600 italic"><i class="fas fa-sticky-note mr-2 text-gray-400"></i><?= htmlspecialchars($asset['description']) ?></p>
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

    <!-- DOCUMENTS (Placeholder) -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 h-fit">
        <h3 class="font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-folder-open text-yellow-500 mr-2"></i> Archivos Adjuntos</h3>
        <div class="text-center py-8 text-gray-400 border-2 border-dashed border-gray-100 rounded-lg">
            <i class="fas fa-cloud-upload-alt text-3xl mb-2 opacity-50"></i>
            <p class="text-xs">No hay documentos adjuntos.</p>
        </div>
        
        <!-- Upload Form Placeholder -->
        <div class="mt-4 pt-4 border-t border-gray-100">
            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Subir Nuevo Documento</label>
            <div class="flex gap-2">
                <input type="file" disabled class="block w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-50 file:text-gray-400 hover:file:bg-gray-100" />
                <button disabled class="bg-gray-100 text-gray-400 px-3 py-1 rounded text-sm"><i class="fas fa-upload"></i></button>
            </div>
            <p class="text-[10px] text-gray-400 mt-1">* Funcionalidad de documentos pendiente de backend</p>
        </div>
    </div>
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
