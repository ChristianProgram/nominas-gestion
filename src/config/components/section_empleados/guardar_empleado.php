<?php
include '../src/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $numero = $_POST['numero'];
    $departamento = $_POST['departamento'];

    $sql = "INSERT INTO empleados (Numero_Empleado, Nombre, Departamento) 
            VALUES (:numero, :nombre, :departamento)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':numero', $numero, PDO::PARAM_STR);
    $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
    $stmt->bindParam(':departamento', $departamento, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo "Empleado agregado correctamente.";
    } else {
        echo "Error al agregar el empleado.";
    }
}
?>