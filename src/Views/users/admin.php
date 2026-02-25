<?php ob_start(); ?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Gestión de Acceso al Sistema</h1>
        <p class="text-sm text-gray-500 mt-1">Otorgue permisos de administración o edición a empleados existentes.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- TARJETA: OTORGAR ACCESO -->
    <div class="bg-white p-6 rounded-xl shadow border border-gray-200 h-fit">
        <h2 class="font-bold text-lg mb-4 border-b pb-2 text-blue-600">
            <i class="fas fa-user-plus mr-2"></i> Conceder Acceso
        </h2>
        <form action="/admin/users/grant" method="POST">
            
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Seleccionar Empleado</label>
                <div class="relative">
                    <select name="user_id" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition bg-white appearance-none pr-8" required>
                        <option value="">-- Buscar empleado --</option>
                        <?php if (!empty($employees)): ?>
                            <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>">
                                <?= htmlspecialchars($emp['name']) ?> (<?= htmlspecialchars($emp['email']) ?>)
                            </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-3.5 text-gray-400 pointer-events-none"></i>
                </div>
                <p class="text-xs text-gray-400 mt-1">Solo se muestran empleados sin acceso actual.</p>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Contraseña de Sistema</label>
                <input type="password" name="password" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="Mínimo 6 caracteres" required>
            </div>

            <div class="mb-6">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nivel de Acceso</label>
                <div class="relative">
                    <select name="role" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition bg-white appearance-none pr-8">
                        <option value="normal">Normal (Editor de Inventario)</option>
                        <option value="admin">Administrador (Total + Auditoría)</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-3.5 text-gray-400 pointer-events-none"></i>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2.5 rounded-lg hover:bg-blue-700 shadow transition transform hover:scale-105">
                <i class="fas fa-key mr-2"></i> Habilitar Acceso
            </button>
        </form>
    </div>

    <!-- LISTA: USUARIOS CON ACCESO -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow overflow-hidden w-full border border-gray-200">
        <div class="p-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
            <h3 class="font-bold text-gray-700">Usuarios Habilitados (<?= count($systemUsers) ?>)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="p-4 text-sm font-semibold tracking-wide">Usuario / Email</th>
                        <th class="p-4 text-sm font-semibold tracking-wide">Departamento</th>
                        <th class="p-4 text-sm font-semibold tracking-wide text-center">Rol Sistema</th>
                        <th class="p-4 text-sm font-semibold tracking-wide text-center">Acciones</th>
                    </tr>
                </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($systemUsers)): ?>
                    <?php foreach ($systemUsers as $u): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-4">
                            <div class="font-bold text-gray-800"><?= htmlspecialchars($u['name']) ?></div>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars($u['email']) ?></div>
                        </td>
                        <td class="p-4 text-sm text-gray-700">
                            <?= htmlspecialchars($u['department'] ?? '-') ?>
                        </td>
                        <td class="p-4 text-center">
                            <?php 
                                $sys_role = strtolower($u['system_role'] ?? 'normal');
                                $is_admin = $sys_role === 'admin';
                                $role_cls = $is_admin ? 'bg-purple-100 text-purple-700 border-purple-200' : 'bg-green-100 text-green-700 border-green-200';
                            ?>
                            <span class="px-2 py-1 rounded-full text-xs font-bold border <?= $role_cls ?>">
                                <?= strtoupper($sys_role) ?>
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex justify-center gap-2">
                                <!-- Prevent deleting self or main admin if easy to identify, else just link -->
                                <a href="/admin/users/revoke/<?= $u['id'] ?>" 
                                   class="text-red-600 hover:bg-red-50 border border-red-200 p-2 rounded transition" 
                                   onclick="return confirm('¿Seguro que desea revocar el acceso al sistema de <?= htmlspecialchars($u['name']) ?>? El empleado NO será eliminado, solo su login.')"
                                   title="Revocar Acceso">
                                    <i class="fas fa-user-slash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-400 italic">No hay usuarios con acceso configurado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
