<?php
session_start();
require 'config.php';

// Manejar el inicio de sesión
// Guardar el evento seleccionado en la sesión si viene como parámetro
if (isset($_GET['evento'])) {
    $_SESSION['evento_seleccionado'] = $_GET['evento'];
}

// Manejar el inicio de sesión
if (isset($_POST['login'])) {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    // Verificar en usuarios
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE correo = ?');
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && hash('sha256', $contrasena) === $usuario['contrasena']) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['rol'] = 'usuario';

        // Redirigir a usuario.php con el evento seleccionado
        header('Location: usuario.php');
        exit();
    }

    // Verificar en funcionarios
    $stmt = $pdo->prepare('SELECT * FROM funcionarios WHERE correo = ?');
    $stmt->execute([$correo]);
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($funcionario && hash('sha256', $contrasena) === $funcionario['contrasena']) {
        $_SESSION['funcionario_id'] = $funcionario['id'];
        $_SESSION['rol'] = 'funcionario';
        header('Location: funcionario.php');
        exit();
    }

    $error = 'Correo o contraseña incorrectos';
}

// Manejar el registro de usuario
if (isset($_POST['register'])) {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];
    $rol = 'usuario';

    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE correo = ?');
    $stmt->execute([$correo]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        $error = 'El correo ya está registrado';
    } else {
        $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, correo, contrasena, rol) VALUES (?, ?, ?, ?)');
        $stmt->execute([$nombre, $correo, hash('sha256', $contrasena), $rol]);
        $success = 'Registro exitoso. Puedes iniciar sesión ahora.';
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login y Registro</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="shortcut icon" href="../img/logotipo.png">
    <script src="../bootstrap/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../css/index.css">
    
    <style>
        #registroModal .modal-body, 
        #registroModal .modal-title, 
        #registroModal .form-label, 
        #registroModal .form-control {
            color: black; /* Establece el color del texto a negro */
        }
    </style>
