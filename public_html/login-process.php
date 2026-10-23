<?php
/**
 * login-process.php ― ログイン処理（画面は出さない。最後は必ずどこかへ移動する）
 */
require_once __DIR__ . '/../includes/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

const MAX_FAILED_ATTEMPTS = 5;
const LOCK_MINUTES        = 5;
$genericError = 'メールアドレスまたはパスワードが違います';

// ① メールアドレスでユーザーを探す
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

// ④ 上級：ロック中なら、パスワードを見る前に追い返す
if ($user && $user['locked_until'] !== null && strtotime($user['locked_until']) > time()) {
    $_SESSION['login_error'] = '一定時間ログインできません。しばらくしてから再度お試しください';
    header('Location: login.php');
    exit;
}

// ② いる かつ パスワード一致 → 成功
if ($user && $password === $user['password']) {
    $reset = $pdo->prepare('UPDATE users SET failed_count = 0, locked_until = NULL WHERE id = ?');
    $reset->execute([$user['id']]);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role']    = $user['role'];
    header('Location: mypage.php');
    exit;
}

// ④ 上級：いる人だけ失敗回数を+1。5回目でロック
if ($user) {
    $newCount  = $user['failed_count'] + 1;
    $lockUntil = null;
    if ($newCount >= MAX_FAILED_ATTEMPTS) {
        $lockUntil = date('Y-m-d H:i:s', time() + LOCK_MINUTES * 60);
    }
    $upd = $pdo->prepare('UPDATE users SET failed_count = ?, locked_until = ? WHERE id = ?');
    $upd->execute([$newCount, $lockUntil, $user['id']]);
}

// ③ 失敗はすべてここ。理由を区別しない同じ一文
$_SESSION['login_error'] = $genericError;
header('Location: login.php');
exit;
