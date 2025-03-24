<?php
// index.php
include '../src/config/db.php';

// Función para contar empleados usando PDO
function contarEmpleados($pdo) {
    try {
        // Llamar al procedimiento almacenado
        $stmt = $pdo->query("CALL ContarEmpleados()");
        $result = $stmt->fetch(PDO::FETCH_ASSOC); // Obtener el resultado
        return $result['total_empleados']; // Devolver el número de empleados
    } catch (PDOException $e) {
        // Manejar errores
        error_log("Error al contar empleados: " . $e->getMessage());
        return 0; // En caso de error, devolvemos 0
    }
}

function obtenerFaltasPorDepartamento($pdo, $fechaInicio, $fechaFin) {
    try {
        // Llamar al procedimiento almacenado
        $stmt = $pdo->prepare("CALL ObtenerFaltasPorDepartamento(:fecha_inicio, :fecha_fin)");
        $stmt->execute([
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ]);

        // Obtener los resultados
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultados;
    } catch (PDOException $e) {
        error_log("Error al obtener faltas por departamento: " . $e->getMessage());
        return [];
    }
}

// Función para contar empleados que faltaron hoy
// function contarFaltasHoy($pdo) {
//     try {
//         $fechaHoy = date('Y-m-d'); // Obtener la fecha actual

//         // Consulta SQL para contar empleados únicos que faltaron hoy
//         $sql = "SELECT COUNT(DISTINCT Numero_Empleado) AS total_faltas 
//                 FROM faltas 
//                 WHERE fecha = :fecha";
        
//         // Preparar y ejecutar la consulta
//         $stmt = $pdo->prepare($sql);
//         $stmt->execute(['fecha' => $fechaHoy]);

//         // Obtener el resultado
//         $result = $stmt->fetch(PDO::FETCH_ASSOC);

//         // Devolver el número de faltas
//         return $result['total_faltas'] ?? 0; // Si no hay resultado, devolver 0
//     } catch (PDOException $e) {
//         error_log("Error al contar faltas: " . $e->getMessage());
//         return 0; // En caso de error, devolver 0
//     }
// }

