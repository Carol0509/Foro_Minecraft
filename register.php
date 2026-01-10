<?php
session_start();

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
];

try {
     $pdo = new PDO($dsn, $user_db, $pass_db, $options);
} catch (\PDOException $e) {
     die("Error de conexión: " . $e->getMessage());
}

$error = null;
$avatars = ['avatar1.webp', 'avatar2.png', 'avatar3.png', 'avatar4.webp', 'avatar5.jpg'];

if (isset($_POST['register'])) {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);
    $avatar = $_POST['avatar'] ?? 'avatar1.webp';

    // Verificar si el usuario ya existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$user]);
    
    if ($stmt->fetchColumn() > 0) {
        $error = "Usuario ya existe";
    } else {
        // Insertar en la base de datos
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        if ($stmt->execute([$user, $pass])) {
            $new_id = $pdo->lastInsertId();
            
            $_SESSION['user'] = $user;
            $_SESSION['user_id'] = $new_id;
            
            header("Location: foro.php");
            exit;
        }
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
    <h1>¡Regístrate!</h1>
    <?php if ($error): ?>
        <p style="color:red"><?= $error ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Usuario" required><br>
        <input type="password" name="password" placeholder="Contraseña" required><br>
        <p>Elige tu avatar:</p>
        <?php foreach ($avatars as $a): ?>
            <label>
                <input type="radio" name="avatar" value="<?= $a ?>" required>
                <img src="avatars/<?= $a ?>" style="width:50px; height:50px; image-rendering: pixelated;">
            </label>
        <?php endforeach; ?><br>
        <button name="register">Registrarse</button>
    </form>
    <p class="text-center"><a href="index.php">Volver al inicio</a></p>
</div>
</body>
</html>