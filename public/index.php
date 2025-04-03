<?php
// index.php
include '../src/config/db.php';

// Función para contar empleados usando PDO
function contarEmpleados($pdo) {
    try {
        // Llamar al procedimiento almacenado
        $stmt = $pdo->query("CALL ContarEmpleados()");
        $result = $stmt->fetch(PDO::FETCH_ASSOC); 
        return $result['total_empleados']; 
    } catch (PDOException $e) {
        error_log("Error al contar empleados: " . $e->getMessage());
        return 0; 
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Nominas</title>
    <link rel="stylesheet" href="../public/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
            color: var(--text-color);
            background-color: var(--light-bg);
            line-height: 1.5;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar (mantenido intacto) */
        .sidebar {
            width: 250px;
            background-color: #1e293b;
            color: #ffffff;
            padding: 1rem;
            flex-shrink: 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        /* Contenido principal mejorado */
        .content {
            flex: 1;
            padding: 2rem 2.5rem;
            background-color: #ffffff;
            overflow-y: auto;
        }
        
        /* Header del contenido */
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }
        
        /* Reloj mejorado */
        .clock-widget {
            background: white;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 180px;
            transition: var(--transition);
        }
        
        .clock-widget:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .clock-title {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .clock-title a {
            color: inherit;
            text-decoration: none;
        }
        
        .clock-title a:hover {
            text-decoration: underline;
        }
        
        /* Tarjetas del dashboard mejoradas */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border-left: 4px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card.gray {
            border-color: #64748b;
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        }
        
        .stat-card.green {
            border-color: var(--success-color);
            background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 100%);
        }
        
        .stat-card.red {
            border-color: var(--danger-color);
            background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
        }
        
        .stat-card.blue {
            border-color: var(--secondary-color);
            background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin: 0.5rem 0;
            color: var(--primary-color);
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--text-light);
            margin: 0;
        }
        
        /* Sección de gráficas mejorada */
        .analytics-section {
            background: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }
        
        /* Filtros mejorados */
        .filters-container {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .filter-label {
            font-size: 0.85rem;
            color: var(--text-light);
            white-space: nowrap;
        }
        
        .flatpickr-input, .period-selector {
            padding: 0.6rem 0.75rem;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            font-size: 0.9rem;
            transition: var(--transition);
        }
        
        .flatpickr-input:focus, .period-selector:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        
        .btn {
            padding: 0.6rem 1.25rem;
            border-radius: 6px;
            border: none;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
        }
        
        /* Gráfica mejorada */
        .chart-container {
            margin-top: 1rem;
            position: relative;
            height: 400px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .content {
                padding: 1.5rem;
            }
            
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            
            .filters-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar (mantenido intacto) -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Nóminas</h2>
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
        
        <!-- Contenido principal mejorado -->
        <div class="content">
            <!-- Header con título y reloj -->
            <div class="content-header">
                <h1 class="page-title">Informe Diario</h1>
                <div class="clock-widget">
                    <span class="clock-title"><a href="https://www.zeitverschiebung.net/es/city/3981941">Hora en Tepic, MX</a></span>
                    <iframe src="https://www.zeitverschiebung.net/clock-widget-iframe-v2?language=es&size=small&timezone=America%2FMazatlan" 
                            width="160" height="90" frameborder="0" seamless title="Reloj de Tepic"></iframe>
                </div>
            </div>
            
            <!-- Tarjetas de estadísticas -->
            <div class="dashboard-cards">
                <div class="stat-card gray">
                    <div class="stat-value"><?php echo $totalEmpleados; ?></div>
                    <p class="stat-label">Empleados Totales</p>
                </div>
                
                <!-- Puedes agregar más tarjetas según necesites -->
                <!-- Ejemplo de tarjeta adicional (descomenta si la necesitas) -->
                <!--
                <div class="stat-card blue">
                    <div class="stat-value">24</div>
                    <p class="stat-label">Nuevos este mes</p>
                </div>
                -->
                
                <!--
                <div class="stat-card green">
                    <div class="stat-value">92%</div>
                    <p class="stat-label">Asistencia hoy</p>
                </div>
                -->
                
                <!--
                <div class="stat-card red">
                    <div class="stat-value">5</div>
                    <p class="stat-label">Faltas hoy</p>
                </div>
                -->
            </div>
            
            <!-- Sección de análisis -->
            <div class="analytics-section">
                <div class="section-header">
                    <h2 class="section-title">Informe de Faltas por Departamento</h2>
                    
                    <div class="filters-container">
                        <div class="filter-group">
                            <span class="filter-label">Rango de fechas:</span>
                            <input type="text" id="datePicker" class="flatpickr-input" placeholder="Seleccionar fechas">
                        </div>
                        
                        <div class="filter-group">
                            <span class="filter-label">Periodo:</span>
                            <select id="periodSelector" class="period-selector">
                                <option value="semanal">Semanal</option>
                                <option value="mensual">Mensual</option>
                                <option value="anual">Anual</option>
                            </select>
                        </div>
                        
                        <button id="generarEstadisticas" class="btn btn-primary">
                            <i class="fas fa-chart-line"></i> Generar
                        </button>
                    </div>
                </div>
                
                <div class="chart-container">
                    <canvas id="faltasChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inicializar Flatpickr en español
            let datePicker = flatpickr("#datePicker", {
                mode: 'range',
                dateFormat: "Y-m-d",
                defaultDate: "today",
                locale: "es",
                allowInput: true
            });

            // Colores para la gráfica
            const chartColors = [
                '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', 
                '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#d946ef'
            ];
            
            // Inicializar gráfica con opciones mejoradas
            const ctx = document.getElementById('faltasChart').getContext('2d');
            const faltasChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Faltas',
                        data: [],
                        backgroundColor: chartColors,
                        borderColor: '#ffffff',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.raw}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                color: '#e2e8f0'
                            },
                            ticks: {
                                color: '#64748b'
                            },
                            title: {
                                display: true,
                                text: 'Número de Faltas',
                                color: '#64748b',
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: '#64748b'
                            },
                            title: {
                                display: true,
                                text: 'Departamentos',
                                color: '#64748b',
                                font: {
                                    weight: 'bold'
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });

            // Función para actualizar la gráfica
            async function updateChart() {
                const selectedPeriod = document.getElementById('periodSelector').value;
                const selectedDates = datePicker.selectedDates;

                if (!selectedDates || selectedDates.length < 2) {
                    showAlert('Por favor, selecciona un rango de fechas válido', 'error');
                    return;
                }

                let fechaInicio, fechaFin;

                try {
                    if (selectedPeriod === 'semanal') {
                        const diffDays = Math.round((selectedDates[1] - selectedDates[0]) / (1000 * 60 * 60 * 24));
                        if (diffDays !== 6) {
                            showAlert('Selecciona exactamente 7 días para el periodo semanal', 'warning');
                            return;
                        }
                        fechaInicio = formatDate(selectedDates[0]);
                        fechaFin = formatDate(selectedDates[1]);
                    } else if (selectedPeriod === 'mensual') {
                        fechaInicio = selectedDates[0].toISOString().substring(0, 7) + '-01';
                        const endMonth = new Date(selectedDates[0]);
                        endMonth.setMonth(endMonth.getMonth() + 1);
                        endMonth.setDate(0);
                        fechaFin = formatDate(endMonth);
                    } else if (selectedPeriod === 'anual') {
                        fechaInicio = selectedDates[0].toISOString().substring(0, 4) + '-01-01';
                        fechaFin = selectedDates[0].toISOString().substring(0, 4) + '-12-31';
                    }

                    // Mostrar loader
                    document.getElementById('generarEstadisticas').innerHTML = 
                        '<i class="fas fa-spinner fa-spin"></i> Cargando...';

                    const response = await fetch(
                        `obtener_faltas_por_departamento.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`
                    );
                    
                    if (!response.ok) throw new Error('Error en la respuesta del servidor');
                    
                    const data = await response.json();

                    if (!Array.isArray(data) || data.length === 0) {
                        showAlert('No hay datos para el rango seleccionado', 'info');
                        return;
                    }

                    // Actualizar gráfica
                    faltasChart.data.labels = data.map(item => item.area);
                    faltasChart.data.datasets[0].data = data.map(item => item.total_faltas);
                    faltasChart.update();

                } catch (error) {
                    console.error('Error:', error);
                    showAlert('Error al obtener datos. Por favor, inténtalo de nuevo.', 'error');
                } finally {
                    // Restaurar botón
                    document.getElementById('generarEstadisticas').innerHTML = 
                        '<i class="fas fa-chart-line"></i> Generar';
                }
            }

            // Helper functions
            function formatDate(date) {
                return date.toISOString().split('T')[0];
            }

            function showAlert(message, type = 'info') {
                // Implementar un sistema de notificaciones bonito aquí
                alert(message); // Temporal
            }

            // Event listeners
            document.getElementById('periodSelector').addEventListener('change', function() {
                const mode = this.value === 'semanal' ? 'range' : 'single';
                datePicker.set('mode', mode);
            });

            document.getElementById('generarEstadisticas').addEventListener('click', updateChart);
        });
    </script>
</body>
</html>