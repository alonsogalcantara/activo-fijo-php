<?php ob_start(); ?>

<?php
// Security Warning: Check if install directory is still accessible
$installDirAccessible = is_dir(__DIR__ . '/../../public/install') && 
                        !file_exists(__DIR__ . '/../../public/install/install.lock');
?>

<?php if ($installDirAccessible): ?>
<div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 mb-6 rounded-r shadow-sm">
    <div class="flex items-start">
        <i class="fas fa-exclamation-triangle text-red-500 text-xl mr-3 mt-1"></i>
        <div>
            <h3 class="font-bold text-lg mb-1">⚠️ Advertencia de Seguridad</h3>
            <p class="text-sm mb-2">
                El directorio de instalación <code class="bg-red-100 px-2 py-1 rounded">/public/install/</code> 
                aún es accesible. Esto representa un riesgo de seguridad.
            </p>
            <p class="text-sm font-semibold">
                <strong>Acción requerida:</strong> Elimine o restrinja el acceso al directorio 
                <code class="bg-red-100 px-2 py-1 rounded">public/install/</code> después de completar la instalación.
            </p>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="mb-8 flex justify-between items-center mt-6">
    <div class="flex items-center">
        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl mr-4 shadow-sm border border-blue-200">
            <i class="fas fa-chart-pie"></i>
        </div>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Panel de Control</h1>
            <p class="text-sm text-gray-500 mt-1">Resumen general del sistema y métricas clave.</p>
        </div>
    </div>
    <div class="flex gap-3">
        <a href="/assets/create" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-bold shadow-md transition flex items-center transform hover:scale-105">
            <i class="fas fa-laptop text-sm mr-2"></i> Nuevo Activo
        </a>
        <a href="/accounts/create" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2 rounded-lg font-bold shadow-md transition flex items-center transform hover:scale-105">
            <i class="fas fa-cloud text-sm mr-2"></i> Nuevo Servicio
        </a>
    </div>
</div>

<!-- TOP CARDS -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Activos -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between hover:shadow-md transition">
        <div>
            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Total Activos</p>
            <p class="text-3xl font-bold text-gray-800 mt-1"><?= $data['total_assets'] ?? 0 ?></p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 text-xl shadow-sm">
            <i class="fas fa-laptop"></i>
        </div>
    </div>

    <!-- Usuarios Activos -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between hover:shadow-md transition">
        <div>
            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Usuarios Activos</p>
            <p class="text-3xl font-bold text-gray-800 mt-1"><?= $data['total_users'] ?? 0 ?></p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-xl shadow-sm">
            <i class="fas fa-users-cog"></i>
        </div>
    </div>

    <!-- Servicios -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between hover:shadow-md transition">
        <div>
            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Servicios / SaaS</p>
            <p class="text-3xl font-bold text-gray-800 mt-1"><?= $data['total_accounts'] ?? 0 ?></p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600 text-xl shadow-sm">
            <i class="fas fa-cloud"></i>
        </div>
    </div>

    <!-- Gasto Mensual -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between relative overflow-hidden hover:shadow-md transition">
        <div class="relative z-10">
            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Gasto Mensual Est.</p>
            
            <p class="text-2xl font-bold text-emerald-600 mt-1" id="monthlySpendMXN">
                $<?= number_format($data['monthly_spend_mxn'] ?? 0, 2) ?> <span class="text-xs text-gray-400">MXN</span>
            </p>
            
            <?php if (($data['monthly_spend_usd'] ?? 0) > 0): ?>
            <p class="text-sm font-bold text-emerald-500" id="monthlySpendUSD">
                $<?= number_format($data['monthly_spend_usd'], 2) ?> <span class="text-xs text-gray-400">USD</span>
            </p>
            <?php endif; ?>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 text-xl relative z-10 shadow-sm">
            <i class="fas fa-money-bill-wave"></i>
        </div>
    </div>
</div>

