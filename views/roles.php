<?php
error_reporting(E_ALL); // Mostrar todos los errores
ini_set('display_errors', 1); // Mostrar errores en pantalla

include '../src/config/db.php'; // Asegúrate de que la ruta sea correcta

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
    <link rel="stylesheet" href="../public/styles.css"> <!-- Ajusta la ruta del CSS -->
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
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>📊 Menú</h2>
            </div>
            <ul>
                <li><a href="checadas.php" class="active">🕒 Checadas</a></li>
                <li><a href="bonos.php" class="active">💰 Bonos</a></li>
                <li><a href="empleados.php">👨‍💼 Personal</a></li>
                <li><a href="calculo.php">📉 Cálculo</a></li>
                <li><a href="roles.php">🏆 Cargos</a></li>
                <li><a href="importar.php">📂 Importar</a></li>
            </ul>
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