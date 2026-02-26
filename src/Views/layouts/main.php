<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Activos</title>
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/css/tailwind.css?v=<?= time() ?>">
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
    if (strpos($uri, '/admin/users') !== false) $active_page = 'admin_users';
    elseif (strpos($uri, '/audit') !== false) $active_page = 'audit';
    elseif (strpos($uri, '/assets') !== false) $active_page = 'assets';
    elseif (strpos($uri, '/users') !== false) $active_page = 'users';
    elseif (strpos($uri, '/accounts') !== false && strpos($uri, 'accounting') === false) $active_page = 'accounts';
    elseif (strpos($uri, '/accounting') !== false) $active_page = 'accounting';
    elseif (strpos($uri, '/dashboard') !== false) $active_page = 'dashboard';
    ?>

    <?php include __DIR__ . '/sidebar.php'; ?>

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
