<?php
error_reporting(E_ALL); // Mostrar todos los errores
ini_set('display_errors', 1); // Mostrar errores en pantalla

include '../src/config/db.php'; 

// Procesar el formulario para agregar un nuevo rol
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agregar_rol'])) {
    $nombreCargo = $_POST['nombre_cargo'];
    $sueldoDiario = $_POST['sueldo_diario'];

    try {
        // Llamar al procedimiento almacenado
        $sql = "CALL InsertarRol(:nombre_cargo, :sueldo_diario)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nombre_cargo', $nombreCargo, PDO::PARAM_STR);
        $stmt->bindParam(':sueldo_diario', $sueldoDiario, PDO::PARAM_STR);
        $stmt->execute();

        echo "<script>alert('Rol agregado correctamente.'); window.location.href = 'roles.php';</script>";
    } catch (PDOException $e) {
        die("Error al agregar el rol: " . $e->getMessage());
    }
}

// Procesar la eliminación de un rol
if (isset($_GET['eliminar'])) {
    $rol_id = $_GET['eliminar'];

    try {
        // Llamar al procedimiento almacenado para eliminar el rol
        $sql = "CALL EliminarRol(:rol_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':rol_id', $rol_id, PDO::PARAM_INT);
        $stmt->execute();

        echo "<script>alert('Rol eliminado correctamente.'); window.location.href = 'roles.php';</script>";
    } catch (PDOException $e) {
        die("Error al eliminar el rol: " . $e->getMessage());
    }
}

// Obtener todos los roles
$sql = "SELECT * FROM roles";
$stmt = $pdo->query($sql);
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles | Sistema de Nómina</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #00263F;
            --secondary-color: #3b82f6;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --light-bg: #f8fafc;
            --border-color: #e2e8f0;
            --text-color: #334155;
            --text-light: #64748b;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
            background-color: var(--light-bg);
        }
        
        /* Sidebar */
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
            overflow-x: auto;
        }
        
        .section-header {
            margin-bottom: 1.5rem;
        }
        
        .section-title {
            color: var(--primary-color);
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        /* Formulario */
        .form-container {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .form-row {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        /* Botones */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
        }
        
        .btn-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #0f9e6e;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #dc2626;
        }
        
        /* Tabla */
        .roles-table-container {
            overflow-x: auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .roles-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .roles-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 500;
            position: sticky;
            top: 0;
        }
        
        .roles-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }
        
        .roles-table tr:nth-child(even) {
            background-color: var(--light-bg);
        }
        
        .roles-table tr:hover {
            background-color: #f1f5f9;
        }
        
        .sueldo-cell {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .action-links {
            display: flex;
            gap: 0.5rem;
        }
        
        /* Mensaje cuando no hay roles */
        .empty-message {
            padding: 1.5rem;
            background-color: #f1f5f9;
            border-radius: 6px;
            text-align: center;
            color: var(--text-light);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                padding: 1rem;
            }
            
            .main-content {
                padding: 1.5rem;
            }
            
            .form-row {
                flex-direction: column;
            }
            
            .form-group {
                width: 100%;
            }
            
            .action-links {
                flex-direction: column;
                gap: 0.5rem;
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
                <li><a href="../views/empleados.php"><i class="fas fa-users"></i> Empleados</a></li>
                <li><a href="../views/calculo.php"><i class="fas fa-calculator"></i> Deducciones</a></li>
                <li><a href="../views/bonos.php"><i class="fas fa-gift"></i> Bonos</a></li>
                <li><a href="../views/roles.php" class="active"><i class="fas fa-briefcase"></i> Cargos</a></li>
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
        <div class="section-header">
            <h1 class="section-title"><i class="fas fa-briefcase"></i> Roles y Cargos</h1>
        </div>

        <!-- Formulario para agregar un nuevo rol -->
        <form method="POST" action="roles.php" class="form-container">
            <h3 style="margin-top: 0; margin-bottom: 1rem;">Agregar Nuevo Cargo</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="nombre_cargo" class="form-label">Nombre del Cargo</label>
                    <input type="text" id="nombre_cargo" name="nombre_cargo" class="form-input" placeholder="Ej. Gerente de Ventas" required>
                </div>
                
                <div class="form-group">
                    <label for="sueldo_diario" class="form-label">Sueldo Diario</label>
                    <input type="number" id="sueldo_diario" name="sueldo_diario" class="form-input" step="0.01" min="0" placeholder="0.00" required>
                </div>
                
                <div class="form-group" style="align-self: flex-end;">
                    <button type="submit" name="agregar_rol" class="btn btn-success">
                        <i class="fas fa-plus"></i> Agregar Rol
                    </button>
                </div>
            </div>
        </form>

        <!-- Tabla de Roles -->
        <div class="roles-table-container">
            <table class="roles-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Cargo</th>
                        <th>Sueldo Diario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($roles)): ?>
                        <?php foreach ($roles as $rol): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($rol['id']); ?></td>
                                <td><?php echo htmlspecialchars($rol['nombre_cargo']); ?></td>
                                <td class="sueldo-cell">$<?php echo number_format($rol['sueldo_diario'], 2); ?></td>
                                <td>
                                    <div class="action-links">
                                        <a href="roles.php?eliminar=<?php echo $rol['id']; ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('¿Estás seguro de eliminar este rol?');">
                                            <i class="fas fa-trash-alt"></i> Eliminar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">
                                <div class="empty-message">
                                    <i class="fas fa-briefcase" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                                    <p>No se han registrado roles aún</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Confirmación antes de eliminar
        function confirmarEliminacion(e) {
            if (!confirm('¿Estás seguro de eliminar este rol? Esta acción no se puede deshacer.')) {
                e.preventDefault();
            }
        }

        // Asignar eventos de confirmación a todos los enlaces de eliminación
        document.querySelectorAll('a[onclick*="confirm"]').forEach(link => {
            link.addEventListener('click', confirmarEliminacion);
        });
    </script>
</body>
</html>