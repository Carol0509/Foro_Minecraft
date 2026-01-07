<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Configuración de la base de datos
$host = 'localhost';
$db   = 'minecraft_forum';
$user_db = 'root'; 
$pass_db = '';     
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
     $pdo = new PDO($dsn, $user_db, $pass_db, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (\PDOException $e) {
     die("Error: " . $e->getMessage());
}

$user = $_SESSION['user'];
$user_id = $_SESSION['user_id'];
$avatar = $_SESSION['avatar'] ?? 'avatar1.webp';

// Cambiar avatar (solo en sesión ya que no hay columna en tu tabla users)
if (isset($_POST['change_avatar'], $_POST['new_avatar'])) {
    $_SESSION['avatar'] = $_POST['new_avatar'];
    $avatar = $_POST['new_avatar'];
}

// Cambiar contraseña en la DB
if (isset($_POST['change_password'], $_POST['new_password'])) {
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$_POST['new_password'], $user_id]);
}

// Borrar cuenta en la DB
if (isset($_POST['delete_account'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    session_destroy();
    header("Location: index.php");
    exit;
}

// Obtener MIS posts de la base de datos
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$myPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi perfil</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Mantenemos tus estilos originales */
        .profile-header { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; }
        .profile-avatar { width: 64px; height: 64px; border: 3px solid #000; image-rendering: pixelated; }
        .profile-buttons button { background-color: #7CFC00; border: 3px solid #000; padding: 8px 12px; font-family: 'Press Start 2P', cursive; font-size: 10px; cursor: pointer; margin-right: 10px; box-shadow: 3px 3px 0 #000; }
        .profile-section { display: none; margin-bottom: 20px; }
        .post { background-color: #4f4f4f; border: 4px solid #000; padding: 15px; margin-bottom: 10px; }
        .delete-btn { background-color: #FF6347; border: 3px solid #000; padding: 8px 12px; font-family: 'Press Start 2P', cursive; font-size: 10px; box-shadow: 3px 3px 0 #000; cursor: pointer; }
    </style>
    <script>
        function toggleSection(id) {
            const section = document.getElementById(id);
            section.style.display = (section.style.display === "block") ? "none" : "block";
        }
    </script>
</head>
<body>
<div class="container">
    <div class="profile-header">
        <img src="avatars/<?= htmlspecialchars($avatar) ?>" class="profile-avatar">
        <p><strong><?= htmlspecialchars($user) ?></strong></p>
    </div>

    <div class="profile-buttons">
        <button onclick="toggleSection('change-avatar')">Cambiar Avatar</button>
        <button onclick="toggleSection('change-password')">Cambiar Contraseña</button>
        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que quieres borrar tu cuenta?')">
            <button type="submit" name="delete_account" class="delete-btn">Eliminar mi cuenta</button>
        </form>
    </div>

    <div id="change-avatar" class="profile-section">
        <form method="POST">
            <div style="display:flex; gap:10px; flex-wrap: wrap;">
                <?php foreach (glob("avatars/*") as $a): ?>
                    <label>
                        <input type="radio" name="new_avatar" value="<?= basename($a) ?>" required>
                        <img src="<?= $a ?>" style="width:50px; height:50px; image-rendering: pixelated; border:2px solid #000;">
                    </label>
                <?php endforeach; ?>
            </div>
            <button name="change_avatar">Guardar Avatar</button>
        </form>
    </div>

    <div id="change-password" class="profile-section">
        <form method="POST">
            <input type="password" name="new_password" placeholder="Nueva contraseña" required>
            <button name="change_password">Guardar Contraseña</button>
        </form>
    </div>

    <h2>Mis publicaciones</h2>
    <?php foreach ($myPosts as $post): ?>
        <div class="post">
            <?= htmlspecialchars($post['content']) ?>
            <p><small>Publicado el: <?= $post['created_at'] ?></small></p>
        </div>
    <?php endforeach; ?>

    <br><a href="foro.php" class="btn">Volver al foro</a>
</div>
</body>
</html>