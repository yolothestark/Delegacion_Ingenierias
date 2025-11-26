<?php
    // Sube 2 niveles para la conexión MongoDB
    include '../../includes/db_connect_mongo.php';
    include '../layouts/header.php';

    // 1. Validar ID en URL
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo "<h2>Error: ID de documento no proporcionado.</h2>";
        include '../layouts/admin_footer.php';
        exit;
    }

    $id_doc_str = $_GET['id'];
    $documento = null;

    try {
        // 2. Convertir String a ObjectId y buscar
        $objectId = new MongoDB\BSON\ObjectId($id_doc_str);
        $documento = $db->documentos->findOne(['_id' => $objectId]);

    } catch (Exception $e) {
        // Si el formato del ID es inválido (no es hexadecimal de 24 chars)
        echo "<h2>Error: Formato de ID inválido.</h2>";
        include '../layouts/admin_footer.php';
        exit;
    }

    // Si la búsqueda no devolvió nada
    if (!$documento) {
        echo "<h2>Error: No se encontró el documento.</h2>";
        include '../layouts/admin_footer.php';
        exit;
    }

    // 3. Obtener Tipos de Documentos para el Select
    // Asumimos colección 'tipos_documentos' y ordenamos por nombre
    $cursor_tipos = $db->tipos_documentos->find([], ['sort' => ['nombre_tipo' => 1]]);
?>

<h2>Editando Documento: <?php echo htmlspecialchars($documento['nombre_documento']); ?></h2>

<form action="editar_documento_proceso.php" method="POST" class="admin-form">
    
    <input type="hidden" name="id_documento" value="<?php echo (string)$documento['_id']; ?>">
    
    <div class="input-group">
        <label for="nombre_documento">Nombre del Documento:</label>
        <input type="text" id="nombre_documento" name="nombre_documento" value="<?php echo htmlspecialchars($documento['nombre_documento']); ?>" required>
    </div>

    <div class="input-group">
        <label for="id_tipo_fk">Tipo de Documento:</label>
        <select id="id_tipo_fk" name="id_tipo_fk" required>
            <option value="">-- Selecciona un tipo --</option>
            <?php foreach ($cursor_tipos as $tipo): ?>
                <?php 
                    // Lógica para marcar SELECTED:
                    // 1. Buscamos en la estructura embebida nueva: ['tipo_documento']['id']
                    // 2. Si no existe, buscamos en la estructura vieja: ['id_tipo_fk']
                    $id_tipo_actual = $documento['tipo_documento']['id'] ?? $documento['id_tipo_fk'] ?? null;
                    
                    // Comparamos con el ID del tipo que estamos iterando
                    $isSelected = ($tipo['id_tipo'] == $id_tipo_actual) ? 'selected' : '';
                ?>
                <option value="<?php echo $tipo['id_tipo']; ?>" <?php echo $isSelected; ?>>
                    <?php echo htmlspecialchars($tipo['nombre_tipo']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="input-group">
        <label for="version">Versión (ej. 2.0, 2025, etc.):</label>
        <input type="text" id="version" name="version" value="<?php echo htmlspecialchars($documento['version'] ?? ''); ?>">
    </div>
    
    <div class="input-group">
        <label>Archivo Actual:</label>
        <p><i>Para cambiar el archivo PDF, por favor, bórralo y súbelo de nuevo. La edición de archivos no está permitida por seguridad.</i></p>
        <?php if (!empty($documento['ruta_archivo'])): ?>
            <p><small>Ruta actual: <?php echo htmlspecialchars($documento['ruta_archivo']); ?></small></p>
        <?php endif; ?>
    </div>
    
    <button type="submit" class="btn-guardar">Actualizar Información</button>
</form>

<?php
    include '../layouts/admin_footer.php';
?>