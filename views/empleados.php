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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Empleados | Sistema de Nómina</title>
    <link rel="stylesheet" href="../public/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/all.min.css" rel="stylesheet">
    <style>
        /* Estilos generales */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            color: #334155;
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar (consistente con el diseño anterior) */
        .sidebar {
            width: 250px;
            background-color: #1e293b;
            color: #ffffff;
            padding: 1rem;
            flex-shrink: 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        .sidebar-header {
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1.5rem;
        }
        .sidebar h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ffffff;
        }
        .sidebar-section {
            margin-bottom: 1.5rem;
        }
        .sidebar-section h3 {
            font-size: 0.85rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            padding: 0.5rem 1rem;
            background-color: rgba(0, 38, 63, 0.5);
            border-radius: 4px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar ul li {
            margin: 0.25rem 0;
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
            font-size: 0.95rem;
        }
        .sidebar ul li a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .sidebar ul li a.active {
            background: rgba(5, 56, 90, 0.8);
            color: white;
            font-weight: 500;
        }
        .sidebar ul li a i {
            font-size: 1rem;
            width: 20px;
            text-align: center;
        }
        
        /* Contenido principal */
        .main-content {
            flex: 1;
            padding: 2rem;
            background-color: #fff;
        }
        
        .content-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .section-header {
            margin-bottom: 2rem;
        }
        
        .section-title {
            color: #00263F;
            font-size: 1.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .section-title i {
            color: #3b82f6;
        }
        
        /* Formulario de búsqueda */
        .search-form {
            margin-bottom: 2rem;
            display: flex;
            gap: 10px;
            max-width: 600px;
        }
        
        .search-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .search-button {
            background-color: #3b82f6;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .search-button:hover {
            background-color: #2563eb;
        }
        
        /* Tabla de empleados */
        .payroll-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .payroll-table th {
            background-color: #00263F;
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 500;
        }
        
        .payroll-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        
        .payroll-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .payroll-table tr:hover {
            background-color: #f1f5f9;
        }
        
        /* Botones de acción */
        .action-button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .edit-button {
            background-color: #3b82f6;
            color: white;
        }
        
        .edit-button:hover {
            background-color: #2563eb;
        }
        
        /* Mensaje cuando no hay empleados */
        .empty-message {
            padding: 1.5rem;
            background-color: #f1f5f9;
            border-radius: 6px;
            text-align: center;
            color: #64748b;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                padding: 1rem 0;
            }
            
            .main-content {
                padding: 1.5rem;
            }
            
            .payroll-table {
                display: block;
                overflow-x: auto;
            }
            
            .search-form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Nóminas</h2>
        </div>

        <!-- Sección: Informes -->
        <div class="sidebar-section">
            <h3>Informes</h3>
            <ul>
                <li><a href="../public/index.php"><i class="fas fa-chart-bar"></i> Resumen</a></li>
            </ul>
        </div>

        <!-- Sección: Gestionar -->
        <div class="sidebar-section">
            <h3>Gestionar</h3>
            <ul>
                <li><a href="../views/checadas.php"><i class="fas fa-calendar-alt"></i> Asistencia</a></li>
                <li><a href="../views/empleados.php" class="active"><i class="fas fa-users"></i> Empleados</a></li>
                <li><a href="../views/calculo.php"><i class="fas fa-calculator"></i> Deducciones</a></li>
                <li><a href="../views/bonos.php"><i class="fas fa-gift"></i> Bonos</a></li>
                <li><a href="../views/roles.php"><i class="fas fa-briefcase"></i> Cargos</a></li>
                <li><a href="../views/importar.php"><i class="fas fa-file-import"></i> Importar datos</a></li>
            </ul>
        </div>

        <!-- Sección: Imprimibles -->
        <div class="sidebar-section">
            <h3>Imprimibles</h3>
            <ul>
                <li><a href="../views/reportes.php"><i class="fas fa-file-alt"></i> Reportes PDF</a></li>
            </ul>
        </div>
    </div>
    
    <div class="main-content">
        <div class="content-container">
            <div class="section-header">
                <h1 class="section-title"><i class="fas fa-users"></i> Lista de Empleados</h1>
            </div>

            <!-- Formulario de búsqueda mejorado -->
            <form method="GET" action="empleados.php" class="search-form">
                <input type="text" name="search" class="search-input" placeholder="Buscar por nombre o número de empleado..." value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
                <button type="submit" class="search-button">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </form>

            <!-- Tabla de empleados mejorada -->
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
                    <?php if (!empty($empleados)): ?>
                        <?php foreach ($empleados as $empleado): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($empleado['ID'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($empleado['Nombre'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($empleado['Numero_Empleado'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($empleado['Departamento'] ?? ''); ?></td>
                                <td>
                                    <a href="editar_empleado.php?id=<?php echo $empleado['ID']; ?>" class="action-button edit-button">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-message">
                                    <i class="fas fa-user-slash"></i> No se encontraron empleados
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>