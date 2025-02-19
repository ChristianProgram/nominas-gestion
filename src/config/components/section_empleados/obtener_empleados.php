<?php
include '../src/config/db.php';

// Consulta para obtener todos los empleados
$query = "SELECT * FROM empleados";
$stmt = $pdo->prepare($query);
$stmt->execute();
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mostrar empleados
if (count($empleados) > 0) {
    foreach ($empleados as $empleado) {
        echo "
            <div class='empleado'>
                <p><strong>{$empleado['Nombre']}</strong> ({$empleado['Numero_Empleado']})</p>
                <p>Departamento: {$empleado['Departamento']}</p>
                <a href='editar_empleado.php?id={$empleado['ID']}'>
                    <button>Editar</button>
                </a>
            </div>
        ";
    }
} else {
    echo "<p>No se encontraron empleados.</p>";
}
?>
