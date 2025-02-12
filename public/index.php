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
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Menú</h2>
            <ul>
                <li><a href="../views/importar.php">Importar</a></li>
                <li><a href="../views/checadas.php">Checadas</a></li>
                <li><a href="../views/calculo.php">Calculo</a></li>
            </ul>
        </div>
        <div class="content">
            <h1>Bienvenido al Checador</h1>
            <p>Selecciona una opción del menú para comenzar.</p>
        </div>
    </div>
</body>
</html>
