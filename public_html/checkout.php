<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$userId = (int) $_SESSION['user_id'];
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

if (!$isPost && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit;
}
if ($isPost) {
    $token = $_POST['checkout_token'] ?? null;
    if (!isset($_SESSION['checkout']['token']) || !is_string($token) ||
        !hash_equals($_SESSION['checkout']['token'], $token)) {
        http_response_code(403);
        exit('確認画面を開き直してください。');
    }
}

try {
    if ($isPost) {
        $pdo->beginTransaction();
    }
    $sql = (
        'SELECT cart.id, cart.item_id, cart.quantity, items.name, items.price '
        . 'FROM cart JOIN items ON cart.item_id = items.id '
        . 'WHERE cart.user_id = ? ORDER BY cart.id'
    );
    $stmt = $pdo->prepare($sql . ($isPost ? ' FOR UPDATE' : ''));
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll();

    if ($isPost && $cartItems !== $_SESSION['checkout']['items']) {
        $pdo->rollBack();
        header('Location: checkout.php?changed=1', true, 303);
        exit;
    }
    if (!$cartItems) {
        header('Location: cart.php', true, 303);
        exit;
    }
    $total = 0;
    foreach ($cartItems as $row) {
        $total += (int) $row['price'] * (int) $row['quantity'];
    }
    if ($total > 2147483647 || $total < 0) {
        if ($isPost) {
            $pdo->rollBack();
        }
        http_response_code(400);
        exit('合計金額を確認してください。');
    }

    if ($isPost) {
        $order = $pdo->prepare('INSERT INTO orders (user_id, total_price) VALUES (?, ?)');
        $order->execute([$userId, $total]);
        $orderId = (int) $pdo->lastInsertId();

        $detail = $pdo->prepare(
            'INSERT INTO order_details (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)'
        );
        foreach ($cartItems as $row) {
            $detail->execute([$orderId, $row['item_id'], $row['quantity'], $row['price']]);
        }

        $delete = $pdo->prepare('DELETE FROM cart WHERE user_id = ?');
        $delete->execute([$userId]);
        $pdo->commit();
        unset($_SESSION['checkout']);
        header('Location: checkout-done.php?id=' . $orderId, true, 303);
        exit;
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    exit('注文処理に失敗しました。カートからやり直してください。');
}

$token = bin2hex(random_bytes(32));
$_SESSION['checkout'] = ['token' => $token, 'items' => $cartItems];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>購入確認 ― Tsuchi-to-Hi</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        th, td { padding: .65rem .35rem; border-bottom: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>
    <header><a href="items.php">Tsuchi-to-Hi</a></header>
    <main>
        <p><a href="cart.php">カートに戻る</a></p>
        <h1>購入確認</h1>
        <?php if (isset($_GET['changed'])): ?>
            <p>カートの内容が変わりました。もう一度確認してください。</p>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>商品名</th>
                    <th>単価</th>
                    <th>数量</th>
                    <th>小計</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= number_format((int) $row['price']) ?>円</td>
                        <td><?= (int) $row['quantity'] ?></td>
                        <td><?= number_format((int) $row['price'] * (int) $row['quantity']) ?>円</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h2>合計 <?= number_format($total) ?>円</h2>
        <form action="checkout.php" method="post">
            <input type="hidden" name="checkout_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit">注文を確定する</button>
        </form>
    </main>
</body>
</html>
