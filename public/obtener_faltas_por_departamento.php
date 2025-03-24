<?php
include '../src/config/db.php';

// Obtener las fechas de inicio y fin desde la solicitud GET
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');

try {
    // Llamar al procedimiento almacenado o hacer la consulta SQL
    $stmt = $pdo->prepare("CALL ContarFaltasPorCargo(:fecha_inicio, :fecha_fin)");
    $stmt->execute([
        'fecha_inicio' => $fechaInicio,
        'fecha_fin' => $fechaFin
    ]);

    // Obtener los resultados
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si no hay resultados, devolver un array vacío
    if (empty($resultados)) {
        $resultados = [];
    }

    // Devolver los resultados en formato JSON
    header('Content-Type: application/json');
    echo json_encode($resultados);
} catch (PDOException $e) {
    // En caso de error, devolver un mensaje de error en formato JSON
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Error al obtener datos de faltas: ' . $e->getMessage()
    ]);
}