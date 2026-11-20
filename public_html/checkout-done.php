<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$userId = (int) $_SESSION['user_id'];
$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$orderId) {
    http_response_code(404);
    exit('注文が見つかりません。');
}
$stmt = $pdo->prepare('SELECT id, total_price FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();
if (!$order) {
    http_response_code(404);
    exit('注文が見つかりません。');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>購入完了 ― Tsuchi-to-Hi</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header><a href="items.php">Tsuchi-to-Hi</a></header>
    <main>
        <h1>購入完了</h1>
        <p>ご注文を受け付けました。</p>
        <p>注文番号 <?= (int) $order['id'] ?></p>
        <p>合計 <?= number_format((int) $order['total_price']) ?>円</p>
        <p><a href="items.php">商品一覧へ戻る</a></p>
    </main>
</body>
</html>
