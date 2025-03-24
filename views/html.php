<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
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
                    <li><a href="#"><i class="fas fa-file-alt"></i> Reportes</a></li>
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