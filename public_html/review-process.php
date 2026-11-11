<?php
/**
 * review-process.php ― レビューの保存（画面は出さない。最後はレビュー一覧へ戻る）
 * 保存は第2回からのプレースホルダなので、SQL インジェクションの心配はない。
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: items.php');
    exit;
}

$itemId = (int) ($_POST['item_id'] ?? 0);
$rating = (int) ($_POST['rating'] ?? 0);
$body   = trim($_POST['body'] ?? '');

// 入力チェック：評価は1〜5、本文は1〜500文字
if ($rating < 1 || $rating > 5 || $body === '' || mb_strlen($body) > 500) {
    header('Location: reviews.php?id=' . $itemId);
    exit;
}

$stmt = $pdo->prepare('INSERT INTO reviews (item_id, user_id, rating, body) VALUES (?, ?, ?, ?)');
$stmt->execute([$itemId, $_SESSION['user_id'], $rating, $body]);

header('Location: reviews.php?id=' . $itemId);
exit;
