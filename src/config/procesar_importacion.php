<?php
include '../src/config/db.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $archivo = $_FILES["archivo"]["tmp_name"];
    $nombreArchivo = $_FILES["archivo"]["name"];
    $extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);

    if ($extension == "csv") {
        $datos = [];
        if (($handle = fopen($archivo, "r")) !== FALSE) {
            while (($fila = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $datos[] = $fila;
            }
            fclose($handle);
        }
    } elseif (in_array($extension, ["xls", "xlsx"])) {
        $spreadsheet = IOFactory::load($archivo);
        $sheet = $spreadsheet->getActiveSheet();
        $datos = $sheet->toArray();
    } else {
        header("Location: importar.php?error=Formato de archivo no soportado.");
        exit();
    }

    // Inserción en la base de datos
    $conn->beginTransaction();
    try {
        foreach ($datos as $index => $fila) {
            if ($index == 0) continue; // Omitir la primera fila (encabezado)

            $stmt = $conn->prepare("INSERT INTO nominasueldo (numero, nombre) VALUES (?, ?)");
            $stmt->execute([$fila[0], $fila[1]]);
        }
        $conn->commit();
        header("Location: importar.php?mensaje=Datos importados correctamente.");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        header("Location: importar.php?error=Error en la importación.");
        exit();
    }
}
?>
