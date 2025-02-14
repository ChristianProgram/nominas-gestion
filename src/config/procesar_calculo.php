<?php
// Incluir la configuración de la base de datos
include '../src/config/db.php';

try {
    // Verificar si la conexión a la base de datos es válida
    if (!$pdo) {
        throw new Exception("No se pudo conectar a la base de datos.");
    }

    // Llamada al procedimiento CalcularNomina
    $query = "CALL CalcularNomina();";
    $stmt = $pdo->prepare($query);

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . implode(" ", $pdo->errorInfo()));
    }

    $stmt->execute();

    // Recuperar los resultados
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mostrar los resultados
    if ($result) {
        echo "<table>";
        echo "<tr><th>ID Nomina</th><th>Nombre</th><th>Total Percepciones</th><th>Importe Final</th></tr>";
        foreach ($result as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['idNomina']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Total_Percepciones']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Importe_Final']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No se encontraron resultados.";
    }
} catch (PDOException $e) {
    echo "Error de base de datos: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>