<?php
/**
 * includes/upload_helper.php
 *
 * 画像アップロードの安全な受け取り方（第3回・上級）。
 *
 * 「.php」ファイルを画像と偽ってアップロードされ、拡張子チェックだけを
 * すり抜けてサーバー上で実行されてしまう（Webシェル設置）事故を防ぐため、
 * 以下の3段階でチェックすること。
 *   1. 拡張子のホワイトリストチェック
 *   2. finfo_file() による実体（MIMEタイプ）チェック（拡張子の偽装を見抜く）
 *   3. 保存ファイル名をランダム化（元のファイル名を一切信用しない）
 *
 * @return array{ok:bool, filename:?string, error:?string}
 */
function handleImageUpload(array $file, string $destDir): array
{
    $allowedExt  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize     = 2 * 1024 * 1024; // 2MB

    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'filename' => null, 'error' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'filename' => null, 'error' => 'アップロードに失敗しました'];
    }

    // TODO（中級）: $file['size'] が $maxSize を超えていたらエラーを返す

    // TODO（中級）: $file['name'] の拡張子を取得し、$allowedExt に含まれるかチェックする
    //   ヒント：pathinfo($file['name'], PATHINFO_EXTENSION) と strtolower() を使う

    // TODO（上級）: finfo_open(FILEINFO_MIME_TYPE) と finfo_file() で
    //   $file['tmp_name'] の「実際の中身」のMIMEタイプを取得し、$allowedMime に含まれるかチェックする
    //   （拡張子だけを.jpgに偽装した実行ファイルを見抜くための重要なチェック）

    // TODO（上級）: 元のファイル名は使わず、bin2hex(random_bytes(16)) 等でランダムな名前を作る
    //   TODO: move_uploaded_file() で $destDir に保存する

    return ['ok' => false, 'filename' => null, 'error' => '未実装です（TODOを埋めてください）'];
}
