<?php
include '../src/config/db.php';

// Obtener parámetros
$fecha = $_GET['fecha'] ?? '';
$departamento = $_GET['departamento'] ?? '';

// Validar fecha
if (empty($fecha) || !DateTime::createFromFormat('Y-m-d', $fecha)) {
    die("Fecha no válida");
}

// Obtener datos del reporte
try {
    $sql = "CALL FiltroReporteAsistenciaSemanal(:fecha)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Filtrar por departamento si se especificó
    if (!empty($departamento)) {
        $resultados = array_filter($resultados, function($item) use ($departamento) {
            return $item['Departamento'] === $departamento;
        });
    }
    
    // Calcular total general
    $totalGeneral = array_sum(array_column($resultados, 'Total Asistencias'));
    
    // Calcular fechas de la semana
    $fechaInicio = new DateTime($fecha);
    $fechaInicio->modify('monday this week');
    $fechaFin = clone $fechaInicio;
    $fechaFin->modify('sunday this week');
    
} catch (PDOException $e) {
    die("Error al generar reporte: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Semanal de Asistencias</title>
    <style>
        /* Estilos base optimizados para impresión */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
            color: #000;
            line-height: 1.4;
            font-size: 12pt;
        }
        
        .reporte-container {
            max-width: 100%;
            margin: 0 auto;
            border: 1px solid #ddd;
        }
        
        .header {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
            position: relative;
        }
        
        .header h1 {
            margin: 10px 0 5px;
            font-size: 18pt;
            font-weight: bold;
        }
        
        .logo {
            height: 40px;
            margin-bottom: 5px;
        }
        
        .info-reporte {
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
            font-size: 11pt;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
        }
        
        th {
            background-color: #f2f2f2;
            padding: 8px 10px;
            text-align: left;
            border-bottom: 2px solid #ddd;
            font-weight: bold;
        }
        
        td {
            padding: 6px 10px;
            border-bottom: 1px solid #eee;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #f2f2f2;
        }
        
        .footer {
            padding: 10px;
            text-align: center;
            font-size: 10pt;
            color: #555;
            border-top: 1px solid #ddd;
        }
        
        /* Estilos específicos para impresión */
        @media print {
            body {
                padding: 0;
                font-size: 10pt;
            }
            
            .reporte-container {
                border: none;
            }
            
            .header {
                padding: 10px;
            }
            
            .logo {
                height: 35px;
            }
            
            .no-print {
                display: none;
            }
            
            table {
                page-break-inside: avoid;
            }
            
            th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background-color: #f2f2f2 !important;
            }
            
            .total-row {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background-color: #f2f2f2 !important;
                color: #000 !important;
            }
        }
        
        /* Estilos para pantalla */
        @media screen {
            .reporte-container {
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                border-radius: 5px;
            }
            
            .print-btn {
                background: #3498db;
                color: white;
                border: none;
                padding: 8px 15px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
                margin: 15px auto;
                display: block;
            }
            
            .print-btn:hover {
                background: #2980b9;
            }
        }
    </style>
</head>
<body>
    <div class="reporte-container">
        <div class="header">
            <img src="../src/config/components/assets/logo_avion.png" alt="Logo Empresa" class="logo">
            <h1>Reporte Semanal de Asistencias</h1>
        </div>
        
        <div class="info-reporte">
            <div><strong>Generado:</strong> <?php echo date('d/m/Y H:i'); ?></div>
            <div><strong>Período:</strong> <?php echo $fechaInicio->format('d/m/Y'); ?> al <?php echo $fechaFin->format('d/m/Y'); ?></div>
            <?php if (!empty($departamento)): ?>
                <div><strong>Departamento:</strong> <?php echo htmlspecialchars($departamento); ?></div>
            <?php endif; ?>
            <div><strong>Total:</strong> <?php echo count($resultados); ?> registros</div>
        </div>
        
        <table id="tabla-reporte">
            <thead>
                <tr>
                    <th>Departamento</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Total Asistencias</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados as $fila): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['Departamento']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($fila['Fecha Inicio'])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($fila['Fecha Fin'])); ?></td>
                        <td><?php echo htmlspecialchars($fila['Total Asistencias']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3">Total General de Asistencias</td>
                    <td><?php echo $totalGeneral; ?></td>
                </tr>
            </tfoot>
        </table>
        
        <div class="footer no-print">
            Sistema de Gestión de Nóminas &copy; <?php echo date('Y'); ?>
        </div>
    </div>
    
    <button onclick="window.print()" class="print-btn no-print">
        Imprimir Reporte
    </button>
</body>
</html>