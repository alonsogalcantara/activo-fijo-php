<?php ob_start(); ?>

<div class="mb-6"><a href="/accounts" class="text-gray-500 hover:text-gray-800 transition"><i class="fas fa-arrow-left mr-2"></i> Volver a Servicios</a></div>

<div class="bg-white p-8 rounded-xl shadow-lg border border-gray-100 mb-8">
    <div class="flex flex-col md:flex-row justify-between items-start gap-6">
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-lg bg-yellow-50 flex items-center justify-center text-yellow-600 text-2xl">
                    <i class="fas fa-cloud"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($account['service_name']) ?></h1>
                    <span class="px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-600 uppercase">
                        <?= htmlspecialchars($account['account_type']) ?>
                    </span>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
                <div class="bg-gray-50 p-3 rounded hover:bg-gray-100 transition">
                    <span class="block text-xs uppercase font-bold text-gray-400 mb-1">Proveedor</span>
                    <span class="font-medium text-gray-800"><?= htmlspecialchars($account['provider'] ?? '-') ?></span>
                </div>
                <div class="bg-gray-50 p-3 rounded hover:bg-gray-100 transition">
                    <span class="block text-xs uppercase font-bold text-gray-400 mb-1">Costo</span>
                    <span class="font-medium text-emerald-600">$<?= number_format($account['cost'], 2) ?> <?= htmlspecialchars($account['currency']) ?></span>
                </div>
                <div class="bg-gray-50 p-3 rounded hover:bg-gray-100 transition">
                    <span class="block text-xs uppercase font-bold text-gray-400 mb-1">Frecuencia</span>
                    <span class="font-medium text-gray-800"><?= htmlspecialchars($account['frequency']) ?></span>
                </div>
                <div class="bg-gray-50 p-3 rounded hover:bg-gray-100 transition">
                    <span class="block text-xs uppercase font-bold text-gray-400 mb-1">Próxima Renovación</span>
                    <?php 
                        $renewal = new DateTime($account['renewal_date']);
                        $now = new DateTime();
                        $diff = $now->diff($renewal);
                        $days = $diff->invert ? -$diff->days : $diff->days;
                        $color = $days < 30 ? 'text-red-600' : 'text-gray-800';
                    ?>
                    <span class="font-medium <?= $color ?>"><?= htmlspecialchars($account['renewal_date']) ?></span>
                    <span class="text-xs text-gray-500 block"><?= $days ?> días restantes</span>
                </div>
            </div>
            
            <div class="mt-6 border-t border-gray-100 pt-6 flex justify-between items-center">
                <h3 class="font-bold text-gray-700">Credenciales de Acceso</h3>
                <a href="#" onclick="exportAccountPdf(<?= $account['id'] ?>)" class="text-sm text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1">
                    <i class="fas fa-file-pdf"></i> Exportar Detalles
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                     <div>
                         <span class="text-xs text-gray-400 uppercase font-bold">Usuario / Email</span>
                         <div class="flex items-center mt-1">
                             <code class="bg-slate-100 px-3 py-2 rounded text-slate-700 font-mono select-all"><?= htmlspecialchars($account['username']) ?></code>
                             <button class="ml-2 text-gray-400 hover:text-blue-500" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($account['username']) ?>')">
                                 <i class="fas fa-copy"></i>
                             </button>
                         </div>
                     </div>
                     <div>
                         <span class="text-xs text-gray-400 uppercase font-bold">Contraseña</span>
                         <div class="flex items-center mt-1">
                             <code class="bg-slate-100 px-3 py-2 rounded text-slate-700 font-mono select-all blur-sm hover:blur-none transition cursor-pointer"><?= htmlspecialchars($account['password']) ?></code>
                             <button class="ml-2 text-gray-400 hover:text-blue-500" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($account['password']) ?>')">
                                 <i class="fas fa-copy"></i>
                             </button>
                         </div>
                     </div>
                 </div>
             </div>
             
             <?php if (!empty($account['observations'])): ?>
             <div class="mt-6">
                 <h3 class="font-bold text-gray-700 mb-2">Observaciones</h3>
                 <p class="text-gray-600 bg-yellow-50 p-4 rounded-lg border border-yellow-100 italic">
                     <?= nl2br(htmlspecialchars($account['observations'])) ?>
                 </p>
             </div>
             <?php endif; ?>
        </div>
        
        <!-- Actions & Assignment -->
        <div class="w-full md:w-80 flex flex-col gap-4">
             <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
                 <h3 class="font-bold text-gray-700 mb-3">Asignación</h3>
                 <?php if(!empty($account['assigned_to'])): ?>
                     <div class="flex items-center mb-4">
                         <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold mr-3">
                             <i class="fas fa-user"></i>
                         </div>
                         <div>
                             <p class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($account['assigned_user_name'] ?? 'Usuario ID ' . $account['assigned_to']) ?></p>
                             <p class="text-xs text-gray-500">Responsable Principal</p>
                         </div>
                     </div>
                 <?php else: ?>
                     <p class="text-gray-400 italic text-sm mb-4">No asignado a un usuario específico (Uso General o Stock)</p>
                 <?php endif; ?>
                 
                 <a href="/accounts/edit/<?= $account['id'] ?>" class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg font-bold hover:bg-blue-700 transition mb-2">
                     <i class="fas fa-pen mr-2"></i> Editar Servicio
                 </a>
                 <a href="/accounts/delete/<?= $account['id'] ?>" onclick="return confirm('¿Eliminar servicio?')" class="block w-full text-center border border-red-200 text-red-600 py-2 rounded-lg font-bold hover:bg-red-50 transition">
                     <i class="fas fa-trash-alt mr-2"></i> Eliminar
                 </a>
             </div>
             
             <!-- Contract Info Placeholder -->
             <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 text-center">
                 <i class="fas fa-file-contract text-gray-300 text-3xl mb-2"></i>
                 <p class="text-xs text-gray-500">Referencia de Contrato</p>
                 <p class="font-mono font-bold text-gray-700"><?= htmlspecialchars($account['contract_ref'] ?? 'N/A') ?></p>
             </div>
        </div>
    </div>
</div>

<script>
function exportAccountPdf(id) {
    alert('Funcionalidad de exportación de cuenta en desarrollo. ID: ' + id);
    // Future: window.open('/reports/account_detail/' + id, '_blank');
}
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
