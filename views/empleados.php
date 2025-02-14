<?php
include '../src/config/db.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Personal</title>
    <link rel="stylesheet" href="../public/styles.css">
    <!-- Bootstrap CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link href="css/all.min.css" rel="stylesheet">
    <style>
        /* Estilos para la paginación */
        .paginacion {
            margin-top: 20px;
            text-align: center;
        }
        .paginacion a {
            margin: 0 5px;
            text-decoration: none;
            color: #4CAF50;
        }
        .paginacion a:hover {
            text-decoration: underline;
        }
        .paginacion .activo {
            font-weight: bold;
            color: #000;
        }
    </style>
    <script>
        // Función para búsqueda dinámica con AJAX
        function buscarEmpleados() {
            const busqueda = document.getElementById('busqueda').value;
            const pagina = document.getElementById('pagina').value;

            fetch(`buscar_empleados.php?busqueda=${busqueda}&pagina=${pagina}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('resultados').innerHTML = data;
                })
                .catch(error => console.error('Error:', error));
        }

        // Función para cambiar de página
        function cambiarPagina(pagina) {
            document.getElementById('pagina').value = pagina;
            buscarEmpleados();
        }

        // Cargar resultados al inicio
        window.onload = buscarEmpleados;
    </script>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Menú</h2>
            <ul>
                <li><a href="importar.php">Importar</a></li>
                <li><a href="checadas.php">Checadas</a></li>
                <li><a href="calculo.php">Calculo</a></li>
                <li><a href="empleados.php">Personal</a></li>
            </ul>
        </div>
        <div class="content">
            <h1>Apartado de Personal</h1>
            <p>Podrás verificar todo el personal del departamento.</p>

            <!-- Formulario de Búsqueda -->
            <form method="GET" action="empleados.php" onsubmit="event.preventDefault(); buscarEmpleados();">
                <input type="text" id="busqueda" name="busqueda" placeholder="Buscar por nombre o número">
                <input type="hidden" id="pagina" name="pagina" value="1">
                <button type="submit">Buscar</button>
            </form>

            <!-- Contenedor para los resultados -->
            <div id="resultados"></div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>