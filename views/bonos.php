<?php
include '../src/config/db.php';

// Obtener la fecha seleccionada o usar la fecha de hoy
$fechaSeleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Calcular el inicio y fin de la semana
$fechaInicio = date('Y-m-d', strtotime('monday this week', strtotime($fechaSeleccionada)));
$fechaFin = date('Y-m-d', strtotime('sunday this week', strtotime($fechaSeleccionada)));

// Generar un idFecha único para la semana (puede ser el timestamp de la fecha de inicio)
$idFecha = strtotime($fechaInicio);

// Definir el término de búsqueda
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Paginación
$empleadosPorPagina = 10; // Número de empleados por página
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaActual - 1) * $empleadosPorPagina;

// Obtener lista de empleados paginada
$sqlEmpleados = "SELECT Numero_Empleado, Nombre FROM empleados WHERE Nombre LIKE :search OR Numero_Empleado LIKE :search ORDER BY Nombre ASC LIMIT :limit OFFSET :offset";
$stmtEmpleados = $pdo->prepare($sqlEmpleados);
$stmtEmpleados->bindValue(':limit', $empleadosPorPagina, PDO::PARAM_INT);
$stmtEmpleados->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtEmpleados->bindValue(':search', "%$searchTerm%", PDO::PARAM_STR);
$stmtEmpleados->execute();
$empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);

// Obtener el total de empleados para la paginación (con búsqueda)
$sqlTotalEmpleados = "SELECT COUNT(*) as total FROM empleados WHERE Nombre LIKE :search OR Numero_Empleado LIKE :search";
$stmtTotalEmpleados = $pdo->prepare($sqlTotalEmpleados);
$stmtTotalEmpleados->bindValue(':search', "%$searchTerm%", PDO::PARAM_STR);
$stmtTotalEmpleados->execute();
$totalEmpleados = $stmtTotalEmpleados->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($totalEmpleados / $empleadosPorPagina);

// Obtener bonos entre las fechas seleccionadas
$sqlBonos = "SELECT numero_empleado, cantidad, razon, fecha, idFecha FROM bonos WHERE idFecha = :idFecha";
$stmtBonos = $pdo->prepare($sqlBonos);
$stmtBonos->bindParam(':idFecha', $idFecha, PDO::PARAM_INT);
$stmtBonos->execute();
$bonos = $stmtBonos->fetchAll(PDO::FETCH_ASSOC);

// Convertir los bonos a un formato más manejable
$bonosPorEmpleado = [];
foreach ($bonos as $bono) {
    $bonosPorEmpleado[$bono['numero_empleado']][] = $bono;
}

