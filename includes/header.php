<?php
// 1. Incluir conexión a MongoDB si no existe
// Usamos __DIR__ para asegurar la ruta relativa correcta hacia el mismo directorio 'includes'
require_once __DIR__ . '/db_connect_mongo.php';

// 2. Definir la RUTA BASE automáticamente
// Si estamos en localhost, usamos la carpeta del proyecto.
// Si estamos en Render (cualquier otro dominio), usamos la raíz vacía "".
// IMPORTANTE: Asegúrate que "Delegacion_Ingenierias" coincida con el nombre de tu carpeta en Laragon
$es_local = ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1');
$ruta_base = $es_local ? "/Delegacion_Ingenierias" : "";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Vinculación de CSS -->
    <!-- IMPORTANTE: Las rutas empiezan con $ruta_base para funcionar en ambos entornos -->
    <link rel="stylesheet" href="<?php echo $ruta_base; ?>/css/main.css">
    <link rel="stylesheet" href="<?php echo $ruta_base; ?>/css/detalle.css">
    
    <!-- Favicon opcional -->
    <!-- <link rel="icon" href="<?php echo $ruta_base; ?>/imagenes/favicon.ico"> -->

    <title><?php echo $titulo_pagina ?? 'Delegación Académica de Ingenierías'; ?></title>
</head>
<body>

<header class="main-header">
    
    <!-- 1. Bloque de Identidad (Logo y Texto) -->
    <div class="logo-container">
        <a href="<?php echo $ruta_base; ?>/index.php">
            <!-- Asegúrate de que la imagen exista en la carpeta imagenes -->
            <img src="<?php echo $ruta_base; ?>/imagenes/LG.jpg" alt="Logo Delegación" class="logo">
        </a>
        <h1>Delegación Académica de Ingenierías</h1>
    </div>
    
    <!-- 3. Menú de Navegación -->
    <nav class="main-nav">
        <ul>
            <!-- CORRECCIÓN: Rutas con 'modulos' en minúscula para compatibilidad con Linux/Render -->
            <li><a href="<?php echo $ruta_base; ?>/index.php">Inicio</a></li>
            <li><a href="<?php echo $ruta_base; ?>/modulos/noticias_lista.php">Noticias</a></li>
            <li><a href="<?php echo $ruta_base; ?>/modulos/directorio.php">Directorio</a></li>
            <li><a href="<?php echo $ruta_base; ?>/modulos/documentos.php">Normatividad</a></li>
            <li><a href="<?php echo $ruta_base; ?>/modulos/mision.php">Misión</a></li>
            <li><a href="<?php echo $ruta_base; ?>/modulos/contacto.php">Contacto</a></li>
            <li><a href="<?php echo $ruta_base; ?>/modulos/soporte.php">Soporte</a></li>
        </ul>
    </nav>
    
</header>