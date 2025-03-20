<?php
include '../src/config/db.php';

$numeroEmpleado = $_GET['numero_empleado'];
$fechaInicio = $_GET['fecha_inicio'];
$fechaFin = $_GET['fecha_fin'];

// Obtener los bonos del empleado en el rango de fechas
$sqlBonos = "SELECT cantidad, razon, fecha FROM bonos 
             WHERE numero_empleado = :numero_empleado 
             AND fecha BETWEEN :fecha_inicio AND :fecha_fin 
             ORDER BY fecha DESC";
$stmtBonos = $pdo->prepare($sqlBonos);
$stmtBonos->bindParam(':numero_empleado', $numeroEmpleado, PDO::PARAM_INT);
$stmtBonos->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
$stmtBonos->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
$stmtBonos->execute();
$bonos = $stmtBonos->fetchAll(PDO::FETCH_ASSOC);

if (empty($bonos)) {
    echo "<p>No se encontraron bonos para este empleado en la semana seleccionada.</p>";
} else {
    echo "<table class='bonos-table'>";
    echo "<thead><tr><th>Cantidad</th><th>Razón</th><th>Fecha</th></tr></thead>";
    echo "<tbody>";
    foreach ($bonos as $bono) {
        echo "<tr>";
        echo "<td>" . $bono['cantidad'] . "</td>";
        echo "<td>" . $bono['razon'] . "</td>";
        echo "<td>" . $bono['fecha'] . "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
}
?>