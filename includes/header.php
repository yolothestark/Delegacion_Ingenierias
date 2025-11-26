<?php
// 1. Incluir conexión a MongoDB
// Usamos __DIR__ para referenciar la carpeta actual (layouts) y subir un nivel a (includes)
// Esto asegura que la conexión cargue bien desde index.php o desde Modulos/archivo.php
include_once __DIR__ . '/../includes/db_connect_mongo.php';

// Definimos la RUTA RAÍZ de tu proyecto.
$ruta_base = "/Delegacion_ingenierias"; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="<?php echo $ruta_base; ?>/css/main.css">
    <link rel="stylesheet" href="<?php echo $ruta_base; ?>/css/detalle.css">
    
    <title><?php echo $titulo_pagina ?? 'Delegación Académica de Ingenierías'; ?></title>
</head>
<body>

<header class="main-header">
    
    <div class="logo-container">
        <a href="<?php echo $ruta_base; ?>/index.php">
            <img src="<?php echo $ruta_base; ?>/imagenes/LG.jpg" alt="Logo Delegación" class="logo">
        </a>
        <h1>Delegación Académica de Ingenierías</h1>
    </div>
    
    <nav class="main-nav">
        <ul>
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