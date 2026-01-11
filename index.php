<?php
session_start();

// Si ya inició sesión, redirigir al foro
if (isset($_SESSION['user'])) {
    header("Location: foro.php");
    exit;
}

// Configuración de la base de datos
$host = 'localhost';
$db   = 'minecraft_forum';
$user_db = 'root'; 
$pass_db = '';     
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user_db, $pass_db, $options);
} catch (\PDOException $e) {
     die("Error de conexión: " . $e->getMessage());
}

$error = null;

// Guardar en variables los datos de registro
if (isset($_POST['login'])) {
    $user_input = trim($_POST['username']);
    $pass_input = trim($_POST['password']);

    // Consultar el usuario en la base de datos
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user_input]);
    $user_data = $stmt->fetch();

    // Verificación
    if ($user_data && $user_data['password'] === $pass_input) {
        $_SESSION['user'] = $user_data['username'];
        $_SESSION['user_id'] = $user_data['id'];
        $_SESSION['avatar'] = 'avatar1.webp';
        
        header("Location: foro.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Foro-Minecraft</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1 class="text-center">¡Bienvenido al Foro de Minecraft!</h1>
    <p class="text-center">
        Comparte tus conocimientos acerca de mods y trucos de minecraft.<br>
        <strong> ¡ ÚNETE A NOSOTROS SI NO TIENES CUENTA! </strong>
    </p>

<?php if ($error): ?>
<p style="color:red"><?= $error ?></p>
<?php endif; ?>

<h2>Iniciar sesión</h2>
<form method="POST">
    <input type="text" name="username" placeholder="Usuario" required><br>
    <input type="password" name="password" placeholder="Contraseña" required><br>
    <button name="login">Entrar</button>
</form>

<p class="text-center">¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
</div>
</body>
</html>