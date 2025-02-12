<?php
include '../src/config/db.php';

header('Content-Type: text/html; charset=utf-8');

$query = "CALL CalcularNomina()"; // Llamamos al procedimiento
$result = $pdo->query($query);

if ($result) {
    // Comienza la tabla de resultados
    echo "<table>";
    echo "<thead>";
    echo "<tr><th>ID Nómina</th><th>Nombre</th><th>Total Percepciones</th><th>Importe Final</th></tr>";
    echo "</thead><tbody>";

    // Mostramos los resultados de la consulta
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['idNomina']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Total_Percepciones']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Importe_Final']) . "</td>";
        echo "</tr>";
    }

    echo "</tbody></table>";
} else {
    echo "Error al calcular las percepciones.";
}
?>
