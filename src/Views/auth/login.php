<?php ob_start(); ?>

<div class="flex items-center justify-center w-full min-h-[80vh]">
    <div class="max-w-md w-full mx-auto space-y-8 bg-white rounded-2xl shadow-xl border border-gray-100 p-10">
        <div>
            <div class="w-20 h-20 mx-auto bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-4xl shadow-sm mb-4">
                <i class="fas fa-cubes"></i>
            </div>
            <h2 class="text-center font-extrabold text-3xl text-gray-900">Bienvenido de nuevo</h2>
            <p class="mt-2 text-center text-sm text-gray-500">
                Ingresa tus credenciales para acceder al sistema
            </p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm flex items-start mt-4" role="alert">
                <i class="fas fa-exclamation-circle mt-1 mr-3"></i>
                <span class="block sm:inlinetext-sm"><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST" class="mt-8 space-y-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1" for="email">
                        Correo Electrónico
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 focus:bg-white transition text-sm text-gray-900" id="email" name="email" type="email" placeholder="ejemplo@correo.com" required>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1" for="password">
                        Contraseña
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 focus:bg-white transition text-sm text-gray-900" id="password" name="password" type="password" placeholder="••••••••••••" required>
                    </div>
                </div>
            </div>

            <div>
                <button class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-md transform transition" type="submit">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-sign-in-alt text-blue-500 group-hover:text-blue-400 transition"></i>
                    </span>
                    Iniciar Sesión
                </button>
            </div>
        </form>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
