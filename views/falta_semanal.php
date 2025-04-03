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
    $sql = "CALL FiltroReporteSemanal(:fecha)";
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
    $totalGeneral = array_sum(array_column($resultados, 'Total Faltas'));
    
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
    <title>Reporte Semanal de Faltas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15px;
            color: #333;
            line-height: 1.4;
        }
        
        .reporte-container {
            max-width: 1000px;
            margin: 0 auto;
            border: 1px solid #ddd;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .header {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
            position: relative;
        }
        
        .header h1 {
            margin: 10px 0 5px;
            font-size: 20px;
            font-weight: normal;
        }
        
        .logo {
            height: 40px;
            margin-bottom: 10px;
        }
        
        .info-reporte {
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        
        th {
            background-color: #f2f2f2;
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
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
            padding: 10px 15px;
            text-align: center;
            font-size: 11px;
            color: #777;
            border-top: 1px solid #ddd;
        }
        
        @media print {
            body {
                padding: 5px;
                font-size: 11pt;
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
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    <div class="reporte-container">
        <div class="header">
            <img src="../src/config/components/assets/logo_avion.png" alt="Logo Empresa" class="logo">
            <h1>Reporte Semanal de Faltas</h1>
        </div>
        
        <div class="info-reporte">
            <strong>Período:</strong> <?php echo $fechaInicio->format('d/m/Y'); ?> al <?php echo $fechaFin->format('d/m/Y'); ?>
            <?php if (!empty($departamento)): ?>
                | <strong>Departamento:</strong> <?php echo htmlspecialchars($departamento); ?>
            <?php endif; ?>
            | <strong>Total:</strong> <?php echo count($resultados); ?> registros
        </div>
        
        <table id="tabla-reporte">
            <thead>
                <tr>
                    <th>Departamento</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Total Faltas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados as $fila): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['Departamento']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($fila['Fecha Inicio'])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($fila['Fecha Fin'])); ?></td>
                        <td><?php echo htmlspecialchars($fila['Total Faltas']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3">Total General de Faltas</td>
                    <td><?php echo $totalGeneral; ?></td>
                </tr>
            </tfoot>
        </table>
        
        <div class="footer no-print">
            Generado el <?php echo date('d/m/Y H:i'); ?> | Sistema de Gestión de Nóminas
        </div>
    </div>
    
    <div class="no-print" style="text-align: center; margin-top: 15px;">
        <button onclick="window.print()" style="
            background: #555;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        ">
            Imprimir Reporte
        </button>
    </div>
</body>
</html>