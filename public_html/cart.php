<?php
// 既習：DB接続・ログイン確認。HTML出力より前に実行する。
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$userId = (int) $_SESSION['user_id'];
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$stmt = $pdo->prepare(
    'SELECT cart.id, cart.quantity, items.name, items.price
     FROM cart JOIN items ON cart.item_id = items.id
     WHERE cart.user_id = ? ORDER BY cart.id'
);
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = 0;
foreach ($cartItems as $row) {
    $subtotal = (int) $row['price'] * (int) $row['quantity'];
    $total += $subtotal;
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>カート ― Tsuchi-to-Hi</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        table {
            width:100%;
            border-collapse:collapse;
        }
        th,td {
            padding:12px 6px;
            border-bottom:1px solid #ddd;
            text-align:left;
        }
        form {
            margin:0;
        }
        input[type=number] {
            width:70px;
        }
        button {
            white-space:nowrap;
        }
        main {
            overflow-x:auto;
        }
    </style>
</head>
<body>
    <header>
        <a href="items.php">Tsuchi-to-Hi</a>
    </header>
    <main>
        <p>
            <a href="items.php">← 商品一覧へ</a>
        </p>
        <h1>ショッピングカート</h1>
        <?php if (!$cartItems): ?>
            <p>カートは空です</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>商品名</th>
                        <th>単価</th>
                        <th>数量</th>
                        <th>小計</th>
                        <th>削除</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= number_format((int) $row['price']) ?>円</td>
                            <td>
                                <form action="cart-update.php" method="post">
                                    <input type="hidden" name="cart_id" value="<?= (int) $row['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                    <input aria-label="数量" type="number" name="quantity" min="1" max="2147483647" value="<?= (int) $row['quantity'] ?>" required>
                                    <button>更新</button>
                                </form>
                            </td>
                            <td><?= number_format((int) $row['price'] * (int) $row['quantity']) ?>円</td>
                            <td>
                                <form action="cart-delete.php" method="post">
                                    <input type="hidden" name="cart_id" value="<?= (int) $row['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                    <button>削除</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <h2>合計 <?= number_format($total) ?>円</h2>
    </main>
</body>
</html>
