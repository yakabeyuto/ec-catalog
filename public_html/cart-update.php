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
$cartId = filter_input(
    INPUT_POST,
    'cart_id',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$quantity = filter_input(
    INPUT_POST,
    'quantity',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1, 'max_range' => 2147483647]]
);
if (
    $cartId === false || $cartId === null ||
    $quantity === false || $quantity === null
) {
    http_response_code(400);
    exit;
}
$stmt = $pdo->prepare(
    'UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?'
);
$stmt->execute([$quantity, $cartId, $userId]);
header('Location: cart.php');
exit;
