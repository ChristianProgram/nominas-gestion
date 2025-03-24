<?php
include '../src/config/db.php';

// Obtener el término de búsqueda si se proporciona
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Modificar la consulta para incluir la búsqueda y el nombre del cargo
$sqlEmpleados = "
    SELECT e.ID, e.Nombre, e.Numero_Empleado, r.nombre_cargo AS Departamento 
    FROM empleados e
    LEFT JOIN roles r ON e.Departamento = r.id
    WHERE e.Nombre LIKE :search OR e.Numero_Empleado LIKE :search
    ORDER BY e.Nombre ASC
";
$stmtEmpleados = $pdo->prepare($sqlEmpleados);
$stmtEmpleados->bindValue(':search', "%$searchTerm%", PDO::PARAM_STR);
$stmtEmpleados->execute();
$empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Empleados</title>
    <link rel="stylesheet" href="../public/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .asistencias-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .asistencias-table th, .asistencias-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
        .asistencias-table th {
            background-color: #f2f2f2;
        }
        .present {
            background-color: green;
            color: white;
        }
        .absent {
            background-color: red;
            color: white;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a, .pagination span {
            margin: 0 5px;
            text-decoration: none;
            color: #333;
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .pagination a.active {
            font-weight: bold;
            color: #000;
            background-color: #f2f2f2;
        }
        .pagination a:hover:not(.disabled) {
            background-color: #ddd;
        }
        .pagination .disabled {
            color: #aaa;
            cursor: not-allowed;
            background-color: #f9f9f9;
        }
        .pagination-button {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            transition: background-color 0.3s ease;
        }
        .pagination-button:hover:not(.disabled) {
            background-color: #ddd;
        }
        .pagination-button.disabled {
            color: #aaa;
            cursor: not-allowed;
            background-color: #f9f9f9;
        }
        .filter-form {
            margin-bottom: 20px;
        }
        .filter-form label {
            margin-right: 10px;
        }
        .filter-form select, .filter-form input {
            padding: 5px;
            margin-right: 10px;
        }
        .falta-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .falta-btn.marcada {
            background-color: red;
            color: white;
        }

        .falta-btn.no-marcada {
            background-color: #f2f2f2;
            color: #333;
        }

        /* Estilos para el fondo de las celdas */
        .celda-asistencia {
            padding: 8px;
            text-align: center;
        }

        .celda-asistencia.asistio {
            background-color: green;
            color: white;
        }

        .celda-asistencia.falto {
            background-color: red;
            color: white;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #1e293b;
            color: #ffffff;
            padding: 1rem;
        }
        .sidebar-header {
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sidebar h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-section {
            margin-bottom: 1.5rem;
        }
        .sidebar-section h3 {
            font-size: 1rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            background-color: #00263F;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin: 0.5rem 0;
        }
        .sidebar ul li a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.75rem 1rem;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        .sidebar ul li a:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .sidebar ul li a.active {
            background:rgb(5, 56, 90);
            color: white;
        }
        .sidebar ul li a i {
            font-size: 1rem;
            width: 20px;
            text-align: center;
        }

    </style>
</head>
<body>
    <div class="container">
    <div class="sidebar">
            <div class="sidebar-header">
                <h2>Nominas</h2>
            </div>

            <!-- Sección: Informes -->
            <div class="sidebar-section">
                <h3>Informes</h3>
                <ul>
                    <li><a href="../public/index.php" class="active"><i class="fas fa-chart-bar"></i> Resumen</a></li>
                </ul>
            </div>

            <!-- Sección: Gestionar -->
            <div class="sidebar-section">
                <h3>Gestionar</h3>
                <ul>
                    <li><a href="../views/checadas.php"><i class="fas fa-calendar-alt"></i> Asistencia</a></li>
                    <li><a href="../views/empleados.php"><i class="fas fa-users"></i> Empleados</a></li>
                    <li><a href="../views/calculo.php"><i class="fas fa-calculator"></i> Deducciones</a></li>
                    <li><a href="../views/bonos.php"><i class="fas fa-gift"></i> Bonos</a></li>
                    <li><a href="../views/roles.php"><i class="fas fa-briefcase"></i> Cargos</a></li>
                    <li><a href="../views/importar.php"><i class="fas fa-file-import"></i> Importar datos</a></li>
                    <li><a href="../views/reportes.php"><i class="fas fa-file-alt"></i> Reportes</a></li>
                </ul>
            </div>

            <!-- Sección: Imprimibles -->
            <div class="sidebar-section">
                <h3>Imprimibles</h3>
                <ul>
                    <li><a href="#"><i class="fas fa-print"></i> Reportes PDF</a></li>
                    <li><a href="#"><i class="fas fa-file-excel"></i> Exportar Excel</a></li>
                </ul>
            </div>
        </div>
        <div class="main-content">
            <div class="content-container">
                <div class="section-header">
                    <h1 class="section-title">Lista de Empleados</h1>
                </div>

                <!-- Formulario de búsqueda -->
                <form method="GET" action="empleados.php">
                    <input type="text" name="search" placeholder="Buscar por nombre o número" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button type="submit">Buscar</button>
                </form>

                <table class="payroll-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Número de Empleado</th>
                            <th>Departamento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($empleados as $empleado): ?>
                            <tr>
                                <td><?php echo $empleado['ID']; ?></td>
                                <td><?php echo $empleado['Nombre']; ?></td>
                                <td><?php echo $empleado['Numero_Empleado']; ?></td>
                                <td><?php echo $empleado['Departamento']; ?></td>
                                <td><a href="editar_empleado.php?id=<?php echo $empleado['ID']; ?>">Editar</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if (empty($empleados)): ?>
                    <p>No se encontraron empleados.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>