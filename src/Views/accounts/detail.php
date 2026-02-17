<?php ob_start(); ?>

<div class="mb-6">
    <a href="/accounts" class="text-gray-500 hover:text-gray-800 font-medium transition flex items-center group">
        <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center mr-2 group-hover:bg-gray-100 transition">
            <i class="fas fa-arrow-left"></i>
        </div>
        Volver a Servicios
    </a>
</div>

<!-- TOP ACTIONS -->
<div class="flex justify-end gap-2 mb-6">
    <div class="relative group">
        <button class="bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-lg shadow-sm hover:bg-gray-50 font-medium flex items-center">
            <i class="fas fa-print mr-2"></i> Exportar <i class="fas fa-chevron-down ml-2 text-xs"></i>
        </button>
        <!-- Dropdown -->
        <div class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-100 hidden group-hover:block z-50">
            <button onclick="exportAccountPdf(<?= $account['id'] ?>)" class="block w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                <i class="fas fa-file-pdf text-red-500 mr-2"></i> Detalles de Cuenta
            </button>
        </div>
    </div>

    <a href="/accounts/edit/<?= $account['id'] ?>" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 shadow-md font-bold transition flex items-center">
        <i class="fas fa-pen mr-2"></i> Editar
    </a>
</div>

<!-- MAIN CARD -->
<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-8">
    <div class="md:flex">
        <!-- ICON SECTION -->
        <div class="md:w-1/3 relative bg-gradient-to-br from-yellow-50 to-amber-50 border-r border-gray-100 min-h-[300px] flex items-center justify-center overflow-hidden">
            <div class="text-8xl text-yellow-400 opacity-20 absolute">
                <i class="fas fa-cloud"></i>
            </div>
            <div class="relative z-10 text-center">
                <div class="w-24 h-24 rounded-full bg-white shadow-lg flex items-center justify-center text-yellow-500 text-5xl mx-auto mb-4">
                    <i class="fas fa-cloud"></i>
                </div>
                <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider shadow-md text-gray-600 bg-gray-100">
                    <?= htmlspecialchars($account['account_type']) ?>
                </span>
            </div>
        </div>

        <!-- DETAILS -->
        <div class="md:w-2/3 p-8">
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold uppercase tracking-wide bg-yellow-100 text-yellow-600 px-2 py-0.5 rounded">Servicio</span>
                    <span class="text-xs font-mono text-gray-400">ID: <?= $account['id'] ?></span>
                </div>
                <h1 class="text-4xl font-extrabold text-gray-900 mb-2"><?= htmlspecialchars($account['service_name']) ?></h1>
                <?php if (!empty($account['provider'])): ?>
                <p class="text-gray-500 text-sm bg-gray-50 inline-block px-3 py-1 rounded border border-gray-200">
                    <i class="fas fa-building mr-2 text-gray-400"></i> <?= htmlspecialchars($account['provider']) ?>
                </p>
                <?php endif; ?>
            </div>

            <!-- KEY INFO GRID -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-6 text-sm mb-6 pb-6 border-b border-gray-100">
                <div>
                    <span class="block text-gray-400 text-xs font-bold uppercase mb-1">Costo</span>
                    <span class="font-bold text-emerald-600 text-lg">$<?= number_format($account['cost'], 2) ?> <?= htmlspecialchars($account['currency']) ?></span>
                    <span class="block text-xs text-gray-400 uppercase"><?= htmlspecialchars($account['frequency']) ?></span>
                </div>
                <div>
                    <span class="block text-gray-400 text-xs font-bold uppercase mb-1">Próxima Renovación</span>
                    <?php 
                        if (!empty($account['renewal_date'])) {
                            $renewal = new DateTime($account['renewal_date']);
                            $now = new DateTime();
                            $diff = $now->diff($renewal);
                            $days = $diff->invert ? -$diff->days : $diff->days;
                            $color = $days < 30 ? 'text-red-600' : 'text-gray-800';
                            $dateDisplay = htmlspecialchars($account['renewal_date']);
                        } else {
                            $days = 0;
                            $color = 'text-gray-400';
                            $dateDisplay = 'N/A';
                        }
                    ?>
                    <span class="font-bold <?= $color ?>"><?= $dateDisplay ?></span>
                    <span class="text-xs text-gray-500 block"><?= $days ?> días restantes</span>
                </div>
                <?php if ($account['account_type'] != 'Individual' && !empty($account['max_licenses'])): ?>
                <div>
                    <span class="block text-gray-400 text-xs font-bold uppercase mb-1">Licencias</span>
                    <span class="font-bold text-purple-600 text-lg">
                        <i class="fas fa-users mr-1"></i><?= $account['max_licenses'] ?>
                    </span>
                    <span class="block text-xs text-gray-400">Máximo</span>
                </div>
                <?php endif; ?>
            </div>

            <!-- CREDENTIALS SECTION -->
            <div class="mb-6">
                <h4 class="text-xs font-bold text-gray-400 uppercase mb-3 flex items-center">
                    <i class="fas fa-key mr-2"></i> Credenciales de Acceso
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <div>
                        <span class="block text-xs text-gray-500 mb-1">Usuario / Email</span>
                        <div class="flex items-center gap-2">
                            <code class="bg-white px-3 py-2 rounded text-gray-700 font-mono text-sm border border-gray-200 flex-1 select-all"><?= htmlspecialchars($account['username']) ?></code>
                            <button class="text-gray-400 hover:text-blue-500 transition" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($account['username']) ?>'); showCopyNotification();" title="Copiar">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 mb-1">Contraseña</span>
                        <div class="flex items-center gap-2">
                            <code class="bg-white px-3 py-2 rounded text-gray-700 font-mono text-sm border border-gray-200 flex-1 blur-sm hover:blur-none transition cursor-pointer select-all" title="Hover para ver"><?= htmlspecialchars($account['password']) ?></code>
                            <button class="text-gray-400 hover:text-blue-500 transition" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($account['password']) ?>'); showCopyNotification();" title="Copiar">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONTRACT REF -->
            <?php if (!empty($account['contract_ref'])): ?>
            <div class="mb-4">
                <span class="text-xs text-gray-400 uppercase font-bold block mb-1">Referencia de Contrato</span>
                <p class="font-mono text-gray-700 bg-gray-50 inline-block px-3 py-1 rounded border border-gray-200">
                    <i class="fas fa-file-contract mr-2 text-gray-400"></i><?= htmlspecialchars($account['contract_ref']) ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- OBSERVATIONS -->
            <?php if (!empty($account['observations'])): ?>
            <div class="mt-6 pt-6 border-t border-gray-100">
                <h4 class="text-xs font-bold text-gray-400 uppercase mb-2 flex items-center">
                    <i class="fas fa-sticky-note mr-2"></i> Observaciones
                </h4>
                <p class="text-sm text-gray-600 bg-yellow-50 p-4 rounded-lg border border-yellow-100 italic">
                    <?= nl2br(htmlspecialchars($account['observations'])) ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ASSIGNMENT SECTION -->
