<?php
    // 1. Incluimos la conexión a MongoDB (subimos un nivel desde Modulos/)
    include '../includes/db_connect_mongo.php';
    
    $titulo_pagina = "Reportar Error - Delegación Ingenierías";
    $mensaje = "";
    
    // Procesar el formulario
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        // Limpieza de datos
        $nombre      = trim($_POST['nombre']);
        $email       = trim($_POST['email']);
        $categoria   = trim($_POST['categoria']);
        $descripcion = trim($_POST['descripcion']);
        
        if (!empty($nombre) && !empty($email) && !empty($descripcion)) {
            
            try {
                // Construir el documento del ticket
                $nuevoTicket = [
                    'nombre_usuario'     => $nombre,
                    'email'              => $email,
                    'categoria_problema' => $categoria,
                    'descripcion'        => $descripcion,
                    'estado'             => 'Pendiente', // Estado inicial por defecto
                    'fecha_creacion'     => new MongoDB\BSON\UTCDateTime() // Timestamp automático
                ];

                // Insertar en la colección 'soporte_tickets'
                $resultado = $db->soporte_tickets->insertOne($nuevoTicket);
                
                if ($resultado->getInsertedCount() > 0) {
                    $mensaje = "<div class='alerta-exito'>¡Reporte enviado! Gracias por ayudarnos a mejorar el sitio.</div>";
                } else {
                    $mensaje = "<div class='alerta-error'>Error al enviar el reporte. Intenta de nuevo.</div>";
                }

            } catch (Exception $e) {
                // Error de conexión
                $mensaje = "<div class='alerta-error'>Ocurrió un error técnico. Intenta más tarde.</div>";
            }
            
        } else {
            $mensaje = "<div class='alerta-error'>Por favor completa todos los campos obligatorios.</div>";
        }
    }

    // 2. Incluimos el header
    include '../layouts/header.php';
?>

<main class="container-detalle">
    
    <div class="identidad-header">
        <h1>Reportar Problema Web</h1>
        <p class="identidad-subtitle">Ayúdanos a mantener el sitio funcionando correctamente</p>
        <hr class="identidad-divider">
    </div>

    <div class="contacto-wrapper">
        
        <!-- Columna Izquierda: Información -->
        <div class="columna-info">
            <h2 style="color: #002f55;">¿Encontraste un error?</h2>
            <p style="line-height: 1.6; color: #444;">
                Si tuviste problemas al navegar por nuestro sitio, por favor descríbelo aquí. Tu retroalimentación es vital para el equipo de desarrollo.
            </p>
            <p style="line-height: 1.6; color: #444; margin-top: 15px;">
                <strong>Ejemplos de reportes útiles:</strong>
            </p>
            <ul style="color: #444; margin-bottom: 30px; line-height: 1.5;">
                <li>"No puedo iniciar sesión con mi cuenta."</li>
                <li>"Al subir un documento me sale error."</li>
                <li>"Las imágenes de la galería no cargan."</li>
                <li>"Hay un enlace roto en la sección de Directorio."</li>
            </ul>
            
            <div class="item-contacto">
                <div class="icono">
                    <!-- Icono de 'Bug' (Insecto/Error) -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#003a70" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="9" width="8" height="8" rx="4"></rect><path d="M6 6a7.93 7.93 0 0 1 6-1.7c2.5 0 4.8.8 6.7 2.2"></path><path d="M12 13V9"></path><path d="M12 21v-3"></path><path d="M6 21v-2"></path><path d="M18 21v-2"></path><path d="M2 14h4"></path><path d="M18 14h4"></path></svg>
                </div>
                <div class="texto-info">
                    <h3 class="subtitulo-info">Administrador Web</h3>
                    <p>admin.web@delegacion.udg.mx</p>
                    <p>Tiempo de respuesta: 24-48 hrs.</p>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Formulario -->
        <div class="columna-form">
            <?php echo $mensaje; ?>
            
            <form action="" method="POST">
                <div class="campo">
                    <label for="nombre">Tu Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Ej. Juan Pérez">
                </div>
                
                <div class="campo">
                    <label for="email">Tu Correo (para contactarte):</label>
                    <input type="email" id="email" name="email" required placeholder="ejemplo@alumnos.udg.mx">
                </div>

                <div class="campo">
                    <label for="categoria">¿Dónde está el problema?</label>
                    <select id="categoria" name="categoria" style="width: 100%; padding: 12px; border: 1px solid #002f55; background: white;" required>
                        <option value="">-- Selecciona una categoría --</option>
                        <option value="Acceso/Login">Problemas de Acceso / Login</option>
                        <option value="Carga/Visualización">Página no carga / Se ve mal</option>
                        <option value="Enlaces Rotos">Enlace caído o Error 404</option>
                        <option value="Subida Archivos">Error al subir archivos/imágenes</option>
                        <option value="Información Incorrecta">Datos erróneos en el sitio</option>
                        <option value="Sugerencia">Sugerencia de Mejora</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div class="campo">
                    <label for="descripcion">Detalles del Error:</label>
                    <textarea id="descripcion" name="descripcion" rows="5" required placeholder="Por favor describe qué estabas haciendo cuando ocurrió el error..."></textarea>
                </div>

                <div class="boton-container">
                    <button type="submit" class="btn-enviar-rojo">Enviar Reporte</button>
                </div>
            </form>
        </div>

    </div>
</main>

<?php
    // 3. Incluimos el footer
    include '../layouts/footer.php';
?>