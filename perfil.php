<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$users_file = 'users.json';
$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
if (!is_array($users)) $users = [];

$user = $_SESSION['user'];
$posts = json_decode(file_get_contents('posts.json'), true) ?? [];
$myPosts = array_filter($posts, fn($p) => $p['user'] === $user);

// Avatar actual
$avatar = $_SESSION['users'][$user]['avatar'] ?? 'avatar1.webp';

// Cambiar avatar
if (isset($_POST['change_avatar'], $_POST['new_avatar'])) {
    $_SESSION['users'][$user]['avatar'] = $_POST['new_avatar'];
    $avatar = $_POST['new_avatar'];
}

// Cambiar contraseña
if (isset($_POST['change_password'], $_POST['new_password'])) {
    $_SESSION['users'][$user]['password'] = $_POST['new_password'];
}

//borrar cuenta del ussuario
if (isset($_POST['delete_account'])) {
    if (isset($users[$user])) {
        unset($users[$user]);
        file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi perfil</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
       
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .profile-avatar {
            width: 64px;
            height: 64px;
            border: 3px solid #000;
            image-rendering: pixelated;
        }

        .profile-buttons button {
            background-color: #7CFC00;
            border: 3px solid #000;
            padding: 8px 12px;
            font-family: 'Press Start 2P', cursive;
            font-size: 10px;
            cursor: pointer;
            margin-right: 10px;
            box-shadow: 3px 3px 0 #000;
        }

        .profile-buttons button:hover {
            background-color: #32CD32;
        }

        .profile-buttons button:active {
            box-shadow: 1px 1px 0 #000;
            transform: translate(2px,2px);
        }

        .profile-section {
            display: none; 
            margin-bottom: 20px;
        }

        .post {
            background-color: #4f4f4f;
            border: 4px solid #000;
            padding: 15px;
            margin-bottom: 10px;
        }

        .delete-btn {
            background-color: #FF6347; /* color rojo tipo “tomato” para diferenciar */
            color: #000; /* texto negro */
            border: 3px solid #000;
            padding: 8px 12px;
            font-family: 'Press Start 2P', cursive;
            font-size: 10px;
            cursor: pointer;
            margin-right: 10px;
            box-shadow: 3px 3px 0 #000;
        }

        .delete-btn:hover {
            background-color: #FF4500; /* rojo más intenso al pasar el mouse */
        }

        .delete-btn:active {
            box-shadow: 1px 1px 0 #000;
            transform: translate(2px,2px);
        }

    </style>
    <script>
        function toggleSection(id) {
            const section = document.getElementById(id);
            if(section.style.display === "block") {
                section.style.display = "none";
            } else {
                section.style.display = "block";
            }
        }
    </script>
</head>
<body>
<div class="container">

    <div class="profile-header">
        <img src="avatars/<?= $avatar ?>" class="profile-avatar">
        <p><strong><?= htmlspecialchars($user) ?></strong></p>
    </div>

    <div class="profile-buttons">
        <button onclick="toggleSection('change-avatar')">Cambiar Avatar</button>
        <button onclick="toggleSection('change-password')">Cambiar Contraseña</button>
        <form method="POST" style="display:inline;">
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
    <?php foreach ($myPosts as $i => $post): ?>
    <div class="post">
        <?= htmlspecialchars($post['content']) ?>

        <?php if (!empty($post['comments'])): ?>
            <ul style="margin-top:10px;">
                <?php foreach ($post['comments'] as $comment): ?>
                    <li>
                        <strong><?= htmlspecialchars($comment['user']) ?>:</strong>
                        <?= htmlspecialchars($comment['comment']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

    <br>
    <a href="foro.php" class="btn">Volver al foro</a>

</div>
</body>
</html>