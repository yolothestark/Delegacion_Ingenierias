<?php
    // 1. Incluimos la conexión a MongoDB
    include 'includes/db_connect_mongo.php';
    
    // 2. Definimos el título de ESTA página
    $titulo_pagina = "Inicio - Delegación Ingenierías";

    // 3. Incluimos el encabezado
    // Nota: Como index.php está en la raíz, la ruta a layouts es 'layouts/header.php'
    // Pero según tu estructura anterior, el header busca CSS en '/Delegacion_ingenierias/css/' 
    // así que debería funcionar bien.
    include 'includes/header.php';
?>

    <main>
        <!-- BANNER PRINCIPAL CON VIDEO -->
        <section class="banner-principal">
            
            <!-- 
               Video de fondo
               autoplay: se reproduce solo
               muted: sin sonido (obligatorio para autoplay)
               loop: se repite infinito
            -->
            <video autoplay muted loop playsinline class="video-background">
                <!-- Asegúrate de que el archivo exista en 'imagenes/banner_video.mp4' -->
                <source src="imagenes/banner_video.mp4" type="video/mp4">
                Tu navegador no soporta videos HTML5.
            </video>
            
            <!-- Capa oscura transparente -->
            <div class="overlay"></div>

            <!-- Contenido del Banner -->
            <div class="banner-content">
                <h1>COMUNICADO IMPORTANTE</h1>
                <p>Te invitamos a conocer nuestra misión, visión y los valores que nos definen.</p>
                <a href="Modulos/mision.php" class="btn-principal">Conoce nuestra Misión</a>
            </div>
        </section>

        <!-- SECCIÓN DE NOTICIAS DINÁMICAS -->
        <section class="seccion-noticias">
            <h2>Últimas Noticias</h2>
            
            <div class="noticias-grid">
                
                <?php
                    // CONSULTA MONGODB: Obtener las 3 noticias más recientes
                    // Equivalente a: ORDER BY fecha_publicacion DESC LIMIT 3
                    $opciones = [
                        'sort' => ['fecha_publicacion' => -1], // -1 = Descendente
                        'limit' => 3
                    ];

                    $cursor = $db->noticias->find([], $opciones);
                    $noticias = $cursor->toArray();

                    if (count($noticias) > 0) {
                        
                        foreach ($noticias as $row) {
                            
                            // 1. Procesar ID
                            $id_str = (string)$row['_id'];
                            
                            // 2. Procesar Imagen
                            $placeholder_img = "https://placehold.co/600x400/004a99/FFFFFF?text=Delegacion";
                            $imagen_src = $placeholder_img; 
                            
                            if (!empty($row['ruta_imagen'])) {
                                $ruta_imagen_db = $row['ruta_imagen']; 
                                
                                // Limpieza de ruta
                                $ruta_limpia = ltrim($ruta_imagen_db, '/');
                                
                                // Como estamos en index.php (raíz), la ruta física es directa desde __DIR__
                                $ruta_fisica_absoluta = __DIR__ . '/' . $ruta_limpia;
                                
                                if (file_exists($ruta_fisica_absoluta)) {
                                    // $ruta_base se define en header.php
                                    $imagen_src = $ruta_base . '/' . $ruta_limpia;
                                }
                            }
                            
                            // 3. Procesar Texto
                            $contenido_texto = isset($row['contenido']) ? $row['contenido'] : '';
                            $contenido_corto = htmlspecialchars(substr($contenido_texto, 0, 100)) . '...';
                            
                            // 4. Procesar Fecha
                            $fecha_formateada = "Fecha no disponible";
                            if (isset($row['fecha_publicacion']) && $row['fecha_publicacion'] instanceof MongoDB\BSON\UTCDateTime) {
                                $dt = $row['fecha_publicacion']->toDateTime();
                                $fecha_formateada = $dt->format('d \d\e F, Y');
                            } elseif (isset($row['fecha_publicacion'])) {
                                $fecha_formateada = $row['fecha_publicacion'];
                            }

                            // 5. Categoría (Embebida)
                            $nombre_categoria = $row['categoria']['nombre'] ?? 'General';
                ?>
                
                <article class="noticia-card">
                    <img src="<?php echo htmlspecialchars($imagen_src); ?>" alt="Imagen de la noticia">
                    <div class="card-content">
                        <span class="categoria"><?php echo htmlspecialchars($nombre_categoria); ?></span>
                        <h3>
                            <a href="Modulos/noticia_detalle.php?id=<?php echo $id_str; ?>">
                                <?php echo htmlspecialchars($row['titulo']); ?>
                            </a>
                        </h3>
                        <p><?php echo $contenido_corto; ?></p>
                        <span class="fecha"><?php echo $fecha_formateada; ?></span>
                    </div>
                </article>

                <?php
                        } // Fin foreach
                    } else {
                        echo "<p style='text-align:center; width:100%;'>No hay noticias recientes para mostrar.</p>";
                    }
                ?>

            </div> <!-- Fin de .noticias-grid -->
        </section> <!-- Fin de .seccion-noticias -->

    </main>

    <?php
        // Incluimos el footer (layouts)
        include 'includes/footer.php';
    ?>