<?php
include '../src/config/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM empleados WHERE Numero_Empleado = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "Empleado eliminado correctamente.";
    } else {
        echo "Error al eliminar el empleado.";
    }
}
?>