<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Activos</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/tailwind.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: "Inter", sans-serif; }
    </style>
    <script>
        window.API_BASE = ""; // Adjust if needed
        window.JWT_TOKEN = ""; // Placeholder for now
        window.AUDIT_USER = "<?= htmlspecialchars($_SESSION['user_name'] ?? 'System') ?>";
    </script>
</head>
<body class="bg-gray-100 flex min-h-screen text-gray-800">

    <?php 
    $uri = $_SERVER['REQUEST_URI'];
    // Determine active page for highlighting
    $active_page = 'dashboard';
    if (strpos($uri, '/assets') !== false) $active_page = 'assets';
    elseif (strpos($uri, '/users') !== false) $active_page = 'users';
    elseif (strpos($uri, '/accounts') !== false && strpos($uri, 'accounting') === false) $active_page = 'accounts';
    elseif (strpos($uri, '/accounting') !== false) $active_page = 'accounting';
    elseif (strpos($uri, '/dashboard') !== false) $active_page = 'dashboard';
    ?>

    <?php if (isset($_SESSION['user_id'])): ?>
    <aside class="w-64 bg-slate-900 text-white flex flex-col shadow-xl z-10 shrink-0">
        <div class="h-16 flex items-center justify-center font-bold text-xl border-b border-slate-800 tracking-wider">
            <i class="fas fa-boxes mr-2"></i> Activo Fijo
        </div>
        <nav class="flex-1 p-4 space-y-2 text-sm font-medium overflow-y-auto">
            <a href="/dashboard"
                class="block py-2.5 px-4 rounded transition <?= $active_page == 'dashboard' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800 text-slate-300' ?>">
                <i class="fas fa-home w-6"></i> Inicio
            </a>
            <a href="/assets"
                class="block py-2.5 px-4 rounded transition <?= $active_page == 'assets' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800 text-slate-300' ?>">
                <i class="fas fa-laptop w-6"></i> Activos
            </a>
            <a href="/users"
                class="block py-2.5 px-4 rounded transition <?= $active_page == 'users' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800 text-slate-300' ?>">
                <i class="fas fa-users w-6"></i> Usuarios
            </a>
            <a href="/accounts"
                class="block py-2.5 px-4 rounded transition <?= $active_page == 'accounts' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800 text-slate-300' ?>">
                <i class="fas fa-key w-6"></i> Cuentas
            </a>
            <a href="/accounting"
                class="block py-2.5 px-4 rounded transition <?= $active_page == 'accounting' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800 text-slate-300' ?>">
                <i class="fas fa-calculator w-6"></i> Contabilidad
            </a>

            <!-- SECCIÓN ADMIN -->
            <?php if (($_SESSION['system_role'] ?? '') == 'admin'): ?>
            <div class="pt-4 mt-4 border-t border-slate-800">
                <p class="px-4 text-xs font-bold text-slate-500 uppercase mb-2">
                    Administración
                </p>
                <a href="/admin/users" class="block py-2.5 px-4 rounded transition hover:bg-slate-800 text-slate-300">
                    <i class="fas fa-users-cog w-6"></i> Usuarios Sistema
                </a>
                <a href="/audit" class="block py-2.5 px-4 rounded transition hover:bg-slate-800 text-slate-300">
                    <i class="fas fa-shield-alt w-6"></i> Auditoría
                </a>
            </div>
            <?php endif; ?>

            <!-- SECCIÓN LOGOUT -->
            <div class="pt-4 mt-4 border-t border-slate-800">
                <a href="/logout"
                    class="block py-2.5 px-4 rounded transition hover:bg-red-600 hover:text-white text-slate-300">
                    <i class="fas fa-sign-out-alt w-6"></i> Cerrar Sesión
                </a>
            </div>
        </nav>
        <div class="p-4 border-t border-slate-800 text-xs text-slate-500 text-center">
            v3.5 Auditada
        </div>
    </aside>
    <?php endif; ?>

    <main class="flex-1 p-8 <?= !isset($_SESSION['user_id']) ? 'flex items-center justify-center bg-gray-200' : '' ?>">
        
        <!-- Flash Messages (Placeholder) -->
        <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="mb-4 w-full max-w-lg mx-auto">
            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-2 shadow" role="alert">
                <p><?= htmlspecialchars($_SESSION['flash_message']) ?></p>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        </div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </main>

    <script src="/js/main.js"></script>
    <script>
        // Global helper for delete
        async function deleteResource(endpoint, id) {
             if (!confirm("¿Estás seguro de eliminar este registro?")) return;
             // Basic implementation reusing existing logic or just redirecting if not using API
             window.location.href = `/${endpoint}/delete/${id}`;
        }
    </script>
</body>
</html>
