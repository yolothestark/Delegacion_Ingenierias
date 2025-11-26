<?php
    // Incluir header (que ya tiene db_connect_mongo.php)
    include '../layouts/header.php';

    // 1. Validar ID en URL
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo "<h2>Error: ID de noticia no proporcionado.</h2>";
        include '../layouts/admin_footer.php';
        exit;
    }

    $id_noticia_str = $_GET['id'];
    $noticia = null;

    try {
        // 2. Convertir String a ObjectId y Buscar Noticia
        $objectId = new MongoDB\BSON\ObjectId($id_noticia_str);
        $noticia = $db->noticias->findOne(['_id' => $objectId]);

    } catch (Exception $e) {
        // Si el formato del ID es inválido
        echo "<h2>Error: Formato de ID inválido.</h2>";
        include '../layouts/admin_footer.php';
        exit;
    }

    if (!$noticia) {
        echo "<h2>Error: No se encontró la noticia.</h2>";
        include '../layouts/admin_footer.php';
        exit;
    }

    // 3. Obtener Categorías para el Select
    // Equivalente a: SELECT * FROM categorias ORDER BY nombre_categoria ASC
    $cursor_categorias = $db->categorias->find([], ['sort' => ['nombre_categoria' => 1]]);
?>

<h2>Editando Noticia: <?php echo htmlspecialchars($noticia['titulo']); ?></h2>

<form action="editar_noticia_proceso.php" method="POST" enctype="multipart/form-data" class="admin-form">
    
    <input type="hidden" name="id_noticia" value="<?php echo (string)$noticia['_id']; ?>">
    
    <div class="input-group">
        <label for="titulo">Título de la Noticia:</label>
        <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($noticia['titulo']); ?>" required>
    </div>
    
    <div class="input-group">
        <label for="contenido">Contenido:</label>
        <textarea id="contenido" name="contenido" rows="10" required><?php echo htmlspecialchars($noticia['contenido']); ?></textarea>
    </div>
    
    <div class="input-group">
        <label for="id_categoria_fk">Categoría:</label>
        <select id="id_categoria_fk" name="id_categoria_fk" required>
            <option value="">-- Selecciona una categoría --</option>
            <?php foreach ($cursor_categorias as $cat): ?>
                <?php 
                    // Lógica para marcar SELECTED:
                    // 1. Buscamos en la estructura embebida nueva: ['categoria']['id']
                    // 2. Si no existe, buscamos en la estructura vieja: ['id_categoria_fk']
                    $id_cat_actual = $noticia['categoria']['id'] ?? $noticia['id_categoria_fk'] ?? null;
                    
                    $isSelected = ($cat['id_categoria'] == $id_cat_actual) ? 'selected' : '';
                ?>
                <option value="<?php echo $cat['id_categoria']; ?>" <?php echo $isSelected; ?>>
                    <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="input-group">
        <label for="imagen_noticia">Cambiar Imagen (Opcional):</label>
        <input type="file" id="imagen_noticia" name="imagen_noticia" accept="image/jpeg, image/png, image/gif">
        
        <?php if (!empty($noticia['ruta_imagen'])): ?>
            <p style="margin-top: 10px;"><strong>Imagen Actual:</strong></p>
            
            <?php
                // Lógica para mostrar la imagen (Sistema de archivos)
                $ruta_base_publica = isset($ruta_base_publica) ? $ruta_base_publica : '../../';
                $placeholder_img = "https://placehold.co/200x200/004a99/FFFFFF?text=Img";
                $imagen_src = $placeholder_img;
                
                $ruta_imagen_db = $noticia['ruta_imagen'];
                
                // Limpiamos slash inicial
                $ruta_limpia = ltrim($ruta_imagen_db, '/');
                $ruta_fisica_absoluta = dirname(dirname(__DIR__)) . '/' . $ruta_limpia;
                
                if (file_exists($ruta_fisica_absoluta)) {
                    $imagen_src = $ruta_base_publica . $ruta_limpia;
                }
            ?>
            
            <img src="<?php echo $imagen_src; ?>" alt="Imagen actual" style="max-width: 200px; height: auto; border: 1px solid #ddd;">
            <input type="hidden" name="ruta_imagen_actual" value="<?php echo htmlspecialchars($noticia['ruta_imagen']); ?>">
        <?php endif; ?>
    </div>
    
    <button type="submit" class="btn-guardar">Actualizar Noticia</button>
</form>

<?php
    include '../layouts/admin_footer.php';
?>