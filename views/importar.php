<?php
include '../src/config/db.php';
require '../vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

$vistaPrevia = "";

// Verificar si se ha enviado un archivo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["archivo"])) {
    $archivo = $_FILES["archivo"]["tmp_name"];
    $nombreArchivo = $_FILES["archivo"]["name"];
    $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

    $datos = [];

    // Procesar CSV
    if ($extension == "csv") {
        if (($handle = fopen($archivo, "r")) !== FALSE) {
            while (($fila = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $datos[] = $fila;
            }
            fclose($handle);
        }
    }
    // Procesar Excel (XLS o XLSX)
    elseif (in_array($extension, ["xls", "xlsx"])) {
        $spreadsheet = IOFactory::load($archivo);
        $sheet = $spreadsheet->getActiveSheet();
        $datos = $sheet->toArray();
    } else {
        $vistaPrevia = "<p style='color:red;'>Formato de archivo no soportado.</p>";
    }

    // Generar la vista previa
    if (!empty($datos)) {
        $vistaPrevia .= "<table border='1' cellpadding='5' cellspacing='0'>";
        foreach ($datos as $index => $fila) {
            $vistaPrevia .= "<tr>";
            foreach ($fila as $columna) {
                $vistaPrevia .= $index === 0 
                    ? "<th style='background-color:#f2f2f2;'>" . htmlspecialchars($columna) . "</th>" 
                    : "<td>" . htmlspecialchars($columna) . "</td>";
            }
            $vistaPrevia .= "</tr>";
        }
        $vistaPrevia .= "</table>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Importar Datos</title>
    <link rel="stylesheet" href="../public/styles.css">
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
            <h1>Importar Datos</h1>
            <p>Sube un archivo CSV o Excel y revisa los datos antes de importarlos.</p>

            <!-- Formulario para cargar el archivo -->
            <form action="importar.php" method="post" enctype="multipart/form-data">
                <label for="archivo">Selecciona un archivo CSV o Excel:</label>
                <input type="file" name="archivo" id="archivo" accept=".csv, .xls, .xlsx" required>
                <br><br>
                <button type="submit">Mostrar Vista Previa</button>
            </form>

            <hr>

            <!-- Vista Previa de los datos -->
            <h2>Vista Previa del Archivo</h2>
            <div>
                <?php
                if (!empty($vistaPrevia)) {
                    echo $vistaPrevia;
                } else {
                    echo "<p>No se ha cargado ningún archivo o el archivo está vacío.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
