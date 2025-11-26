<?php
    // 1. Incluimos la conexión a MongoDB
    // Ajusta la ruta si es necesario (aquí asumo que estamos en /Modulos/)
    include '../includes/db_connect_mongo.php';
    include '../includes/header.php';
    
    $titulo_pagina = "Contacto - Delegación Ingenierías";
    $mensaje_exito = "";
    $mensaje_error = "";

    // --- LÓGICA PARA RECIBIR EL FORMULARIO (MONGODB) ---
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        // Limpiar datos (Trim quita espacios al inicio y final)
        // No necesitamos real_escape_string en MongoDB
        $nombre    = trim($_POST['nombre']);
        $apellido  = trim($_POST['apellido']);
        $email     = trim($_POST['email']);
        $contenido = trim($_POST['mensaje']);

        if (!empty($email) && !empty($contenido)) {
            
            try {
                // Preparar el documento
                $nuevoMensaje = [
                    'nombre'      => $nombre,
                    'apellido'    => $apellido,
                    'email'       => $email,
                    'contenido'   => $contenido,
                    'fecha_envio' => new MongoDB\BSON\UTCDateTime(), // Timestamp automático
                    'leido'       => false // Bandera útil para el futuro
                ];

                // Insertar en la colección 'mensajes_contacto'
                $resultado = $db->mensajes_contacto->insertOne($nuevoMensaje);

                if ($resultado->getInsertedCount() > 0) {
                    $mensaje_exito = "¡Gracias! Tu mensaje ha sido enviado correctamente.";
                } else {
                    $mensaje_error = "Hubo un error al enviar el mensaje. Intenta de nuevo.";
                }

            } catch (Exception $e) {
                // Error de conexión o escritura
                $mensaje_error = "Ocurrió un error técnico. Por favor intenta más tarde.";
            }

        } else {
            $mensaje_error = "Por favor completa los campos obligatorios.";
        }
    }
    // -----------------------------------------
    
    // 3. Header
    // Ajustado a la carpeta layouts según la estructura migrada
    include '../layouts/header.php';
?>

<style>
.contacto-wrapper { display: flex; flex-wrap: wrap; gap: 60px; margin-top: 40px; font-family: 'Segoe UI', sans-serif; }

/* COLUMNA INFO */
.columna-info { flex: 1; min-width: 300px; }

/* CAMBIO: Petrol Oscuro */
.titulo-contacto { font-size: 3.5em; color: #003b46; font-weight: 800; margin-bottom: 50px; margin-top: 0; }

.item-contacto { display: flex; margin-bottom: 35px; align-items: flex-start; }
.icono { width: 35px; margin-right: 20px; padding-top: 5px; }

/* CAMBIO: Iconos Petrol Oscuro */
.icono svg { stroke: #003b46; }

/* CAMBIO: Texto Petrol Oscuro */
.texto-info p { margin: 0 0 3px 0; color: #003b46; font-size: 1.15em; line-height: 1.4; }
.texto-info a { color: #003b46; text-decoration: none; }

/* CAMBIO: Subtítulos Azul Aqua */
.subtitulo-info { margin: 0 0 10px 0; font-size: 1.1em; font-weight: 800; color: #009eb3; text-transform: uppercase; letter-spacing: 1px; }

/* COLUMNA FORMULARIO */
.columna-form { flex: 1.2; min-width: 300px; padding-top: 15px; }
.fila-doble { display: flex; gap: 20px; margin-bottom: 20px; }
.fila-doble .campo { flex: 1; }
.campo { margin-bottom: 25px; }

/* CAMBIO: Label Petrol */
.campo label { display: block; color: #003b46; margin-bottom: 8px; font-size: 1em; }

/* CAMBIO: Borde Petrol */
.campo input, .campo textarea { width: 100%; padding: 12px; border: 1px solid #003b46; font-size: 1em; box-sizing: border-box; outline: none; }
.campo input:focus, .campo textarea:focus { border-color: #009eb3; border-width: 2px; } /* Focus Aqua */

.campo textarea { resize: vertical; min-height: 120px; }
.boton-container { text-align: right; margin-top: 10px; }

/* CAMBIO: Botón Verde */
.btn-enviar-rojo { background-color: #6ca842; color: white; border: none; padding: 12px 50px; font-size: 1em; cursor: pointer; transition: background-color 0.2s; }
.btn-enviar-rojo:hover { background-color: #558732; }

/* ALERTAS */
.alerta-exito { background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb; }
.alerta-error { background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb; }

@media (max-width: 768px) { .fila-doble { flex-direction: column; gap: 0; } .contacto-wrapper { flex-direction: column; gap: 30px; } .titulo-contacto { font-size: 2.5em; text-align: center; } }
</style>

<main class="container-detalle">

    <div class="contacto-wrapper">
        
        <div class="columna-info">
            <h1 class="titulo-contacto">Contacto</h1>
            
            <div class="item-contacto">
                <div class="icono">
                    <!-- CAMBIO: stroke='#003b46' -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#003b46" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                </div>
                <div class="texto-info">
                    <p>Nuevo Perif. Ote. 555 #555</p>
                    <p>Ejido San José, Tateposco</p>
                    <p>Tonala, Jalisco</p>
                    <p>C.P. 45425</p>
                </div>
            </div>

            <div class="item-contacto">
                <div class="icono">
                    <!-- CAMBIO: stroke='#003b46' -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#003b46" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                </div>
                <div class="texto-info">
                    <h3 class="subtitulo-info">TELEFONO(S)</h3>
                    <p>3833-3929, 3833-3930</p>
                    <p>3833-3931, 3833-3932</p>
                </div>
            </div>

            <div class="item-contacto">
                <div class="icono">
                    <!-- CAMBIO: stroke='#003b46' -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#003b46" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                </div>
                <div class="texto-info">
                    <p><a href="mailto:sindicatostaudeg@gmail.com">sindicatostaudeg@gmail.com</a></p>
                </div>
            </div>
        </div>

        <div class="columna-form">
            
            <?php if (!empty($mensaje_exito)): ?>
                <div class="alerta-exito"><?php echo $mensaje_exito; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($mensaje_error)): ?>
                <div class="alerta-error"><?php echo $mensaje_error; ?></div>
            <?php endif; ?>

            <form action="" method="POST"> 
                
                <div class="fila-doble">
                    <div class="campo">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre">
                    </div>
                    <div class="campo">
                        <label for="apellido">Apellido</label>
                        <input type="text" id="apellido" name="apellido">
                    </div>
                </div>

                <div class="campo">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="campo">
                    <label for="mensaje">Mensaje</label>
                    <textarea id="mensaje" name="mensaje" rows="5" required></textarea>
                </div>

                <div class="boton-container">
                    <button type="submit" class="btn-enviar-rojo">Enviar</button>
                </div>

            </form>
        </div>

    </div>

</main>

<?php
    // En MongoDB con PHP, no hace falta cerrar la conexión explícitamente.
    // Incluimos el footer desde la carpeta layouts
    include '../includes/footer.php';
?>