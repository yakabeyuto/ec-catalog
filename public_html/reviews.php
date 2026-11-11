<?php
/**
 * reviews.php ― 商品レビューの一覧と投稿フォーム（第12回 配布）
 *
 * 第12回：レビュー本文も htmlspecialchars() を通して出す（XSS 対策）。
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM items WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    echo '商品が見つかりませんでした。<a href="items.php">一覧に戻る</a>';
    exit;
}

// この商品のレビューを新しい順に（投稿者の名前は users から引く）
$stmt = $pdo->prepare(
    'SELECT r.rating, r.body, u.name
       FROM reviews r JOIN users u ON u.id = r.user_id
      WHERE r.item_id = ?
      ORDER BY r.id DESC'
);
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();

$loggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>レビュー：<?= htmlspecialchars($item['name']) ?> ― Tsuchi-to-Hi</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .review { background: #fff; border: 1px solid #ececec; border-radius: 8px; padding: .8rem 1rem; margin-bottom: .8rem; }
        .review .meta { color: #57606f; font-size: .9rem; margin-bottom: .3rem; }
        .review .stars { color: #e0a100; }
    </style>
</head>
<body>
    <header>
        <a href="index.php">Tsuchi-to-Hi</a>
        <?php if ($loggedIn): ?><a href="mypage.php">マイページ</a><?php endif; ?>
    </header>
    <main>
        <p><a href="item.php?id=<?= (int) $item['id'] ?>">← 商品ページに戻る</a></p>
        <h1>カスタマーレビュー：<?= htmlspecialchars($item['name']) ?></h1>

        <?php if (!$reviews): ?>
            <p class="empty-state">まだレビューはありません。</p>
        <?php endif; ?>

        <?php foreach ($reviews as $r): ?>
            <div class="review">
                <div class="meta">
                    <?= htmlspecialchars($r['name']) ?> さん
                    <span class="stars"><?= str_repeat('★', (int) $r['rating']) . str_repeat('☆', 5 - (int) $r['rating']) ?></span>
                </div>
                <div class="review-body"><?= htmlspecialchars($r['body']) ?></div>
            </div>
        <?php endforeach; ?>

        <h2>レビューを書く</h2>
        <?php if ($loggedIn): ?>
            <form action="review-process.php" method="post">
                <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                <p><label>評価<br>
                    <select name="rating">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select></label></p>
                <p><label>本文（500文字まで）<br>
                    <textarea name="body" rows="4" cols="50" required></textarea></label></p>
                <button type="submit">投稿する</button>
            </form>
        <?php else: ?>
            <p><a href="login.php">ログイン</a>すると、レビューを書ける。</p>
        <?php endif; ?>
    </main>
</body>
</html>
