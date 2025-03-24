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
    <title>Roles</title>
    <link rel="stylesheet" href="../public/styles.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Estilos para la tabla */
        .roles-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .roles-table th, .roles-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
        .roles-table th {
            background-color: #f2f2f2;
        }
        /* Estilos para el formulario */
        .form-agregar-rol {
            margin-bottom: 20px;
        }
        .form-agregar-rol input {
            padding: 5px;
            margin-right: 10px;
        }
        .form-agregar-rol button {
            padding: 5px 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        .form-agregar-rol button:hover {
            background-color: #45a049;
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
                    <h1 class="section-title">Roles</h1>
                </div>

                <!-- Formulario para agregar un nuevo rol -->
                <form method="POST" action="roles.php" class="form-agregar-rol">
                    <input type="text" name="nombre_cargo" placeholder="Nombre del cargo" required>
                    <input type="number" name="sueldo_diario" step="0.01" placeholder="Sueldo diario" required>
                    <button type="submit" name="agregar_rol">Agregar Rol</button>
                </form>

                <!-- Tabla de Roles -->
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
                        <?php foreach ($roles as $rol): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($rol['id']); ?></td>
                                <td><?php echo htmlspecialchars($rol['nombre_cargo']); ?></td>
                                <td>$<?php echo number_format($rol['sueldo_diario'], 2); ?></td>
                                <td>
                                    <a href="roles.php?eliminar=<?php echo $rol['id']; ?>" onclick="return confirm('¿Estás seguro de eliminar este rol?');">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>