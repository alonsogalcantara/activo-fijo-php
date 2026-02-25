<?php ob_start(); ?>

<div class="mb-6">
    <a href="/accounts/detail/<?= $account['id'] ?>" class="inline-flex items-center bg-white px-4 py-2 border border-gray-200 shadow-sm text-gray-500 rounded-lg hover:bg-gray-50 hover:text-gray-700 transition font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Cancelar y Volver
    </a>
</div>

<div class="max-w-5xl mx-auto">
    <div class="flex items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800"><i class="fas fa-key mr-3 text-yellow-500"></i>Editar Servicio</h2>
    </div>

    <form id="accountForm" action="/accounts/update/<?= $account['id'] ?>" method="POST" enctype="multipart/form-data">
        
        <div class="space-y-8">
            <!-- SECTION 1: CONTRACT INFO -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-bold text-blue-600 border-b border-gray-100 pb-2 mb-4 flex items-center">
                    <i class="fas fa-file-contract mr-2"></i> Información del Contrato
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Servicio <span class="text-red-500">*</span></label>
                        <input type="text" id="accService" name="service_name" value="<?= htmlspecialchars($account['service_name']) ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition" required placeholder="Ej: Adobe Creative Cloud">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Proveedor</label>
                        <input type="text" id="accProvider" name="provider" value="<?= htmlspecialchars($account['provider'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="Ej: Adobe Systems">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipo Cuenta</label>
                        <select id="accType" name="account_type" class="w-full p-2.5 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 outline-none transition" onchange="toggleFamily()">
                            <option value="Individual" <?= ($account['account_type'] ?? '') == 'Individual' ? 'selected' : '' ?>>Individual (Personal)</option>
                            <option value="Familiar" <?= ($account['account_type'] ?? '') == 'Familiar' ? 'selected' : '' ?>>Familiar / Grupal</option>
                            <option value="Empresarial" <?= ($account['account_type'] ?? '') == 'Empresarial' ? 'selected' : '' ?>>Empresarial / Corporativa</option>
                        </select>
                    </div>
                    <div id="licensesField" class="<?= ($account['account_type'] ?? 'Individual') == 'Individual' ? 'hidden' : '' ?> animate-fade-in-down">
                        <label class="block text-xs font-bold text-purple-700 uppercase mb-1">Max Licencias / Usuarios</label>
                        <input type="number" id="accMaxLicenses" name="max_licenses" value="<?= htmlspecialchars($account['max_licenses'] ?? 1) ?>" min="1" class="w-full p-2.5 border border-purple-300 bg-purple-50 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none transition">
                    </div>
                    <div class="col-span-1 md:col-span-2">
                         <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Referencia / Contrato</label>
                         <input type="text" name="contract_ref" value="<?= htmlspecialchars($account['contract_ref'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>
                </div>
            </div>

            <!-- SECTION 2: CREDENTIALS & ASSIGNMENT -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-bold text-purple-600 border-b border-gray-100 pb-2 mb-4 flex items-center">
                    <i class="fas fa-key mr-2"></i> Credenciales y Asignación
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Usuario (Login)</label>
                        <input type="text" id="accUsername" name="username" value="<?= htmlspecialchars($account['username'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none transition" placeholder="user@example.com">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Password</label>
                        <input type="text" id="accPassword" name="password" value="<?= htmlspecialchars($account['password'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg font-mono focus:ring-2 focus:ring-purple-500 outline-none transition">
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Asignado A (Responsable)</label>
                        <select id="accAssignedTo" name="assigned_to" class="w-full p-2.5 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-purple-500 outline-none transition cursor-pointer">
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
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-bold text-green-600 border-b border-gray-100 pb-2 mb-4 flex items-center">
                    <i class="fas fa-file-invoice-dollar mr-2"></i> Facturación
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Costo</label>
                        <input type="number" id="accCost" name="cost" value="<?= htmlspecialchars($account['cost'] ?? 0) ?>" step="0.01" min="0" class="w-full p-2.5 border border-gray-300 rounded-lg text-right font-bold text-gray-700 focus:ring-2 focus:ring-green-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Moneda</label>
                        <select id="accCurrency" name="currency" class="w-full p-2.5 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-green-500 outline-none transition">
                            <option value="MXN" <?= ($account['currency'] ?? '') == 'MXN' ? 'selected' : '' ?>>MXN</option>
                            <option value="USD" <?= ($account['currency'] ?? '') == 'USD' ? 'selected' : '' ?>>USD</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Frecuencia</label>
                         <select name="frequency" class="w-full p-2.5 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-green-500 outline-none transition">
                             <option value="Mensual" <?= ($account['frequency'] ?? '') == 'Mensual' ? 'selected' : '' ?>>Mensual</option>
                             <option value="Anual" <?= ($account['frequency'] ?? '') == 'Anual' ? 'selected' : '' ?>>Anual</option>
                             <option value="Trimestral" <?= ($account['frequency'] ?? '') == 'Trimestral' ? 'selected' : '' ?>>Trimestral</option>
                             <option value="Único" <?= ($account['frequency'] ?? '') == 'Único' ? 'selected' : '' ?>>Único</option>
                         </select>
                    </div>
                    <div class="col-span-1 md:col-span-3">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Fecha de Renovación</label>
                        <input type="date" id="accRenewal" name="renewal_date" value="<?= htmlspecialchars($account['renewal_date'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none transition">
                    </div>
                    <div class="col-span-1 md:col-span-3">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Fecha de Nacimiento (si aplica)</label>
                        <input type="date" id="accBirthDate" name="birth_date" value="<?= htmlspecialchars($account['birth_date'] ?? '') ?>" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none transition">
                        <p class="text-xs text-gray-500 mt-1">Para cuentas personales que requieren fecha de nacimiento</p>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: OBSERVATIONS -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                <h3 class="font-bold text-gray-500 border-b border-gray-100 pb-2 mb-4 flex items-center">
                    <i class="fas fa-comment-alt mr-2"></i> Observaciones Adicionales
                </h3>
                <textarea name="observations" rows="3" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="Notas sobre la cuenta..."><?= htmlspecialchars($account['observations'] ?? '') ?></textarea>
            </div>

            <!-- SECTION 5: DOCUMENTS -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                <h3 class="text-lg font-bold text-gray-700 mb-4 pb-2 border-b border-gray-200">
                    <i class="fas fa-file-alt mr-2 text-gray-400"></i>Documentos Adjuntos
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Documento Asociado</label>
                        <input type="file" name="document" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer">
                        <p class="text-xs text-gray-500 mt-1">Contrato, Factura, Recibo, etc. Máx 10MB.</p>
                    </div>
                </div>
            </div>

        </div>
        
        <!-- ACTIONS -->
        <div class="flex justify-end gap-3 pt-4 pb-8">
            <a href="/accounts/detail/<?= $account['id'] ?>" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition font-medium shadow-sm">
                Cancelar
            </a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition shadow flex items-center transform hover:scale-105 active:scale-95 font-medium">
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
