<?php
include '../src/config/db.php';

// Obtener el término de búsqueda
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

// Consulta para obtener los empleados
$query = "SELECT * FROM empleados WHERE Nombre LIKE :busqueda OR Numero_Empleado LIKE :busqueda";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':busqueda', '%' . $busqueda . '%', PDO::PARAM_STR);
$stmt->execute();

// Obtener los resultados
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Devolver los resultados en formato JSON
echo json_encode($empleados);
?>
