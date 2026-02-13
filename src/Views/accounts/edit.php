<?php ob_start(); ?>

<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6">Editar Cuenta: <?= htmlspecialchars($account['service_name']) ?></h2>
    
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form action="/accounts/update/<?= $account['id'] ?>" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Nombre del Servicio</label>
                <input type="text" name="service_name" value="<?= htmlspecialchars($account['service_name']) ?>" class="border rounded w-full py-2 px-3" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Proveedor</label>
                <input type="text" name="provider" value="<?= htmlspecialchars($account['provider']) ?>" class="border rounded w-full py-2 px-3">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Usuario (Login)</label>
                <input type="text" name="username" value="<?= htmlspecialchars($account['username']) ?>" class="border rounded w-full py-2 px-3">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Contraseña</label>
                <input type="text" name="password" value="<?= htmlspecialchars($account['password']) ?>" class="border rounded w-full py-2 px-3">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Referencia / Contrato</label>
                <input type="text" name="contract_ref" value="<?= htmlspecialchars($account['contract_ref']) ?>" class="border rounded w-full py-2 px-3">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Fecha de Renovación</label>
                <input type="date" name="renewal_date" value="<?= htmlspecialchars($account['renewal_date']) ?>" class="border rounded w-full py-2 px-3">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Costo</label>
                <input type="number" step="0.01" name="cost" value="<?= htmlspecialchars($account['cost']) ?>" class="border rounded w-full py-2 px-3">
            </div>

            <div class="mb-4">
                 <label class="block text-gray-700 font-bold mb-2">Moneda</label>
                 <select name="currency" class="border rounded w-full py-2 px-3">
                     <option value="MXN" <?= ($account['currency'] ?? '') == 'MXN' ? 'selected' : '' ?>>MXN</option>
                     <option value="USD" <?= ($account['currency'] ?? '') == 'USD' ? 'selected' : '' ?>>USD</option>
                 </select>
            </div>

            <div class="mb-4">
                 <label class="block text-gray-700 font-bold mb-2">Frecuencia de Pago</label>
                 <select name="frequency" class="border rounded w-full py-2 px-3">
                     <option value="Mensual" <?= ($account['frequency'] ?? '') == 'Mensual' ? 'selected' : '' ?>>Mensual</option>
                     <option value="Anual" <?= ($account['frequency'] ?? '') == 'Anual' ? 'selected' : '' ?>>Anual</option>
                     <option value="Trimestral" <?= ($account['frequency'] ?? '') == 'Trimestral' ? 'selected' : '' ?>>Trimestral</option>
                     <option value="Único" <?= ($account['frequency'] ?? '') == 'Único' ? 'selected' : '' ?>>Único</option>
                 </select>
            </div>

            <div class="mb-4">
                 <label class="block text-gray-700 font-bold mb-2">Tipo de Cuenta</label>
                 <select name="account_type" class="border rounded w-full py-2 px-3">
                     <option value="Individual" <?= ($account['account_type'] ?? '') == 'Individual' ? 'selected' : '' ?>>Individual</option>
                     <option value="Corporativa" <?= ($account['account_type'] ?? '') == 'Corporativa' ? 'selected' : '' ?>>Corporativa</option>
                     <option value="Compartida" <?= ($account['account_type'] ?? '') == 'Compartida' ? 'selected' : '' ?>>Compartida</option>
                 </select>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Asignado a (Responsable)</label>
                <select name="assigned_to" class="border rounded w-full py-2 px-3">
                    <option value="">-- Sin Asignar --</option>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= ($account['assigned_to'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Max Licencias/Usuarios</label>
                <input type="number" name="max_licenses" value="<?= htmlspecialchars($account['max_licenses']) ?>" class="border rounded w-full py-2 px-3">
            </div>
            
            <div class="col-span-1 md:col-span-2 mb-4">
                 <label class="block text-gray-700 font-bold mb-2">Observaciones</label>
                 <textarea name="observations" class="border rounded w-full py-2 px-3"><?= htmlspecialchars($account['observations'] ?? '') ?></textarea>
            </div>

        </div>

        <div class="mt-6 flex justify-end">
            <a href="/accounts" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancelar</a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Actualizar</button>
        </div>
    </form>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
