<?php
/**
 * includes/auth.php ― ガード処理をまとめた共通ファイル（第9回・上級）
 *
 * 使い方：ページの先頭で読み込み、どちらか1行を呼ぶ。
 *   require_login();  … ログインしている人だけ（会員限定ページ）
 *   require_admin();  … 管理者だけ（admin フォルダのページ）
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
// 今のページが admin フォルダの中なら、行き先を1つ上（../）から始める
function app_path(string $file): string
{
    $inAdmin = basename(dirname($_SERVER['SCRIPT_FILENAME'])) === 'admin';
    return ($inAdmin ? '../' : '') . $file;
}
 
// 認証：ログインしていなければ、ログイン画面へ
function require_login(): void
{
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['login_error'] = '続けるにはログインが必要です';
        header('Location: ' . app_path('login.php'));
        exit;
    }
}
 
// 認可：ログインしていても、管理者でなければ「権限がありません」の画面へ
function require_admin(): void
{
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        header('Location: ' . app_path('forbidden.php'));
        exit;
    }
}