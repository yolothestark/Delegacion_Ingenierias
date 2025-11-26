<?php
// 1. Cargar librerías de Composer (Vital para Render/Docker)
// __DIR__ es la carpeta 'includes', subimos un nivel para llegar a 'vendor'
require_once __DIR__ . '/../vendor/autoload.php';

try {
    // 2. Obtener la URL de conexión desde las variables de entorno de Render
    // Si no existe (estás en local), usa una por defecto (opcional)
    $uri = getenv('MONGO_URI');
    
    if (!$uri) {
        // Fallback para pruebas locales si no configuras la variable
        // REEMPLAZA ESTO CON TU URL REAL DE ATLAS SI ESTÁS EN XAMPP LOCAL
        $uri = 'mongodb://localhost:27017'; 
    }

    // 3. Conectar a Atlas
    $client = new MongoDB\Client($uri);

    // 4. Seleccionar la base de datos
    // CAMBIA 'delegacion_db' por el nombre exacto de tu base de datos en Atlas
    $db = $client->selectDatabase('delegacion_db');

} catch (Exception $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>