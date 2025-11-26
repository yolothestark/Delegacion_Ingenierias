<?php
    // 1. Incluimos la conexión a MongoDB
    // Aunque este archivo es texto estático, lo incluimos por consistencia
    include '../includes/db_connect_mongo.php';
    
    // 2. Definimos el título
    $titulo_pagina = "Nuestra Misión - Delegación Ingenierías";
    
    // 3. Incluimos el header
    // IMPORTANTE: Ajustamos la ruta a 'layouts' según la estructura nueva
    include '../layouts/header.php';
?>

<main class="container-detalle">
    
    <div style="text-align: center; margin-bottom: 40px;">
        <h1 style="color: #003b46;">Nuestra Identidad</h1>
        <p style="font-size: 1.2em; color: #666;">Delegación Académica de Ingenierías</p>
        <hr style="width: 100px; border: 2px solid #6ca842; margin: 20px auto;">
    </div>

    <section style="margin-bottom: 50px; display: flex; gap: 30px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 300px;">
            <h2 style="color: #009eb3; border-bottom: none; margin-bottom: 15px;">Misión</h2>
            <p style="line-height: 1.8; text-align: justify; color: #444;">
                Somos la instancia encargada de representar, organizar y promover el desarrollo integral 
                de la comunidad académica y estudiantil del área de ingenierías. Nuestra misión es 
                fomentar la excelencia educativa, la innovación tecnológica y el compromiso social, 
                facilitando herramientas y espacios que potencien las capacidades de nuestros futuros ingenieros.
            </p>
        </div>
        <div style="flex: 1; min-width: 300px;">
            <img src="../imagenes/vision.jpg" 
                 alt="Misión" 
                 style="width: 100%; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
        </div>
    </section>

    <section style="margin-bottom: 50px; display: flex; gap: 30px; align-items: center; flex-wrap: wrap; flex-direction: row-reverse;">
        <div style="flex: 1; min-width: 300px;">
            <h2 style="color: #009eb3; border-bottom: none; margin-bottom: 15px;">Visión</h2>
            <p style="line-height: 1.8; text-align: justify; color: #444;">
                Ser reconocidos como un referente nacional en la gestión académica y la formación de ingenieros 
                de clase mundial. Aspiramos a construir una comunidad universitaria dinámica, inclusiva y 
                vanguardista, que lidere soluciones a los desafíos tecnológicos globales con ética y responsabilidad.
            </p>
        </div>
        <div style="flex: 1; min-width: 300px;">
            <img src="../imagenes/misionTonala.jpg" 
                 alt="Visión" 
                 style="width: 100%; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
        </div>
    </section>

    <section style="background-color: #f9f9f9; padding: 40px; border-radius: 8px;">
        <h2 style="text-align: center; color: #003b46; margin-bottom: 30px; border-bottom: none;">Nuestros Valores</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; text-align: center;">
            
            <div style="padding: 10px;">
                <h3 style="color: #009eb3; margin-bottom: 10px;">Innovación</h3>
                <p style="font-size: 0.95em; color: #666;">Buscamos constantemente nuevas formas de resolver problemas.</p>
            </div>
            <div style="padding: 10px;">
                <h3 style="color: #009eb3; margin-bottom: 10px;">Integridad</h3>
                <p style="font-size: 0.95em; color: #666;">Actuamos con honestidad y ética en todas nuestras actividades.</p>
            </div>
            <div style="padding: 10px;">
                <h3 style="color: #009eb3; margin-bottom: 10px;">Excelencia</h3>
                <p style="font-size: 0.95em; color: #666;">Nos esforzamos por la máxima calidad en la educación.</p>
            </div>
            <div style="padding: 10px;">
                <h3 style="color: #009eb3; margin-bottom: 10px;">Colaboración</h3>
                <p style="font-size: 0.95em; color: #666;">Creemos en el trabajo en equipo multidisciplinario.</p>
            </div>

        </div>
    </section>

</main>

<?php
    // 4. Incluimos el footer
    // IMPORTANTE: Ajustamos la ruta a 'layouts'
    include '../layouts/footer.php';
?>