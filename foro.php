<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$avatar = $_SESSION['avatar'] ?? 'avatar1.webp';
$posts_file = 'posts.json';
$posts = [];

// Leer posts
if (file_exists($posts_file)) {
    $posts = json_decode(file_get_contents($posts_file), true) ?? []; //cambiar esto para la base de datos
}

// Crear post
if (isset($_POST['post_content'])) {
    $content = trim($_POST['post_content']);
    if ($content !== '') { 
        array_unshift($posts, [
        'user' => $user,
        'content' => $content,
        'comments' => []
    ]);
    file_put_contents($posts_file, json_encode($posts)); //debes cambiar esto para la base de datos
    header("Location: foro.php#post-$lastIndex");
    exit;
}
}

// Borrar post
if (isset($_POST['delete_post'])) {
    $index = intval($_POST['post_index']);

    if (isset($posts[$index]) && $posts[$index]['user'] === $user) {
        array_splice($posts, $index, 1);
        file_put_contents($posts_file, json_encode($posts));
        header("Location: foro.php");
        exit;
    }
}


// Comentario
if (isset($_POST['comment_content'], $_POST['post_index'])) {
    $index = intval($_POST['post_index']);
    $comment = trim($_POST['comment_content']);
    if (isset($posts[$index])){
    $posts[$index]['comments'][] = [
        'user' => $user,
        'comment' => $comment
    ];
    
    file_put_contents($posts_file, json_encode($posts)); //cambiar esto tambien para la base de datos
    header("Location: foro.php#post-$index");
    exit;
} else {
    $error = "El Post al que intentas comentar no existe";
}
}

$posts = json_decode(file_get_contents($posts_file), true) ?? [];

// Para borrar tus propios comentarios
if (isset($_POST['delete_comment'])) {
    $postIndex = intval($_POST['post_index']);
    $commentIndex = intval($_POST['comment_index']);

    if (
        isset($posts[$postIndex]['comments'][$commentIndex]) &&
        $posts[$postIndex]['comments'][$commentIndex]['user'] === $user
    ) {
        array_splice($posts[$postIndex]['comments'], $commentIndex, 1);
        file_put_contents($posts_file, json_encode($posts));
        header("Location: foro.php#post-$postIndex");
        exit;
    }
}


if (isset($_POST['delete_post'])) {
    $index = intval($_POST['delete_post']);

    // Solo borrar si el post pertenece al usuario logueado
    if (isset($posts[$index]) && $posts[$index]['user'] === $user) {
        unset($posts[$index]);
        $posts = array_values($posts); // Reindexar
        file_put_contents($posts_file, json_encode($posts));
        header("Location: foro.php" . (isset($_GET['my_posts']) ? "?my_posts=1" : ""));
        exit;
    }
}

// Si el usuario pulsa "Mis publicaciones", se filtrara solo los suyos
$showPosts = $posts;

if (isset($_GET['my_posts'])) {
    $showPosts = array_values(array_filter($posts, fn($p) => $p['user'] === $user));
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Foro Minecraft</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="foro">
<div class="container">
    <div class="user-panel">
    <img src="avatars/<?= htmlspecialchars($avatar) ?>" class="avatar">

    <div class="user-info">
        <p><strong><?= htmlspecialchars($user) ?></strong></p>
        <p>
            Posts: <?= count(array_filter($posts, fn($p) => $p['user'] === $user)) ?>
        </p>
        <form method="GET">
            <a href="perfil.php" class="btn profile-btn">Perfil</a>
        </form>
        <form method="GET" style="display:inline-block;">
            <button>Todos los posts</button>
        </form>
    </div>
</div>

<h1>Foro - Bienvenido <?= htmlspecialchars($user) ?></h1>
<a href="logout.php">Cerrar sesión</a>

<h2>Crear nuevo post</h2>
<form method="POST">
    <textarea name="post_content" required placeholder="Escribe tu post..."></textarea><br>
    <button>Publicar</button>
</form>

<h2>Posts recientes</h2>
<?php foreach ($showPosts as $i => $post): ?>
<div class="post" id="post-<?= $i ?>">
    <strong class="post-user"><?= htmlspecialchars($post['user']) ?></strong><br>
    <?= htmlspecialchars($post['content']) ?><br><br>

    <?php if ($post['user'] === $user): ?>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="post_index" value="<?= $i ?>">
            <button name="delete_post" class="delete-btn">Borrar post</button>
        </form>
    <?php endif; ?>
    
    <form method="POST">
        <input type="hidden" name="post_index" value="<?= $i ?>">
        <input type="text" name="comment_content" placeholder="Comentar..." required>
        <button>Comentar</button>
    </form>

    <?php if (!empty($post['comments'])): ?>
        <ul>
        <?php foreach ($post['comments'] as $ci => $c): ?>
            <li>
                <strong><?= htmlspecialchars($c['user']) ?>:</strong>
                <?= htmlspecialchars($c['comment']) ?>
                
                <?php if ($c['user'] === $user): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="post_index" value="<?= $i ?>">
                        <input type="hidden" name="comment_index" value="<?= $ci ?>">
                        <button name="delete_comment" class="delete-btn">Borrar</button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
<?php endforeach; ?>
</div>

</body>
</html>
