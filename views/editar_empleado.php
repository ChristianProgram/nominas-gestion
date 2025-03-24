<?php
error_reporting(E_ALL); // Mostrar todos los errores
ini_set('display_errors', 1); // Mostrar errores en pantalla

include '../src/config/db.php';

try {
    if (!isset($_GET['id'])) {
        die("ID de empleado no proporcionado.");
    }

    $id = $_GET['id'];

    // Obtener datos del empleado usando el procedimiento almacenado
    $sqlEmpleado = "CALL ObtenerEmpleadoPorID(:id)";
    $stmtEmpleado = $pdo->prepare($sqlEmpleado);
    $stmtEmpleado->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtEmpleado->execute();
    $empleado = $stmtEmpleado->fetch(PDO::FETCH_ASSOC);

    if (!$empleado) {
        die("Empleado no encontrado.");
    }

    // Cerrar el cursor del primer conjunto de resultados
    $stmtEmpleado->closeCursor();

    // Obtener la lista de cargos (roles) usando el procedimiento almacenado
    $sqlRoles = "CALL ObtenerRoles()";
    $stmtRoles = $pdo->query($sqlRoles);
    $roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

    // Cerrar el cursor del segundo conjunto de resultados
    $stmtRoles->closeCursor();

    // Actualizar cargo (rol) usando el procedimiento almacenado
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!isset($_POST['departamento'])) {
            die("Cargo no proporcionado.");
        }

        $cargo = (int)$_POST['departamento']; // Convertir a entero

        $sqlUpdate = "CALL ActualizarDepartamentoEmpleado(:id, :cargo)";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtUpdate->bindParam(':cargo', $cargo, PDO::PARAM_INT);
        $stmtUpdate->execute();

        header("Location: empleados.php"); // Redirigir a la lista de empleados
        exit();
    }
} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
} catch (Exception $e) {
    die("Error general: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Empleado</title>
    <link rel="stylesheet" href="../public/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
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
                    <h1 class="section-title">Editar Empleado</h1>
                </div>

                <form method="POST" action="editar_empleado.php?id=<?php echo $id; ?>">
                    <div class="form-group">
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" value="<?php echo htmlspecialchars($empleado['Nombre']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="numero_empleado">Número de Empleado:</label>
                        <input type="text" id="numero_empleado" value="<?php echo htmlspecialchars($empleado['Numero_Empleado']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="departamento">Cargo:</label>
                        <select id="departamento" name="departamento" required>
                            <option value="">Seleccione un cargo</option>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?php echo $rol['id']; ?>" <?php echo ($rol['id'] == $empleado['Departamento']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($rol['nombre_cargo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <a href="empleados.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>