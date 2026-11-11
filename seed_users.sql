-- ============================================================
-- seed_users.sql ― ログイン動作確認用のテストアカウント
-- 第11回: パスワードは password_hash() でハッシュ化済み
--   taro@example.com  / taro1234
--   admin@example.com / adminpass
-- ============================================================
USE ec_shop;

INSERT INTO users (email, password, name, address, role) VALUES
('taro@example.com',  '$2y$10$9j7D9HPqYo3urHIjbFxJdeWXdwZEU5TVQW0l32Mep1PmeLlKySiw2', '山田 太郎', '東京都新宿区1-1-1', 'member'),
('admin@example.com', '$2y$10$Gnyre0JIRtcyijnhtwj5yOqxNIR0u/4v29k3UtBFXF2VVM/0jmRBK', '管理者',   '東京都新宿区1-1-1', 'admin');
