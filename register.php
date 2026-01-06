<?php
session_start();

if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
}

$error = null;

$users_file = 'users.json';
$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
if (!is_array($users)) $users = [];
$avatars = [
    'avatar1.webp',
    'avatar2.png',
    'avatar3.png',
    'avatar4.webp',
    'avatar5.jpg'
];


if (isset($_POST['register'])) {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);
    $avatar = $_POST['avatar'] ?? 'avatar1.webp'; //predeterminado

    if (isset($users[$user])) {
        $error = "Usuario ya existe";
    } else {
        $users[$user] = [
            'password' => $pass,
            'avatar' => $avatar
        ];

        file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
        //inicia sesión automáticamente después de registrarse
        $_SESSION['user'] = $user;
        $_SESSION['avatar'] = $avatar;
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
    <p>Elige tu avatar:</p>
        <?php foreach ($avatars as $a): ?>
            <label>
                <input type="radio" name="avatar" value="<?= $a ?>" required>
                <img src="avatars/<?= $a ?>" style="width:50px; height:50px; image-rendering: pixelated;">
            </label>
        <?php endforeach; ?><br>
    <button name="register">Registrarse</button>
</form>

<p class=""text-center><a href="index.php">Volver al inicio</a></p>
</div>
</body>
</html>