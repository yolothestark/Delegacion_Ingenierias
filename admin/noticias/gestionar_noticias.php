<?php
    // Sube 2 niveles para conexión MongoDB
    include '../../includes/db_connect_mongo.php';
    include '../layouts/header.php'; 
?>

<div class="header-listado">
    <h2>Gestionar Noticias</h2>
    <a href="crear_noticia.php" class="btn-crear">
        + Crear Nueva Noticia
    </a>
</div>

<?php if (isset($_GET['status'])): ?>
    <div style="margin-bottom: 15px; padding: 10px; border-radius: 5px; background-color: #f0f0f0; border-left: 5px solid #333;">
        <?php 
            switch($_GET['status']) {
                case 'creado': echo "Noticia publicada correctamente."; break;
                case 'editado': echo "Noticia actualizada correctamente."; break;
                case 'borrado': echo "Noticia eliminada correctamente."; break;
                case 'error_borrar': echo "Error al intentar borrar la noticia."; break;
                default: echo "Operación realizada.";
            }
        ?>
    </div>
<?php endif; ?>

<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Imagen</th>
                <th>Título</th>
                <th>Categoría</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
                // DEFINICIÓN DE CONSULTA
                // Ordenar por fecha_publicacion DESC (lo más nuevo primero) -> -1
                $opciones = ['sort' => ['fecha_publicacion' => -1]];
                
                $cursor = $db->noticias->find([], $opciones);
                $lista_noticias = $cursor->toArray();

                if (count($lista_noticias) > 0) {
                    foreach($lista_noticias as $noticia) {
                        
                        // 1. Procesar ID
                        $id_str = (string)$noticia['_id'];

                        // 2. Procesar Datos Embebidos (Categoría)
                        // Buscamos en ['categoria']['nombre'] (nuevo esquema) o fallback a N/A
                        $nombre_categoria = $noticia['categoria']['nombre'] ?? 'N/A';

                        // 3. Procesar Fecha (UTCDateTime -> PHP DateTime)
                        $fecha_formateada = "Fecha inválida";
                        if (isset($noticia['fecha_publicacion'])) {
                            if ($noticia['fecha_publicacion'] instanceof MongoDB\BSON\UTCDateTime) {
                                $dt = $noticia['fecha_publicacion']->toDateTime();
                                // Opcional: Ajustar zona horaria si el servidor está en otra zona
                                // $dt->setTimezone(new DateTimeZone('America/Mexico_City'));
                                $fecha_formateada = $dt->format('d/m/Y H:i');
                            } else {
                                // Fallback por si hay datos viejos en string
                                $fecha_formateada = $noticia['fecha_publicacion'];
                            }
                        }

                        // 4. Procesar Imagen
                        $placeholder_img = "https://placehold.co/100x100/004a99/FFFFFF?text=Img";
                        $imagen_src = $placeholder_img;
                        
                        if (!empty($noticia['ruta_imagen'])) {
                            $ruta_imagen_db = $noticia['ruta_imagen'];
                            
                            // Limpieza de ruta para file_exists
                            $ruta_limpia = ltrim($ruta_imagen_db, '/');
                            $ruta_fisica_absoluta = dirname(dirname(__DIR__)) . '/' . $ruta_limpia;
                            
                            // Definir ruta pública relativa si no está definida en header
                            $ruta_base_publica = isset($ruta_base_publica) ? $ruta_base_publica : '../../';

                            if (file_exists($ruta_fisica_absoluta)) {
                                $imagen_src = $ruta_base_publica . '/' . $ruta_limpia;
                            }
                        }
            ?>
                        <tr>
                            <td title="<?php echo $id_str; ?>">
                                <?php echo substr($id_str, -6); ?>...
                            </td>
                            <td>
                                <img src="<?php echo $imagen_src; ?>" alt="Img" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                            </td>
                            <td><?php echo htmlspecialchars($noticia['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($nombre_categoria); ?></td>
                            <td><?php echo $fecha_formateada; ?></td>
                            <td class="acciones">
                                <a href="editar_noticia.php?id=<?php echo $id_str; ?>" class="btn-editar">Editar</a>
                                <a href="borrar_noticia.php?id=<?php echo $id_str; ?>" 
                                   class="btn-borrar" 
                                   onclick="return confirm('¿Estás seguro de eliminar esta noticia?');">
                                   Borrar
                                </a>
                            </td>
                        </tr>
            <?php
                    } // Fin del foreach
                } else {
            ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 20px;">No hay noticias publicadas.</td>
                    </tr>
            <?php
                } 
            ?>
        </tbody>
    </table>
</div>

<?php
    include '../layouts/admin_footer.php';
?>