<?php if (isset($_SESSION['user_id'])): ?>
<aside class="w-64 bg-slate-900 text-white flex flex-col shadow-xl z-10 shrink-0 sticky top-0 h-screen">
    <div class="h-16 flex items-center justify-center font-bold text-xl border-b border-slate-800 tracking-wider shrink-0">
        <i class="fas fa-boxes mr-2"></i> Activo Fijo
    </div>
    <nav class="flex-1 p-4 space-y-2 text-sm font-medium overflow-y-auto">
        <a href="/dashboard"
            class="block py-2.5 px-4 rounded transition <?= $active_page == 'dashboard' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800 text-slate-300' ?>">
            <i class="fas fa-home w-6"></i> Inicio
        </a>
        <a href="/assets"
            class="block py-2.5 px-4 rounded transition <?= $active_page == 'assets' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800 text-slate-300' ?>">
            <i class="fas fa-laptop w-6"></i> Activos
        </a>
        <a href="/users"
            class="block py-2.5 px-4 rounded transition <?= $active_page == 'users' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800 text-slate-300' ?>">
            <i class="fas fa-users w-6"></i> Usuarios
        </a>
        <a href="/accounts"
            class="block py-2.5 px-4 rounded transition <?= $active_page == 'accounts' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800 text-slate-300' ?>">
            <i class="fas fa-key w-6"></i> Cuentas
        </a>
        <a href="/accounting"
            class="block py-2.5 px-4 rounded transition <?= $active_page == 'accounting' ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800 text-slate-300' ?>">
            <i class="fas fa-calculator w-6"></i> Contabilidad
        </a>

        <!-- SECCIÓN ADMIN -->
        <?php if (($_SESSION['system_role'] ?? '') == 'admin'): ?>
        <div class="pt-4 mt-4 border-t border-slate-800">
            <p class="px-4 text-xs font-bold text-slate-500 uppercase mb-2">
                Administración
            </p>
            <a href="/admin/users" class="block py-2.5 px-4 rounded transition hover:bg-slate-800 text-slate-300">
                <i class="fas fa-users-cog w-6"></i> Usuarios Sistema
            </a>
            <a href="/audit" class="block py-2.5 px-4 rounded transition hover:bg-slate-800 text-slate-300">
                <i class="fas fa-shield-alt w-6"></i> Auditoría
            </a>
        </div>
        <?php endif; ?>

        <!-- SECCIÓN LOGOUT -->
        <div class="pt-4 mt-4 border-t border-slate-800">
            <a href="/logout"
                class="block py-2.5 px-4 rounded transition hover:bg-red-600 hover:text-white text-slate-300">
                <i class="fas fa-sign-out-alt w-6"></i> Cerrar Sesión
            </a>
        </div>
    </nav>
    <div class="p-4 border-t border-slate-800 text-xs text-slate-500 text-center">
        v3.5 Auditada
    </div>
</aside>
<?php endif; ?>
