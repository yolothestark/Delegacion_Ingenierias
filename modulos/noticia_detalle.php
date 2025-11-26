<?php
    // 1. Incluimos la conexión a MongoDB (ajustamos ruta para salir de Modulos)
    include '../includes/db_connect_mongo.php';

    // Inicializamos variables
    $noticia = null;
    $error = null;
    $titulo_pagina = "Detalle de Noticia";

    // 2. Verificamos si nos llegó un ID por la URL
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        
        $id_str = $_GET['id'];
        
        try {
            // Convertir a ObjectId
            $objectId = new MongoDB\BSON\ObjectId($id_str);
            
            // Buscar la noticia
            $noticia = $db->noticias->findOne(['_id' => $objectId]);

            if ($noticia) {
                $titulo_pagina = $noticia['titulo']; // Definimos el título dinámico
            } else {
                $error = "Noticia no encontrada.";
                $titulo_pagina = "Error - Noticia no encontrada";
            }

        } catch (Exception $e) {
            $error = "ID de noticia inválido.";
            $titulo_pagina = "Error";
        }
        
    } else {
        $error = "Petición no válida.";
        $titulo_pagina = "Error";
    }

    // 3. Incluimos el header (que define $ruta_base y <head>)
    include '../layouts/header.php';
?>

    <main class="container-detalle">

        <?php if ($error): ?>
            
            <div style="text-align: center; padding: 50px;">
                <h1 class="error-titulo" style="color: #d9534f;"><?php echo $error; ?></h1>
                <a href="<?php echo $ruta_base; ?>/index.php" class="btn-principal" style="background: #003b46; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Volver al inicio</a>
            </div>

        <?php else: ?>
            
            <?php
                // Procesar Fecha
                $fecha_texto = "Fecha desconocida";
                if (isset($noticia['fecha_publicacion'])) {
                    if ($noticia['fecha_publicacion'] instanceof MongoDB\BSON\UTCDateTime) {
                        $dt = $noticia['fecha_publicacion']->toDateTime();
                        // Formatear en español si tienes setlocale configurado, o manual:
                        // $fecha_texto = strftime('%d de %B, %Y', $dt->getTimestamp()); 
                        // Alternativa simple:
                        $fecha_texto = $dt->format('d/m/Y');
                    } else {
                        $fecha_texto = $noticia['fecha_publicacion'];
                    }
                }
                
                // Procesar Categoría (Dato embebido)
                $nombre_categoria = $noticia['categoria']['nombre'] ?? 'General';
                
                // Procesar Imagen
                $placeholder_img = "https://placehold.co/900x400/004a99/FFFFFF?text=Delegacion";
                $imagen_src = $placeholder_img;
                
                if (!empty($noticia['ruta_imagen'])) {
                    $ruta_imagen_db = $noticia['ruta_imagen'];
                    $ruta_limpia = ltrim($ruta_imagen_db, '/');
                    $ruta_fisica_absoluta = dirname(__DIR__) . '/' . $ruta_limpia;
                    
                    if (file_exists($ruta_fisica_absoluta)) {
                        $imagen_src = $ruta_base . '/' . $ruta_limpia;
                    }
                }
            ?>

            <article class="noticia-completa">
                <div class="info-superior">
                    <span class="categoria-detalle"><?php echo htmlspecialchars($nombre_categoria); ?></span>
                    <span class="fecha-detalle"><?php echo htmlspecialchars($fecha_texto); ?></span>
                </div>
                
                <h1><?php echo htmlspecialchars($noticia['titulo']); ?></h1>
                
                <img src="<?php echo htmlspecialchars($imagen_src); ?>" alt="Imagen de <?php echo htmlspecialchars($noticia['titulo']); ?>" class="imagen-detalle">
                
                <div class="contenido-completo">
                    <?php echo nl2br(htmlspecialchars($noticia['contenido'])); ?>
                </div>
                
                <div class="info-autor">
                    <strong>Por: <?php echo htmlspecialchars($noticia['autor']); ?></strong>
                </div>
            </article>

        <?php endif; ?>

    </main>

    <?php
        // No es necesario cerrar conexión manual en Mongo/PHP
        include '../layouts/footer.php';
    ?>