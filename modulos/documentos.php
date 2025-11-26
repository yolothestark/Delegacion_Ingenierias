<?php
    // 1. Conexión a MongoDB (subimos un nivel desde Modulos/)
    include '../includes/db_connect_mongo.php';
    
    $titulo_pagina = "Documentos - Delegación Ingenierías";
    
    // 2. Header (subimos un nivel hasta layouts/)
    include '../includes/header.php';

    $busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
?>

<main class="docs-container-fluid">

    <div class="search-section">
        <form action="" method="GET" class="search-form">
            <input type="text" name="q" placeholder="Buscar documento, tipo, contrato..." value="<?php echo htmlspecialchars($busqueda); ?>" class="search-input">
            <button type="submit" class="search-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                Buscar
            </button>
            <?php if($busqueda): ?>
                <a href="documentos.php" class="search-clear">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <?php
    $grupos_docs = [];

    // 1. Obtener Tipos de Documentos (Ordenados por ID ASC)
    $cursor_tipos = $db->tipos_documentos->find([], ['sort' => ['id_tipo' => 1]]);
    $tipos = $cursor_tipos->toArray();

    if (count($tipos) > 0) {
        foreach($tipos as $tipo) {
            
            $id_tipo = $tipo['id_tipo'];
            $nombre_tipo = $tipo['nombre_tipo'];

            // Lógica de Búsqueda:
            // Si el nombre del TIPO coincide con la búsqueda, mostramos todos sus docs.
            $mostrar_todo_tipo = false;
            if ($busqueda != '' && stripos($nombre_tipo, $busqueda) !== false) {
                $mostrar_todo_tipo = true;
            }

            // Construir filtro para documentos
            // Buscamos documentos que pertenezcan a este tipo (usando campo embebido o fk)
            $filtro = ['tipo_documento.id' => $id_tipo]; // O usa 'id_tipo_fk' => $id_tipo si preferiste no embeber
            
            // Si hay búsqueda y no coincidió el tipo, filtramos por nombre de doc o versión
            if ($busqueda != '' && !$mostrar_todo_tipo) {
                // Regex 'i' = case insensitive (LIKE %...%)
                $regex = new MongoDB\BSON\Regex($busqueda, 'i');
                
                $filtro = [
                    'tipo_documento.id' => $id_tipo,
                    '$or' => [
                        ['nombre_documento' => $regex],
                        ['version' => $regex]
                    ]
                ];
            }

            // Consultar documentos de este tipo
            $opciones = ['sort' => ['fecha_subida' => -1]];
            $cursor_docs = $db->documentos->find($filtro, $opciones);
            $docs = $cursor_docs->toArray();

            // Si hay documentos (o si el usuario buscó y encontró algo), los agregamos al grupo
            if (count($docs) > 0) {
                $grupos_docs[$nombre_tipo] = $docs;
            }
        }
    }

    // Separar lógica visual: Primero (Izq) y Resto (Der)
    $keys = array_keys($grupos_docs);
    $cat_izq_titulo = $keys[0] ?? null; 
    $cat_izq_docs = $grupos_docs[$cat_izq_titulo] ?? [];
    $cat_der_lista = array_slice($grupos_docs, 1); 
    ?>

    <div class="split-layout">
        
        <div class="panel-izquierdo">
            <?php if ($cat_izq_titulo): ?>
                <?php if($busqueda): ?>
                    <h4 style="color: rgba(255,255,255,0.7); margin-top:0; text-transform: uppercase; font-size: 0.9em; letter-spacing: 1px;">Resultados en: <?php echo htmlspecialchars($cat_izq_titulo); ?></h4>
                <?php endif; ?>

                <div class="lista-izq">
                    <?php foreach($cat_izq_docs as $doc): 
                        $id_str = (string)$doc['_id'];
                        // ENLACE AL VISOR (ver_documento.php)
                        $enlace_visor = "ver_documento.php?id=" . $id_str;
                        // ENLACE DIRECTO (Para descarga)
                        // Limpiamos slash inicial para ruta relativa
                        $ruta_limpia = ltrim($doc['ruta_archivo'], '/');
                        $enlace_descarga = "../" . $ruta_limpia;
                    ?>
                        <div class="doc-card-izq">
                            <div class="icono-pdf-box">
                                <span class="pdf-label">PDF</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="#6ca842" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><path d="M9 15l3-3 3 3"></path><path d="M12 12v9"></path></svg>
                            </div>
                            
                            <div class="info-doc-izq">
                                <h3>
                                    <a href="<?php echo $enlace_visor; ?>" class="link-titulo-izq-visor">
                                        <?php echo htmlspecialchars($doc['nombre_documento']); ?>
                                    </a>
                                </h3>
                                <?php if(!empty($doc['version'])): ?>
                                    <span class="version-text"><?php echo htmlspecialchars($doc['version']); ?></span>
                                <?php endif; ?>
                                
                                <a href="<?php echo $enlace_descarga; ?>" target="_blank" class="link-descarga-izq">Descargar</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="color:white; padding:20px;">No hay documentos disponibles.</div>
            <?php endif; ?>
        </div>

        <div class="panel-derecho">
            <?php if (!empty($cat_der_lista)): ?>
                <?php foreach($cat_der_lista as $titulo => $docs): ?>
                    <div class="seccion-der">
                        <h3 class="titulo-cat-der"><?php echo $titulo; ?></h3>
                        <?php foreach($docs as $doc): 
                             $id_str = (string)$doc['_id'];
                             $enlace_visor = "ver_documento.php?id=" . $id_str;
                             $ruta_limpia = ltrim($doc['ruta_archivo'], '/');
                             $enlace_descarga = "../" . $ruta_limpia;
                        ?>
                            <div class="doc-item-der">
                                <div class="icono-pdf-mini">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><path d="M12 18v-6"></path><path d="M9 15l3 3 3-3"></path></svg>
                                </div>
                                <div class="info-doc-der">
                                    <a href="<?php echo $enlace_visor; ?>" class="link-titulo-der">
                                        <?php echo htmlspecialchars($doc['nombre_documento']); ?>
                                    </a>
                                    <a href="<?php echo $enlace_descarga; ?>" target="_blank" class="link-descarga-der">Descargar</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="separador"></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
    // Incluir footer
    include '../includes/footer.php';
?>
<style>
    .link-titulo-izq-visor {
        color: white;
        text-decoration: none;
    }
    .link-titulo-izq-visor:hover {
        text-decoration: underline;
    }
</style>