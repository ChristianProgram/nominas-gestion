<?php
include '../src/config/db.php';

// Obtener el valor del filtro si existe
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1; // Página actual
$registrosPorPagina = 10; // Número de registros por página

// Calcular el offset para la paginación
$offset = ($pagina - 1) * $registrosPorPagina;

// Construcción de la consulta con límite, filtro de búsqueda y paginación
$sql = "SELECT numero, nombre FROM nominasueldo";
$sqlCount = "SELECT COUNT(*) AS total FROM nominasueldo"; // Consulta para contar el total de registros

// Agregar filtro si hay búsqueda
if (!empty($busqueda)) {
    $sql .= " WHERE numero LIKE :busqueda OR nombre LIKE :busqueda";
    $sqlCount .= " WHERE numero LIKE :busqueda OR nombre LIKE :busqueda";
}

// Ordenar y paginar
$sql .= " ORDER BY numero ASC LIMIT :limit OFFSET :offset";

// Preparar y ejecutar la consulta para obtener los registros
$stmt = $pdo->prepare($sql);
$stmtCount = $pdo->prepare($sqlCount);

// Asignar parámetros si hay búsqueda
if (!empty($busqueda)) {
    $busquedaParam = "%" . $busqueda . "%";
    $stmt->bindParam(':busqueda', $busquedaParam, PDO::PARAM_STR);
    $stmtCount->bindParam(':busqueda', $busquedaParam, PDO::PARAM_STR);
}

// Asignar parámetros de paginación
$stmt->bindParam(':limit', $registrosPorPagina, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener el total de registros para la paginación
$stmtCount->execute();
$totalRegistros = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($totalRegistros / $registrosPorPagina); // Calcular el total de páginas
?>

<!-- Tabla de resultados -->
<table border="1">
    <thead>
        <tr>
            <th>Número Empleado</th>
            <th>Nombre</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Mostrar los registros
        if (count($empleados) > 0) {
            foreach ($empleados as $row) {
                echo "<tr>";
                echo "<td>" . $row['numero'] . "</td>";
                echo "<td>" . $row['nombre'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='2'>No se encontró personal.</td></tr>";
        }
        ?>
    </tbody>
</table>

<!-- Paginación -->
<div class="paginacion">
    <?php if ($totalPaginas > 1): ?>
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <a href="#" onclick="cambiarPagina(<?php echo $i; ?>)" class="<?php echo ($i == $pagina) ? 'activo' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    <?php endif; ?>
</div>