<?php
// 第14回はリンク先の案内ページ。第15回で一覧・合計の画面に書き換える。
require_once __DIR__ . '/../includes/auth.php';
require_login();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>カート ― Tsuchi-to-Hi</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header><a href="items.php">Tsuchi-to-Hi</a></header>
    <main>
        <h1>ショッピングカート</h1>
        <p>カート画面への移動ができた。</p>
        <p>保存した商品の一覧は、第15回で作る。</p>
        <p><a href="items.php">← 商品一覧に戻る</a></p>
    </main>
</body>
</html>
