<?php
// Mostrar errores de PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../src/config/db.php'; 

$mensaje = ""; // Mensaje para mostrar el resultado de la importación

// Verificar si se ha enviado un archivo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["archivo"])) {
    $archivo = $_FILES["archivo"]["tmp_name"];
    $nombreArchivo = $_FILES["archivo"]["name"];
    $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

    // Procesar solo archivos CSV
    if ($extension == "csv") {
        if (is_uploaded_file($archivo) && ($handle = fopen($archivo, "r")) !== FALSE) {
            try {
                // Iniciar transacción
                $pdo->beginTransaction();

                // Ignorar la primera fila (encabezados)
                fgetcsv($handle, 1000, ",");

                // Preparar la consulta para nominasueldo
                $queryNomina = "INSERT INTO nominasueldo (
                    numero, nombre, ordinario, septimo_dia, horas_extras, vacaciones, 
                    prima_vac, prim_dom, bono_prod, dia_fest, bono_asist, incapacidad, nomina_total
                ) VALUES (
                    :numero, :nombre, :ordinario, :septimo_dia, :horas_extras, :vacaciones, 
                    :prima_vac, :prim_dom, :bono_prod, :dia_fest, :bono_asist, :incapacidad, :nomina_total
                )";
                $stmt = $pdo->prepare($queryNomina);

                // Preparar la consulta para empleados
                $queryEmpleados = "INSERT INTO empleados (Numero_Empleado, Nombre) VALUES (:numero_empleado, :nombre_empleado)";
                $stmtEmpleados = $pdo->prepare($queryEmpleados);

                // Insertar cada fila en las tablas correspondientes
                while (($fila = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    // Insertar en nominasueldo
                    $stmt->execute([
                        ':numero'       => $fila[0] ?? null,
                        ':nombre'       => $fila[1] ?? null,
                        ':ordinario'    => $fila[2] ?? null,
                        ':septimo_dia'  => $fila[3] ?? null,
                        ':horas_extras' => $fila[4] ?? null,
                        ':vacaciones'   => $fila[5] ?? null,
                        ':prima_vac'    => $fila[6] ?? null,
                        ':prim_dom'     => $fila[7] ?? null,
                        ':bono_prod'    => $fila[8] ?? null,
                        ':dia_fest'     => $fila[9] ?? null,
                        ':bono_asist'   => $fila[10] ?? null,
                        ':incapacidad'  => $fila[11] ?? null,
                        ':nomina_total' => $fila[12] ?? null,
                    ]);

                    // Insertar en empleados
                    $stmtEmpleados->execute([
                        ':numero_empleado' => $fila[0] ?? null,
                        ':nombre_empleado' => $fila[1] ?? null,
                    ]);
                }

                // Confirmar transacción
                $pdo->commit();
                $mensaje = "<p style='color:green;'>Datos importados correctamente.</p>";
            } catch (PDOException $e) {
                // Revertir cambios en caso de error
                $pdo->rollBack();
                $mensaje = "<p style='color:red;'>Error al importar los datos: " . $e->getMessage() . "</p>";
            } finally {
                fclose($handle);
            }
        } else {
            $mensaje = "<p style='color:red;'>No se pudo abrir el archivo CSV.</p>";
        }
    } else {
        $mensaje = "<p style='color:red;'>Solo se permiten archivos CSV.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Importar Datos</title>
    <link rel="stylesheet" href="../public/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/all.min.css" rel="stylesheet">
    <style>
        /* Estilo para el botón de importar */
        .boton-importar {
            margin-top: 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .boton-importar:hover {
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
        <div class="content">
            <h1>Importar Datos</h1>
            <p>Sube un archivo CSV para importar los datos a la base de datos.</p>

            <!-- Formulario para cargar el archivo -->
            <form action="importar.php" method="post" enctype="multipart/form-data">
                <label for="archivo">Selecciona un archivo CSV:</label>
                <input type="file" name="archivo" id="archivo" accept=".csv" required>
                <br><br>
                <button type="submit" class="boton-importar">Importar Datos</button>
            </form>

            <!-- Mensaje de resultado de la importación -->
            <?php if (!empty($mensaje)): ?>
                <div><?php echo $mensaje; ?></div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
