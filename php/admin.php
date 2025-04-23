<?php
session_start();
require 'config.php';

// Función para verificar la autenticación del administrador
function checkAdminAuth() {
    return isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'];
}

// Manejo de inicio de sesión del administrador
if (isset($_POST['admin_login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Las credenciales del administrador para autenticación
    if ($username === 'JFCBM' && $password === 'adminpass') {
        $_SESSION['admin_authenticated'] = true;
        header('Location: admin.php');
        exit();
    } else {
        $admin_error = 'Credenciales de administrador incorrectas';
    }
}

// Manejo de cierre de sesión del administrador
if (isset($_POST['admin_logout'])) {
    unset($_SESSION['admin_authenticated']);
    header('Location: admin.php');
    exit();
}

// Solo permitir acceso si el administrador está autenticado
if (!checkAdminAuth()) {
    $admin_logged_in = false;
} else {
    $admin_logged_in = true;

    // Manejo de agregar nuevo usuario
    if (isset($_POST['add_user'])) {
        $nombre = $_POST['nombre'];
        $correo = $_POST['correo'];
        $contrasena = $_POST['contrasena'];

        $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, correo, contrasena) VALUES (?, ?, ?)');
        $stmt->execute([$nombre, $correo, hash('sha256', $contrasena)]);
        $success_message = 'Usuario agregado exitosamente';
    }

    // Manejo de agregar nuevo funcionario
    if (isset($_POST['add_funcionario'])) {
        $nombre = $_POST['nombre_funcionario'];
        $correo = $_POST['correo_funcionario'];
        $cargo = $_POST['cargo_funcionario'];
        $telefono = $_POST['telefono_funcionario'];
        $contrasena = $_POST['contrasena_funcionario']; // Contraseña del funcionario

        $stmt = $pdo->prepare('INSERT INTO funcionarios (nombre, correo, cargo, telefono, contrasena) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$nombre, $correo, $cargo, $telefono, hash('sha256', $contrasena)]);
        $success_message = 'Funcionario agregado exitosamente';
    }

    // Manejo de cambiar contraseña
    if (isset($_POST['change_password'])) {
        $correo = $_POST['correo'];
        $nueva_contrasena = $_POST['nueva_contrasena'];

        $stmt = $pdo->prepare('UPDATE usuarios SET contrasena = ? WHERE correo = ?');
        $stmt->execute([hash('sha256', $nueva_contrasena), $correo]);
        $success_message = 'Contraseña actualizada exitosamente';
    }

    // Manejo de eliminar usuario
    if (isset($_POST['delete_user'])) {
        $correo = $_POST['correo'];

        $stmt = $pdo->prepare('DELETE FROM usuarios WHERE correo = ?');
        $stmt->execute([$correo]);
        $success_message = 'Usuario eliminado exitosamente';
    }

    // Manejo de eliminar funcionario
    if (isset($_POST['delete_funcionario'])) {
        $correo = $_POST['correo_funcionario'];

        $stmt = $pdo->prepare('DELETE FROM funcionarios WHERE correo = ?');
        $stmt->execute([$correo]);
        $success_message = 'Funcionario eliminado exitosamente';
    }

    // Manejo de búsqueda de usuarios
    $usuarios = [];
    if (isset($_POST['search_users'])) {
        $search_query = $_POST['search_query'];
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE nombre LIKE ? OR correo LIKE ?');
        $stmt->execute(['%' . $search_query . '%', '%' . $search_query . '%']);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Manejo de búsqueda de funcionarios
    $funcionarios = [];
    if (isset($_POST['search_funcionarios'])) {
        $search_funcionario_query = $_POST['search_funcionario_query'];
        $stmt = $pdo->prepare('SELECT * FROM funcionarios WHERE nombre LIKE ? OR correo LIKE ?');
        $stmt->execute(['%' . $search_funcionario_query . '%', '%' . $search_funcionario_query . '%']);
        $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener todos los funcionarios
    if (!isset($_POST['search_funcionarios'])) {
        $stmt = $pdo->query('SELECT * FROM funcionarios');
        $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../html/img/logo.png">
    <style>
        body {
            display: flex;
            height: 100vh;
            background-color: #f8f9fa;
            color: #343a40;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            height: 100%;
            position: fixed;
        }
        .sidebar a {
            color: white;
            padding: 10px;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            flex-grow: 1;
        }
        .form-container {
            display: none;
        }
        .form-section {
            margin-top: 20px;
        }
        .profile-container {
    margin-top: 20px;
}

.profile-img {
    width: 100px;  /* Tamaño de la imagen */
    height: 100px; /* Tamaño de la imagen */
    border-radius: 50%; /* Hace que la imagen sea redonda */
    object-fit: cover; /* Asegura que la imagen mantenga su proporción */
    border: 3px solid #343a40; /* Borde alrededor de la imagen */
}

.profile-container h4 {
    margin-top: 10px;
    font-size: 18px;
    font-weight: 600;
}

.profile-container p {
    font-size: 14px;
    color: #6c757d;
}

    </style>
</head>
<body>
    <?php if (!$admin_logged_in): ?>
        <div class="main-content">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Inicio de Sesión del Administrador</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($admin_error)) echo "<div class='alert alert-danger'>$admin_error</div>"; ?>
                    <form method="post">
                        <div class="form-group">
                            <label for="username">Nombre de Usuario:</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Contraseña:</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" name="admin_login" class="btn btn-primary">Iniciar Sesión</button>
                    </form>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="sidebar">
            <h3 class="text-center text-white">Admin Panel</h3>
            <div class="profile-container text-center">
    <img src="../html/img/admin.png" alt="Foto de Perfil" class="profile-img">
    <h4>Bienvenido admin</h4>
    <p class="text-muted">Administrador</p>
</div>
            <a href="#" onclick="toggleForm('userForm')">Agregar Nuevo Usuario</a>
            <a href="#" onclick="toggleForm('funcionarioForm')">Agregar Nuevo Funcionario</a>
            <a href="#" onclick="toggleForm('passwordForm')">Cambiar Contraseña</a>
            <a href="#" onclick="toggleForm('deleteUserForm')">Eliminar Usuario</a>
            <a href="#" onclick="toggleForm('deleteFuncionarioForm')">Eliminar Funcionario</a>
            <form method="post" class="mt-3">
                <button type="submit" name="admin_logout" class="btn btn-danger w-100">Cerrar Sesión</button>
            </form>
        </div>

        <div class="main-content">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Panel de Administración</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($success_message)) echo "<div class='alert alert-success'>$success_message</div>"; ?>

                    <!-- Formulario de CRUD -->
                    <div id="userForm" class="form-container">
                        <h5>Agregar Nuevo Usuario</h5>
                        <form method="post">
                            <div class="form-group">
                                <label for="nombre">Nombre:</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="form-group">
                                <label for="correo">Correo:</label>
                                <input type="email" class="form-control" id="correo" name="correo" required>
                            </div>
                            <div class="form-group">
                                <label for="contrasena">Contraseña:</label>
                                <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                            </div>
                            <button type="submit" name="add_user" class="btn btn-primary">Agregar Usuario</button>
                        </form>
                    </div>

                    <!-- Otros formularios siguen aquí... -->
                    
                                        <!-- Formulario para agregar nuevo funcionario -->
                                        <div id="funcionarioForm" class="form-container">
                        <h5 class="mt-4">Agregar Nuevo Funcionario</h5>
                        <form method="post">
                            <div class="form-group">
                                <label for="nombre_funcionario">Nombre:</label>
                                <input type="text" class="form-control" id="nombre_funcionario" name="nombre_funcionario" required>
                            </div>
                            <div class="form-group">
                                <label for="correo_funcionario">Correo:</label>
                                <input type="email" class="form-control" id="correo_funcionario" name="correo_funcionario" required>
                            </div>
                            <div class="form-group">
                                <label for="cargo_funcionario">Cargo:</label>
                                <input type="text" class="form-control" id="cargo_funcionario" name="cargo_funcionario">
                            </div>
                            <div class="form-group">
                                <label for="telefono_funcionario">Teléfono:</label>
                                <input type="text" class="form-control" id="telefono_funcionario" name="telefono_funcionario">
                            </div>
                            <div class="form-group">
                                <label for="contrasena_funcionario">Contraseña:</label>
                                <input type="password" class="form-control" id="contrasena_funcionario" name="contrasena_funcionario" required>
                            </div>
                            <button type="submit" name="add_funcionario" class="btn btn-primary">Agregar Funcionario</button>
                        </form>
                    </div>

                    <!-- Formulario para cambiar contraseña -->
                    <div id="passwordForm" class="form-container">
                        <h5 class="mt-4">Cambiar Contraseña</h5>
                        <form method="post">
                            <div class="form-group">
                                <label for="correo">Correo del Usuario:</label>
                                <input type="email" class="form-control" id="correo" name="correo" required>
                            </div>
                            <div class="form-group">
                                <label for="nueva_contrasena">Nueva Contraseña:</label>
                                <input type="password" class="form-control" id="nueva_contrasena" name="nueva_contrasena" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-warning">Cambiar Contraseña</button>
                        </form>
                    </div>

                    <!-- Formulario para eliminar usuario -->
                    <div id="deleteUserForm" class="form-container">
                        <h5 class="mt-4">Eliminar Usuario</h5>
                        <form method="post">
                            <div class="form-group">
                                <label for="correo">Correo del Usuario:</label>
                                <input type="email" class="form-control" id="correo" name="correo" required>
                            </div>
                            <button type="submit" name="delete_user" class="btn btn-danger">Eliminar Usuario</button>
                        </form>
                    </div>

                    <!-- Formulario para eliminar funcionario -->
                    <div id="deleteFuncionarioForm" class="form-container">
                        <h5 class="mt-4">Eliminar Funcionario</h5>
                        <form method="post">
                            <div class="form-group">
                                <label for="correo_funcionario">Correo del Funcionario:</label>
                                <input type="email" class="form-control" id="correo_funcionario" name="correo_funcionario" required>
                            </div>
                            <button type="submit" name="delete_funcionario" class="btn btn-danger">Eliminar Funcionario</button>
                        </form>
                    </div>

                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="jquery-3.5.1.min.js"></script>
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para mostrar u ocultar formularios
        function toggleForm(formId) {
            var form = document.getElementById(formId);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script><!-- Agregar Funcionario, Cambiar Contraseña, Eliminar Usuario, Eliminar Funcionario -->

                </div>
            </div>
        </div>
   

    <script src="jquery-3.5.1.min.js"></script>
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para mostrar u ocultar formularios
        function toggleForm(formId) {
            var form = document.getElementById(formId);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>



