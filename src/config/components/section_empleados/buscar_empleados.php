<?php
include '../src/config/db.php';

$busqueda = $_GET['busqueda'] ?? '';
$pagina = $_GET['pagina'] ?? 1;
$porPagina = 10; // Resultados por página
$offset = ($pagina - 1) * $porPagina;

$sql = "SELECT * FROM empleados 
        WHERE Nombre LIKE :busqueda OR Numero_Empleado LIKE :busqueda 
        ORDER BY Nombre ASC 
        LIMIT :offset, :porPagina";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':busqueda', "%$busqueda%", PDO::PARAM_STR);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':porPagina', $porPagina, PDO::PARAM_INT);
$stmt->execute();
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sqlCount = "SELECT COUNT(*) as total FROM empleados 
             WHERE Nombre LIKE :busqueda OR Numero_Empleado LIKE :busqueda";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->bindValue(':busqueda', "%$busqueda%", PDO::PARAM_STR);
$stmtCount->execute();
$total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($total / $porPagina);

if (count($empleados) > 0) {
    echo '<table class="payroll-table">
            <thead>
                <tr>
                    <th>ID Empleado</th>
                    <th>Nombre</th>
                    <th>Departamento</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($empleados as $empleado) {
        echo "<tr>
                <td>{$empleado['Numero_Empleado']}</td>
                <td>{$empleado['Nombre']}</td>
                <td>{$empleado['Departamento']}</td>
                <td>
                    <button class='btn btn-secondary btn-sm' onclick='obtenerEmpleado({$empleado['Numero_Empleado']})'>
                        <i class='bi bi-pencil'></i> Editar
                    </button>
                    <button class='btn btn-danger btn-sm' onclick='eliminarEmpleado({$empleado['Numero_Empleado']})'>
                        <i class='bi bi-trash'></i> Eliminar
                    </button>
                </td>
              </tr>";
    }

    echo '</tbody></table>';

    // Paginación
    echo '<div class="paginacion">';
    for ($i = 1; $i <= $totalPaginas; $i++) {
        $claseActivo = ($i == $pagina) ? 'activo' : '';
        echo "<a href='#' class='$claseActivo' onclick='cambiarPagina($i)'>$i</a>";
    }
    echo '</div>';
} else {
    echo "<p class='text-center'>No se encontraron resultados.</p>";
}
?>