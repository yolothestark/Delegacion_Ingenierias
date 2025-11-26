<?php
    // Aseguramos la conexión a MongoDB
    include '../../includes/db_connect_mongo.php';
    include '../layouts/header.php';

    // 1. Validar ID en URL
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo "<h2>Error: ID de miembro no proporcionado.</h2>";
        include '../layouts/admin_footer.php';
        exit;
    }

    $id_integrante_str = $_GET['id'];
    $miembro = null;

    try {
        // 2. Convertir string a ObjectId y Buscar Miembro
        $objectId = new MongoDB\BSON\ObjectId($id_integrante_str);
        $miembro = $db->integrantes->findOne(['_id' => $objectId]);

    } catch (Exception $e) {
        // Si el ID no tiene formato válido de MongoDB
        echo "<h2>Error: Formato de ID inválido.</h2>";
        include '../layouts/admin_footer.php';
        exit;
    }

    if (!$miembro) {
        echo "<h2>Error: No se encontró al miembro en la base de datos.</h2>";
        include '../layouts/admin_footer.php';
        exit;
    }

    // 3. Obtener listas para los Selects (Ordenadas)
    $cursor_cargos = $db->cargos->find([], ['sort' => ['nombre_cargo' => 1]]);
    $cursor_deptos = $db->departamentos->find([], ['sort' => ['nombre_depto' => 1]]);
    
    // Convertimos cursores a arrays para poder iterarlos si fuera necesario varias veces (opcional)
    // o simplemente iteramos directamente abajo.
?>

<h2>Editando Miembro: <?php echo htmlspecialchars($miembro['nombre_completo']); ?></h2>

<form action="editar_miembro_proceso.php" method="POST" enctype="multipart/form-data" class="admin-form">
    
    <input type="hidden" name="id_integrante" value="<?php echo (string)$miembro['_id']; ?>">
    
    <div class="input-group">
        <label for="nombre_completo">Nombre Completo:</label>
        <input type="text" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($miembro['nombre_completo']); ?>" required>
    </div>

    <div class="input-group">
        <label for="id_cargo_fk">Cargo:</label>
        <select id="id_cargo_fk" name="id_cargo_fk" required>
            <option value="">-- Selecciona un cargo --</option>
            <?php foreach ($cursor_cargos as $cargo): ?>
                <?php 
                    // Lógica para marcar SELECTED:
                    // Verificamos si existe el dato embebido ['cargo']['id'] (Estructura nueva)
                    // O si existe el campo plano ['id_cargo_fk'] (Estructura vieja o migración directa)
                    $id_actual = $miembro['cargo']['id'] ?? $miembro['id_cargo_fk'] ?? null;
                    $is_selected = ($cargo['id_cargo'] == $id_actual) ? 'selected' : '';
                ?>
                <option value="<?php echo $cargo['id_cargo']; ?>" <?php echo $is_selected; ?>>
                    <?php echo htmlspecialchars($cargo['nombre_cargo']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="input-group">
        <label for="id_depto_fk">Departamento:</label>
        <select id="id_depto_fk" name="id_depto_fk" required>
            <option value="">-- Selecciona un departamento --</option>
            <?php foreach ($cursor_deptos as $depto): ?>
                <?php 
                    // Misma lógica para departamento
                    $id_actual_depto = $miembro['departamento']['id'] ?? $miembro['id_depto_fk'] ?? null;
                    $is_selected = ($depto['id_depto'] == $id_actual_depto) ? 'selected' : '';
                ?>
                <option value="<?php echo $depto['id_depto']; ?>" <?php echo $is_selected; ?>>
                    <?php echo htmlspecialchars($depto['nombre_depto']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="input-group">
        <label for="email">Email (Opcional):</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($miembro['email'] ?? ''); ?>">
    </div>

    <div class="input-group">
        <label for="telefono">Teléfono (Opcional):</label>
        <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($miembro['telefono'] ?? ''); ?>">
    </div>

    <div class="input-group">
        <label for="bio">Biografía Corta (Opcional):</label>
        <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($miembro['bio'] ?? ''); ?></textarea>
    </div>

    <div class="input-group">
        <label for="orden">Orden (1 = primero):</label>
        <input type="number" id="orden" name="orden" value="<?php echo $miembro['orden'] ?? 100; ?>">
    </div>
    
    <div class="input-group">
        <label for="foto_perfil">Cambiar Foto de Perfil (Opcional):</label>
        <input type="file" id="foto_perfil" name="foto_perfil" accept="image/jpeg, image/png, image/gif">
        
        <?php if (!empty($miembro['foto_url'])): ?>
            <p style="margin-top: 10px;"><strong>Foto Actual:</strong></p>
            
             <?php
                // Definimos ruta base pública relativa si no existe
                // Como estamos en admin/directorio, subimos dos niveles para llegar a root
                $ruta_base_publica = isset($ruta_base_publica) ? $ruta_base_publica : '../../';
                
                $placeholder_img = "https://placehold.co/100x100/004a99/FFFFFF?text=Perfil";
                $foto_src = $placeholder_img;
                $foto_url_db = $miembro['foto_url'];
                
                // Limpiamos slash inicial si existe
                $foto_url_limpia = ltrim($foto_url_db, '/');
                $ruta_fisica_absoluta = dirname(dirname(__DIR__)) . '/' . $foto_url_limpia;
                
                if (file_exists($ruta_fisica_absoluta)) {
                    // Concatenamos la ruta relativa para el navegador
                    $foto_src = $ruta_base_publica . $foto_url_limpia;
                }
            ?>
            
            <img src="<?php echo $foto_src; ?>" alt="Foto actual" style="max-width: 100px; height: auto; border: 1px solid #ddd; border-radius: 5px;">
            <input type="hidden" name="foto_url_actual" value="<?php echo htmlspecialchars($miembro['foto_url']); ?>">
        <?php endif; ?>
    </div>
    
    <button type="submit" class="btn-guardar">Actualizar Miembro</button>
</form>

<?php
    include '../layouts/admin_footer.php';
?>