<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cálculo</title>
    <link rel="stylesheet" href="../public/styles.css">
    <!-- Bootstrap CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link href="css/all.min.css" rel="stylesheet">
    <style>
        /* Estilo para la tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        table.visible {
            opacity: 1;
            transform: translateY(0);
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th {
            background-color: #f2f2f2;
            padding: 10px;
            text-align: left;
        }
        td {
            padding: 10px;
        }
        /* Botón */
        .boton-calcular {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .boton-calcular:hover {
            background-color: #45a049;
        }
        /* Carga */
        .cargando {
            display: none;
            font-style: italic;
            color: #999;
        }
        /* Mensaje de error */
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
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
            <h1>Cálculo</h1>
            <p>Presiona el botón para calcular las percepciones de nómina.</p>
            
            <!-- Botón para llamar al procedimiento -->
            <button class="boton-calcular" onclick="calcularNomina()">Calcular Percepciones</button>
            <p class="cargando" id="cargando">Calculando... Por favor, espera.</p>
            <p id="error-message" class="error" style="display: none;">Hubo un error al procesar la solicitud. Por favor, intenta nuevamente.</p>

            <!-- Contenedor para la tabla de resultados -->
            <div id="resultados"></div>
        </div>
    </div>

    <script>
        function calcularNomina() {
            // Mostrar mensaje de carga
            document.getElementById("cargando").style.display = "block";
            document.getElementById("error-message").style.display = "none"; // Ocultar mensaje de error

            fetch('../src/config/procesar_calculo.php')
                .then(response => {
                    // Verificar si la respuesta es correcta
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(data => {
                    // Mostrar los resultados en el contenedor
                    document.getElementById("resultados").innerHTML = data;
                    document.getElementById("cargando").style.display = "none";

                    // Animación para la tabla
                    const tabla = document.querySelector('table');
                    if (tabla) {
                        setTimeout(() => {
                            tabla.classList.add('visible');
                        }, 100);
                    }
                })
                .catch(error => {
                    // Mostrar mensaje de error si la solicitud falla
                    console.error("Error al procesar el cálculo:", error);
                    document.getElementById("cargando").style.display = "none";
                    document.getElementById("error-message").style.display = "block";
                });
        }
    </script>
    <!-- Bootstrap JS -->
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
