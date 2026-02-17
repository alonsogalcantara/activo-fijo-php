<?php ob_start(); ?>

<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Editar Servicio</h1>
            <p class="text-gray-500 text-sm mt-1">Actualizar información de la cuenta/suscripción.</p>
        </div>
        <a href="/accounts/detail/<?= $account['id'] ?>" class="text-gray-500 hover:text-gray-800 font-medium flex items-center bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
            <i class="fas fa-times mr-2"></i> Cancelar
        </a>
    </div>

    <form id="accountForm" action="/accounts/update/<?= $account['id'] ?>" method="POST" class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden mb-8">
        
        <div class="p-8 space-y-8">
            <!-- SECTION 1: CONTRACT INFO -->
            <div>
                <h3 class="font-bold text-blue-600 border-b border-gray-100 pb-2 mb-4 flex items-center">
                    <i class="fas fa-file-contract mr-2"></i> Información del Contrato
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Servicio <span class="text-red-500">*</span></label>
                        <input type="text" id="accService" name="service_name" value="<?= htmlspecialchars($account['service_name']) ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required placeholder="Ej: Adobe Creative Cloud">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Proveedor</label>
                        <input type="text" id="accProvider" name="provider" value="<?= htmlspecialchars($account['provider'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Ej: Adobe Systems">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Tipo Cuenta</label>
                        <select id="accType" name="account_type" class="w-full p-2.5 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 outline-none" onchange="toggleFamily()">
                            <option value="Individual" <?= ($account['account_type'] ?? '') == 'Individual' ? 'selected' : '' ?>>Individual (Personal)</option>
                            <option value="Familiar" <?= ($account['account_type'] ?? '') == 'Familiar' ? 'selected' : '' ?>>Familiar / Grupal</option>
                            <option value="Empresarial" <?= ($account['account_type'] ?? '') == 'Empresarial' ? 'selected' : '' ?>>Empresarial / Corporativa</option>
                        </select>
                    </div>
                    <div id="licensesField" class="<?= ($account['account_type'] ?? 'Individual') == 'Individual' ? 'hidden' : '' ?> animate-fade-in-down">
                        <label class="block text-sm font-bold text-purple-700 mb-1">Max Licencias / Usuarios</label>
                        <input type="number" id="accMaxLicenses" name="max_licenses" value="<?= htmlspecialchars($account['max_licenses'] ?? 1) ?>" min="1" class="w-full p-2.5 border border-purple-300 bg-purple-50 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none">
                    </div>
                    <div>
                         <label class="block text-sm font-bold text-gray-700 mb-1">Referencia / Contrato</label>
                         <input type="text" name="contract_ref" value="<?= htmlspecialchars($account['contract_ref'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
            </div>

            <!-- SECTION 2: CREDENTIALS & ASSIGNMENT -->
            <div>
                <h3 class="font-bold text-purple-600 border-b border-gray-100 pb-2 mb-4 flex items-center">
                    <i class="fas fa-key mr-2"></i> Credenciales y Asignación
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Usuario (Login)</label>
                        <input type="text" id="accUsername" name="username" value="<?= htmlspecialchars($account['username'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none" placeholder="user@example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                        <input type="text" id="accPassword" name="password" value="<?= htmlspecialchars($account['password'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg font-mono focus:ring-2 focus:ring-purple-500 outline-none">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Asignado A (Responsable)</label>
                        <select id="accAssignedTo" name="assigned_to" class="w-full p-2.5 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-purple-500 outline-none cursor-pointer">
                            <option value="">-- Nadie / Sin Asignar --</option>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= ($account['assigned_to'] ?? '') == $user['id'] ? 'selected' : '' ?>><?= htmlspecialchars($user['name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: BILLING -->
            <div>
                <h3 class="font-bold text-green-600 border-b border-gray-100 pb-2 mb-4 flex items-center">
                    <i class="fas fa-file-invoice-dollar mr-2"></i> Facturación
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Costo</label>
                        <input type="number" id="accCost" name="cost" value="<?= htmlspecialchars($account['cost'] ?? 0) ?>" step="0.01" min="0" class="w-full p-2.5 border border-gray-300 rounded-lg text-right font-bold text-gray-700 focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Moneda</label>
                        <select id="accCurrency" name="currency" class="w-full p-2.5 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-green-500 outline-none">
                            <option value="MXN" <?= ($account['currency'] ?? '') == 'MXN' ? 'selected' : '' ?>>MXN</option>
                            <option value="USD" <?= ($account['currency'] ?? '') == 'USD' ? 'selected' : '' ?>>USD</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Frecuencia</label>
                         <select name="frequency" class="w-full p-2.5 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-green-500 outline-none">
                             <option value="Mensual" <?= ($account['frequency'] ?? '') == 'Mensual' ? 'selected' : '' ?>>Mensual</option>
                             <option value="Anual" <?= ($account['frequency'] ?? '') == 'Anual' ? 'selected' : '' ?>>Anual</option>
                             <option value="Trimestral" <?= ($account['frequency'] ?? '') == 'Trimestral' ? 'selected' : '' ?>>Trimestral</option>
                             <option value="Único" <?= ($account['frequency'] ?? '') == 'Único' ? 'selected' : '' ?>>Único</option>
                         </select>
                    </div>
                    <div class="col-span-1 md:col-span-3">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Fecha de Renovación</label>
                        <input type="date" id="accRenewal" name="renewal_date" value="<?= htmlspecialchars($account['renewal_date'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                    <div class="col-span-1 md:col-span-3">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Fecha de Nacimiento (si aplica)</label>
                        <input type="date" id="accBirthDate" name="birth_date" value="<?= htmlspecialchars($account['birth_date'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                        <p class="text-xs text-gray-500 mt-1">Para cuentas personales que requieren fecha de nacimiento</p>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: OBSERVATIONS -->
            <div>
                <h3 class="font-bold text-gray-500 border-b border-gray-100 pb-2 mb-4 flex items-center">
                    <i class="fas fa-comment-alt mr-2"></i> Observaciones Adicionales
                </h3>
                <textarea name="observations" rows="3" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-400 outline-none" placeholder="Notas sobre la cuenta..."><?= htmlspecialchars($account['observations'] ?? '') ?></textarea>
            </div>

        </div>
        
        <div class="bg-gray-50 p-6 flex justify-end border-t border-gray-100">
            <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold text-lg hover:bg-blue-700 shadow-lg flex items-center transform transition hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                <i class="fas fa-save mr-2"></i> Actualizar Servicio
            </button>
        </div>
    </form>

</div>

<script>
    function toggleFamily() {
        const type = document.getElementById('accType').value;
        const isGroup = type !== 'Individual';
        const licenseField = document.getElementById('licensesField');
        
        if (isGroup) {
            licenseField.classList.remove('hidden');
        } else {
            licenseField.classList.add('hidden');
            document.getElementById('accMaxLicenses').value = 1;
        }
    }

    // Initialize state
    document.addEventListener('DOMContentLoaded', () => {
        toggleFamily();
    });
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
