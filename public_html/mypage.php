<?php
/**
 * mypage.php ― マイページ（ログイン中なら名前を表示する）
 * 未ログインでも開けてしまうのは第9回でガードを作るまでの仮の状態。
 */
require_once __DIR__ . '/../includes/config.php';
session_start();

$user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT name, role FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>マイページ ― Tsuchi-to-Hi</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <a href="index.php">Tsuchi-to-Hi</a>
        <?php if ($user): ?><a href="logout.php">ログアウト</a><?php endif; ?>
    </header>
    <main>
        <?php if ($user): ?>
            <h1>ようこそ、<?= htmlspecialchars($user['name']) ?> さん</h1>
            <p>会員種別：<?= $user['role'] === 'admin' ? '管理者' : '一般会員' ?></p>
        <?php else: ?>
            <h1>ようこそ、ゲスト さん</h1>
            <p><a href="login.php">ログインする</a></p>
        <?php endif; ?>
    </main>
</body>
</html>
