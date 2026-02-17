<?php ob_start(); ?>

<div class="mb-6"><a href="/users" class="text-gray-500 hover:text-gray-800 transition"><i class="fas fa-arrow-left mr-2"></i> Volver a Usuarios</a></div>

<div class="bg-white p-8 rounded-xl shadow-lg mb-8 border border-gray-100">
    
    <!-- HEADER -->
    <div class="mb-8 pb-6 border-b border-gray-200">
        <div class="flex items-center gap-3 mb-3">
            <h1 class="text-4xl font-bold text-gray-900"><?= htmlspecialchars($user['name']) ?></h1>
            <?php 
                $statusClass = ($user['status'] == 'Activo') ? 'bg-green-100 text-green-700 border-green-300' : 'bg-red-100 text-red-700 border-red-300';
            ?>
            <span class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase border <?= $statusClass ?>">
                <?= htmlspecialchars($user['status']) ?>
            </span>
        </div>
        <p class="text-gray-500 text-lg flex items-center gap-2"><i class="fas fa-envelope text-gray-400"></i> <?= htmlspecialchars($user['email']) ?></p>
    </div>

    <!-- SECTION 1: NOMBRE COMPLETO -->
    <?php if (!empty($user['first_name']) || !empty($user['last_name'])): ?>
    <div class="mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center border-b pb-2">
            <i class="fas fa-id-card mr-2 text-blue-500"></i> Nombre Completo
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Primer Nombre</span>
                <span class="font-bold text-gray-900"><?= htmlspecialchars($user['first_name'] ?? '-') ?></span>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Segundo Nombre</span>
                <span class="font-bold text-gray-900"><?= htmlspecialchars($user['middle_name'] ?? '-') ?></span>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Apellido Pat.</span>
                <span class="font-bold text-gray-900"><?= htmlspecialchars($user['last_name'] ?? '-') ?></span>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Apellido Mat.</span>
                <span class="font-bold text-gray-900"><?= htmlspecialchars($user['second_last_name'] ?? '-') ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- SECTION 2: INFORMACIÓN LABORAL -->
    <div class="mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center border-b pb-2">
            <i class="fas fa-briefcase mr-2 text-purple-500"></i> Información Laboral
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Empresa</span>
                <span class="font-bold text-gray-900"><?= htmlspecialchars($user['company'] ?? '-') ?></span>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Departamento</span>
                <span class="font-bold text-gray-900"><?= htmlspecialchars($user['department'] ?? '-') ?></span>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Rol/Puesto</span>
                <span class="font-bold text-gray-900"><?= htmlspecialchars($user['role'] ?? '-') ?></span>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Fecha Ingreso</span>
                <span class="font-mono text-gray-900"><?= !empty($user['entry_date']) ? date('d/m/Y', strtotime($user['entry_date'])) : '-' ?></span>
            </div>
        </div>
    </div>
    
    <!-- SECTION 3: CONTACTO Y PERSONAL -->
    <div class="mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center border-b pb-2">
            <i class="fas fa-user mr-2 text-green-500"></i> Contacto y Datos Personales
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Teléfono</span>
                <span class="font-bold text-gray-900"><?= htmlspecialchars($user['phone'] ?? '-') ?></span>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Género</span>
                <span class="font-bold text-gray-900"><?= htmlspecialchars($user['gender'] ?? '-') ?></span>
            </div>
            <?php if (!empty($user['system_role'])): ?>
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <span class="block text-xs text-purple-600 mb-1 uppercase font-bold">Rol del Sistema</span>
                <span class="px-2 py-0.5 rounded bg-purple-100 text-purple-700 text-sm font-bold"><?= htmlspecialchars($user['system_role']) ?></span>
            </div>
            <?php endif; ?>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Creado</span>
                <span class="font-mono text-gray-900"><?= !empty($user['created_at']) ? date('d/m/Y H:i', strtotime($user['created_at'])) : '-' ?></span>
            </div>
        </div>
    </div>

</div>

        <!-- Action Buttons -->
        <div class="flex flex-col gap-2 w-full md:w-auto">
            <a href="/reports/kardex/<?= $user['id'] ?>" target="_blank" class="bg-white text-indigo-600 border border-indigo-200 hover:bg-indigo-50 px-4 py-2 rounded-lg shadow-sm font-bold flex items-center justify-center transition" title="Descargar Historial de Movimientos">
                <i class="fas fa-file-contract mr-2"></i> Kardex / Reporte RRHH
            </a>
            
            <button onclick="openAssignModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 font-bold flex items-center justify-center transition">
                <i class="fas fa-laptop-medical mr-2"></i> Asignar Equipo
            </button>

            <!-- Need edit route for users -->
            <!-- <a href="/users/edit/<?= $user['id'] ?>" class="bg-yellow-500 text-white px-4 py-2 rounded-lg shadow hover:bg-yellow-600 font-bold flex items-center justify-center transition">
                <i class="fas fa-user-edit mr-2"></i> Editar Usuario
            </a> -->
        </div>
    </div>
</div>

