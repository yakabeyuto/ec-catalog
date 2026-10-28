<?php
/**
 * register-process.php ― 会員登録の処理（画面は出さない。最後は必ずどこかへ移動する）
 */
require_once __DIR__ . '/../includes/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['password_confirm'] ?? '';

$errors = [];

// 必須チェック：空なら理由を入れる（キーは項目名）
if ($name === '')     $errors['name']     = '氏名を入力してください';
if ($email === '')    $errors['email']    = 'メールアドレスを入力してください';
if ($password === '') $errors['password'] = 'パスワードを入力してください';

// 形式チェック：メールアドレスらしい形か
if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    $errors['email'] = 'メールアドレスの形式が正しくありません';
}

// 1つでもあれば、理由を預けて登録画面へ戻す
if ($errors) {
    $_SESSION['register_errors'] = $errors;
    header('Location: register.php');
    exit;
}

// 登録（パスワードはまだ平文。第11回でハッシュ化する）
$stmt = $pdo->prepare('INSERT INTO users (email, password, name) VALUES (?, ?, ?)');
$stmt->execute([$email, $password, $name]);

header('Location: register-done.php');
exit;
