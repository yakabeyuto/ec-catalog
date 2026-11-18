<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$stmt = $pdo->prepare(
    'SELECT items.id, items.name, items.price
     FROM favorites JOIN items ON favorites.item_id = items.id
     WHERE favorites.user_id = ? ORDER BY items.id'
);
$stmt->execute([(int) $_SESSION['user_id']]);
$favoriteItems = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>お気に入り一覧 ― Tsuchi-to-Hi</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header><a href="index.php">Tsuchi-to-Hi</a></header>
    <main>
        <h1>お気に入り一覧</h1>
        <p><a href="items.php">商品一覧へ</a></p>
        <?php if (empty($favoriteItems)): ?>
            <p>まだお気に入りがありません。</p>
        <?php else: ?>
            <ul>
                <?php foreach ($favoriteItems as $row): ?>
                    <li>
                        <a href="item.php?id=<?= (int) $row['id'] ?>"><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></a>
                        ¥<?= number_format($row['price']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</body>
</html>