<!-- Assigned Assets List -->
<h2 class="font-bold text-xl mb-4 text-gray-700 flex items-center"><i class="fas fa-laptop mr-2 text-blue-500"></i> Activos Asignados (<?= count($assigned_assets) ?>)</h2>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <?php if (!empty($assigned_assets)): ?>
    <?php foreach ($assigned_assets as $a): ?>
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md hover:border-blue-300 transition relative group">
        <a href="/assets/detail/<?= $a['id'] ?>" class="block pr-8">
            <div class="flex items-start justify-between mb-2">
                <div class="bg-blue-50 text-blue-600 w-10 h-10 rounded flex items-center justify-center text-lg"><i class="fas fa-desktop"></i></div>
                <span class="text-xs font-mono text-gray-400 bg-gray-50 px-2 py-1 rounded"><?= htmlspecialchars($a['serial_number'] ?? 'S/N') ?></span>
            </div>
            <p class="font-bold text-gray-800 group-hover:text-blue-600 transition"><?= htmlspecialchars($a['name']) ?></p>
            <p class="text-xs text-gray-500 uppercase font-semibold mt-1"><?= htmlspecialchars($a['brand']) ?> <?= htmlspecialchars($a['model']) ?></p>
        </a>

        <!-- Unassign Button -->
        <div class="absolute top-4 right-4 z-10">
            <form action="/assets/unassign/<?= $a['id'] ?>" method="POST" onsubmit="return confirm('¿Estás seguro de DAR DE BAJA este equipo del usuario? El activo pasará a estado Disponible.');">
                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                <button type="submit" class="text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-full w-8 h-8 flex items-center justify-center transition" title="Dar de Baja / Devolver">
                    <i class="fas fa-times-circle text-xl"></i>
                </button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
    <div class="col-span-full bg-gray-50 p-6 rounded-lg text-center text-gray-400 italic border border-dashed border-gray-300">
        <p>Este usuario no tiene activos asignados actualmente.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Assigned Accounts List -->
<h2 class="font-bold text-xl mb-4 mt-8 text-gray-700 flex items-center"><i class="fas fa-key mr-2 text-yellow-500"></i> Cuentas y Accesos (<?= count($assigned_accounts) ?>)</h2>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php if (!empty($assigned_accounts)): ?>
    <?php foreach ($assigned_accounts as $acc): ?>
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 relative group">
        <div class="absolute top-4 right-4">
            <a href="/accounts/edit/<?= $acc['id'] ?>" class="text-gray-300 hover:text-blue-500 transition"><i class="fas fa-pen"></i></a>
        </div>
        <div class="flex items-center gap-3 mb-3">
            <div class="w-8 h-8 rounded-full bg-yellow-50 flex items-center justify-center text-yellow-600"><i class="fas fa-lock"></i></div>
            <p class="font-bold text-gray-800"><?= htmlspecialchars($acc['service_name']) ?></p>
        </div>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between border-b border-gray-50 pb-1">
                <span class="text-gray-400 text-xs">Usuario</span>
                <span class="text-gray-700 font-medium"><?= htmlspecialchars($acc['username']) ?></span>
            </div>
            <div class="flex justify-between items-center pt-1">
                <span class="text-gray-400 text-xs">Password</span>
                <code class="bg-gray-100 px-2 py-0.5 rounded text-xs font-mono text-gray-600 select-all cursor-pointer hover:bg-gray-200 transition" title="Clic para copiar" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($acc['password']) ?>'); alert('Contraseña copiada');"><?= htmlspecialchars($acc['password']) ?></code>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
    <div class="col-span-full bg-gray-50 p-6 rounded-lg text-center text-gray-400 italic border border-dashed border-gray-300">
        <p>Este usuario no tiene cuentas asignadas.</p>
    </div>
    <?php endif; ?>
</div>

<!-- ASSIGNMENT MODAL -->
<div id="assignModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Asignar Equipo</h3>
            <button onclick="closeAssignModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="p-6 space-y-6">
            <!-- Option 1: New Asset (Redirect to create form with pre-filled user) -->
            <a href="/assets/create?assigned_to=<?= $user['id'] ?>" class="block w-full text-left group">
                <div class="border border-blue-200 bg-blue-50 rounded-lg p-4 hover:bg-blue-100 hover:border-blue-300 transition flex items-center">
                    <div class="w-12 h-12 rounded-full bg-blue-200 text-blue-700 flex items-center justify-center text-xl mr-4 group-hover:bg-blue-600 group-hover:text-white transition">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-blue-800">Registrar Nuevo Activo</h4>
                        <p class="text-sm text-blue-600">Crear un activo desde cero y asignarlo inmediatamente.</p>
                    </div>
                </div>
            </a>

            <div class="relative flex py-1 items-center">
                <div class="flex-grow border-t border-gray-300"></div>
                <span class="flex-shrink mx-4 text-gray-400 text-sm font-semibold">O asignar existente</span>
                <div class="flex-grow border-t border-gray-300"></div>
            </div>

            <!-- Option 2: Select Existing -->
            <form action="/assets/assign_existing" method="POST">
                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Seleccionar de Inventario Disponible</label>
                    <select name="asset_id" class="w-full p-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-indigo-500 outline-none" required>
                        <option value="">-- Seleccione un Activo --</option>
                        <?php foreach ($available_assets as $item): ?>
                        <option value="<?= $item['id'] ?>">
                            <?= htmlspecialchars($item['name']) ?> - <?= htmlspecialchars($item['serial_number'] ?? 'S/N') ?> (<?= htmlspecialchars($item['brand']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-700 transition">
                    Asignar Seleccionado
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function openAssignModal() {
        document.getElementById('assignModal').classList.remove('hidden');
    }
    function closeAssignModal() {
        document.getElementById('assignModal').classList.add('hidden');
    }
    document.getElementById('assignModal').addEventListener('click', function(e) {
        if (e.target === this) closeAssignModal();
    });
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