// Obtener los datos
$totalEmpleados = contarEmpleados($pdo);
// $faltasHoy = contarFaltasHoy($pdo);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../public/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- FontAwesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Estilos generales */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            display: flex;
            min-height: 100vh;
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

        /* Contenido principal */
        .content {
            flex: 1;
            padding: 2rem;
            background-color: #ffffff;
        }

        /* Tarjetas del dashboard */
        .dashboard {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .card {
            flex: 1;
            padding: 1.5rem;
            border-radius: 8px;
            color: #fff;
            text-align: center;
            font-size: 1.2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card.gray { background: #64748b; }
        .card.green { background: #16a34a; }
        .card.orange { background: #f97316; }
        .card.red { background: #dc2626; }

        /* Filtros y gráfica */
        .filters-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: center;
        }
        .flatpickr-input {
            padding: 0.5rem;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 1rem;
            width: 200px;
        }
        .period-selector {
            padding: 0.5rem;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }
        .chart-container {
            margin-top: 2rem;
            background: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-generar {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            border: none;
            background-color: #16a34a;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .btn-generar:hover {
            background-color: #15803d;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Nominas</h2>
            </div>

            <!-- Sección: Informes -->
            <div class="sidebar-section">
                <h3>Informes</h3>
                <ul>
                    <li><a href="#" class="active"><i class="fas fa-chart-bar"></i> Resumen</a></li>
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

        <!-- Contenido principal -->
        <div class="content">
            <!-- Tarjetas del dashboard -->
            <h1>Informe Diario</h1>
            <div class="dashboard">
                <div class="card gray"><?php echo $totalEmpleados; ?><br>Empleados Totales</div>
                <!-- Se elimino esta funcion debido a que no se logra hacer funcionar, esto se pondra en la seccion de Reportes -->
                <!-- <div class="card red"><?php echo $faltasHoy; ?><br>Faltas</div> -->
            </div>

            <!-- Filtros y gráfica -->
            <div class="chart-container">
            <h3>Informe de Faltas por Departamento</h3>
                <div class="filters-container">
                    <input type="text" id="datePicker" class="flatpickr-input" placeholder="Selecciona una fecha">
                    <select id="periodSelector" class="period-selector">
                        <option value="semanal">Semanal</option>
                        <option value="mensual">Mensual</option>
                        <option value="anual">Anual</option>
                    </select>
                    <button id="generarEstadisticas" class="btn-generar">Generar Estadísticas</button>
                </div>
            <!-- Asegúrate de que este canvas tenga el ID "faltasChart" -->
                <canvas id="faltasChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
        // Inicializar Flatpickr para selección de rango
        let datePicker = flatpickr("#datePicker", {
            mode: 'range', // Permitir selección de rango
            dateFormat: "Y-m-d",
            defaultDate: "today",
        });

        // Inicializar la gráfica (con colores variados)
        const colores = [
            '#dc2626', '#2563eb', '#16a34a', '#d97706', '#9333ea', '#f43f5e', '#0d9488', '#f59e0b'
        ];
        const ctx = document.getElementById('faltasChart').getContext('2d');
        const faltasChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [], // Inicialmente vacío
                datasets: [{
                    label: 'Faltas',
                    data: [], // Inicialmente vacío
                    backgroundColor: colores, // Usar el array de colores
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Faltas'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Departamentos'
                        }
                    }
                }
            }
        });

        // Función para actualizar la gráfica
        function updateChart() {
            const selectedPeriod = periodSelector.value;
            const selectedDates = datePicker.selectedDates;

            if (!selectedDates || selectedDates.length < 2) {
                alert("Por favor, selecciona un rango de fechas.");
                return;
            }

            let fechaInicio, fechaFin;

            if (selectedPeriod === 'semanal') {
                // Asegurarse de que el rango seleccionado sea exactamente de 7 días
                const diferenciaDias = (selectedDates[1] - selectedDates[0]) / (1000 * 60 * 60 * 24);
                if (diferenciaDias !== 6) {
                    alert("Por favor, selecciona un rango de exactamente 7 días (una semana).");
                    return;
                }
                fechaInicio = selectedDates[0].toISOString().split('T')[0]; // Fecha inicial
                fechaFin = selectedDates[1].toISOString().split('T')[0]; // Fecha final
            } else if (selectedPeriod === 'mensual') {
                // Seleccionar el primer y último día del mes
                fechaInicio = selectedDates[0].toISOString().substring(0, 7) + '-01'; // Primer día del mes
                const finMes = new Date(selectedDates[0]);
                finMes.setMonth(finMes.getMonth() + 1);
                finMes.setDate(0); // Último día del mes
                fechaFin = finMes.toISOString().split('T')[0]; // Formatear como YYYY-MM-DD
            } else if (selectedPeriod === 'anual') {
                // Seleccionar el primer y último día del año
                fechaInicio = selectedDates[0].toISOString().substring(0, 4) + '-01-01'; // Primer día del año
                fechaFin = selectedDates[0].toISOString().substring(0, 4) + '-12-31'; // Último día del año
            }

            // Obtener datos de faltas por departamento desde el servidor (usando AJAX)
            fetch(`obtener_faltas_por_departamento.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`)
                .then(response => response.json())
                .then(data => {
                    console.log("Datos recibidos del servidor:", data); // Depuración

                    // Verificar si data es un array
                    if (Array.isArray(data)) {
                        // Verificar si hay datos
                        if (data.length > 0) {
                            // Asignar los labels y los datos a la gráfica
                            faltasChart.data.labels = data.map(item => item.area); // Cambiar 'departamento' por 'area'
                            faltasChart.data.datasets[0].data = data.map(item => item.total_faltas);

                            console.log("Labels:", faltasChart.data.labels); // Depuración
                            console.log("Datos:", faltasChart.data.datasets[0].data); // Depuración

                            // Actualizar la gráfica
                            faltasChart.update();
                        } else {
                            console.warn("No hay datos para el rango de fechas seleccionado."); // Depuración
                            alert("No hay datos para el rango de fechas seleccionado.");
                        }
                    } else {
                        console.error("Error: La respuesta del servidor no es un array.", data); // Depuración
                        alert("Error al obtener los datos. Por favor, inténtalo de nuevo.");
                    }
                })
                .catch(error => {
                    console.error("Error al obtener datos de faltas:", error); // Depuración
                    alert("Error al obtener los datos. Por favor, inténtalo de nuevo.");
                });
        }

        // Seleccionar período
        const periodSelector = document.getElementById('periodSelector');
        periodSelector.addEventListener('change', () => {
            const selectedPeriod = periodSelector.value;

            if (selectedPeriod === 'semanal') {
                datePicker.set('mode', 'range'); // Cambiar a modo rango
                datePicker.set('dateFormat', 'Y-m-d');
            } else if (selectedPeriod === 'mensual') {
                datePicker.set('mode', 'single'); // Cambiar a modo single para seleccionar un mes
                datePicker.set('dateFormat', 'Y-m');
            } else if (selectedPeriod === 'anual') {
                datePicker.set('mode', 'single'); // Cambiar a modo single para seleccionar un año
                datePicker.set('dateFormat', 'Y');
            }
        });

        // Botón para generar estadísticas
        const generarEstadisticasBtn = document.getElementById('generarEstadisticas');
        generarEstadisticasBtn.addEventListener('click', () => {
            updateChart();
        });
    });
    </script>
</body>
</html>