<!-- MAIN GRID -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- CHART SECTION -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 lg:col-span-1">
        <h3 class="text-lg font-bold text-gray-800 mb-6">Estado del Inventario</h3>
        <div class="relative h-64 w-full flex justify-center">
            <canvas id="assetsChart"></canvas>
        </div>
        <div class="mt-6 space-y-3">
            <?php $stats = $data['asset_stats'] ?? []; ?>
            <div class="flex justify-between text-sm">
                <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-emerald-500 mr-2"></span> Disponible</span>
                <span class="font-bold text-gray-700"><?= $stats['Disponible'] ?? 0 ?></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span> Asignado</span>
                <span class="font-bold text-gray-700"><?= $stats['Asignado'] ?? 0 ?></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-amber-500 mr-2"></span> Mantenimiento</span>
                <span class="font-bold text-gray-700"><?= $stats['En Mantenimiento'] ?? 0 ?></span>
            </div>
        </div>
    </div>

    <!-- RENEWALS & ACTIVITY -->
    <div class="lg:col-span-2 space-y-8">
        
        <!-- Próximos Pagos -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center border-b pb-2">
                <i class="fas fa-bell text-amber-500 mr-2"></i> Próximos Vencimientos
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase font-bold text-[10px] tracking-wider border-b border-gray-200">
                        <tr>
                            <th class="p-3">Servicio</th>
                            <th class="p-3">Responsable</th>
                            <th class="p-3">Fecha</th>
                            <th class="p-3 text-right">Estatus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (!empty($data['renewals'])): ?>
                        <?php foreach ($data['renewals'] as $r): ?>
                        <?php 
                            $days_left = $r['days_left'];
                            $color = ($days_left < 0) ? 'text-red-600 bg-red-50 border-red-200' : (($days_left < 7) ? 'text-amber-600 bg-amber-50 border-amber-200' : 'text-blue-600 bg-blue-50 border-blue-200');
                            $status_text = ($days_left < 0) ? "Venció hace " . abs($days_left) . " días" : "Vence en " . $days_left . " días";
                        ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3 font-bold text-gray-700"><?= htmlspecialchars($r['service_name']) ?></td>
                            <td class="p-3 text-gray-500"><?= htmlspecialchars($r['username'] ?: 'N/A') ?></td>
                            <td class="p-3 text-gray-500 font-mono text-xs"><?= date('d/m/Y', strtotime($r['renewal_date'])) ?></td>
                            <td class="p-3 text-right">
                                <span class="px-2 py-1 rounded-full text-xs font-bold border <?= $color ?>"><?= $status_text ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr><td colspan="4" class="p-4 text-center text-gray-400 italic">No hay vencimientos en los próximos 30 días.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Actividad Reciente -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center border-b pb-2">
                <i class="fas fa-stream text-gray-400 mr-2"></i> Actividad Reciente
            </h3>
            <div class="space-y-4">
                <?php if (!empty($data['recent_activity'])): ?>
                <?php foreach ($data['recent_activity'] as $log): ?>
                <div class="flex items-start pb-4 border-b border-gray-50 last:border-0 last:pb-0">
                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 mr-3 shrink-0 text-xs">
                        <i class="fas fa-info"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-800">
                            <span class="font-bold"><?= htmlspecialchars($log['action']) ?></span>: <?= htmlspecialchars($log['details']) ?>
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <?= htmlspecialchars($log['created_at']) ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p class="text-gray-400 italic text-center">No hay actividad reciente.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('assetsChart').getContext('2d');
        // Valores seguros usando default
        const d_disp = <?= $data['asset_stats']['Disponible'] ?? 0 ?>;
        const d_asig = <?= $data['asset_stats']['Asignado'] ?? 0 ?>;
        const d_mant = <?= $data['asset_stats']['En Mantenimiento'] ?? 0 ?>;
        const d_baja = <?= $data['asset_stats']['De Baja'] ?? 0 ?>;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Disponible', 'Asignado', 'Mantenimiento', 'Baja'],
                datasets: [{
                    data: [d_disp, d_asig, d_mant, d_baja],
                    backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                cutout: '75%'
            }
        });
    });
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/layouts/main.php'; ?>
