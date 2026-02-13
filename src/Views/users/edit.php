<?php ob_start(); ?>

<div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6">Editar Usuario: <?= htmlspecialchars($user['name']) ?></h2>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form action="/users/update/<?= $user['id'] ?>" method="POST">
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Nombre Completo</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="border rounded w-full py-2 px-3" required>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="border rounded w-full py-2 px-3" required>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Contraseña (Dejar en blanco para mantener)</label>
            <input type="password" name="password" class="border rounded w-full py-2 px-3">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Rol</label>
            <select name="role" class="border rounded w-full py-2 px-3">
                <option value="General" <?= ($user['role'] ?? '') == 'General' ? 'selected' : '' ?>>General</option>
                <option value="Admin" <?= ($user['role'] ?? '') == 'Admin' ? 'selected' : '' ?>>Admin</option>
                <option value="Soporte" <?= ($user['role'] ?? '') == 'Soporte' ? 'selected' : '' ?>>Soporte</option>
            </select>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Estado</label>
            <select name="status" class="border rounded w-full py-2 px-3">
                <option value="Activo" <?= ($user['status'] ?? '') == 'Activo' ? 'selected' : '' ?>>Activo</option>
                <option value="Inactivo" <?= ($user['status'] ?? '') == 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>

        <div class="flex justify-end">
            <a href="/users" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancelar</a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Actualizar</button>
        </div>
    </form>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
