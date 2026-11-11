<?php
// 退会処理：第12回の認証・CSRF確認と、関連データの後片付け。
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: withdraw.php');
    exit;
}

// CSRF 対策：セッションのトークンと、送られてきたトークンが一致しなければ止める
$sent  = $_POST['csrf_token'] ?? '';
$saved = $_SESSION['csrf_token'] ?? '';
if (!is_string($sent) || $saved === '' || !hash_equals($saved, $sent)) {
    http_response_code(400);
    exit('不正なリクエストです');
}

// 第16回までの関連データも先に削除する。レビューはON DELETE CASCADEで消える。
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('DELETE FROM cart WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $stmt = $pdo->prepare('DELETE FROM favorites WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}

// ログアウトと同じく、セッションを捨てる
$_SESSION = [];
session_destroy();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>退会しました ― Tsuchi-to-Hi</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header><a href="index.php">Tsuchi-to-Hi</a></header>
    <main>
        <h1>退会しました</h1>
        <p>ご利用ありがとうございました。</p>
        <p><a href="index.php">トップへ</a></p>
    </main>
</body>
</html>
