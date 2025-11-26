<?php
    // Sube 2 niveles para buscar la conexión a MongoDB
    include '../../includes/db_connect_mongo.php';
    include '../layouts/header.php'; 
?>

<div class="header-listado">
    <h2>Gestionar Directorio</h2>
    <a href="crear_miembro.php" class="btn-crear">
        + Agregar Miembro
    </a>
</div>

<?php if (isset($_GET['status'])): ?>
    <div style="margin-bottom: 15px; padding: 10px; border-radius: 5px; background-color: #f0f0f0; border-left: 5px solid #333;">
        <?php 
            switch($_GET['status']) {
                case 'creado': echo "Miembro creado correctamente."; break;
                case 'editado': echo "Miembro actualizado correctamente."; break;
                case 'borrado': echo "Miembro eliminado correctamente."; break;
                case 'error_db': echo "Error en la base de datos."; break;
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
                <th>Foto</th>
                <th>Nombre Completo</th>
                <th>Cargo</th>
                <th>Departamento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
                // DEFINICIÓN DE LA CONSULTA MONGODB
                // Equivalente a: ORDER BY d.nombre_depto, i.orden, i.nombre_completo ASC
                // MongoDB usa notación de punto para ordenar por campos embebidos
                $opciones = [
                    'sort' => [
                        'departamento.nombre' => 1, // 1 = ASC
                        'orden' => 1,
                        'nombre_completo' => 1
                    ]
                ];

                // Ejecutamos la consulta (find devuelve un Cursor iterable)
                $cursor = $db->integrantes->find([], $opciones);
                
                // Convertimos a array para contar resultados (opcional, pero útil para verificar si está vacío)
                $integrantes = $cursor->toArray();

                if (count($integrantes) > 0) {
                    foreach($integrantes as $miembro) {
                        
                        // 1. Obtener ID (Convertir ObjectId a String)
                        $id_str = (string)$miembro['_id'];

                        // 2. Lógica de Imagen
                        // Definimos ruta base pública relativa si no existe
                        $ruta_base_publica = isset($ruta_base_publica) ? $ruta_base_publica : '../../';
                        
                        $placeholder_img = "https://placehold.co/100x100/004a99/FFFFFF?text=Perfil";
                        $foto_src = $placeholder_img;
                        
                        // Verificamos si existe el campo y no está vacío
                        if (!empty($miembro['foto_url'])) {
                            $foto_url_db = $miembro['foto_url'];
                            
                            // Limpieza de ruta para file_exists (Sistema de archivos)
                            $ruta_limpia = ltrim($foto_url_db, '/');
                            $ruta_fisica_absoluta = dirname(dirname(__DIR__)) . '/' . $ruta_limpia;

                            if (file_exists($ruta_fisica_absoluta)) {
                                // Ruta para el navegador (HTML)
                                $foto_src = $ruta_base_publica . $ruta_limpia;
                            }
                        }

                        // 3. Obtener Datos Embebidos (Cargo y Depto)
                        // Intentamos leer del sub-documento 'cargo', si no existe ponemos N/A
                        $nombre_cargo = $miembro['cargo']['nombre'] ?? 'N/A';
                        $nombre_depto = $miembro['departamento']['nombre'] ?? 'N/A';
            ?>
                        <tr>
                            <td title="<?php echo $id_str; ?>">
                                <?php echo substr($id_str, -6); ?>...
                            </td>
                            <td>
                                <img src="<?php echo $foto_src; ?>" alt="Foto" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                            </td>
                            <td><?php echo htmlspecialchars($miembro['nombre_completo']); ?></td>
                            <td><?php echo htmlspecialchars($nombre_cargo); ?></td>
                            <td><?php echo htmlspecialchars($nombre_depto); ?></td>
                            <td class="acciones">
                                <a href="editar_miembro.php?id=<?php echo $id_str; ?>" class="btn-editar">Editar</a>
                                <a href="borrar_miembro.php?id=<?php echo $id_str; ?>" 
                                   class="btn-borrar" 
                                   onclick="return confirm('¿Estás seguro de borrar a <?php echo htmlspecialchars($miembro['nombre_completo']); ?>?');">
                                   Borrar
                                </a>
                            </td>
                        </tr>
            <?php
                    } 
                } else {
            ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 20px;">No hay miembros en el directorio.</td>
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