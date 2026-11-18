-- 第2回で作成済みなら追加不要。既存データは消さない。
CREATE TABLE IF NOT EXISTS favorites (
    user_id INT NOT NULL,
    item_id INT NOT NULL,
    PRIMARY KEY (user_id, item_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (item_id) REFERENCES items(id)
);
