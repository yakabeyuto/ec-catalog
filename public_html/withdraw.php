<?php
/**
 * withdraw.php ― 退会の確認画面（第12回 配布）
 *
 * 第12回：CSRF トークンを発行して、フォームに隠して埋め込む。
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

// CSRF トークン：まだ無ければ、推測できないランダムな文字列を作ってセッションに預ける
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>退会 ― Tsuchi-to-Hi</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header><a href="index.php">Tsuchi-to-Hi</a><a href="mypage.php">マイページ</a></header>
    <main>
        <h1>退会</h1>
        <p>退会すると、会員情報と書いたレビューがすべて消える。元には戻せない。</p>
        <form action="withdraw-process.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <button type="submit" class="btn danger">退会する</button>
        </form>
    </main>
</body>
</html>
