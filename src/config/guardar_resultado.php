<?php
include '../src/config/db.php';

if (isset($_POST['guardar'])) {
    $idNomina = $_POST['idNomina'];
    $nombre = $_POST['nombre'];
    $totalPercepciones = $_POST['totalPercepciones'];
    $importeFinal = $_POST['importeFinal'];

    // Insertar en la tabla definitiva
    $sql = "INSERT INTO resultados_nomina (idNomina, Nombre, Total_Percepciones, Importe_Final)
            VALUES ('$idNomina', '$nombre', '$totalPercepciones', '$importeFinal')";
    
    if ($conn->query($sql)) {
        echo "Resultado guardado exitosamente.";
    } else {
        echo "Error al guardar: " . $conn->error;
    }
}
?>
