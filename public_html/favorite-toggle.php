<?php
// 既習：接続・ログイン・POST・CSRF・商品IDの確認は残す。
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$userId = (int) $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('POSTで送信してください');
}
if (!isset($_SESSION['csrf_token'], $_POST['csrf_token']) ||
    !is_string($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    exit('不正なリクエストです');
}
$itemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]);
if ($itemId === false || $itemId === null) {
    http_response_code(400);
    exit('商品IDを確認してください');
}
$stmt = $pdo->prepare('SELECT id FROM items WHERE id = ?');
$stmt->execute([$itemId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    exit('商品が見つかりません');
}
$stmt = $pdo->prepare(
    'SELECT 1 FROM favorites WHERE user_id = ? AND item_id = ?'
);
$stmt->execute([$userId, $itemId]);
if ($stmt->fetchColumn()) {
    $stmt = $pdo->prepare(
        'DELETE FROM favorites WHERE user_id = ? AND item_id = ?'
    );
} else {
    $stmt = $pdo->prepare(
        'INSERT INTO favorites (user_id, item_id) VALUES (?, ?)'
    );
}
$stmt->execute([$userId, $itemId]);
header('Location: item.php?id=' . $itemId);
exit;
