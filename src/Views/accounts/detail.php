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
<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-8 p-8">
    
    <!-- HEADER -->
    <div class="mb-8 pb-6 border-b border-gray-200">
        <div class="flex items-center gap-3 mb-3">
            <span class="text-xs font-bold uppercase tracking-wide bg-yellow-100 text-yellow-700 px-3 py-1.5 rounded-lg border border-yellow-300"><i class="fas fa-cloud mr-2"></i><?= htmlspecialchars($account['account_type']) ?></span>
            <span class="text-xs text-gray-400">ID: <?= $account['id'] ?></span>
        </div>
        <h1 class="text-4xl font-extrabold text-gray-900 mb-2"><?= htmlspecialchars($account['service_name']) ?></h1>
        <?php if (!empty($account['provider'])): ?>
        <p class="text-gray-500 text-lg flex items-center gap-2">
            <i class="fas fa-building text-gray-400"></i> <?= htmlspecialchars($account['provider']) ?>
        </p>
        <?php endif; ?>
    </div>

    <!-- SECTION 1: INFORMACIÓN GENERAL -->
    <div class="mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center border-b pb-2">
            <i class="fas fa-info-circle mr-2 text-blue-500"></i> Información General
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-200">
                <span class="block text-xs text-emerald-600 mb-1 uppercase font-bold"><i class="fas fa-dollar-sign mr-1"></i>Costo</span>
                <span class="font-bold text-emerald-900 text-lg">$<?= number_format($account['cost'], 2) ?> <?= htmlspecialchars($account['currency']) ?></span>
                <span class="block text-xs text-emerald-600 uppercase mt-1"><?= htmlspecialchars($account['frequency']) ?></span>
            </div>
            <?php 
                $renewal_bg_color = 'gray';
                $renewal_border_color = 'gray';
                $renewal_text_color = 'gray';
                $days = 0;
                $dateDisplay = 'N/A';

                if (!empty($account['renewal_date'])) {
                    $renewal = new DateTime($account['renewal_date']);
                    $now = new DateTime();
                    $diff = $now->diff($renewal);
                    $days = $diff->invert ? -$diff->days : $diff->days;
                    $dateDisplay = date('d/m/Y', strtotime($account['renewal_date']));

                    if ($days < 30) {
                        $renewal_bg_color = 'red';
                        $renewal_border_color = 'red';
                        $renewal_text_color = 'red';
                    } else {
                        $renewal_bg_color = 'blue';
                        $renewal_border_color = 'blue';
                        $renewal_text_color = 'blue';
                    }
                }
            ?>
            <div class="bg-<?= $renewal_bg_color ?>-50 p-4 rounded-lg border border-<?= $renewal_border_color ?>-200">
                <span class="block text-xs text-<?= $renewal_text_color ?>-600 mb-1 uppercase font-bold">Próxima Renovación</span>
                <span class="font-bold text-gray-900"><?= $dateDisplay ?></span>
                <span class="text-xs text-gray-500 block mt-1"><?= $days ?> días</span>
            </div>
            <?php if (!empty($account['birth_date'])): ?>
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <span class="block text-xs text-purple-600 mb-1 uppercase font-bold">Fecha de Nacimiento</span>
                <span class="font-bold text-gray-900"><?= date('d/m/Y', strtotime($account['birth_date'])) ?></span>
                <span class="text-xs text-purple-600 block mt-1">
                    <?php 
                        $birth = new DateTime($account['birth_date']);
                        $age = $birth->diff(new DateTime())->y;
                        echo "$age años";
                    ?>
                </span>
            </div>
            <?php endif; ?>
            <?php if ($account['account_type'] != 'Individual' && !empty($account['max_licenses'])): ?>
            <div class="bg-amber-50 p-4 rounded-lg border border-amber-200">
                <span class="block text-xs text-amber-600 mb-1 uppercase font-bold"><i class="fas fa-users mr-1"></i>Licencias</span>
                <span class="font-bold text-amber-900 text-lg"><?= $account['max_licenses'] ?></span>
                <span class="block text-xs text-amber-600 uppercase mt-1">Máximo</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- SECTION 2: CREDENCIALES DE ACCESO -->
    <div class="mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center border-b pb-2">
            <i class="fas fa-key mr-2 text-green-500"></i> Credenciales de Acceso
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Usuario / Email</span>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-gray-900 flex-1"><?= htmlspecialchars($account['username'] ?? '-') ?></span>
                    <?php if (!empty($account['username'])): ?>
                    <button onclick="navigator.clipboard.writeText('<?= htmlspecialchars($account['username']) ?>'); showCopyNotification();" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-copy"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Contraseña</span>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-gray-900 flex-1"><?= !empty($account['password']) ? '••••••••' : '-' ?></span>
                    <?php if (!empty($account['password'])): ?>
                    <button onclick="navigator.clipboard.writeText('<?= htmlspecialchars($account['password']) ?>'); showCopyNotification();" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-copy"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 3: INFORMACIÓN ADICIONAL -->
    <?php if (!empty($account['contract_ref']) || !empty($account['observations'])): ?>
    <div class="mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center border-b pb-2">
            <i class="fas fa-sticky-note mr-2 text-amber-500"></i> Información Adicional
        </h3>
        <?php if (!empty($account['contract_ref'])): ?>
        <div class="mb-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
            <span class="block text-xs text-gray-500 mb-1 uppercase font-bold">Referencia de Contrato</span>
            <p class="text-sm text-gray-900 font-mono"><?= htmlspecialchars($account['contract_ref']) ?></p>
        </div>
        <?php endif; ?>
        <?php if (!empty($account['observations'])): ?>
        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
            <span class="block text-xs text-yellow-700 mb-2 uppercase font-bold"><i class="fas fa-exclamation-triangle mr-1"></i>Observaciones</span>
            <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($account['observations'])) ?></p>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- TIMESTAMPS -->
    <div class="pt-6 border-t border-gray-200">
        <div class="text-xs text-gray-500">
            <span class="block text-gray-400 mb-1 uppercase font-bold">Creado</span>
            <span class="font-mono"><?= !empty($account['created_at']) ? date('d/m/Y H:i', strtotime($account['created_at'])) : '-' ?></span>
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

<!-- DOCUMENTS PARTIAL -->
<?php 
$documents = $account['documents'] ?? [];
$entityType = 'account';
$entityId = $account['id'];
include __DIR__ . '/../partials/documents_list.php'; 
?>

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
