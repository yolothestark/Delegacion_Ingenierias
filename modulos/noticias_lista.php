<?php
    // 1. Incluimos la conexión a MongoDB (subimos un nivel desde Modulos/)
    include '../includes/db_connect_mongo.php';
    include '../includes/header.php';
    
    // --- LÓGICA DE PAGINACIÓN (MONGODB) ---
    $noticias_por_pagina = 6;
    
    // Contar total de documentos
    $total_noticias = $db->noticias->countDocuments();
    
    $total_paginas = ceil($total_noticias / $noticias_por_pagina);
    
    // Validación de página actual
    $pagina_actual = 1;
    if (isset($_GET['pagina']) && is_numeric($_GET['pagina'])) {
        $pagina_actual = (int)$_GET['pagina'];
    }
    
    if ($pagina_actual > $total_paginas && $total_paginas > 0) {
        $pagina_actual = $total_paginas;
    }
    if ($pagina_actual < 1) {
        $pagina_actual = 1;
    }
    
    // Calcular SKIP (Equivalente a OFFSET en SQL)
    $skip = ($pagina_actual - 1) * $noticias_por_pagina;
    // --- FIN LÓGICA PAGINACIÓN ---

    // 7. Definimos el título de la página
    $titulo_pagina = "Noticias - Página $pagina_actual - Delegación Ingenierías";
    
    // 8. Incluimos el encabezado
    include '../includes/header.php';
?>

<main class="container-detalle">
    
    <h2>Archivo de Noticias</h2>
    <p>Consulta todas las noticias publicadas por la delegación.</p>

    <!-- 
      SECCIÓN DE NOTICIAS DINÁMICAS (PAGINADAS)
    -->
    <section class="seccion-noticias">
        
        <div class="noticias-grid">
            
            <?php
                // 9. Consulta MongoDB con Paginación
                // Ordenar: Fecha descendente (-1)
                // Limit: $noticias_por_pagina
                // Skip: $skip
                $opciones = [
                    'sort'  => ['fecha_publicacion' => -1],
                    'limit' => $noticias_por_pagina,
                    'skip'  => $skip
                ];

                $cursor = $db->noticias->find([], $opciones);
                $noticias = $cursor->toArray();

                if (count($noticias) > 0) {
                    
                    foreach ($noticias as $row) {
                        
                        // ID a String
                        $id_str = (string)$row['_id'];
                        
                        // Imagen
                        $placeholder_img = "https://placehold.co/600x400/004a99/FFFFFF?text=Delegacion";
                        $imagen_src = $placeholder_img; 
                        
                        // Validar ruta física
                        if (!empty($row['ruta_imagen'])) {
                            $ruta_imagen_db = $row['ruta_imagen'];
                            $ruta_limpia = ltrim($ruta_imagen_db, '/');
                            $ruta_fisica_absoluta = dirname(__DIR__) . '/' . $ruta_limpia;
                            
                            if (file_exists($ruta_fisica_absoluta)) {
                                // $ruta_base viene del header
                                $imagen_src = $ruta_base . '/' . $ruta_limpia;
                            }
                        }
                        
                        // Contenido Corto
                        $contenido_texto = isset($row['contenido']) ? $row['contenido'] : '';
                        $contenido_corto = htmlspecialchars(substr($contenido_texto, 0, 100)) . '...';
                        
                        // Fecha
                        $fecha_formateada = "Fecha no disponible";
                        if (isset($row['fecha_publicacion']) && $row['fecha_publicacion'] instanceof MongoDB\BSON\UTCDateTime) {
                            $dt = $row['fecha_publicacion']->toDateTime();
                            // Formato simple d de F, Y (Nota: F saldrá en inglés a menos que configures setlocale)
                            $fecha_formateada = $dt->format('d \d\e F, Y');
                        }
                        
                        // Categoría (Dato embebido)
                        $nombre_categoria = $row['categoria']['nombre'] ?? 'General';
            ?>
            
            <article class="noticia-card">
                <img src="<?php echo htmlspecialchars($imagen_src); ?>" alt="Imagen de la noticia">
                <div class="card-content">
                    <span class="categoria"><?php echo htmlspecialchars($nombre_categoria); ?></span>
                    <h3>
                        <!-- Enlace al detalle con ID de Mongo -->
                        <a href="<?php echo $ruta_base; ?>/modulos/noticia_detalle.php?id=<?php echo $id_str; ?>">
                            <?php echo htmlspecialchars($row['titulo']); ?>
                        </a>
                    </h3>
                    <p><?php echo $contenido_corto; ?></p>
                    <span class="fecha"><?php echo $fecha_formateada; ?></span>
                </div>
            </article>

            <?php
                    } // Fin del foreach
                    
                } else {
                    echo "<p>No hay noticias para mostrar en este momento.</p>";
                }
            ?>

        </div> <!-- Fin de .noticias-grid -->
    </section>

    <!-- 
      SECCIÓN DE PAGINACIÓN (LOS NÚMEROS)
    -->
    <nav class="paginacion">
        <?php if ($total_paginas > 1): ?>
            
            <!-- Botón ANTERIOR -->
            <?php if ($pagina_actual > 1): ?>
                <a href="?pagina=<?php echo $pagina_actual - 1; ?>">&laquo; Anterior</a>
            <?php endif; ?>

            <!-- Números de página -->
            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <a href="?pagina=<?php echo $i; ?>" 
                   class="<?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                   <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <!-- Botón SIGUIENTE -->
            <?php if ($pagina_actual < $total_paginas): ?>
                <a href="?pagina=<?php echo $pagina_actual + 1; ?>">Siguiente &raquo;</a>
            <?php endif; ?>

        <?php endif; ?>
    </nav>

</main>

<?php
    // 10. Incluimos el pie de página
    include '../includes/footer.php';
?>