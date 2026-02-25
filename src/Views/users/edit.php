<?php ob_start(); ?>

<div class="mb-6">
    <a href="/users" class="inline-flex items-center bg-white px-4 py-2 border border-gray-200 shadow-sm text-gray-500 rounded-lg hover:bg-gray-50 hover:text-gray-700 transition font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Volver a Usuarios
    </a>
</div>

<div class="max-w-5xl mx-auto">
    <div class="flex items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800"><i class="fas fa-user-edit mr-3 text-blue-500"></i>Editar Usuario: <?= htmlspecialchars($user['name']) ?></h2>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form action="/users/update/<?= $user['id'] ?>" method="POST">
        <!-- PERSONAL INFORMATION -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <h3 class="text-lg font-bold text-gray-700 mb-4 pb-2 border-b border-gray-200">
                <i class="fas fa-id-card mr-2 text-gray-400"></i>Información Personal
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre(s) <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Segundo Nombre</label>
                    <input type="text" name="middle_name" value="<?= htmlspecialchars($user['middle_name'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Apellido Paterno <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Apellido Materno</label>
                    <input type="text" name="second_last_name" value="<?= htmlspecialchars($user['second_last_name'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre Completo (Display) <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="Ej: Juan Pérez García" required>
                    <p class="text-xs text-gray-500 mt-1">Nombre como aparecerá en el sistema</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Género</label>
                    <select name="gender" class="w-full p-2.5 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 outline-none transition">
                        <option value="">-- Seleccionar --</option>
                        <option value="Masculino" <?= ($user['gender'] ?? '') == 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                        <option value="Femenino" <?= ($user['gender'] ?? '') == 'Femenino' ? 'selected' : '' ?>>Femenino</option>
                        <option value="Otro" <?= ($user['gender'] ?? '') == 'Otro' ? 'selected' : '' ?>>Otro</option>
                        <option value="Prefiero no decir" <?= ($user['gender'] ?? '') == 'Prefiero no decir' ? 'selected' : '' ?>>Prefiero no decir</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- CONTACT INFORMATION -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <h3 class="text-lg font-bold text-gray-700 mb-4 pb-2 border-b border-gray-200">
                <i class="fas fa-address-book mr-2 text-gray-400"></i>Información de Contacto
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Teléfono</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="Ej: +52 123 456 7890">
                </div>
            </div>
        </div>

        <!-- COMPANY INFORMATION -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <h3 class="text-lg font-bold text-gray-700 mb-4 pb-2 border-b border-gray-200">
                <i class="fas fa-building mr-2 text-gray-400"></i>Información Laboral
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Empresa</label>
                    <input type="text" name="company" value="<?= htmlspecialchars($user['company'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Departamento</label>
                    <input type="text" name="department" value="<?= htmlspecialchars($user['department'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="Ej: Tecnología, RRHH, Finanzas">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Puesto/Rol</label>
                    <input type="text" name="role" value="<?= htmlspecialchars($user['role'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="Ej: Desarrollador, Gerente, Analista">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Fecha de Ingreso</label>
                    <input type="date" name="entry_date" value="<?= htmlspecialchars($user['entry_date'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>
            </div>
        </div>

        <!-- SYSTEM ACCESS -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <h3 class="text-lg font-bold text-gray-700 mb-4 pb-2 border-b border-gray-200">
                <i class="fas fa-key mr-2 text-gray-400"></i>Acceso al Sistema
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Contraseña</label>
                    <input type="password" name="password" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
                    <p class="text-xs text-gray-500 mt-1">Dejar en blanco para mantener la contraseña actual</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Estado <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full p-2.5 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 outline-none transition">
                        <option value="Activo" <?= ($user['status'] ?? '') == 'Activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= ($user['status'] ?? '') == 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- ACTIONS -->
        <div class="flex justify-end gap-3 pt-4">
            <a href="/users" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition font-medium shadow-sm">
                Cancelar
            </a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition shadow flex items-center transform hover:scale-105 active:scale-95 font-medium">
                <i class="fas fa-save mr-2"></i> Actualizar Usuario
            </button>
        </div>
    </form>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
