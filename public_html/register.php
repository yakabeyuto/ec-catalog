<?php
/**
 * register.php ― 会員登録の画面（表示だけ。チェックと登録は register-process.php が行う）
 */
session_start();

$errors = $_SESSION['register_errors'] ?? [];
unset($_SESSION['register_errors']);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>会員登録 ― Tsuchi-to-Hi</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header><a href="index.php">Tsuchi-to-Hi</a></header>
    <main>
        <h1>会員登録</h1>

        <?php if ($errors): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="register-process.php" method="post">
            <p><label>氏名<br><input type="text" name="name" required></label></p>
            <p><label>メールアドレス<br><input type="email" name="email" required></label></p>
            <p><label>パスワード（8文字以上）<br><input type="password" name="password" required></label></p>
            <p><label>パスワード（確認）<br><input type="password" name="password_confirm" required></label></p>
            <button type="submit">登録する</button>
        </form>
        <p><a href="login.php">登録済みの方はログイン</a></p>
    </main>
</body>
</html>
