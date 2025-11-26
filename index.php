<?php
    // 1. Incluimos la conexión a MongoDB
    include 'includes/db_connect_mongo.php';
    
    $titulo_pagina = "Inicio - Delegación Ingenierías";

    // 2. Incluimos el encabezado
    include 'includes/header.php';
?>

    <main>
        <!-- BANNER PRINCIPAL CON VIDEO -->
        <section class="banner-principal">
            <video autoplay muted loop playsinline class="video-background">
                <source src="imagenes/banner_video.mp4" type="video/mp4">
            </video>
            <div class="overlay"></div>
            <div class="banner-content">
                <h1>COMUNICADO IMPORTANTE</h1>
                <p>Te invitamos a conocer nuestra misión, visión y los valores que nos definen.</p>
                <a href="modulos/mision.php" class="btn-principal">Conoce nuestra Misión</a>
            </div>
        </section>

        <!-- SECCIÓN DE NOTICIAS -->
        <section class="seccion-noticias">
            <h2>Últimas Noticias</h2>
            <div class="noticias-grid">
                <?php
                    $opciones = [
                        'sort' => ['fecha_publicacion' => -1], 
                        'limit' => 3
                    ];

                    try {
                        if (isset($db)) {
                            $cursor = $db->noticias->find([], $opciones);
                            $noticias = $cursor->toArray();

                            if (count($noticias) > 0) {
                                foreach ($noticias as $row) {
                                    $id_str = (string)$row['_id'];
                                    $placeholder_img = "https://placehold.co/600x400/004a99/FFFFFF?text=Delegacion";
                                    $imagen_src = $placeholder_img; 
                                    
                                    if (!empty($row['ruta_imagen'])) {
                                        $ruta_limpia = ltrim($row['ruta_imagen'], '/');
                                        if (file_exists(__DIR__ . '/' . $ruta_limpia)) {
                                            $imagen_src = $ruta_base . '/' . $ruta_limpia;
                                        }
                                    }
                                    
                                    $contenido_texto = isset($row['contenido']) ? $row['contenido'] : '';
                                    $contenido_corto = htmlspecialchars(substr($contenido_texto, 0, 100)) . '...';
                                    
                                    $fecha_formateada = "Fecha no disponible";
                                    if (isset($row['fecha_publicacion']) && $row['fecha_publicacion'] instanceof MongoDB\BSON\UTCDateTime) {
                                        $fecha_formateada = $row['fecha_publicacion']->toDateTime()->format('d/m/Y');
                                    }

                                    $nombre_categoria = $row['categoria']['nombre'] ?? 'General';
                ?>
                <article class="noticia-card">
                    <img src="<?php echo htmlspecialchars($imagen_src); ?>" alt="Noticia">
                    <div class="card-content">
                        <span class="categoria"><?php echo htmlspecialchars($nombre_categoria); ?></span>
                        <h3>
                            <a href="modulos/noticia_detalle.php?id=<?php echo $id_str; ?>">
                                <?php echo htmlspecialchars($row['titulo']); ?>
                            </a>
                        </h3>
                        <p><?php echo $contenido_corto; ?></p>
                        <span class="fecha"><?php echo $fecha_formateada; ?></span>
                    </div>
                </article>
                <?php
                                }
                            } else {
                                echo "<p style='text-align:center; width:100%;'>No hay noticias recientes.</p>";
                            }
                        }
                    } catch (Exception $e) {
                        // --- AQUI ESTA EL CAMBIO PARA VER EL ERROR ---
                        echo "<div style='color: red; background: #ffe6e6; padding: 10px; border: 1px solid red; margin: 10px;'>";
                        echo "<strong>Error de MongoDB:</strong> " . $e->getMessage();
                        echo "</div>";
                    }
                ?>
            </div>
        </section>
    </main>

<?php
    include 'includes/footer.php';
?>