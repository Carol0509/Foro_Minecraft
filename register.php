<?php
session_start();

if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
}

$error = null;


if (isset($_POST['register'])) {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    if (isset($_SESSION['users'][$user])) {
        $error = "Usuario ya existe";
    } else {
        $_SESSION['users'][$user] = $pass;
        //inicia sesión automáticamente después de registrarse
        $_SESSION['user'] = $user;
        header("Location: foro.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registro Foro Minecraft</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>¡Registrate!</h1>

<?php if ($error): ?>
<p style="color:red"><?= $error ?></p>
<?php endif; ?>

<form method="POST">
    <input type="text" name="username" placeholder="Usuario" required><br>
    <input type="password" name="password" placeholder="Contraseña" required><br>
    <button name="register">Registrarse</button>
</form>

<p class=""text-center><a href="index.php">Volver al inicio</a></p>
</div>
</body>
</html>
