<?php
/**
 * includes/upload_helper.php
 *
 * 画像アップロードの安全な受け取り方（第3回・上級）。
 *
 * 「.php」ファイルを画像と偽ってアップロードされ、拡張子チェックだけを
 * すり抜けてサーバー上で実行されてしまう（Webシェル設置）事故を防ぐため、
 * 以下の3段階でチェックする。
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
        // アップロード自体が任意（未選択でもOK）の場合はここで正常終了させる
        return ['ok' => true, 'filename' => null, 'error' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'filename' => null, 'error' => 'アップロードに失敗しました'];
    }

    if ($file['size'] > $maxSize) {
        return ['ok' => false, 'filename' => null, 'error' => 'ファイルサイズは2MB以内にしてください'];
    }

    // ① 拡張子チェック（見た目だけの一次チェック。これだけでは不十分）
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return ['ok' => false, 'filename' => null, 'error' => '対応していないファイル形式です'];
    }

    // ② 実体チェック：拡張子を「.jpg」に偽装した実行ファイル等を弾く
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $realMime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($realMime, $allowedMime, true)) {
        return ['ok' => false, 'filename' => null, 'error' => 'ファイルの中身が画像として認識できません'];
    }

    // ③ 元のファイル名は一切使わず、ランダムな名前で保存する
    $randomName = bin2hex(random_bytes(16)) . '.' . $ext;
    $destPath   = rtrim($destDir, '/') . '/' . $randomName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['ok' => false, 'filename' => null, 'error' => 'ファイルの保存に失敗しました'];
    }

    return ['ok' => true, 'filename' => $randomName, 'error' => null];
}
