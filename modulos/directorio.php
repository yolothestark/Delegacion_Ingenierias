<?php
    // 1. Incluimos la conexión a MongoDB
    // Ajustamos la ruta para subir un nivel desde Modulos/
    include '../includes/db_connect_mongo.php';
    
    // 2. Definimos el título
    $titulo_pagina = "Directorio - Delegación Ingenierías";
    
    // 3. Incluimos el encabezado
    // Nota: Según la estructura que migramos, el header está en '../layouts/'
    include '../layouts/header.php';
?>

<main class="container-detalle">
    
    <h2 style="color: #003b46; font-weight: 800;">Directorio y Organigrama</h2>
    <p style="color: #666;">Conoce a los miembros que integran la Delegación Académica de Ingenierías.</p>
    
    <?php
        // 1. Obtenemos todos los DEPARTAMENTOS para agrupar
        // Equivalente a: SELECT * FROM departamentos ORDER BY nombre_depto ASC
        $cursor_deptos = $db->departamentos->find([], ['sort' => ['nombre_depto' => 1]]);
        
        // Convertimos a array para poder contar
        $departamentos = $cursor_deptos->toArray();

        if (count($departamentos) > 0) {
            
            // 2. Iteramos sobre cada DEPARTAMENTO
            foreach($departamentos as $depto) {
                
                $id_depto = $depto['id_depto']; // ID numérico
                $nombre_depto = htmlspecialchars($depto['nombre_depto']);
                
                // 3. Buscamos los miembros de ESTE depto
                // Filtramos donde 'departamento.id' coincida con el departamento actual
                // Ordenamos por 'orden' (asc) y luego por 'nombre_completo' (asc)
                $filtro_miembros = ['departamento.id' => $id_depto];
                $opciones_miembros = [
                    'sort' => [
                        'orden' => 1,
                        'nombre_completo' => 1
                    ]
                ];
                
                $cursor_miembros = $db->integrantes->find($filtro_miembros, $opciones_miembros);
                $miembros = $cursor_miembros->toArray();

                // Solo mostramos el título del departamento si tiene miembros
                if (count($miembros) > 0) {
                    
                    echo "<h3 class='depto-titulo'>" . $nombre_depto . "</h3>"; 
                    
                    echo "<div class='directorio-grid'>"; // Inicio Grid

                    foreach($miembros as $miembro) {
                        
                        // Procesar Imagen
                        // $ruta_base viene del header.php
                        $placeholder_foto = $ruta_base . '/imagenes/placeholder_perfil.png'; 
                        $foto_src = $placeholder_foto;

                        // Verificamos si hay foto en BD
                        if (!empty($miembro['foto_url'])) {
                            $ruta_foto_db = $miembro['foto_url'];
                            
                            // Limpiamos slash inicial
                            $ruta_limpia = ltrim($ruta_foto_db, '/');
                            
                            // Ruta física absoluta para validar existencia
                            $ruta_fisica_absoluta = dirname(__DIR__) . '/' . $ruta_limpia;
                            
                            if (file_exists($ruta_fisica_absoluta)) {
                                $foto_src = $ruta_base . '/' . $ruta_limpia;
                            }
                        }
                        
                        // Obtener nombre del cargo (dato embebido)
                        $nombre_cargo = $miembro['cargo']['nombre'] ?? 'Sin Cargo';
                        $bio = $miembro['bio'] ?? '';
    ?>
                        <div class="miembro-card">
                            <img src="<?php echo $foto_src; ?>" alt="Foto de <?php echo htmlspecialchars($miembro['nombre_completo']); ?>">
                            
                            <div class="miembro-info">
                                <span class="miembro-nombre"><?php echo htmlspecialchars($miembro['nombre_completo']); ?></span>
                                <span class="miembro-cargo"><?php echo htmlspecialchars($nombre_cargo); ?></span>
                                
                                <?php if(!empty($bio)): ?>
                                    <span class="miembro-bio"><?php echo htmlspecialchars($bio); ?></span>
                                <?php endif; ?>
                                
                                <div class="miembro-contacto">
                                    <?php if(!empty($miembro['email'])): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($miembro['email']); ?>">Email</a>
                                    <?php endif; ?>
                                    
                                    <?php if(!empty($miembro['telefono'])): ?>
                                        <span>Tel: <?php echo htmlspecialchars($miembro['telefono']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
    <?php
                    } // Fin foreach miembros
                    
                    echo "</div>"; // Fin .directorio-grid
                } 
            } // Fin foreach departamentos
            
        } else {
            echo "<p>No hay departamentos definidos.</p>";
        }
    ?>

</main>

<style>
.depto-titulo {
    font-size: 1.8em;
    /* Azul Aqua */
    color: #009eb3;
    /* Borde inferior Verde */
    border-bottom: 2px solid #6ca842;
    padding-bottom: 10px;
    margin-top: 40px;
    margin-bottom: 20px;
    font-weight: 700;
}
.directorio-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}
.miembro-card {
    background-color: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    text-align: center;
    transition: transform 0.2s, box-shadow 0.2s;
}
/* Efecto hover en la tarjeta */
.miembro-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-color: #009eb3; /* Borde Aqua al pasar el mouse */
}

.miembro-card img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #f0f0f0;
    margin-bottom: 15px;
    transition: border-color 0.3s;
}
.miembro-card:hover img {
    /* El borde de la foto se pone verde al pasar el mouse */
    border-color: #6ca842; 
}

.miembro-info .miembro-nombre {
    font-size: 1.3em;
    font-weight: bold;
    /* Petrol Oscuro */
    color: #003b46;
    display: block;
}
.miembro-info .miembro-cargo {
    font-size: 1em;
    /* Azul Aqua */
    color: #009eb3;
    font-weight: bold;
    display: block;
    margin-bottom: 10px;
}
.miembro-info .miembro-bio {
    font-size: 0.9em;
    color: #666;
    display: block;
    margin-bottom: 15px;
}
.miembro-contacto {
    font-size: 0.9em;
}
.miembro-contacto a {
    /* Azul Aqua */
    color: #009eb3;
    text-decoration: none;
    margin-right: 10px;
    font-weight: 600;
}
.miembro-contacto a:hover {
    /* Verde al pasar el mouse */
    color: #6ca842;
    text-decoration: underline;
}
.miembro-contacto span {
    color: #555;
}
</style>

<?php
    // 4. Incluimos el pie de página
    include '../layouts/footer.php';
?>