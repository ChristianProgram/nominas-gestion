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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empleado | Sistema de Nómina</title>
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
            max-width: 800px;
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
        
        /* Formulario de edición */
        .edit-form {
            background: #fff;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #00263F;
        }
        
        input[type="text"], 
        input[type="number"],
        select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        input[type="text"]:disabled,
        input[type="number"]:disabled {
            background-color: #f8fafc;
            color: #64748b;
            border-color: #e2e8f0;
        }
        
        input[type="text"]:focus, 
        input[type="number"]:focus,
        select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
        }
        
        /* Botones */
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            border: none;
        }
        
        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
        }
        
        .btn-secondary {
            background-color: #e2e8f0;
            color: #334155;
        }
        
        .btn-secondary:hover {
            background-color: #cbd5e1;
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
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
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
                <h1 class="section-title"><i class="fas fa-user-edit"></i> Editar Empleado</h1>
            </div>

            <form method="POST" action="editar_empleado.php?id=<?php echo htmlspecialchars($id); ?>" class="edit-form">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($empleado['Nombre'] ?? ''); ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label for="numero_empleado">Número de Empleado:</label>
                    <input type="text" id="numero_empleado" name="numero_empleado" value="<?php echo htmlspecialchars($empleado['Numero_Empleado'] ?? ''); ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label for="departamento">Cargo:</label>
                    <select id="departamento" name="departamento" required>
                        <option value="">Seleccione un cargo</option>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?php echo htmlspecialchars($rol['id'] ?? ''); ?>" <?php echo (isset($empleado['Departamento']) && $rol['id'] == $empleado['Departamento']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($rol['nombre_cargo'] ?? ''); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                    <a href="empleados.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>