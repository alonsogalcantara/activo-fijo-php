<?php ob_start(); ?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Registro de Auditoría</h1>

<?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="p-3">Fecha</th>
                    <th class="p-3">Usuario</th>
                    <th class="p-3">Acción</th>
                    <th class="p-3">Tabla</th>
                    <th class="p-3">ID Reg.</th>
                    <th class="p-3">Detalle (Cambios)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 text-gray-500 whitespace-nowrap"><?= htmlspecialchars($log['timestamp']) ?></td>
                        <td class="p-3 font-bold text-blue-600">
                            <?= htmlspecialchars($log['actor_username'] ?? 'Sistema') ?>
                            <div class="text-[10px] font-normal text-gray-400"><?= htmlspecialchars($log['actor_email'] ?? '') ?></div>
                        </td>
                        <td class="p-3">
                            <?php
                                $action = strtoupper($log['action']);
                                $colorClass = 'bg-gray-100 text-gray-700';
                                if ($action === 'CREATE') $colorClass = 'bg-green-100 text-green-700';
                                elseif ($action === 'UPDATE') $colorClass = 'bg-yellow-100 text-yellow-700';
                                elseif ($action === 'DELETE') $colorClass = 'bg-red-100 text-red-700';
                            ?>
                            <span class="px-2 py-0.5 rounded text-xs font-bold <?= $colorClass ?>">
                                <?= htmlspecialchars($action) ?>
                            </span>
                        </td>
                        <td class="p-3 text-gray-600"><?= htmlspecialchars($log['table_name']) ?></td>
                        <td class="p-3 text-gray-500 font-mono"><?= htmlspecialchars($log['record_id']) ?></td>
                        <td class="p-3 font-mono text-xs text-gray-600 max-w-md truncate" title="<?= htmlspecialchars($log['new_value'] ?? '') ?>">
                            <?php if ($action === 'UPDATE'): ?>
                               <?php if($log['old_value']): ?><span class="text-gray-400">Old: <?= htmlspecialchars(substr($log['old_value'] ?? '', 0, 50)) ?>...</span><br><?php endif; ?>
                               <span class="text-blue-500">New: <?= htmlspecialchars(substr($log['new_value'] ?? '', 0, 50)) ?>...</span>
                            <?php else: ?>
                               <?= htmlspecialchars($log['new_value'] ?? $log['old_value'] ?? '-') ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="p-6 text-center text-gray-500 italic">No hay registros de auditoría.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
