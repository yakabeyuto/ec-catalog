<?php
/**
 * login.php ― ログイン画面（表示だけ。照合は login-process.php が行う）
 */
session_start();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン ― Tsuchi-to-Hi</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header><a href="index.php">Tsuchi-to-Hi</a></header>
    <main>
        <h1>ログイン</h1>

        <?php if (!empty($_SESSION['login_error'])): ?>
            <div class="error"><?= htmlspecialchars($_SESSION['login_error']) ?></div>
            <?php unset($_SESSION['login_error']); ?>
        <?php endif; ?>

        <form action="login-process.php" method="post">
            <p><label>メールアドレス<br><input type="email" name="email" required></label></p>
            <p><label>パスワード<br><input type="password" name="password" required></label></p>
            <button type="submit">ログイン</button>
        </form>
        <p><a href="register.php">はじめての方は会員登録</a></p>
    </main>
</body>
</html>
git status