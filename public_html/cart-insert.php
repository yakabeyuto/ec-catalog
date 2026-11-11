<?php
// 既習：DB接続・ログイン確認。HTML出力より前に実行する。
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$userId = (int) $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('POSTで送信してください');
}
if (
    !isset($_SESSION['csrf_token'], $_POST['csrf_token']) ||
    !is_string($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    exit('不正なリクエストです');
}
$itemId = filter_input(
    INPUT_POST,
    'item_id',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
if ($itemId === false || $itemId === null) {
    http_response_code(400);
    exit('商品IDを確認してください');
}
$check = $pdo->prepare('SELECT id FROM items WHERE id = ?');
$check->execute([$itemId]);
if (!$check->fetch()) {
    http_response_code(404);
    exit('商品が見つかりません');
}
$stmt = $pdo->prepare(
    'INSERT INTO cart (user_id, item_id, quantity) VALUES (?, ?, 1)'
);
$stmt->execute([$userId, $itemId]);
$_SESSION['cart_message'] = 'カートに1個追加しました';
header('Location: item.php?id=' . $itemId);
exit;
