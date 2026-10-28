<?php
/**
 * register.php ― 会員登録の画面（表示だけ。チェックと登録は register-process.php が行う）
 */
session_start();

$errors = $_SESSION['register_errors'] ?? [];
$old    = $_SESSION['register_old'] ?? [];
unset($_SESSION['register_errors'], $_SESSION['register_old']);

// 項目の下に出すメッセージ（エラーが無ければ何も出さない）
function field_error(array $errors, string $key): string
{
    if (!isset($errors[$key])) {
        return '';
    }
    return '<p class="field-error">' . htmlspecialchars($errors[$key]) . '</p>';
}
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
            <div class="error">入力内容に <?= count($errors) ?> 件のエラーがあります。赤い文字の項目を直してください。</div>
        <?php endif; ?>

        <form action="register-process.php" method="post">
            <p><label>氏名<br><input type="text" name="name" required
                value="<?= htmlspecialchars($old['name'] ?? '') ?>"></label>
                <?= field_error($errors, 'name') ?></p>
            <p><label>メールアドレス<br><input type="email" name="email" required
                value="<?= htmlspecialchars($old['email'] ?? '') ?>"></label>
                <?= field_error($errors, 'email') ?></p>
            <p><label>パスワード（8文字以上）<br><input type="password" name="password" required></label>
                <?= field_error($errors, 'password') ?></p>
            <p><label>パスワード（確認）<br><input type="password" name="password_confirm" required></label>
                <?= field_error($errors, 'password_confirm') ?></p>
            <button type="submit">登録する</button>
        </form>
        <p><a href="login.php">登録済みの方はログイン</a></p>
    </main>
</body>
</html>
