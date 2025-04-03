<?php
include '../src/config/db.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selección de Reportes | Sistema de Nóminas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #7c3aed;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --light-bg: #f8fafc;
            --sidebar-bg: #1e293b;
            --sidebar-active: #0f172a;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --card-shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--light-bg);
            color: #334155;
            line-height: 1.5;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar mejorado */
        :root {
            --primary-color: #00263F;
            --secondary-color: #3b82f6;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --light-bg: #f8fafc;
            --border-color: #e2e8f0;
            --text-color: #334155;
            --text-light: #64748b;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
            background-color: var(--light-bg);
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color:rgb(19, 26, 37);
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
            overflow-y: auto;
        }
        
        .page-header {
            margin-bottom: 2.5rem;
        }
        
        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .page-header p {
            color: #64748b;
            font-size: 1rem;
        }
        
        /* Grid de reportes mejorado */
        .reportes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }
        
        .reporte-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid #e2e8f0;
        }
        
        .reporte-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--card-shadow-hover);
            border-color: #cbd5e1;
        }
        
        .reporte-header {
            padding: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .reporte-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
        }
        
        .reporte-header i {
            font-size: 2.75rem;
            margin-bottom: 1.25rem;
            color: white;
            position: relative;
            z-index: 1;
        }
        
        .reporte-header h2 {
            margin: 0;
            font-size: 1.375rem;
            font-weight: 600;
            color: white;
            position: relative;
            z-index: 1;
        }
        
        /* Colores para cada tipo de reporte */
        .reporte-faltas .reporte-header {
            background: linear-gradient(135deg, var(--danger-color), #dc2626);
        }
        
        .reporte-faltas .reporte-header::before {
            background: linear-gradient(90deg, #ef4444, #f87171);
        }
        
        .reporte-asistencia .reporte-header {
            background: linear-gradient(135deg, var(--success-color), #059669);
        }
        
        .reporte-asistencia .reporte-header::before {
            background: linear-gradient(90deg, #10b981, #34d399);
        }
        
        .reporte-nomina .reporte-header {
            background: linear-gradient(135deg, var(--secondary-color), #6d28d9);
        }
        
        .reporte-nomina .reporte-header::before {
            background: linear-gradient(90deg, #7c3aed, #a78bfa);
        }
        
        .reporte-body {
            padding: 2rem;
        }
        
        .reporte-body p {
            color: #64748b;
            margin-bottom: 1.75rem;
            font-size: 0.9375rem;
            line-height: 1.6;
        }
        
        .btn-reporte {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9375rem;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }
        
        .btn-reporte i {
            margin-right: 8px;
            font-size: 0.9rem;
        }
        
        .reporte-faltas .btn-reporte {
            background-color: var(--danger-color);
            color: white;
        }
        
        .reporte-faltas .btn-reporte:hover {
            background-color: #dc2626;
        }
        
        .reporte-asistencia .btn-reporte {
            background-color: var(--success-color);
            color: white;
        }
        
        .reporte-asistencia .btn-reporte:hover {
            background-color: #059669;
        }
        
        .reporte-nomina .btn-reporte {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .reporte-nomina .btn-reporte:hover {
            background-color: #6d28d9;
        }
        
        /* Efecto de onda en el header */
        .wave-effect {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 20px;
            background: url('data:image/svg+xml;utf8,<svg viewBox="0 0 1200 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"><path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" fill="rgba(255,255,255,0.1)" opacity=".25"/><path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" fill="rgba(255,255,255,0.1)" opacity=".5"/><path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
            }
            
            .content {
                padding: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 72px;
                overflow: hidden;
            }
            
            .sidebar-header h2, 
            .sidebar-section h3,
            .sidebar ul li a span {
                display: none;
            }
            
            .sidebar ul li a {
                justify-content: center;
                padding: 0.75rem;
            }
            
            .sidebar ul li a i {
                font-size: 1.25rem;
            }
            
            .reportes-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .content {
                padding: 1.5rem;
            }
            
            .page-header h1 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar mejorado -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><span>Nóminas</span></h2>
            </div>

            <!-- Sección: Informes -->
            <div class="sidebar-section">
                <h3><span>Informes</span></h3>
                <ul>
                    <li><a href="../public/index.php" class="active"><i class="fas fa-chart-bar"></i> <span>Resumen</span></a></li>
                </ul>
            </div>

            <!-- Sección: Gestionar -->
            <div class="sidebar-section">
                <h3><span>Gestionar</span></h3>
                <ul>
                    <li><a href="../views/checadas.php"><i class="fas fa-calendar-alt"></i> <span>Asistencia</span></a></li>
                    <li><a href="../views/empleados.php"><i class="fas fa-users"></i> <span>Empleados</span></a></li>
                    <li><a href="../views/calculo.php"><i class="fas fa-calculator"></i> <span>Deducciones</span></a></li>
                    <li><a href="../views/bonos.php"><i class="fas fa-gift"></i> <span>Bonos</span></a></li>
                    <li><a href="../views/roles.php"><i class="fas fa-briefcase"></i> <span>Cargos</span></a></li>
                    <li><a href="../views/importar.php"><i class="fas fa-file-import"></i> <span>Importar datos</span></a></li>
                </ul>
            </div>

            <!-- Sección: Imprimibles -->
            <div class="sidebar-section">
                <h3><span>Imprimibles</span></h3>
                <ul>
                    <li><a href="#"><i class="fas fa-file-alt"></i> <span>Reportes PDF</span></a></li>
                </ul>
            </div>
        </div>
        
        <!-- Contenido principal mejorado -->
        <div class="content">
            <div class="page-header">
                <h1><i class="fas fa-file-alt"></i> Seleccionar Tipo de Reporte</h1>
                <p>Elija el tipo de reporte que desea generar del sistema</p>
            </div>
            
            <div class="reportes-grid">
                <!-- Reporte de Faltas -->
                <div class="reporte-card reporte-faltas">
                    <div class="reporte-header">
                        <i class="fas fa-user-clock"></i>
                        <h2>Reporte de Faltas</h2>
                        <div class="wave-effect"></div>
                    </div>
                    <div class="reporte-body">
                        <p>Genere reportes detallados de faltas por empleado, departamento o período específico con análisis de tendencias y comparativas.</p>
                        <a href="reportes_falta.php" class="btn-reporte">
                            <i class="fas fa-arrow-right"></i> Generar Reporte
                        </a>
                    </div>
                </div>
                
                <!-- Reporte de Asistencia -->
                <div class="reporte-card reporte-asistencia">
                    <div class="reporte-header">
                        <i class="fas fa-user-check"></i>
                        <h2>Reporte de Asistencia</h2>
                        <div class="wave-effect"></div>
                    </div>
                    <div class="reporte-body">
                        <p>Reportes completos de asistencia, horas trabajadas, puntualidad y patrones de comportamiento del personal con gráficos integrados.</p>
                        <a href="reportes_asistencia.php" class="btn-reporte">
                            <i class="fas fa-arrow-right"></i> Generar Reporte
                        </a>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</body>
</html>