</head>
<body>
<header class="welcome-section text-center">
    <div class="overlay"><br>
        <h1 class="display-4 fw-bold">Iniciar Sesión</h1>
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="../index.html"><img src="../img/logotipo.png" alt="Logo de Age" width="100px"></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                    aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavDropdown">
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="../index.html">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="../html/eventos.html">Eventos</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Login</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</header>
    <div class="login-container mt-5">
        <center>
            <a class="navbar-brand" href="../index.html"><img src="../img/logotipo.png" alt="Logo de Age" width="150px"></a>
        </center>
        <?php if (isset($error) && isset($_POST['login'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label for="correo_login" class="form-label">Correo</label>
                <input type="email" id="correo_login" name="correo" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="contrasena_login" class="form-label">Contraseña</label>
                <input type="password" id="contrasena_login" name="contrasena" class="form-control" required>
            </div><br>
            <button type="submit" name="login" class="btn btn-primary w-100">Iniciar Sesión</button>
        </form>
        <p class="mt-3 text-center">
            <a href="#" style="text-decoration: none" data-bs-toggle="modal" data-bs-target="#registroModal" class="btn btn-link">Registrarse</a>
        </p>
        <p class="text-center">
            <a href="recuperar_contrasena.php" style="text-decoration:none" class="btn btn-link">¿Olvidaste tu contraseña?</a>
        </p>
    </div>

    <!-- Modal de Registro -->
    <div class="modal fade" id="registroModal" tabindex="-1" aria-labelledby="registroModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registroModalLabel">Registro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (isset($error) && isset($_POST['register'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="correo_registro" class="form-label">Correo</label>
                            <input type="email" id="correo_registro" name="correo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="contrasena_registro" class="form-label">Contraseña</label>
                            <input type="password" id="contrasena_registro" name="contrasena" class="form-control" required>
                        </div>
                        <input type="hidden" name="rol" value="usuario"> <!-- El rol está predefinido como usuario -->
                        <button type="submit" name="register" class="btn btn-primary w-100">Registrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div><br><br><br>

    <footer class="footer bg-dark text-white pt-4 pb-2">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h4>Contáctenos:</h4>
                    <p><strong>NEIVA - HUILA</strong><br><strong>Celular:</strong> +91-321 567 3567<br><strong>Email:</strong> <a href="mailto:AGE@gmail.com">AGE@gmail.com</a></p>
                </div>
                <div class="col-lg-6 text-lg-end">
                    <h4>Síguenos:</h4>
                    <a href="https://facebook.com" target="_blank"><img src="../img/facebook.jpeg" alt="Facebook" class="social-icon"></a>
                    <a href="https://web.whatsapp.com" target="_blank"><img src="../img/WhatsApp.png" alt="WhatsApp" class="social-icon"></a>
                    <a href="https://instagram.com" target="_blank"><img src="../img/hashtag.png" alt="Instagram" class="social-icon"></a>
                </div>
            </div>
            <div class="footer-bottom text-center pt-3">
                &copy; 2024. Todos los Derechos Reservados - AGE.<br>
                <a href="http://www.agereservas.com" target="_blank">www.agereservas.com</a>
            </div>
        </div>
    </footer>    
   <!-- Contenedor del Chat -->
<div id="chat-container" class="chat-container">
    <div id="chat-box" class="chat-box">
        <div class="chat-messages">
            <div class="bot-message">
                ¡Hola! Soy el asistente de la Agencia de Gestión de Eventos. ¿Cómo puedo ayudarte hoy?
            </div>
        </div>
        <input type="text" id="user-input" placeholder="Escribe un mensaje..." onkeydown="checkEnter(event)">
        <button onclick="sendMessage()">Enviar</button>
    </div>
</div>

<!-- Chat bubble button -->
<div class="chat-bubble" onclick="toggleChat()">
  <img src="../img/bot.png" alt="bot" width="120x">
</div>

<!-- Chat container -->
<div class="chat-container" id="chatContainer">
    <div class="chat-header">
        <span>Asistente AGE</span>
        <span class="close-chat" onclick="toggleChat()">✖</span>
    </div>
    <div class="chat-box" id="chatBox">
        <div class="bot-message">
            ¡Hola! Soy el asistente virtual de AGE. ¿En qué puedo ayudarte hoy?
        </div>
    </div>
    <div class="chat-input-container">
        <input type="text" class="chat-input" id="userInput" placeholder="Escribe tu mensaje..." onkeypress="handleKeyPress(event)">
        <button class="send-button" onclick="sendMessage()">Enviar</button>
    </div>
</div>

<script>
    function toggleChat() {
        const chatContainer = document.getElementById('chatContainer');
        chatContainer.classList.toggle('active');
    }

    function handleKeyPress(event) {
        if (event.key === 'Enter') {
            sendMessage();
        }
    }

    function sendMessage() {
        const input = document.getElementById('userInput');
        const message = input.value.trim();
        
        if (message === '') return;
        
        displayMessage(message, 'user');
        input.value = '';
        
        // Procesar respuesta del bot
        setTimeout(() => {
            const response = getBotResponse(message.toLowerCase());
            displayMessage(response, 'bot');
        }, 500);
    }

    function displayMessage(message, sender) {
        const chatBox = document.getElementById('chatBox');
        const messageDiv = document.createElement('div');
        messageDiv.className = sender + '-message';
        messageDiv.textContent = message;
        chatBox.appendChild(messageDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function getBotResponse(message) {
        const responses = {
    'hola': '¡Hola! Como puedo ayudarte hoy? 😊',
    'adios': '¡Hasta luego! Que tengas un excelente día. 👋',
    'gracias': 'De nada, estoy para servirte. 🤝',
    'que servicios ofrecen': '📅 Ofrecemos una variedad de servicios para distintos tipos de eventos. Estos son algunos de nuestros paquetes más populares:\n\n- **Cumpleaños** 🎂\n- **Bodas** 💒\n- **XV Años** 👗\n- **Baby Showers** 👶\n- **Despedidas de Solteros** 🍸\n- **Graduaciones** 🎓\n\nSi deseas más información sobre algún servicio en particular, ¡no dudes en preguntar!',
    'Nuestro de servios': '📅 Ofrecemos una variedad de servicios para distintos tipos de eventos. Estos son algunos de nuestros paquetes más populares:\n\n- **Cumpleaños** 🎂\n- **Bodas** 💒\n- **XV Años** 👗\n- **Baby Showers** 👶\n- **Despedidas de Solteros** 🍸\n- **Graduaciones** 🎓\n\nSi deseas más información sobre algún servicio en particular, ¡no dudes en preguntar!',
    'horarios': '🕒 Nuestro horario de atención es:\n- Lunes a Viernes: 8:00 AM - 6:00 PM\n- Sábados: 9:00 AM - 2:00 PM\n\n¡Te esperamos con gusto durante este horario!',
    'Horarios': '🕒 Nuestro horario de atención es:\n- Lunes a Viernes: 8:00 AM - 6:00 PM\n- Sábados: 9:00 AM - 2:00 PM\n\n¡Te esperamos con gusto durante este horario!',
    'donde estan ubicados': '📍 Estamos ubicados en el **Club Campestre** en Huila, Colombia. Si necesitas indicaciones o más detalles sobre cómo llegar, no dudes en pedírmelo. También puedes contactarnos al 📞 +91-321 567 3567.',
    'como puedo hacer una reserva': '📝 Puedes reservar de tres formas:\n1. 📞 Llamando al +91-321 567 3567\n2. 📧 Enviando un correo a AGE@gmail.com\n3. 🏢 Visitando nuestra oficina\n\n¿Te gustaría hacer una reserva ahora? Si es así, haz clic aquí para reservar: [Reservar](html/cumpleaños.html)',
    'quiero hacer mi reserva': '📝 Puedes reservar de tres formas:\n1. 📞 Llamando al +91-321 567 3567\n2. 📧 Enviando un correo a AGE@gmail.com\n3. 🏢 Visitando nuestra oficina\n\nHaz clic aquí para hacer tu reserva ahora: [Reservar](html/cumpleaños.html)',
    'reservar': '📝 ¿Quieres hacer una reserva? Puedes hacerlo llamando al +91-321 567 3567, enviando un correo a AGE@gmail.com o visitando nuestra oficina. Haz clic aquí para reservar ahora: [Reservar](html/cumpleaños.html)',
    'que incluye el paquete de bodas': '💒 Nuestro paquete de bodas incluye:\n- Decoración elegante\n- Catering gourmet\n- Música en vivo/DJ\n- Fotografía profesional\n- Coordinación del evento\n¿Te gustaría conocer más detalles?',
    'tienen descuentos': '💰 En este momento, no ofrecemos descuentos en nuestros paquetes. Todos nuestros servicios están cuidadosamente diseñados para garantizar la mejor calidad para tu evento.',
    'aceptan tarjetas de credito': '💳 Aceptamos **dos métodos de pago** en Colombia:\n1. **Billeteras digitales** (como Nequi, Daviplata, o cualquier billetera digital colombiana), con solo enviar la **confirmación de pago**.\n2. **Pago presencial** en nuestra oficina, con un temporizador de **24 horas** para completar el pago. Si necesitas más detalles sobre estos métodos, no dudes en preguntarme.',
    'Formas de pagos': '💳 Aceptamos **dos métodos de pago** en Colombia:\n1. **Billeteras digitales** (como Nequi, Daviplata, o cualquier billetera digital colombiana), con solo enviar la **confirmación de pago**.\n2. **Pago presencial** en nuestra oficina, con un temporizador de **24 horas** para completar el pago. Si necesitas más detalles sobre estos métodos, no dudes en preguntarme.',
    'precio': '💵 Los precios varían según el tipo de evento, la cantidad de invitados y los servicios incluidos. Cada paquete es personalizado según tus necesidades. Si tienes una idea más clara sobre el tipo de evento o los servicios que deseas, puedo proporcionarte una estimación de precios. ¿Sobre qué paquete te gustaría saber más?',
    'Precios': '💵 Los precios varían según el tipo de evento, la cantidad de invitados y los servicios incluidos. Cada paquete es personalizado según tus necesidades. Si tienes una idea más clara sobre el tipo de evento o los servicios que deseas, puedo proporcionarte una estimación de precios. ¿Sobre qué paquete te gustaría saber más?',
    'Reservas': '📝 ¿Quieres hacer una reserva? Puedes hacerlo llamando al +91-321 567 3567, enviando un correo a AGE@gmail.com o visitando nuestra oficina. Haz clic aquí para reservar ahora: [Reservar](html/cumpleaños.html)',
    'quiero mas informacion': '📚 Claro, ¿sobre qué te gustaría saber más?\n- **Paquetes disponibles**\n- **Servicios adicionales**\n- **Fechas disponibles**\n- **Promociones actuales**\n\n¡Estoy aquí para brindarte toda la información que necesites!',
    'tienen disponibilidad': '📆 La disponibilidad varía según la fecha.\n¿Para qué fecha estás planeando tu evento?',
    'necesito ayuda': '🤝 ¡Por supuesto! Si necesitas ayuda, puedes contactarnos directamente por:\n- 📞 Llamada al +91-321 567 3567\n- 📧 Envíanos un correo a AGE@gmail.com\n- 🏢 Visítanos en nuestra oficina en el Club Campestre, Huila.\n\nEstaremos encantados de asistirte con cualquier consulta. ¿Cómo te gustaría proceder?',
    'que incluye el paquete de cumpleaños': '🎂 El paquete de cumpleaños incluye:\n- Decoración temática\n- Catering personalizado\n- Música y animación\n- Pastel personalizado\n- Coordinación del evento',
    'que incluye el paquete de xv años': '👗 El paquete de XV años incluye:\n- Decoración elegante\n- Catering completo\n- Música y DJ\n- Fotografía/Video\n- Coordinación del evento\n- Coreografía',
    'que incluye el paquete de baby shower': '👶 El paquete de Baby Shower incluye:\n- Decoración temática\n- Mesa de postres\n- Juegos y actividades\n- Catering especial\n- Coordinación del evento',
    'ofrecen servicios adicionales': '✨ Sí, ofrecemos servicios adicionales como:\n- Fotografía profesional\n- Video/Drone\n- Hora loca\n- Barman\n- Transportes\n- Shows especiales',
    'no entiendo': 'Lo siento, aún no tengo suficiente información sobre esa pregunta ya que estamos en proceso de programación. 😅\nSin embargo, puedes preguntarme sobre:\n- Nuestros servicios\n- Precios\n- Reservas\n- Horarios\n- Ubicación\n- Formas de pago'
};


        // Buscar la mejor coincidencia para la respuesta
        for (let key in responses) {
            if (message.includes(key)) {
                return responses[key];
            }
        }

        return "No entiendo tu pregunta. ¿Podrías reformularla? O puedes preguntarme sobre:\n- Nuestros servicios\n- Precios\n- Reservas\n- Horarios\n- Ubicación\n- Formas de pago";
    
    }

</script>
</body>
</html>
