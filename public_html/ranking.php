<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$stmt = $pdo->query(
    'SELECT items.id, items.name, COUNT(*) AS favorite_count
     FROM favorites JOIN items ON favorites.item_id = items.id
     GROUP BY items.id, items.name
     ORDER BY favorite_count DESC, items.id ASC LIMIT 5'
);
$ranking = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>人気ランキング（上位5件） ― Tsuchi-to-Hi</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header><a href="index.php">Tsuchi-to-Hi</a></header>
    <main>
        <h1>人気ランキング（上位5件）</h1>
        <p><a href="items.php">商品一覧へ</a></p>
        <?php if (empty($ranking)): ?>
            <p>まだお気に入りがありません。</p>
        <?php else: ?>
            <ul>
                <?php foreach ($ranking as $row): ?>
                    <li>
                        <a href="item.php?id=<?= (int) $row['id'] ?>"><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></a>
                        <?= (int) $row['favorite_count'] ?>人
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</body>
</html>
