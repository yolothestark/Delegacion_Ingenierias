<?php
    // El header ya incluye la conexión a MongoDB ($db)
    include '../layouts/header.php';

    // Consultamos las categorías para el <select>
    // Equivalente a: SELECT * FROM categorias ORDER BY nombre_categoria ASC
    $cursor_categorias = $db->categorias->find([], ['sort' => ['nombre_categoria' => 1]]);
?>

<h2>Crear Nueva Noticia</h2>

<form action="crear_noticia_proceso.php" method="POST" enctype="multipart/form-data" class="admin-form">
    
    <div class="input-group">
        <label for="titulo">Título de la Noticia:</label>
        <input type="text" id="titulo" name="titulo" required>
    </div>
    
    <div class="input-group">
        <label for="contenido">Contenido:</label>
        <textarea id="contenido" name="contenido" rows="10" required></textarea>
    </div>
    
    <div class="input-group">
        <label for="id_categoria_fk">Categoría:</label>
        <select id="id_categoria_fk" name="id_categoria_fk" required>
            <option value="">-- Selecciona una categoría --</option>
            <?php
                // Iteramos directamente sobre el cursor de MongoDB
                foreach ($cursor_categorias as $cat) {
                    // Usamos 'id_categoria' (el entero) como valor, ya que el proceso de guardado
                    // usa este ID para buscar y embeber el nombre de la categoría.
            ?>
                <option value="<?php echo $cat['id_categoria']; ?>">
                    <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                </option>
            <?php
                }
            ?>
        </select>
    </div>
    
    <div class="input-group">
        <label for="imagen_noticia">Imagen (Opcional):</label>
        <input type="file" id="imagen_noticia" name="imagen_noticia" accept="image/jpeg, image/png, image/gif">
    </div>
    
    <button type="submit" class="btn-guardar">Guardar Noticia</button>
</form>

<?php
    include '../layouts/admin_footer.php';
?>