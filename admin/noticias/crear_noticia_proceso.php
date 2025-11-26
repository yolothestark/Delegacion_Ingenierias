<?php
session_start();

// Sube 2 niveles (a la raíz) y baja a 'includes' para conexión MongoDB
include '../../includes/db_connect_mongo.php';
// Sube 1 nivel (a 'admin') y busca 'auth_check.php'
include '../auth_check.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recogida y limpieza de datos
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $id_categoria = (int)$_POST['id_categoria_fk']; // Convertir a entero para búsqueda
    
    // Obtenemos el autor de la sesión (asumimos que guardas 'username' o 'nombre')
    $autor = $_SESSION['username'] ?? 'Anónimo'; 
    
    $ruta_imagen_db = null; 

    // 2. Lógica de Subida de Imagen (Filesystem)
    if (isset($_FILES['imagen_noticia']) && $_FILES['imagen_noticia']['error'] == 0) {
        
        // Sube 2 niveles a la raíz, luego entra a uploads/noticias/
        $target_dir = dirname(dirname(__DIR__)) . "/uploads/noticias/"; 
        
        if (!is_dir($target_dir)) {
             mkdir($target_dir, 0755, true);
        }
        
        // Generamos nombre único para evitar colisiones
        $extension = pathinfo($_FILES["imagen_noticia"]["name"], PATHINFO_EXTENSION);
        $nombre_archivo_unico = time() . '_' . uniqid() . '.' . $extension;
        
        $target_file_path = $target_dir . $nombre_archivo_unico;
        
        if (move_uploaded_file($_FILES["imagen_noticia"]["tmp_name"], $target_file_path)) {
            // Guardamos la ruta relativa
            $ruta_imagen_db = "uploads/noticias/" . $nombre_archivo_unico;
        }
    }

    try {
        // --- LÓGICA MONGODB ---

        // A. Buscar el nombre de la categoría para embeberlo
        // Asumimos que la colección se llama 'categorias' o 'categorias_noticias'
        // Si tu colección tiene otro nombre, cámbialo aquí:
        $coleccionCategorias = $db->categorias; 
        
        // Buscamos por el ID numérico (asumiendo que así migraste las categorías)
        $catDoc = $coleccionCategorias->findOne(['id_categoria' => $id_categoria]);
        $nombre_categoria = $catDoc ? $catDoc['nombre_categoria'] : 'General';

        // B. Construir el documento
        $documento = [
            'titulo'            => $titulo,
            'contenido'         => $contenido,
            'ruta_imagen'       => $ruta_imagen_db,
            'autor'             => $autor,
            'id_usuario_fk'     => $_SESSION['user_id'] ?? null, // Es bueno guardar también el ID del autor
            
            // Fecha automática (MongoDB UTC)
            'fecha_publicacion' => new MongoDB\BSON\UTCDateTime(),
            
            // Datos embebidos de la categoría
            'categoria'         => [
                'id'     => $id_categoria,
                'nombre' => $nombre_categoria
            ]
        ];

        // C. Insertar
        $resultado = $db->noticias->insertOne($documento);

        if ($resultado->getInsertedCount() > 0) {
            header("Location: gestionar_noticias.php?status=creado");
        } else {
            header("Location: gestionar_noticias.php?status=error_crear");
        }

    } catch (Exception $e) {
        // Manejo de errores de conexión o escritura
        // error_log($e->getMessage());
        header("Location: gestionar_noticias.php?status=error_excepcion");
    }
    
    exit;

} else {
    header("Location: crear_noticia.php");
    exit;
}
?>