<?php
declare(strict_types=1);
session_start();

// --- Configuration (modifiable via variables d'environnement) ---
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$name = getenv('DB_NAME') ?: 'simple_site';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Connexion à la base impossible. Avez-vous lancé la migration (php migrate.php) ? ');
}

// --- Protection CSRF ---
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

// --- Actions (POST puis redirection) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('Jeton CSRF invalide.');
    }

    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        if ($title !== '' && mb_strlen($title) <= 255) {
            $pdo->prepare('INSERT INTO tasks (title) VALUES (?)')->execute([$title]);
        }
    } elseif ($action === 'toggle' && $id > 0) {
        $pdo->prepare('UPDATE tasks SET done = NOT done WHERE id = ?')->execute([$id]);
    } elseif ($action === 'delete' && $id > 0) {
        $pdo->prepare('DELETE FROM tasks WHERE id = ?')->execute([$id]);
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$tasks = $pdo->query('SELECT id, title, done, created_at FROM tasks ORDER BY done ASC, id DESC')->fetchAll();

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ma liste de tâches</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 560px; margin: 2rem auto; padding: 0 1rem; color: #222; }
        h1 { margin-bottom: 1rem; }
        form.add { display: flex; gap: .5rem; margin-bottom: 1.5rem; }
        form.add input[type=text] { flex: 1; padding: .6rem; border: 1px solid #ccc; border-radius: 6px; }
        button { padding: .5rem .9rem; border: 0; border-radius: 6px; background: #2563eb; color: #fff; cursor: pointer; }
        button.danger { background: #dc2626; }
        button.ghost { background: #e5e7eb; color: #222; }
        ul { list-style: none; padding: 0; }
        li { display: flex; align-items: center; gap: .5rem; padding: .6rem 0; border-bottom: 1px solid #eee; }
        li .title { flex: 1; }
        li.done .title { text-decoration: line-through; color: #888; }
        small { color: #999; }
        form.inline { margin: 0; }
    </style>
</head>
<body>
    <h1>Ma liste de tâches</h1>

    <form class="add" method="post">
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
        <input type="hidden" name="action" value="add">
        <input type="text" name="title" placeholder="Nouvelle tâche…" maxlength="255" required autofocus>
        <button type="submit">Ajouter</button>
    </form>

    <?php if (!$tasks): ?>
        <p>Aucune tâche pour le moment.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($tasks as $t): ?>
                <li class="<?= $t['done'] ? 'done' : '' ?>">
                    <span class="title"><?= e($t['title']) ?> <small><?= e($t['created_at']) ?></small></span>
                    <form class="inline" method="post">
                        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                        <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                        <button class="ghost" name="action" value="toggle"><?= $t['done'] ? 'Rouvrir' : 'Terminer' ?></button>
                        <button class="danger" name="action" value="delete">Supprimer</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
