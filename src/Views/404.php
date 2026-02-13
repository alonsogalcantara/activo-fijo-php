<?php ob_start(); ?>

<div class="flex flex-col items-center justify-center h-full text-center space-y-6">
    <!-- Icono grande o Ilustración -->
    <div class="text-blue-200">
        <i class="fas fa-ghost text-9xl"></i>
    </div>

    <div class="space-y-2">
        <h1 class="text-6xl font-extrabold text-slate-800">404</h1>
        <h2 class="text-2xl font-semibold text-slate-600">Página no encontrada</h2>
    </div>

    <p class="text-slate-500 max-w-md">
        Lo sentimos, la ruta <strong><?= htmlspecialchars($_SERVER['REQUEST_URI']) ?></strong> no existe en este sistema o no tienes permisos para verla.
    </p>

    <div class="pt-4">
        <a href="/dashboard" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-lg flex items-center justify-center gap-2">
            <i class="fas fa-arrow-left"></i>
            Volver al Inicio
        </a>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php 
// If session is active, use main layout, else simpler layout or just main if it handles no-session
// main.php handles !isset($_SESSION) by adding bg-gray-200 to main
include __DIR__ . '/layouts/main.php'; 
?>
