<?php
include '../src/config/db.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Personal</title>
    <link rel="stylesheet" href="../public/styles.css">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="../src/javascript/empleados.js" defer></script>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Menú</h2>
            <ul>
                <li><a href="checadas.php">Checadas</a></li>
                <li><a href="empleados.php" class="active">Personal</a></li>
                <li><a href="calculo.php">Cálculo</a></li>
                <li><a href="importar.php">Importar</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="content-container">
                <div class="section-header">
                    <h1 class="section-title">Gestión de Personal</h1>
                    <p class="section-subtitle">Administra y verifica la información del personal.</p>
                </div>

                <!-- Botón para agregar nuevo empleado -->
                <button class="btn btn-primary btn-agregar" onclick="abrirModalAgregar()">
                    <i class="bi bi-plus-lg"></i> Agregar Empleado
                </button>

                <!-- Formulario de Búsqueda -->
                <form class="search-form" onsubmit="event.preventDefault(); buscarEmpleados();">
                    <input type="text" id="busqueda" name="busqueda" placeholder="Buscar por nombre o número">
                    <input type="hidden" id="pagina" name="pagina" value="1">
                    <button type="submit">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </form>

                <!-- Contenedor para los resultados -->
                <div id="resultados"></div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar empleado -->
    <div id="modalAgregar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModalAgregar()">&times;</span>
            <h2>Agregar Nuevo Empleado</h2>
            <form id="formAgregar" onsubmit="guardarEmpleado(event)">
                <div class="input-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="input-group">
                    <label for="numero">Número de Empleado:</label>
                    <input type="text" id="numero" name="numero" required>
                </div>
                <div class="input-group">
                    <label for="departamento">Departamento:</label>
                    <input type="text" id="departamento" name="departamento" required>
                </div>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </form>
        </div>
    </div>

    <!-- Modal para editar empleado -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModalEditar()">&times;</span>
            <h2>Editar Empleado</h2>
            <form id="formEditar" onsubmit="guardarCambios(event)">
                <input type="hidden" id="editarId" name="id">
                <div class="input-group">
                    <label for="editarNombre">Nombre:</label>
                    <input type="text" id="editarNombre" name="nombre" required>
                </div>
                <div class="input-group">
                    <label for="editarNumero">Número de Empleado:</label>
                    <input type="text" id="editarNumero" name="numero" required>
                </div>
                <div class="input-group">
                    <label for="editarDepartamento">Departamento:</label>
                    <input type="text" id="editarDepartamento" name="departamento" required>
                </div>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <!-- Estilos para los modales -->
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
        }

        .close {
            float: right;
            cursor: pointer;
            font-size: 1.5rem;
        }
    </style>
</body>
</html>