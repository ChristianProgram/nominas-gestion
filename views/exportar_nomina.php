<?php
include '../src/config/db.php';

// Obtener parámetros
$fechaSeleccionada = $_GET['fecha'] ?? date('Y-m-d');

// Validar fecha
if (!DateTime::createFromFormat('Y-m-d', $fechaSeleccionada)) {
    die("Fecha no válida");
}

// Calcular fechas de la semana
$fechaInicioSemana = date('Y-m-d', strtotime('monday this week', strtotime($fechaSeleccionada)));
$fechaFinSemana = date('Y-m-d', strtotime('sunday this week', strtotime($fechaSeleccionada)));

// Obtener datos de nómina
try {
    $sql = "CALL ObtenerNominaSemanalPorRango(?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fechaInicioSemana, $fechaFinSemana]);
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    
    if (empty($empleados)) {
        die("No hay datos de nómina para la semana seleccionada");
    }
    
    // Calcular totales
    $totalNomina = array_reduce($empleados, function($carry, $item) {
        return $carry + $item['resultado_total'];
    }, 0);
    
    $totalBonos = array_reduce($empleados, function($carry, $item) {
        return $carry + $item['total_bonos'];
    }, 0);
    
    $totalDescuentos = array_reduce($empleados, function($carry, $item) {
        return $carry + $item['descuento'];
    }, 0);
    
    // Obtener departamento (asumimos el primero para el título)
    $departamento = $empleados[0]['departamento'] ?? 'General';
    
} catch (PDOException $e) {
    die("Error al generar el reporte: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Nómina Semanal - Agropecuaria El Avión</title>
    <style>
        :root {
            --text-color: #212529;
            --border-color: #dee2e6;
            --light-gray: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', sans-serif;
            margin: 0;
            padding: 0;
            color: var(--text-color);
            background-color: white;
            line-height: 1.3;
            font-size: 13px;
        }
        
        .reporte-container {
            width: 100%;
            margin: 0 auto;
            background: white;
        }
        
        .header {
            background: white;
            color: var(--text-color);
            padding: 2px 15px 5px 15px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }
        
        .header h1 {
            margin: 2px 0;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .header .empresa {
            font-size: 11px;
            font-weight: 600;
            margin: 1px 0;
        }
        
        .header .subtitle {
            font-size: 11px;
            color: var(--text-color);
            margin-top: 1px;
            opacity: 0.9;
        }
        
        .logo {
            height: 30px;
            max-width: 90px;
            margin-bottom: 1px;
        }
        
        .info-reporte {
            background-color: white;
            padding: 3px 10px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 5px;
            border-bottom: 1px solid var(--border-color);
            font-size: 10px;
        }
        
        .info-item strong {
            display: block;
            font-size: 9px;
            margin-bottom: 1px;
        }
        
        .info-item span {
            font-size: 10px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 5px;
        }
        
        .data-table thead th {
            padding: 5px 6px;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--light-gray);
            font-weight: 600;
            text-align: left;
        }
        
        .data-table tbody td {
            padding: 4px 5px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: top;
        }
        
        .data-table .numero {
            font-size: 10px;
            font-weight: 500;
        }
        
        .nombre-empleado {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.2;
            max-height: 2.4em;
        }
        
        .text-money {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            font-weight: 600;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 4px 8px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            font-size: 9px;
        }
        
        @media print {
            @page {
                size: letter portrait;
                margin: 0.5cm 0.7cm 0.7cm 0.7cm;
                
                @bottom-center {
                    content: "Página " counter(page) " de " counter(pages);
                    font-size: 8pt;
                    font-family: 'Segoe UI', sans-serif;
                    color: #555;
                }
            }
            
            body {
                font-size: 9pt;
                padding: 0;
                margin: 0;
                counter-reset: page;
            }
            
            .reporte-container {
                width: 100%;
            }
            
            .header {
                padding: 0 10px 2px 10px;
            }
            
            .header h1 {
                font-size: 14pt;
                margin: 1px 0;
            }
            
            .logo {
                height: 28px;
            }
            
            .data-table {
                font-size: 8pt;
            }
            
            .data-table td, .data-table th {
                padding: 3px 4px;
            }
            
            .data-table .numero {
                font-size: 8pt;
            }
            
            .text-money {
                font-size: 8pt;
            }
            
            .no-print {
                display: none !important;
            }
            
            .footer {
                position: fixed;
                bottom: 5px;
                font-size: 7pt;
                padding: 2px 5px;
            }
            
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="reporte-container">
        <div class="header">
            <img src="../src/config/components/assets/logo_avion.png" alt="Logo Empresa" class="logo">
            <div class="header-content">
                <div class="empresa">AGROPECUARIA EL AVIÓN S.R.P. DE R.L.</div>
                <h1>REPORTE DE NÓMINA SEMANAL</h1>
                <div class="subtitle">Resumen detallado de pagos y deducciones</div>
            </div>
        </div>
        
        <div class="info-reporte">
            <div class="info-item">
                <strong>Período</strong>
                <span><?= date('d/m/Y', strtotime($fechaInicioSemana)) ?> - <?= date('d/m/Y', strtotime($fechaFinSemana)) ?></span>
            </div>
            <div class="info-item">
                <strong>Total Empleados</strong>
                <span><?= count($empleados) ?></span>
            </div>
            <div class="info-item no-print">
                <strong>Páginas</strong>
                <span id="page-info">1</span>
            </div>
        </div>
        
        <?php
        $porPagina = 40;  // Aumentamos el número de filas por página
        $totalPaginas = ceil(count($empleados) / $porPagina);
        $grupos = array_chunk($empleados, $porPagina);
        
        foreach ($grupos as $indice => $grupo): 
            $paginaActual = $indice + 1;
            $esUltimaPagina = ($paginaActual == $totalPaginas);
        ?>
            <div class="<?= !$esUltimaPagina ? 'page-break' : '' ?>">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>No. Emp.</th>
                            <th>Dep.</th>
                            <th>Faltas</th>
                            <th>Bonos</th>
                            <th>Descuentos</th>
                            <th>Deduccion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grupo as $empleado): ?>
                            <tr>
                                <td><div class="nombre-empleado"><?= htmlspecialchars($empleado['nombre']) ?></div></td>
                                <td class="numero"><?= htmlspecialchars($empleado['numero_empleado']) ?></td>
                                <td><?= htmlspecialchars($empleado['departamento']) ?></td>
                                <td class="numero"><?= htmlspecialchars($empleado['faltas']) ?></td>
                                <td class="text-money">$<?= number_format($empleado['total_bonos'], 2) ?></td>
                                <td class="text-money">$<?= number_format($empleado['descuento'], 2) ?></td>
                                <td class="text-money">$<?= number_format($empleado['resultado_total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php if($esUltimaPagina): ?>
                        <tfoot>
                            <tr>
                                <td colspan="3">Totales Generales (<?= count($empleados) ?> empleados)</td>
                                <td class="numero"><?= array_sum(array_column($empleados, 'faltas')) ?></td>
                                <td class="text-money">$<?= number_format($totalBonos, 2) ?></td>
                                <td class="text-money">$<?= number_format($totalDescuentos, 2) ?></td>
                                <td class="text-money">$<?= number_format($totalNomina, 2) ?></td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
                
                <div class="footer">
                    <div>Sistema de Nóminas © <?= date('Y') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
                        
    <div class="no-print" style="text-align: center; padding: 15px 0;">
        <button onclick="window.print()" style="padding: 6px 12px; cursor: pointer; background: #f8f9fa; color: #212529; border: 1px solid #dee2e6; border-radius: 3px; font-weight: 500; font-size: 12px;">
            <i class="fas fa-print"></i> Imprimir Reporte
        </button>
    </div>

    <script>
        // Mostrar total de páginas en pantalla
        document.getElementById('page-info').textContent = '<?= $totalPaginas ?>';
        
        // Auto-impresión al cargar
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };
        
        // Atajo Ctrl+P
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>