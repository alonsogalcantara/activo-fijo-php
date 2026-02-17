<?php ob_start(); ?>

<div class="mb-6">
    <a href="/users" class="text-gray-500 hover:text-gray-800 font-medium transition flex items-center group">
        <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center mr-2 group-hover:bg-gray-100 transition">
            <i class="fas fa-arrow-left"></i>
        </div>
        Volver a Usuarios
    </a>
</div>

<div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-lg border border-gray-100">
    <h2 class="text-3xl font-bold mb-6 text-gray-800"><i class="fas fa-user-plus mr-3 text-blue-500"></i>Crear Usuario</h2>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form action="/users/store" method="POST">
        <!-- PERSONAL INFORMATION -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-gray-700 mb-4 pb-2 border-b border-gray-200">
                <i class="fas fa-id-card mr-2 text-gray-400"></i>Información Personal
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm">Nombre(s) <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm">Segundo Nombre</label>
                    <input type="text" name="middle_name" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm">Apellido Paterno <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm">Apellido Materno</label>
                    <input type="text" name="second_last_name" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm">Nombre Completo (Display) <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ej: Juan Pérez García" required>
                    <p class="text-xs text-gray-500 mt-1">Nombre como aparecerá en el sistema</p>
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm">Género</label>
                    <select name="gender" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Seleccionar --</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                        <option value="Otro">Otro</option>
                        <option value="Prefiero no decir">Prefiero no decir</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- CONTACT INFORMATION -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-gray-700 mb-4 pb-2 border-b border-gray-200">
                <i class="fas fa-address-book mr-2 text-gray-400"></i>Información de Contacto
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm">Teléfono</label>
                    <input type="tel" name="phone" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ej: +52 123 456 7890">
                </div>
            </div>
        </div>

        <!-- COMPANY INFORMATION -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-gray-700 mb-4 pb-2 border-b border-gray-200">
                <i class="fas fa-building mr-2 text-gray-400"></i>Información Laboral
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm">Empresa</label>
                    <input type="text" name="company" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm">Departamento</label>
                    <input type="text" name="department" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ej: Tecnología, RRHH, Finanzas">
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm">Puesto/Rol</label>
                    <input type="text" name="role" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ej: Desarrollador, Gerente, Analista">
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm">Fecha de Ingreso</label>
                    <input type="date" name="entry_date" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- SYSTEM ACCESS -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-gray-700 mb-4 pb-2 border-b border-gray-200">
                <i class="fas fa-key mr-2 text-gray-400"></i>Acceso al Sistema
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm">Contraseña <span class="text-red-500">*</span></label>
                    <input type="password" name="password" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <p class="text-xs text-gray-500 mt-1">Mínimo 6 caracteres recomendado</p>
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm">Estado <span class="text-red-500">*</span></label>
                    <select name="status" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Activo" selected>Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- ACTIONS -->
        <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
            <a href="/users" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-lg transition shadow">
                <i class="fas fa-times mr-2"></i>Cancelar
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition shadow">
                <i class="fas fa-save mr-2"></i>Crear Usuario
            </button>
        </div>
    </form>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
