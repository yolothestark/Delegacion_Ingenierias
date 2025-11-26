<?php
    // 1. Incluimos la conexión a MongoDB primero para tener disponible la variable $db
    include '../../includes/db_connect_mongo.php';
    include '../layouts/header.php';

    // 2. Consultamos los tipos de documento para el <select>
    // Equivalente a: SELECT * FROM tipos_documentos ORDER BY nombre_tipo ASC
    // En MongoDB usamos 'find' y pasamos las opciones de ordenamiento (1 = ASC, -1 = DESC)
    $cursor_tipos = $db->tipos_documentos->find([], ['sort' => ['nombre_tipo' => 1]]);
?>

<h2>Subir Nuevo Documento</h2>

<form action="subir_documento_proceso.php" method="POST" enctype="multipart/form-data" class="admin-form">
    
    <div class="input-group">
        <label for="nombre_documento">Nombre del Documento:</label>
        <input type="text" id="nombre_documento" name="nombre_documento" required>
    </div>

    <div class="input-group">
        <label for="id_tipo_fk">Tipo de Documento:</label>
        <select id="id_tipo_fk" name="id_tipo_fk" required>
            <option value="">-- Selecciona un tipo --</option>
            <?php
                // Iteramos directamente sobre el Cursor de MongoDB
                foreach ($cursor_tipos as $tipo) {
                    // Usamos 'id_tipo' (el entero) como value, ya que 'subir_documento_proceso.php'
                    // espera un entero para buscar y embeber el nombre del tipo.
            ?>
                <option value="<?php echo $tipo['id_tipo']; ?>">
                    <?php echo htmlspecialchars($tipo['nombre_tipo']); ?>
                </option>
            <?php
                }
            ?>
        </select>
    </div>
    
    <div class="input-group">
        <label for="version">Versión (ej. 2.0, 2025, etc.):</label>
        <input type="text" id="version" name="version">
    </div>
    
    <div class="input-group">
        <label for="archivo_pdf">Archivo (SÓLO PDF):</label>
        <input type="file" id="archivo_pdf" name="archivo_pdf" accept=".pdf" required>
    </div>
    
    <button type="submit" class="btn-guardar">Guardar Documento</button>
</form>

<?php
    // 3. Incluimos el pie de página
    include '../layouts/admin_footer.php';
?>