<div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8">
    <h3 class="font-bold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-user-tag text-blue-500 mr-2"></i> Asignación de Usuario
    </h3>
    
    <?php if (!empty($account['assigned_to'])): ?>
    <div class="flex items-center justify-between bg-blue-50 p-4 rounded-xl border border-blue-100">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-white text-blue-600 rounded-full flex items-center justify-center font-bold text-lg mr-3 shadow-sm border border-blue-200">
                <?= strtoupper(substr($account['assigned_user_name'] ?? 'U', 0, 1)) ?>
            </div>
            <div>
                <p class="font-bold text-gray-900 text-lg"><?= htmlspecialchars($account['assigned_user_name'] ?? 'Usuario ID ' . $account['assigned_to']) ?></p>
                <p class="text-xs text-gray-500 uppercase">Usuario Responsable</p>
            </div>
        </div>
        <div>
            <a href="/users/detail/<?= $account['assigned_to'] ?>" class="text-xs bg-white text-blue-600 border border-blue-200 px-3 py-2 rounded-lg hover:bg-blue-50 font-bold transition inline-flex items-center">
                <i class="fas fa-user mr-1"></i> Ver Perfil
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="text-center py-8 text-gray-400 border-2 border-dashed border-gray-100 rounded-lg">
        <i class="fas fa-users text-3xl mb-2 opacity-50"></i>
        <p class="text-sm">No asignado a un usuario específico</p>
        <p class="text-xs mt-1">Uso General o de Administración</p>
    </div>
    <?php endif; ?>
</div>

<!-- DANGER ZONE -->
<div class="bg-white p-6 rounded-xl shadow-sm border-t-4 border-red-500 mb-8">
    <h3 class="font-bold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i> Zona de Peligro
    </h3>
    <div class="flex items-center justify-between">
        <div>
            <p class="font-bold text-gray-700">Eliminar este servicio</p>
            <p class="text-sm text-gray-500">Esta acción no se puede deshacer. Se eliminarán todos los datos asociados.</p>
        </div>
        <a href="/accounts/delete/<?= $account['id'] ?>" onclick="return confirm('¿Estás completamente seguro de eliminar este servicio? Esta acción no se puede deshacer.')" class="bg-red-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-red-700 transition flex items-center">
            <i class="fas fa-trash-alt mr-2"></i> Eliminar Servicio
        </a>
    </div>
</div>

<script>
function exportAccountPdf(id) {
    alert('Funcionalidad de exportación de cuenta en desarrollo. ID: ' + id);
    // Future: window.open('/reports/account_detail/' + id, '_blank');
}

function showCopyNotification() {
    // Create a simple notification
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in-down';
    notification.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Copiado al portapapeles';
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 2000);
}
</script>

<style>
@keyframes fade-in-down {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in-down {
    animation: fade-in-down 0.3s ease-out;
}
</style>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
