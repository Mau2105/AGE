<?php
session_start();
// Lógica para manejar la recuperación de contraseña
if (isset($_POST['submit'])) {
    $correo = $_POST['correo'];

    // Aquí iría la lógica para enviar un correo de recuperación de contraseña

    $message = 'Si el correo ingresado está registrado, recibirás un mensaje con instrucciones para recuperar tu contraseña.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="../bootstrap/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="shortcut icon" href="../img/logo.png">
    <style>
        body {
            background-color: #f8f9fa; /* Color de fondo claro */
        }
        .recovery-container {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="recovery-container mt-5">
        <h1 class="text-center mb-4">Recuperar Contraseña</h1>
        <?php if (isset($message)): ?>
            <div class="alert alert-info" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label for="correo" class="form-label">Correo</label>
                <input type="email" id="correo" name="correo" class="form-control" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary w-100">Enviar Instrucciones</button>
        </form>
        <p class="mt-3 text-center">
            <a href="login.php" style="text-decoration: none" class="btn btn-link">Volver al Inicio de Sesión</a>
        </p>
    </div>
</body>
</html>
