<?php
session_start();

// Sube 2 niveles y baja a 'includes' para conexión MongoDB
include '../../includes/db_connect_mongo.php';
// Sube 1 nivel
include '../auth_check.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    try {
        // 1. Validar ID
        if (empty($_POST['id_noticia'])) {
            throw new Exception("ID no proporcionado");
        }
        $objectId = new MongoDB\BSON\ObjectId($_POST['id_noticia']);

        // 2. Recoger datos
        $titulo           = trim($_POST['titulo']);
        $contenido        = trim($_POST['contenido']);
        $id_categoria     = (int)$_POST['id_categoria_fk']; // Convertir a entero es vital
        
        // Si quieres que el autor cambie al editor actual:
        $autor            = $_SESSION['username']; 
        
        // Recuperamos la ruta actual
        $ruta_imagen_db   = $_POST['ruta_imagen_actual'] ?? null;

        // 3. Lógica de Imagen (File System)
        if (isset($_FILES['imagen_noticia']) && $_FILES['imagen_noticia']['error'] == 0) {
            
            // A. Borrar imagen vieja si existe
            if (!empty($ruta_imagen_db)) {
                // Sube 2 niveles hasta la raíz
                $ruta_fisica_vieja = dirname(dirname(__DIR__)) . "/" . ltrim($ruta_imagen_db, '/');
                if (file_exists($ruta_fisica_vieja) && is_file($ruta_fisica_vieja)) {
                    unlink($ruta_fisica_vieja); 
                }
            }

            // B. Subir nueva imagen
            $target_dir = dirname(dirname(__DIR__)) . "/uploads/noticias/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            $extension = pathinfo($_FILES["imagen_noticia"]["name"], PATHINFO_EXTENSION);
            $nombre_archivo_unico = time() . '_' . uniqid() . '.' . $extension;
            $target_file_path = $target_dir . $nombre_archivo_unico;
            
            if (move_uploaded_file($_FILES["imagen_noticia"]["tmp_name"], $target_file_path)) {
                $ruta_imagen_db = "uploads/noticias/" . $nombre_archivo_unico;
            }
        }

        // --- LÓGICA MONGODB ---

        // 4. Buscar información de la Categoría para embeberla
        $coleccionCategorias = $db->categorias; 
        $catDoc = $coleccionCategorias->findOne(['id_categoria' => $id_categoria]);
        
        $nombre_categoria = $catDoc ? $catDoc['nombre_categoria'] : 'Sin Categoría';

        // 5. Preparar Update ($set)
        $datosActualizados = [
            'titulo'        => $titulo,
            'contenido'     => $contenido,
            'ruta_imagen'   => $ruta_imagen_db,
            'autor'         => $autor,
            'fecha_edicion' => new MongoDB\BSON\UTCDateTime(), // Timestamp de edición
            
            // Actualizamos el objeto embebido
            'categoria'     => [
                'id'     => $id_categoria,
                'nombre' => $nombre_categoria
            ]
            // Nota: Si mantienes el campo plano por compatibilidad:
            // 'id_categoria_fk' => $id_categoria 
        ];

        // 6. Ejecutar Update
        $resultado = $db->noticias->updateOne(
            ['_id' => $objectId],
            ['$set' => $datosActualizados]
        );

        // Validar si encontró el documento (MatchedCount)
        if ($resultado->getMatchedCount() > 0) {
            header("Location: gestionar_noticias.php?status=editado");
        } else {
            header("Location: gestionar_noticias.php?status=error_editar");
        }

    } catch (Exception $e) {
        // Log error si es necesario
        // error_log($e->getMessage());
        header("Location: gestionar_noticias.php?status=error_excepcion");
    }
    
    exit;

} else {
    header("Location: gestionar_noticias.php");
    exit;
}
?>