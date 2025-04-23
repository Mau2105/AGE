<?php
session_start();
require 'config.php'; // Asegúrate de que config.php esté en el mismo directorio

if (isset($_SESSION['funcionario_id'])) {
    header('Location: reservas.php');
    exit();
}

$message = '';
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verificar las credenciales del administrador
    $stmt = $pdo->prepare('SELECT id, username, password FROM funcionarios WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['funcionario_id'] = $user['id'];
        header('Location: reservas.php');
        exit();
    } else {
        $message = 'Usuario o contraseña incorrectos.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrador</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: auto;
            padding-top: 100px;
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Inicio de Sesión Administrador</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label for="username">Nombre de Usuario:</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary">Iniciar Sesión</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
