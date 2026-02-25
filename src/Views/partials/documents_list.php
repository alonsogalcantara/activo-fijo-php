<?php
// Expected variables:
// $documents: Array of document objects
// $entityType: 'asset', 'account', 'user'
// $entityId: ID of the entity
?>
<div class="bg-white rounded-xl shadow overflow-hidden mb-8" id="documents-section">
    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
        <h3 class="font-bold text-gray-700 flex items-center">
            <i class="fas fa-paperclip mr-2 text-gray-500"></i> Documentos y Adjuntos
        </h3>
        <span class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full font-bold"><?= count($documents) ?></span>
    </div>

    <div class="p-6">
        <!-- List of Documents -->
        <?php if (!empty($documents)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <?php foreach ($documents as $doc): ?>
                    <div class="border border-gray-200 rounded-lg p-3 flex items-start hover:shadow-md transition bg-white group relative">
                        <!-- Icon based on file type -->
                        <div class="mr-3 mt-1">
                            <?php
                            $ext = strtolower(pathinfo($doc['filename'], PATHINFO_EXTENSION));
                            $iconClass = 'fa-file';
                            $iconColor = 'text-gray-400';
                            
                            if (in_array($ext, ['pdf'])) { $iconClass = 'fa-file-pdf'; $iconColor = 'text-red-500'; }
                            elseif (in_array($ext, ['doc', 'docx'])) { $iconClass = 'fa-file-word'; $iconColor = 'text-blue-500'; }
                            elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) { $iconClass = 'fa-file-excel'; $iconColor = 'text-green-500'; }
                            elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) { $iconClass = 'fa-file-image'; $iconColor = 'text-purple-500'; }
                            elseif (in_array($ext, ['zip', 'rar'])) { $iconClass = 'fa-file-archive'; $iconColor = 'text-yellow-500'; }
                            ?>
                            <i class="fas <?= $iconClass ?> <?= $iconColor ?> text-2xl"></i>
                        </div>
                        
                        <div class="flex-1 overflow-hidden">
                            <a href="/uploads/<?= htmlspecialchars($doc['filename']) ?>" target="_blank" class="font-medium text-gray-700 hover:text-blue-600 truncate block transition" title="<?= htmlspecialchars($doc['filename']) ?>">
                                <?= htmlspecialchars($doc['filename']) ?>
                            </a>
                            <div class="text-xs text-gray-400 mt-1 flex items-center gap-2">
                                <span><?= date('d/m/Y H:i', strtotime($doc['uploaded_at'])) ?></span>
                                <span>&bull;</span>
                                <span><?= round($doc['file_size'] / 1024, 1) ?> KB</span>
                            </div>
                        </div>

                        <!-- Delete Button -->
                        <form action="/documents/delete/<?= $doc['id'] ?>" method="POST" onsubmit="return confirm('¿Eliminar este documento permanentemente?');" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                             <button type="submit" class="text-red-400 hover:text-red-600 bg-white rounded-full p-1 shadow-sm hover:shadow-md transition">
                                 <i class="fas fa-trash-alt"></i>
                             </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200 mb-6">
                <i class="fas fa-cloud-upload-alt text-gray-300 text-4xl mb-2"></i>
                <p class="text-gray-500 italic">No hay documentos adjuntos aún.</p>
            </div>
        <?php endif; ?>

        <!-- Upload Form -->
        <form action="" method="POST" enctype="multipart/form-data" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <input type="hidden" name="action" value="upload_document">
            <input type="hidden" name="entity_type" value="<?= htmlspecialchars($entityType) ?>">
            <input type="hidden" name="entity_id" value="<?= htmlspecialchars($entityId) ?>">
            <input type="hidden" name="redirect_url" value="<?= $_SERVER['REQUEST_URI'] ?>">
            
            <div class="flex items-center gap-4">
                <label class="block">
                    <span class="sr-only">Elegir archivo</span>
                    <input type="file" name="document" required 
                        class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100 transition
                    "/>
                </label>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-blue-700 transition shadow flex items-center">
                    <i class="fas fa-upload mr-2"></i> Subir
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-2 ml-2">Formatos permitidos: PDF, Word, Excel, Imágenes, ZIP. Máx 10MB.</p>
        </form>
    </div>
</div>
