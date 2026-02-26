<?php ob_start(); ?>

<div class="max-w-5xl mx-auto">
    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Nuevo Activo</h1>
            <p class="text-gray-500 text-sm mt-1">Gestión de inventario y asignación de recursos.</p>
        </div>
        <a href="/assets"
            class="text-gray-500 hover:text-gray-800 font-medium transition flex items-center bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
            <i class="fas fa-times mr-2"></i> Cancelar
        </a>
    </div>

    <!-- MAIN FORM -->
    <form id="assetForm" action="/assets/store" method="POST" enctype="multipart/form-data" class="space-y-6">
        
        <!-- SECCIÓN 0: DEFINICIÓN INTELIGENTE -->
        <div class="bg-white rounded-xl shadow-lg border-l-4 border-blue-600 p-6">
            <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3 mb-6 flex items-center">
                <i class="fas fa-sliders-h mr-3 text-blue-600"></i> Definición del Activo
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                <!-- PREGUNTA 1: CATEGORÍA -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">1. ¿Qué tipo de activo es?</label>
                    <div class="relative">
                        <select id="assetCategory" name="category"
                            class="w-full p-3 pl-4 border border-gray-300 rounded-xl bg-gray-50 font-semibold text-gray-800 focus:ring-2 focus:ring-blue-500 outline-none cursor-pointer transition"
                            onchange="updateFormFields()">
                            <option value="Computadora">Laptop / Computadora</option>
                            <option value="Vehículo">Vehículo / Flotilla</option>
                            <option value="Herramienta">Herramienta / Maquinaria</option>
                            <option value="Celular">Tablet / Celular</option>
                            <option value="Mobiliario">Mobiliario / Oficina</option>
                            <option value="Periférico">Periférico</option>
                            <option value="Uniforme">Uniforme / Ropa</option>
                            <option value="Otro">Otro</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <p id="catHint" class="text-xs text-gray-400 mt-2 italic hidden"></p>
                </div>

                <!-- PREGUNTA 2: TIPO DE ADQUISICIÓN (DINÁMICO) -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">2. ¿Cuál es el origen del bien?</label>
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Opción Compra -->
                        <label class="cursor-pointer relative group">
                            <input type="radio" name="acquisition_type" value="Compra" class="peer sr-only"
                                onchange="toggleLeaseFields()" checked>
                            <div class="p-3 rounded-xl border border-gray-200 bg-gray-50 hover:bg-white peer-checked:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:text-emerald-700 transition text-center h-full flex flex-col justify-center items-center">
                                <i class="fas fa-shopping-cart mb-1 block text-lg"></i>
                                <span class="font-bold text-sm">Compra Propia</span>
                            </div>
                        </label>

                        <!-- Opción Arrendamiento -->
                        <label class="cursor-pointer relative group" id="leaseOptionContainer">
                            <input type="radio" name="acquisition_type" value="Arrendamiento" id="radioLease"
                                class="peer sr-only" onchange="toggleLeaseFields()">
                            <div class="p-3 rounded-xl border border-gray-200 bg-gray-50 hover:bg-white peer-checked:bg-purple-50 peer-checked:border-purple-500 peer-checked:text-purple-700 peer-disabled:opacity-50 peer-disabled:cursor-not-allowed transition text-center h-full flex flex-col justify-center items-center relative overflow-hidden">
                                <i class="fas fa-file-contract mb-1 block text-lg"></i>
                                <span class="font-bold text-sm">Arrendamiento</span>
                                <!-- Badge de Bloqueo -->
                                <div id="leaseLockBadge"
                                    class="hidden absolute inset-0 bg-gray-100/90 flex-col items-center justify-center text-gray-400 text-xs font-bold">
                                    <i class="fas fa-ban mb-1 text-base"></i>
                                    No Disponible
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- CAMPO ARRENDADORA -->
                    <div id="leasingCompanyField" class="hidden mt-4 animate-fade-in-down">
                        <label class="block text-xs font-bold text-purple-800 uppercase mb-1">Empresa Arrendadora <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <i class="fas fa-building absolute left-3 top-2.5 text-purple-400"></i>
                            <input type="text" id="leasingCompany" name="leasing_company"
                                class="w-full pl-9 p-2 border border-purple-300 rounded-lg bg-purple-50 focus:bg-white focus:ring-2 focus:ring-purple-400 outline-none transition"
                                placeholder="Ej: CHG Meridians, HP Financial Services...">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 1: DATOS GENERALES -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-700 border-b border-gray-100 pb-3 mb-6">
                <i class="fas fa-info-circle mr-2 text-gray-400"></i> Detalles Generales
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="col-span-1 md:col-span-3 lg:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre / Descripción <span class="text-red-500">*</span></label>
                    <input type="text" id="assetName" name="name"
                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition"
                        required placeholder="Ej: Laptop Dell Latitude 7420">
                </div>
                <div>
                    <label id="lblSerial" class="block text-xs font-bold text-gray-500 uppercase mb-1">No. Serie / Identificador</label>
                    <input type="text" id="assetSerial" name="serial_number"
                        class="w-full p-2.5 border border-gray-300 rounded-lg font-mono text-sm uppercase">
                </div>
                <div id="brandField">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Marca</label>
                    <input type="text" id="assetBrand" name="brand"
                        class="w-full p-2.5 border border-gray-300 rounded-lg">
                </div>
                <div id="modelField">
                    <label id="lblModel" class="block text-xs font-bold text-gray-500 uppercase mb-1">Modelo</label>
                    <input type="text" id="assetModel" name="model"
                        class="w-full p-2.5 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Centro de Costos</label>
                    <input type="text" id="assetCostCenter" name="cost_center"
                        class="w-full p-2.5 border border-gray-300 rounded-lg placeholder-gray-300"
                        placeholder="Ej: Ventas, TI, Administración...">
                </div>
                <!-- STOCK / CANTIDAD -->
                <div class="md:col-span-3 grid grid-cols-2 gap-4 bg-yellow-50 p-4 rounded-lg border border-yellow-100 mt-2">
                    <div>
                        <label class="block text-xs font-bold text-yellow-800 uppercase mb-1">Cantidad (Stock)</label>
                        <input type="number" id="assetQty" name="quantity" value="1" min="1"
                            class="w-full p-2 border border-yellow-300 rounded-lg font-bold text-center bg-white focus:ring-2 focus:ring-yellow-400 outline-none"
                            title="Para activos únicos dejar en 1">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-yellow-800 uppercase mb-1">No. Lote / Batch</label>
                        <input type="text" id="assetBatch" name="batch_number"
                            class="w-full p-2 border border-yellow-300 rounded-lg bg-white focus:ring-2 focus:ring-yellow-400 outline-none"
                            placeholder="Opcional">
                    </div>
                </div>
            </div>
        </div>

        <!-- CAMPOS ESPECÍFICOS (VISIBILIDAD CONTROLADA POR JS) -->
        <div id="specificFieldsContainer" class="space-y-6">

            <!-- VEHÍCULOS -->
            <div id="form-Vehículo" class="specific-form bg-white rounded-xl shadow-sm border border-gray-200 p-6 hidden">
                <h3 class="text-lg font-bold text-blue-800 border-b border-gray-100 pb-3 mb-6 flex items-center"><i class="fas fa-car mr-2"></i> Datos del Vehículo</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Placas</label>
                    <input type="text" name="license_plate" class="w-full p-2.5 border border-gray-300 rounded-lg uppercase placeholder-gray-300" placeholder="ABC-123"></div>
                    
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">VIN / NIV</label>
                    <input type="text" name="vin" class="w-full p-2.5 border border-gray-300 rounded-lg uppercase font-mono text-sm placeholder-gray-300" maxlength="17" placeholder="17 Caracteres"></div>
                    
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Año</label>
                    <input type="number" name="vehicle_year" min="1900" max="2100" class="w-full p-2.5 border border-gray-300 rounded-lg placeholder-gray-300" placeholder="YYYY"></div>
                    
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kilometraje</label>
                    <input type="number" name="mileage" min="0" step="0.01" class="w-full p-2.5 border border-gray-300 rounded-lg text-right placeholder-gray-300" placeholder="0"></div>
                    
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Color</label>
                    <input type="text" name="color" class="w-full p-2.5 border border-gray-300 rounded-lg"></div>
                </div>
            </div>

            <!-- UNIFORMES -->
            <div id="form-Uniforme" class="specific-form bg-white rounded-xl shadow-sm border border-gray-200 p-6 hidden">
                <h3 class="text-lg font-bold text-orange-600 border-b border-gray-100 pb-3 mb-6 flex items-center"><i class="fas fa-tshirt mr-2"></i> Detalles de Prenda</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Talla</label>
                    <input type="text" name="size" class="w-full p-2.5 border border-gray-300 rounded-lg placeholder-gray-300" placeholder="S, M, L, 32..."></div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Corte / Género</label>
                        <select name="gender_cut" class="w-full p-2.5 border border-gray-300 rounded-lg bg-white">
                            <option value="">Seleccione</option>
                            <option value="Caballero">Caballero</option>
                            <option value="Dama">Dama</option>
                            <option value="Unisex">Unisex</option>
                        </select>
                    </div>
                    
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Material / Tela</label>
                    <input type="text" name="material" class="w-full p-2.5 border border-gray-300 rounded-lg"></div>
                    
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Color</label>
                    <input type="text" name="color" class="w-full p-2.5 border border-gray-300 rounded-lg"></div>
                </div>
            </div>

            <!-- MOBILIARIO -->
            <div id="form-Mobiliario" class="specific-form bg-white rounded-xl shadow-sm border border-gray-200 p-6 hidden">
                <h3 class="text-lg font-bold text-amber-600 border-b border-gray-100 pb-3 mb-6 flex items-center">
                    <i class="fas fa-chair mr-2"></i> Detalles del Mobiliario
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Dimensiones / Medidas</label>
                    <input type="text" name="dimensions" class="w-full p-2.5 border border-gray-300 rounded-lg placeholder-gray-300" placeholder="Largo x Ancho x Alto"></div>
                    
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Material / Acabado</label>
                    <input type="text" name="material" class="w-full p-2.5 border border-gray-300 rounded-lg"></div>
                    
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Color</label>
                    <input type="text" name="color" class="w-full p-2.5 border border-gray-300 rounded-lg"></div>
                </div>
            </div>

            <!-- HERRAMIENTA -->
            <div id="form-Herramienta" class="specific-form bg-white rounded-xl shadow-sm border border-gray-200 p-6 hidden">
                <h3 class="text-lg font-bold text-amber-600 border-b border-gray-100 pb-3 mb-6 flex items-center">
                    <i class="fas fa-tools mr-2"></i> Detalles de Herramienta
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Material</label>
                    <input type="text" name="material" class="w-full p-2.5 border border-gray-300 rounded-lg"></div>
                    
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Color</label>
                    <input type="text" name="color" class="w-full p-2.5 border border-gray-300 rounded-lg"></div>
                </div>
            </div>

            <!-- CÓMPUTO Y CELULAR -->
            <div id="form-Tech" class="specific-form bg-white rounded-xl shadow-sm border border-gray-200 p-6 hidden">
                <h3 class="text-lg font-bold text-purple-700 border-b border-gray-100 pb-3 mb-6 flex items-center"><i class="fas fa-microchip mr-2"></i> Especificaciones Técnicas y Acceso</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6">
                    <div class="lg:col-span-2"><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Procesador</label>
                    <input type="text" name="processor" class="w-full p-2.5 border border-gray-300 rounded-lg"></div>
                    
                    <div class="lg:col-span-1"><label class="block text-xs font-bold text-gray-500 uppercase mb-1">RAM</label>
                    <input type="text" name="ram" class="w-full p-2.5 border border-gray-300 rounded-lg"></div>
                    
                    <div class="lg:col-span-1"><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Almacenamiento</label>
                    <input type="text" name="storage" class="w-full p-2.5 border border-gray-300 rounded-lg"></div>
                    
                    <div class="lg:col-span-2"><label class="block text-xs font-bold text-gray-500 uppercase mb-1">SO / Versión</label>
                    <input type="text" name="operating_system" class="w-full p-2.5 border border-gray-300 rounded-lg"></div>

                    <div class="lg:col-span-3">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Usuario del Equipo</label>
                        <input type="text" name="device_user" class="w-full p-2.5 border border-gray-300 rounded-lg" placeholder="Ej: admin, jperez">
                    </div>
                    
                    <div class="lg:col-span-3">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Contraseña del Equipo</label>
                        <input type="text" name="device_password" class="w-full p-2.5 border border-gray-300 rounded-lg" placeholder="Contraseña de acceso local">
                    </div>
                </div>
            </div>
            
        </div>

        <!-- SECCIÓN 2: ESTADO Y FINANZAS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div id="assignmentSection" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-2 opacity-10 text-9xl text-gray-300 pointer-events-none"><i class="fas fa-users"></i></div>
                <h3 class="text-sm font-bold text-gray-800 uppercase mb-4 flex items-center text-blue-600 relative z-10">
                    <i class="fas fa-user-tag mr-2"></i> Asignación y Estado
                </h3>

                <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 mb-4 relative z-10">
                    <label class="block text-xs font-bold text-blue-800 mb-1">Usuario Responsable</label>
                    <select id="assetAssignedTo" name="assigned_to"
                        class="w-full p-2.5 border border-blue-200 rounded-lg bg-white outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <option value="">-- En Inventario (Sin Asignar) --</option>
                        <?php if(!empty($users)): foreach($users as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                    <p class="text-[10px] text-blue-600 mt-1 italic"><i class="fas fa-bolt mr-1"></i>Al asignar un usuario, el estado cambiará automáticamente.</p>
                </div>

                <div class="relative z-10">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Estado del Activo</label>
                    <select id="assetStatus" name="status"
                        class="w-full p-2.5 border border-gray-300 rounded-lg bg-white outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Disponible">Disponible</option>
                        <option value="Asignado">Asignado</option>
                        <option value="En Mantenimiento">En Mantenimiento</option>
                        <option value="En Reparación">En Reparación</option>
                        <option value="De Baja">De Baja</option>
                    </select>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase mb-4 flex items-center text-green-600"><i class="fas fa-file-invoice-dollar mr-2"></i> Detalles Financieros</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Fecha Contrato / Compra</label>
                    <input type="date" id="assetDate" name="purchase_date" class="w-full p-2.5 border border-gray-300 rounded-lg"></div>
                    
                    <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Costo / Valor ($)</label>
                    <input type="number" id="assetCost" name="purchase_cost" value="0" min="0" step="0.01" class="w-full p-2.5 border border-gray-300 rounded-lg font-bold text-gray-700 text-right"></div>
                    
                    <div class="col-span-2"><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Notas Financieras</label>
                    <textarea id="assetDesc" name="description" rows="2" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm placeholder-gray-300" placeholder="Detalles de compra, garantía, vencimiento de contrato, etc."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- ARCHIVOS -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-700 mb-4"><i class="fas fa-camera mr-2"></i> Fotos y Documentos</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Foto Principal</label>
                <input type="file" id="assetPhoto" name="photo"
                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                </div>
                <div><label class="block text-xs font-bold text-gray-500 uppercase mb-1">Documento / Factura</label>
                <input type="file" id="assetDocument" name="document"
                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer">
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4 pb-12">
            <button type="submit"
                class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold text-lg hover:bg-blue-700 shadow-lg flex items-center transform transition hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                <i class="fas fa-save mr-2"></i> Guardar Activo
            </button>
        </div>
    </form>
</div>

<script>
    // --- CONFIGURACIÓN DE REGLAS DE NEGOCIO ---
    const CATEGORY_RULES = {
        'Uniforme': { canLease: false, hint: 'Los uniformes son de compra directa.' },
        'Periférico': { canLease: false, hint: 'Teclados y mouses suelen ser compra.' },
        'Vehículo': { canLease: true, hint: '' },
        'Computadora': { canLease: true, hint: '' },
        'Herramienta': { canLease: true, hint: '' },
        'Mobiliario': { canLease: true, hint: '' },
        'Celular': { canLease: true, hint: '' }
    };

    // --- LÓGICA DE ESTADO AUTOMÁTICO ---
    const assignSelect = document.getElementById('assetAssignedTo');
    const statusSelect = document.getElementById('assetStatus');

    assignSelect.addEventListener('change', function () {
        if (this.value && this.value !== "") {
            statusSelect.value = "Asignado";
        } else {
            if (statusSelect.value === 'Asignado') {
                statusSelect.value = "Disponible";
            }
        }
    });

    statusSelect.addEventListener('change', function () {
        if (this.value === 'Disponible' && assignSelect.value !== "") {
            if (confirm("Al marcar como Disponible se quitará la asignación del usuario. ¿Continuar?")) {
                assignSelect.value = "";
            } else {
                this.value = "Asignado";
            }
        }
    });

    // --- LÓGICA VISUAL DE CATEGORÍAS Y ARRENDAMIENTO ---
    function updateFormFields() {
        const cat = document.getElementById('assetCategory').value;
        const radioLease = document.getElementById('radioLease');
        const radioBuy = document.querySelector('input[name="acquisition_type"][value="Compra"]');
        const badge = document.getElementById('leaseLockBadge');
        const hintText = document.getElementById('catHint');

        // 1. Ocultar todas las formas específicas y desactivar sus inputs
        document.querySelectorAll('.specific-form').forEach(form => {
            form.classList.add('hidden');
            form.querySelectorAll('input, select, textarea').forEach(input => {
                input.disabled = true;
            });
        });

        document.getElementById('lblSerial').textContent = 'No. Serie / Identificador';
        document.getElementById('lblModel').textContent = 'Modelo';

        // 2. Mostrar la forma específica según la categoría y activarla
        let activeFormId = null;
        if (cat === 'Vehículo') {
            activeFormId = 'form-Vehículo';
            document.getElementById('lblSerial').textContent = 'VIN (NIV)';
            document.getElementById('lblModel').textContent = 'Versión';
        } else if (cat === 'Uniforme') {
            activeFormId = 'form-Uniforme';
            document.getElementById('lblSerial').textContent = 'SKU / Código';
            document.getElementById('lblModel').textContent = 'Tipo';
        } else if (cat === 'Mobiliario') {
            activeFormId = 'form-Mobiliario';
            document.getElementById('lblSerial').textContent = 'Cód. Inventario';
        } else if (cat === 'Herramienta') {
            activeFormId = 'form-Herramienta';
            document.getElementById('lblSerial').textContent = 'No. Serie / ID';
        } else if (cat === 'Computadora' || cat === 'Laptop' || cat === 'Celular') {
            activeFormId = 'form-Tech';
            if (cat === 'Celular') {
                document.getElementById('lblSerial').textContent = 'IMEI / Serie';
            }
        }

        if (activeFormId) {
            const activeForm = document.getElementById(activeFormId);
            if (activeForm) {
                activeForm.classList.remove('hidden');
                activeForm.querySelectorAll('input, select, textarea').forEach(input => {
                    input.disabled = false;
                });
            }
        }

        // 3. Mostrar / Ocultar bloque de asignación
        const assignmentSection = document.getElementById('assignmentSection');
        if (cat === 'Mobiliario' || cat === 'Herramienta' || cat === 'Otro') {
            assignmentSection.classList.add('hidden');
            document.getElementById('assetAssignedTo').value = ''; // Clean assignment
            document.getElementById('assetAssignedTo').dispatchEvent(new Event('change')); // Trigger status update
        } else {
            assignmentSection.classList.remove('hidden');
        }

        // 2. Aplicar Reglas de Arrendamiento
        const rules = CATEGORY_RULES[cat] || { canLease: true, hint: '' };

        if (!rules.canLease) {
            radioLease.disabled = true;
            badge.classList.remove('hidden');
            badge.classList.add('flex');
            if (radioLease.checked) {
                radioBuy.checked = true;
                toggleLeaseFields();
            }
            hintText.textContent = rules.hint ? `Nota: ${rules.hint}` : '';
            hintText.classList.remove('hidden');
        } else {
            radioLease.disabled = false;
            badge.classList.add('hidden');
            badge.classList.remove('flex');
            hintText.classList.add('hidden');
        }
    }

    // --- LÓGICA DE ARRENDAMIENTO ---
    function toggleLeaseFields() {
        const isLease = document.querySelector('input[name="acquisition_type"]:checked').value === 'Arrendamiento';
        const leaseDiv = document.getElementById('leasingCompanyField');
        if (isLease) {
            leaseDiv.classList.remove('hidden');
            setTimeout(() => document.getElementById('leasingCompany').focus(), 100);
        } else {
            leaseDiv.classList.add('hidden');
            document.getElementById('leasingCompany').value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateFormFields();
        toggleLeaseFields();
    });
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/main.php'; ?>
