<?php
include 'db.php';

try {
    // Obtener todos los empleados
    $sql = "SELECT Numero_Empleado, Nombre FROM empleados";
    $stmt = $pdo->query($sql);
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Inicializar un array para almacenar los resultados
    $resultados = [];

    // Recorrer cada empleado y calcular su nómina
    foreach ($empleados as $empleado) {
        $numeroEmpleado = $empleado['Numero_Empleado'];
        $nombre = $empleado['Nombre'];

        // Aquí puedes agregar la lógica para calcular cada campo de la nómina
        // Por ejemplo:
        $ordinario = calcularOrdinario($numeroEmpleado);
        $septimoDia = calcularSeptimoDia($numeroEmpleado);
        $horasExtras = calcularHorasExtras($numeroEmpleado);
        $vacaciones = calcularVacaciones($numeroEmpleado);
        $primaVac = calcularPrimaVacacional($numeroEmpleado);
        $primDom = calcularPrimaDominical($numeroEmpleado);
        $bonoProd = calcularBonoProductividad($numeroEmpleado);
        $diaFest = calcularDiaFestivo($numeroEmpleado);
        $bonoAsist = calcularBonoAsistencia($numeroEmpleado);
        $incapacidad = calcularIncapacidad($numeroEmpleado);

        // Calcular el total de la nómina
        $nominaTotal = $ordinario + $septimoDia + $horasExtras + $vacaciones + $primaVac + $primDom + $bonoProd + $diaFest + $bonoAsist + $incapacidad;

        // Almacenar los resultados
        $resultados[] = [
            'numero' => $numeroEmpleado,
            'nombre' => $nombre,
            'ordinario' => $ordinario,
            'septimo_dia' => $septimoDia,
            'horas_extras' => $horasExtras,
            'vacaciones' => $vacaciones,
            'prima_vac' => $primaVac,
            'prim_dom' => $primDom,
            'bono_prod' => $bonoProd,
            'dia_fest' => $diaFest,
            'bono_asist' => $bonoAsist,
            'incapacidad' => $incapacidad,
            'nomina_total' => $nominaTotal
        ];
    }

    // Generar la tabla HTML con los resultados
    $html = '<table class="visible">';
    $html .= '<thead>
                <tr>
                    <th>Número</th>
                    <th>Nombre</th>
                    <th>Ordinario</th>
                    <th>Séptimo Día</th>
                    <th>Horas Extras</th>
                    <th>Vacaciones</th>
                    <th>Prima Vacacional</th>
                    <th>Prima Dominical</th>
                    <th>Bono Productividad</th>
                    <th>Día Festivo</th>
                    <th>Bono Asistencia</th>
                    <th>Incapacidad</th>
                    <th>Nómina Total</th>
                </tr>
              </thead>';
    $html .= '<tbody>';

    foreach ($resultados as $resultado) {
        $html .= '<tr>';
        $html .= '<td>' . $resultado['numero'] . '</td>';
        $html .= '<td>' . $resultado['nombre'] . '</td>';
        $html .= '<td>' . number_format($resultado['ordinario'], 2) . '</td>';
        $html .= '<td>' . number_format($resultado['septimo_dia'], 2) . '</td>';
        $html .= '<td>' . number_format($resultado['horas_extras'], 2) . '</td>';
        $html .= '<td>' . number_format($resultado['vacaciones'], 2) . '</td>';
        $html .= '<td>' . number_format($resultado['prima_vac'], 2) . '</td>';
        $html .= '<td>' . number_format($resultado['prim_dom'], 2) . '</td>';
        $html .= '<td>' . number_format($resultado['bono_prod'], 2) . '</td>';
        $html .= '<td>' . number_format($resultado['dia_fest'], 2) . '</td>';
        $html .= '<td>' . number_format($resultado['bono_asist'], 2) . '</td>';
        $html .= '<td>' . number_format($resultado['incapacidad'], 2) . '</td>';
        $html .= '<td>' . number_format($resultado['nomina_total'], 2) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';

    // Devolver la tabla HTML
    echo $html;
} catch (PDOException $e) {
    // En caso de error, devolver un mensaje de error
    echo '<p class="error">Hubo un error al procesar la solicitud: ' . $e->getMessage() . '</p>';
}

// Funciones de ejemplo para calcular cada campo (debes implementar la lógica real)
function calcularOrdinario($numeroEmpleado) {
    // Lógica para calcular el salario ordinario
    return 5000.00; // Ejemplo
}

function calcularSeptimoDia($numeroEmpleado) {
    // Lógica para calcular el séptimo día
    return 300.00; // Ejemplo
}

function calcularHorasExtras($numeroEmpleado) {
    // Lógica para calcular las horas extras
    return 200.00; // Ejemplo
}

function calcularVacaciones($numeroEmpleado) {
    // Lógica para calcular las vacaciones
    return 100.00; // Ejemplo
}

function calcularPrimaVacacional($numeroEmpleado) {
    // Lógica para calcular la prima vacacional
    return 50.00; // Ejemplo
}

function calcularPrimaDominical($numeroEmpleado) {
    // Lógica para calcular la prima dominical
    return 75.00; // Ejemplo
}

function calcularBonoProductividad($numeroEmpleado) {
    // Lógica para calcular el bono de productividad
    return 150.00; // Ejemplo
}

function calcularDiaFestivo($numeroEmpleado) {
    // Lógica para calcular el día festivo
    return 100.00; // Ejemplo
}

function calcularBonoAsistencia($numeroEmpleado) {
    // Lógica para calcular el bono de asistencia
    return 80.00; // Ejemplo
}

function calcularIncapacidad($numeroEmpleado) {
    // Lógica para calcular la incapacidad
    return 0.00; // Ejemplo
}
?>