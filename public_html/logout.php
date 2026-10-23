<?php
/**
 * logout.php ― ログアウト（セッションを3か所とも捨てる）
 */
session_start();

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path']);
}

session_destroy();

header('Location: login.php');
exit;
