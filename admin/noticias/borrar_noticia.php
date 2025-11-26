<?php
session_start();

// Sube 2 niveles (a la raíz) y baja a 'includes' para conexión MongoDB
include '../../includes/db_connect_mongo.php';
// Sube 1 nivel (a 'admin') y busca 'auth_check.php'
include '../auth_check.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $id_noticia_str = $_GET['id']; 

    try {
        // 1. Convertimos el ID string a ObjectId de MongoDB
        $objectId = new MongoDB\BSON\ObjectId($id_noticia_str);
        
        // Seleccionamos la colección
        $coleccionNoticias = $db->noticias;

        // 2. Buscar el documento para obtener la ruta de la imagen
        $noticia = $coleccionNoticias->findOne(['_id' => $objectId]);

        if ($noticia) {
            
            // --- Borrado del Archivo Físico ---
            if (!empty($noticia['ruta_imagen'])) {
                
                // Construimos la ruta física absoluta
                // dirname(__DIR__, 2) sube dos niveles desde 'admin/noticias' -> raíz del proyecto
                $ruta_base = dirname(__DIR__, 2);
                $ruta_relativa = $noticia['ruta_imagen'];
                
                // Limpiamos slash inicial para evitar duplicados
                $ruta_relativa = ltrim($ruta_relativa, '/');
                
                $ruta_fisica = $ruta_base . '/' . $ruta_relativa;

                // Verificamos si existe antes de intentar borrar
                if (file_exists($ruta_fisica) && is_file($ruta_fisica)) {
                    unlink($ruta_fisica); 
                }
            }

            // 3. Borrar el documento de la base de datos
            $resultado = $coleccionNoticias->deleteOne(['_id' => $objectId]);

            if ($resultado->getDeletedCount() > 0) {
                // Éxito
                header("Location: gestionar_noticias.php?status=borrado");
            } else {
                // El registro no se borró de la BD (raro si ya lo encontró antes)
                header("Location: gestionar_noticias.php?status=error_borrar");
            }

        } else {
            // El ID no existe en la base de datos
            header("Location: gestionar_noticias.php?status=error_no_encontrado");
        }

    } catch (Exception $e) {
        // Error al convertir ID (formato inválido) o error de conexión
        // error_log($e->getMessage()); 
        header("Location: gestionar_noticias.php?status=error_excepcion");
    }
    
    exit;

} else {
    // Si no enviaron ID
    header("Location: gestionar_noticias.php");
    exit;
}
?>