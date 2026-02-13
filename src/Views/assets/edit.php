<?php ob_start(); ?>

<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6">Editar Activo: <?= htmlspecialchars($asset['name']) ?></h2>

    <form action="/assets/update/<?= $asset['id'] ?>" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Basic Info -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Nombre</label>
                <input type="text" name="name" value="<?= htmlspecialchars($asset['name']) ?>" class="border rounded w-full py-2 px-3" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Categoría</label>
                <select name="category" class="border rounded w-full py-2 px-3" required>
                    <option value="Computadora" <?= $asset['category'] == 'Computadora' ? 'selected' : '' ?>>Computadora</option>
                    <option value="Vehículo" <?= $asset['category'] == 'Vehículo' ? 'selected' : '' ?>>Vehículo</option>
                    <option value="Mobiliario" <?= $asset['category'] == 'Mobiliario' ? 'selected' : '' ?>>Mobiliario</option>
                    <option value="Servidor" <?= $asset['category'] == 'Servidor' ? 'selected' : '' ?>>Servidor</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Marca</label>
                <input type="text" name="brand" value="<?= htmlspecialchars($asset['brand']) ?>" class="border rounded w-full py-2 px-3">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Modelo</label>
                <input type="text" name="model" value="<?= htmlspecialchars($asset['model']) ?>" class="border rounded w-full py-2 px-3">
            </div>

            <!-- Financials -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Costo de Compra</label>
                <input type="number" step="0.01" name="purchase_cost" value="<?= htmlspecialchars($asset['purchase_cost']) ?>" class="border rounded w-full py-2 px-3" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Fecha de Compra</label>
                <input type="date" name="purchase_date" value="<?= htmlspecialchars($asset['purchase_date']) ?>" class="border rounded w-full py-2 px-3" required>
            </div>

            <!-- Identifiers -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Número de Serie</label>
                <input type="text" name="serial_number" value="<?= htmlspecialchars($asset['serial_number']) ?>" class="border rounded w-full py-2 px-3">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Tipo de Adquisición</label>
                <select name="acquisition_type" class="border rounded w-full py-2 px-3">
                    <option value="Compra" <?= $asset['acquisition_type'] == 'Compra' ? 'selected' : '' ?>>Compra</option>
                    <option value="Arrendamiento" <?= $asset['acquisition_type'] == 'Arrendamiento' ? 'selected' : '' ?>>Arrendamiento</option>
                    <option value="Donación" <?= $asset['acquisition_type'] == 'Donación' ? 'selected' : '' ?>>Donación</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Asignado a</label>
                <select name="assigned_to" class="border rounded w-full py-2 px-3">
                    <option value="">-- Sin Asignar --</option>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= $asset['assigned_to'] == $user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

             <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Estado</label>
                <select name="status" class="border rounded w-full py-2 px-3">
                    <option value="Disponible" <?= $asset['status'] == 'Disponible' ? 'selected' : '' ?>>Disponible</option>
                    <option value="Asignado" <?= $asset['status'] == 'Asignado' ? 'selected' : '' ?>>Asignado</option>
                    <option value="En Reparación" <?= $asset['status'] == 'En Reparación' ? 'selected' : '' ?>>En Reparación</option>
                    <option value="De Baja" <?= $asset['status'] == 'De Baja' ? 'selected' : '' ?>>De Baja</option>
                </select>
            </div>

             <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Descripción</label>
                <textarea name="description" class="border rounded w-full py-2 px-3"><?= htmlspecialchars($asset['description']) ?></textarea>
            </div>

             <!-- Hidden fields for non-editable but required fields in update for now if not in form -->
             <input type="hidden" name="quantity" value="<?= $asset['quantity'] ?>">
             <input type="hidden" name="batch_number" value="<?= $asset['batch_number'] ?>">
             <input type="hidden" name="leasing_company" value="<?= $asset['leasing_company'] ?>">
             <input type="hidden" name="cost_center" value="<?= $asset['cost_center'] ?>">

        </div>

        <div class="mt-6 flex justify-end">
            <a href="/assets" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancelar</a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Actualizar</button>
        </div>
    </form>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
