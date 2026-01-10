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

$user = $_SESSION['user'];
$user_id = $_SESSION['user_id'] ?? 0; 
$avatar = $_SESSION['avatar'] ?? 'avatar1.webp';

// --- ACCIONES ---

// Crear post
if (isset($_POST['post_content'])) {
    $content = trim($_POST['post_content']);
    if ($content !== '') { 
        $stmt = $pdo->prepare("INSERT INTO posts (title, content, type, user_id) VALUES (?, ?, 'MOD', ?)");
        $stmt->execute(['Nuevo Post', $content, $user_id]);
        header("Location: foro.php");
        exit;
    }
}


// Crear Comentario
if (isset($_POST['comment_content'], $_POST['post_id'])) {
    $post_id = intval($_POST['post_id']);
    $comment = trim($_POST['comment_content']);
    if ($comment !== '') {
        $stmt = $pdo->prepare("INSERT INTO comments (content, post_id, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$comment, $post_id, $user_id]);
        header("Location: foro.php#post-$post_id");
        exit;
    }
}

// Borrar post
if (isset($_POST['delete_post'])) {
    $post_id = intval($_POST['post_id']);
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$post_id, $user_id]);
    header("Location: foro.php");
    exit;
}

// Borrar comentario
if (isset($_POST['delete_comment'])) {
    $comment_id = intval($_POST['comment_id']); // Ahora recibimos el ID real de la tabla
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    $stmt->execute([$comment_id, $user_id]); // Solo el dueño puede borrarlo
    header("Location: foro.php");
    exit;
}

// --- OBTENCIÓN DE DATOS CON FILTROS ---

$params = [];
$where_clauses = [];

// Base de la consulta
$query = "SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id";

// Filtro por Usuario (Nombre parcial o exacto)
if (!empty($_GET['filter_user'])) {
    $where_clauses[] = "u.username LIKE ?";
    $params[] = "%" . $_GET['filter_user'] . "%";
}

// Filtro por Fecha
if (!empty($_GET['filter_date'])) {
    $where_clauses[] = "DATE(p.created_at) = ?";
    $params[] = $_GET['filter_date'];
}

// Filtro de "Mis posts"
if (isset($_GET['my_posts'])) {
    $where_clauses[] = "p.user_id = ?";
    $params[] = $user_id;
}

// Unir las cláusulas WHERE si existen
if (count($where_clauses) > 0) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

// Ordenar siempre por fecha descendente
$query .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$showPosts = $stmt->fetchAll();
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
            <p>Posts: <?= $pdo->query("SELECT COUNT(*) FROM posts WHERE user_id = $user_id")->fetchColumn() ?></p>
            <a href="perfil.php" class="btn profile-btn">Perfil</a>
            <form method="GET" style="display:inline-block;">
                <button name="all_posts">Todos los posts</button>
            </form>
            <form method="GET" style="display:inline-block;">
                <button name="my_posts" value="1">Mis posts</button>
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
    <br>
    <div class="filters" style="background: #333; padding: 15px; margin-bottom: 20px; border: 1px dashed #7CFC00;">
        <h3 style="font-size: 12px; color: #7CFC00;">Filtrar búsqueda</h3>
        <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
            
            <input type="text" name="filter_user" placeholder="Usuario..." 
                value="<?= htmlspecialchars($_GET['filter_user'] ?? '') ?>" 
                style="padding: 5px; font-family: 'Press Start 2P'; font-size: 10px;">

            <input type="date" name="filter_date" 
                value="<?= htmlspecialchars($_GET['filter_date'] ?? '') ?>" 
                style="padding: 5px; font-family: 'Press Start 2P'; font-size: 10px;">

            <button type="submit" class="btn">Aplicar</button>
            <a href="foro.php" style="color: #FF6347; font-size: 10px; text-decoration: none;">Limpiar</a>
        </form>
    </div>

    <h2>Posts recientes</h2>
    <?php foreach ($showPosts as $post): ?>
        <div class="post" id="post-<?= $post['id'] ?>" style="border: 1px solid #555; padding: 15px; margin-bottom: 20px; background: #222;">
            <strong style="color: #7CFC00;"><?= htmlspecialchars($post['username']) ?></strong>
            <p style="margin: 10px 0;"><?= htmlspecialchars($post['content']) ?></p>

            <?php if ($post['user_id'] == $user_id): ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                    <button name="delete_post" class="delete-btn">Borrar post</button>
                </form>
            <?php endif; ?>
            
            <form method="POST" style="margin-top: 15px;">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <input type="text" name="comment_content" placeholder="Escribe un comentario..." required style="width: 70%; padding: 5px;">
                <button type="submit">Comentar</button>
            </form>

            <?php 
            // Consultar comentarios dentro del bucle para evitar solapamientos
            $stmt_c = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC");
            $stmt_c->execute([$post['id']]);
            $comments = $stmt_c->fetchAll();
            
            if (!empty($comments)): ?>
                <div style="background: #333; margin-top: 10px; padding: 10px; border-radius: 5px;">
                    <?php foreach ($comments as $c): ?>
    <div style="border-bottom: 1px solid #444; padding: 5px 0;">
        <strong style="color: #32CD32; font-size: 0.8em;"><?= htmlspecialchars($c['username']) ?>:</strong>
        <span style="font-size: 0.9em;"><?= htmlspecialchars($c['content']) ?></span>

        <?php if ($c['user_id'] == $user_id): ?>
            <form method="POST" style="display:inline; margin-left: 10px;">
                <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                <button name="delete_comment" class="delete-btn" style="font-size: 8px; padding: 2px 5px; background-color: #FF6347; border: 1px solid #000; cursor: pointer;">
                    Borrar
                </button>
            </form>
        <?php endif; ?>
    </div>
        <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>