// Guardado automático (simulación al final de la semana)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fechaSeleccionada = $_POST['fecha'];

    try {
        $pdo->beginTransaction();

        // Insertar nuevos bonos solo para empleados con datos asignados
        foreach ($_POST['bonos'] as $numeroEmpleado => $bono) {
            // Verificar si el empleado tiene un bono asignado
            if (!empty($bono['cantidad']) && !empty($bono['razon'])) {
                // Llamar al procedimiento almacenado
                $sql = "CALL InsertarOActualizarBono(:numero_empleado, :cantidad, :razon, :fecha, :idFecha)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':numero_empleado' => $numeroEmpleado,
                    ':cantidad' => $bono['cantidad'],
                    ':razon' => $bono['razon'],
                    ':fecha' => $fechaSeleccionada,
                    ':idFecha' => $idFecha
                ]);
            }
        }

        $pdo->commit();
        echo "<script>
                alert('Bonos guardados correctamente.');
                window.location.href = 'bonos.php?fecha=$fechaSeleccionada&search=" . urlencode($searchTerm) . "';
              </script>";
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<script>alert('Error al guardar los bonos: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bonos</title>
    <link rel="stylesheet" href="../public/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
    <style>
        /* Mantén los mismos estilos que en la página de checadas */
        .bonos-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .bonos-table th, .bonos-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
        .bonos-table th {
            background-color: #f2f2f2;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a, .pagination span {
            margin: 0 5px;
            text-decoration: none;
            color: #333;
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .pagination a.active {
            font-weight: bold;
            color: #000;
            background-color: #f2f2f2;
        }
        .pagination a:hover:not(.disabled) {
            background-color: #ddd;
        }
        .pagination .disabled {
            color: #aaa;
            cursor: not-allowed;
            background-color: #f9f9f9;
        }
        .filter-form {
            margin-bottom: 20px;
        }
        .filter-form label {
            margin-right: 10px;
        }
        .filter-form select, .filter-form input {
            padding: 5px;
            margin-right: 10px;
        }

        /* Estilos para el modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #ccc;
            width: 60%;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        /* Estilos para la tabla de bonos dentro del modal */
        #lista-bonos table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        #lista-bonos th, #lista-bonos td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        #lista-bonos th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        #lista-bonos tr:hover {
            background-color: #f5f5f5;
        }

        /* Estilos para la navegación semanal */
        .navegacion-semanal {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .navegacion-semanal button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .navegacion-semanal button:hover {
            background-color: #0056b3;
        }

        .navegacion-semanal span {
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>📊 Menú</h2>
            </div>
            <ul>
                <li><a href="checadas.php">🕒 Checadas</a></li>
                <li><a href="bonos.php" class="active">💰 Bonos</a></li>
                <li><a href="empleados.php">👨‍💼 Personal</a></li>
                <li><a href="calculo.php">📉 Cálculo</a></li>
                <li><a href="roles.php">🏆 Cargos</a></li>
                <li><a href="importar.php">📂 Importar</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="content-container">
                <div class="section-header">
                    <h1 class="section-title">Bonos Semanales</h1>
                </div>

                <!-- Filtro por Semana -->
                <form method="GET" action="bonos.php" class="filter-form">
                    <label for="fecha">Selecciona una semana:</label>
                    <input type="text" id="fecha" name="fecha" value="<?php echo $fechaSeleccionada; ?>" onchange="this.form.submit()">

                    <!-- Campo oculto para conservar el término de búsqueda -->
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
                </form>

                <!-- Formulario de búsqueda -->
                <form method="GET" action="bonos.php">
                    <input type="text" name="search" placeholder="Buscar por nombre o número" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <!-- Campo oculto para conservar la fecha -->
                    <input type="hidden" name="fecha" value="<?php echo $fechaSeleccionada; ?>">
                    <button type="submit">Buscar</button>
                </form>

                <!-- Tabla de Bonos -->
                <form method="POST" action="bonos.php">
                    <input type="hidden" name="fecha" value="<?php echo $fechaSeleccionada; ?>">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button type="submit" style="margin-top: 20px;">Guardar Bonos</button>
                    <table class="bonos-table">
                        <thead>
                            <tr>
                                <th>Número de Empleado</th>
                                <th>Nombre</th>
                                <th>Cantidad</th>
                                <th>Razón</th>
                                <th>Ver Bonos</th> <!-- Nueva columna -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($empleados as $empleado) {
                                $numeroEmpleado = $empleado['Numero_Empleado'];
                                $bonoEmpleado = $bonosPorEmpleado[$numeroEmpleado][0] ?? null;
                                echo "<tr>";
                                echo "<td>" . $empleado['Numero_Empleado'] . "</td>";
                                echo "<td>" . $empleado['Nombre'] . "</td>";
                                echo "<td><input type='number' name='bonos[$numeroEmpleado][cantidad]' value='" . ($bonoEmpleado ? $bonoEmpleado['cantidad'] : '0') . "' step='0.01'></td>";
                                echo "<td><input type='text' name='bonos[$numeroEmpleado][razon]' value='" . ($bonoEmpleado ? $bonoEmpleado['razon'] : '') . "'></td>";
                                echo "<td><button type='button' class='ver-bonos-btn' data-numero-empleado='$numeroEmpleado'>Ver Bonos</button></td>"; 
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </form>

                <!-- Modal para mostrar bonos semanales -->
                <div id="modalBonos" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Bonos Semanales</h2>
                        <div class="navegacion-semanal">
                            <button id="semanaAnterior">&lt; Semana Anterior</button>
                            <span id="rangoSemanal"></span>
                            <button id="semanaSiguiente">Semana Siguiente &gt;</button>
                        </div>
                        <div id="lista-bonos"></div>
                    </div>
                </div>

                <!-- Paginación -->
                <div class="pagination">
                    <!-- Botón "Anterior" -->
                    <?php if ($paginaActual > 1): ?>
                        <a href="bonos.php?pagina=<?php echo $paginaActual - 1; ?>&fecha=<?php echo $fechaSeleccionada; ?>&search=<?php echo urlencode($searchTerm); ?>" class="pagination-button">Anterior</a>
                    <?php else: ?>
                        <span class="pagination-button disabled">Anterior</span>
                    <?php endif; ?>

                    <!-- Números de página -->
                    <?php
                    for ($i = 1; $i <= $totalPaginas; $i++) {
                        $clase = ($i == $paginaActual) ? 'active' : '';
                        echo "<a href='bonos.php?pagina=$i&fecha=$fechaSeleccionada&search=" . urlencode($searchTerm) . "' class='$clase'>$i</a>";
                    }
                    ?>

                    <!-- Botón "Siguiente" -->
                    <?php if ($paginaActual < $totalPaginas): ?>
                        <a href="bonos.php?pagina=<?php echo $paginaActual + 1; ?>&fecha=<?php echo $fechaSeleccionada; ?>&search=<?php echo urlencode($searchTerm); ?>" class="pagination-button">Siguiente</a>
                    <?php else: ?>
                        <span class="pagination-button disabled">Siguiente</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        let fechaActual = new Date(); // Fecha actual para la navegación semanal
        let numeroEmpleadoActual = null; // Almacena el número de empleado seleccionado

        // Función para formatear la fecha como YYYY-MM-DD
        function formatearFecha(date) {
            return date.toISOString().split('T')[0];
        }

        // Función para obtener el rango de la semana (lunes a domingo)
        function obtenerRangoSemanal(date) {
            const inicioSemana = new Date(date);
            inicioSemana.setDate(date.getDate() - date.getDay() + (date.getDay() === 0 ? -6 : 1)); // Lunes
            const finSemana = new Date(inicioSemana);
            finSemana.setDate(inicioSemana.getDate() + 6); // Domingo
            return {
                inicio: formatearFecha(inicioSemana),
                fin: formatearFecha(finSemana)
            };
        }

        // Función para cargar los bonos de la semana actual
        function cargarBonosSemana() {
            const rango = obtenerRangoSemanal(fechaActual);
            document.getElementById('rangoSemanal').textContent = `${rango.inicio} a ${rango.fin}`;

            fetch(`obtener_bonos.php?numero_empleado=${numeroEmpleadoActual}&fecha_inicio=${rango.inicio}&fecha_fin=${rango.fin}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('lista-bonos').innerHTML = data;
                });
        }

        // Abrir modal y cargar bonos del empleado
        document.querySelectorAll('.ver-bonos-btn').forEach(button => {
            button.addEventListener('click', function () {
                numeroEmpleadoActual = this.getAttribute('data-numero-empleado');
                fechaActual = new Date(); // Reiniciar a la semana actual
                cargarBonosSemana();
                document.getElementById('modalBonos').style.display = 'block';
            });
        });

        // Navegación semanal
        document.getElementById('semanaAnterior').addEventListener('click', function () {
            fechaActual.setDate(fechaActual.getDate() - 7); // Retroceder una semana
            cargarBonosSemana();
        });

        document.getElementById('semanaSiguiente').addEventListener('click', function () {
            fechaActual.setDate(fechaActual.getDate() + 7); // Avanzar una semana
            cargarBonosSemana();
        });

        // Cerrar modal
        document.querySelector('.close').addEventListener('click', function () {
            document.getElementById('modalBonos').style.display = 'none';
        });

        // Cerrar modal al hacer clic fuera de él
        window.addEventListener('click', function (event) {
            if (event.target === document.getElementById('modalBonos')) {
                document.getElementById('modalBonos').style.display = 'none';
            }
        });
        // Flatpickr para el calendario
        flatpickr("#fecha", {
            dateFormat: "Y-m-d",
            locale: "es"
        });
    </script>
</body>
</html>