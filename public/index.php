<?php
// index.php
include '../src/config/db.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Página de Bienvenida</title>
    <link rel="stylesheet" href="../public/styles.css">
    <script src="../src/javascript/script.js" defer></script>
    <style>
        /* Estilos adicionales para mejorar la apariencia */
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

        .content {
            flex: 1;
            padding: 2rem;
            background-color: #ffffff;
            border-radius: 8px;
            margin: 1rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .content h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .content p {
            color: #666;
            font-size: 1.2rem;
            line-height: 1.6;
        }

        .welcome-message {
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .welcome-message h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .welcome-message p {
            color: #34495e;
            font-size: 1.2rem;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            color: #fff;
            background-color: #3498db;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #2980b9;
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
                <li><a href="../views/checadas.php" class="active">🕒 Checadas</a></li>
                <li><a href="../views/empleados.php">👨‍💼 Personal</a></li>
                <li><a href="../views/calculo.php">📉 Cálculo</a></li>
                <li><a href="../views/roles.php">🏆 Cargos</a></li>
                <li><a href="../views/importar.php">📂 Importar</a></li>
            </ul>
        </div>
        <div class="content">
            <div class="welcome-message">
                <h1>Bienvenido al Checador</h1>
                <p>Selecciona una opción del menú para comenzar.</p>
                <a href="../views/checadas.php" class="btn">Comenzar</a>
            </div>
        </div>
    </div>
</body>
</html>