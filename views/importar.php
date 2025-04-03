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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Datos | Sistema de Nómina</title>
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
        
        /* Sidebar (mejoras adicionales) */
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
        .content {
            flex: 1;
            padding: 2.5rem;
            background-color: #fff;
        }
        
        h1 {
            color: #00263F;
            margin-bottom: 1.5rem;
            font-size: 2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        h1 i {
            color: #3b82f6;
        }
        
        p {
            color: #64748b;
            margin-bottom: 2rem;
            font-size: 1.05rem;
            max-width: 800px;
            line-height: 1.6;
        }
        
        /* Tarjeta del formulario */
        .form-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            max-width: 800px;
            border: 1px solid #e2e8f0;
        }
        
        /* Estilos para el formulario */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 500;
            color: #00263F;
            font-size: 1rem;
        }
        
        .file-input-container {
            position: relative;
            width: 100%;
        }
        
        .file-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px dashed #cbd5e1;
            border-radius: 6px;
            background-color: #f8fafc;
            color: #475569;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .file-input:hover {
            border-color: #94a3b8;
            background-color: #f1f5f9;
        }
        
        .file-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        /* Botón de importar */
        .boton-importar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background-color: #3b82f6;
            color: white;
            border: none;
            padding: 0.75rem 1.75rem;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 1rem;
        }
        
        .boton-importar:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .boton-importar:active {
            transform: translateY(0);
        }
        
        /* Mensajes de resultado */
        .mensaje {
            margin-top: 1.5rem;
            padding: 1rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            border: 1px solid transparent;
        }
        
        .mensaje-exito {
            background-color: #ecfdf5;
            color: #065f46;
            border-color: #a7f3d0;
        }
        
        .mensaje-error {
            background-color: #fef2f2;
            color: #b91c1c;
            border-color: #fecaca;
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
            
            .content {
                padding: 1.5rem;
            }
            
            .sidebar-section {
                margin-bottom: 1rem;
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
    
    <div class="content">
        <h1><i class="fas fa-file-import"></i> Importar Datos</h1>
        <p>Sube un archivo CSV para importar los datos de nómina a la base de datos. El archivo debe contener los campos requeridos en el formato especificado.</p>
        
        <div class="form-card">
            <form action="importar.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="archivo">Selecciona un archivo CSV:</label>
                    <div class="file-input-container">
                        <input type="file" name="archivo" id="archivo" class="file-input" accept=".csv" required>
                    </div>
                </div>
                
                <button type="submit" class="boton-importar">
                    <i class="fas fa-upload"></i> Importar Datos
                </button>
            </form>
            
            <?php if (!empty($mensaje)): ?>
                <div class="mensaje <?php echo strpos($mensaje, 'correctamente') !== false ? 'mensaje-exito' : 'mensaje-error'; ?>">
                    <i class="fas <?php echo strpos($mensaje, 'correctamente') !== false ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo strip_tags($mensaje); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>