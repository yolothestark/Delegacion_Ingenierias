<?php
    // Aseguramos la conexión a MongoDB antes del header
    include '../../includes/db_connect_mongo.php';
    include '../layouts/header.php';

    // 1. Obtener Cargos (Ordenados alfabéticamente A-Z)
    // Equivalente a: SELECT * FROM cargos ORDER BY nombre_cargo ASC
    $cursor_cargos = $db->cargos->find([], ['sort' => ['nombre_cargo' => 1]]);

    // 2. Obtener Departamentos (Ordenados alfabéticamente A-Z)
    // Equivalente a: SELECT * FROM departamentos ORDER BY nombre_depto ASC
    $cursor_deptos = $db->departamentos->find([], ['sort' => ['nombre_depto' => 1]]);
?>

<h2>Agregar Nuevo Miembro al Directorio</h2>

<form action="crear_miembro_proceso.php" method="POST" enctype="multipart/form-data" class="admin-form">
    
    <div class="input-group">
        <label for="nombre_completo">Nombre Completo:</label>
        <input type="text" id="nombre_completo" name="nombre_completo" required>
    </div>

    <div class="input-group">
        <label for="id_cargo_fk">Cargo:</label>
        <select id="id_cargo_fk" name="id_cargo_fk" required>
            <option value="">-- Selecciona un cargo --</option>
            <?php foreach ($cursor_cargos as $cargo): ?>
                <option value="<?php echo $cargo['id_cargo']; ?>">
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
                <option value="<?php echo $depto['id_depto']; ?>">
                    <?php echo htmlspecialchars($depto['nombre_depto']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="input-group">
        <label for="email">Email (Opcional):</label>
        <input type="email" id="email" name="email">
    </div>

    <div class="input-group">
        <label for="telefono">Teléfono (Opcional):</label>
        <input type="tel" id="telefono" name="telefono">
    </div>

    <div class="input-group">
        <label for="bio">Biografía Corta (Opcional):</label>
        <textarea id="bio" name="bio" rows="4"></textarea>
    </div>

    <div class="input-group">
        <label for="orden">Orden (Opcional, 1 = primero):</label>
        <input type="number" id="orden" name="orden" value="100">
    </div>
    
    <div class="input-group">
        <label for="foto_perfil">Foto de Perfil (Opcional):</label>
        <input type="file" id="foto_perfil" name="foto_perfil" accept="image/jpeg, image/png, image/gif">
    </div>
    
    <button type="submit" class="btn-guardar">Guardar Miembro</button>
</form>

<?php
    include '../layouts/admin_footer.php';
?>