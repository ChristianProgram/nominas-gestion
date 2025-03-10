<?php
include '../src/config/db.php'; // Asegúrate de incluir la conexión a la base de datos

// Procesar el formulario para agregar un nuevo rol
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agregar_rol'])) {
    $nombreCargo = $_POST['nombre_cargo'];
    $sueldoDiario = $_POST['sueldo_diario'];

    $sql = "INSERT INTO roles (nombre_cargo, sueldo_diario) VALUES (:nombre_cargo, :sueldo_diario)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre_cargo' => $nombreCargo,
        ':sueldo_diario' => $sueldoDiario
    ]);
}

// Procesar la eliminación de un rol
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $sql = "DELETE FROM roles WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
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
            <h2>Menú</h2>
            <ul>
                <li><a href="checadas.php">Checadas</a></li>
                <li><a href="empleados.php">Personal</a></li>
                <li><a href="calculo.php">Cálculo</a></li>
                <li><a href="roles.php">Cargos</a></li>
                <li><a href="importar.php">Importar</a></li>
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
                                    <a href="editar_rol.php?id=<?php echo $rol['id']; ?>">Editar</a>
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