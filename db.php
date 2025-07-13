<?php
// db.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $db = new PDO('sqlite:/home/veksair/database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // جدول لینک‌ها
    $db->exec("CREATE TABLE IF NOT EXISTS links (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        description TEXT,
        url TEXT NOT NULL,
        category_id INTEGER,
        tags TEXT,
        views INTEGER DEFAULT 0,
        video_url TEXT,
        author TEXT,
        pinned INTEGER DEFAULT 0,
        rating REAL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // جدول تصاویر
    $db->exec("CREATE TABLE IF NOT EXISTS images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        link_id INTEGER,
        image_path TEXT NOT NULL,
        FOREIGN KEY (link_id) REFERENCES links(id)
    )");
    
    // جدول دسته‌بندی‌ها
    $db->exec("CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL
    )");
    
    // جدول کاربران
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        password TEXT NOT NULL,
        email TEXT,
        points INTEGER DEFAULT 0
    )");
    
    // جدول نظرات
    $db->exec("CREATE TABLE IF NOT EXISTS comments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        link_id INTEGER,
        comment TEXT NOT NULL,
        user_name TEXT,
        user_id INTEGER,
        parent_id INTEGER,
        likes INTEGER DEFAULT 0,
        dislikes INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (link_id) REFERENCES links(id),
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (parent_id) REFERENCES comments(id)
    )");
    
    // جدول خبرنامه
    $db->exec("CREATE TABLE IF NOT EXISTS newsletter (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL,
        subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // کاربر پیش‌فرض
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    if ($count == 0) {
        $password = password_hash('Veksa2025!', PASSWORD_DEFAULT);
        $db->exec("INSERT INTO users (username, password, email) VALUES ('admin', '$password', 'admin@veksa.com')");
    }
    
    // دسته‌بندی‌های پیش‌فرض
    $stmt = $db->query("SELECT COUNT(*) as count FROM categories");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    if ($count == 0) {
        $db->exec("INSERT INTO categories (name) VALUES ('داستان'), ('مقاله'), ('شعر'), ('غیره')");
    }
    
    // مهاجرت برای ستون‌های جدید
    $columns = $db->query("PRAGMA table_info(links)")->fetchAll(PDO::FETCH_ASSOC);
    $column_names = array_column($columns, 'name');
    
    if (!in_array('tags', $column_names)) {
        $db->exec("ALTER TABLE links ADD COLUMN tags TEXT");
    }
    if (!in_array('views', $column_names)) {
        $db->exec("ALTER TABLE links ADD COLUMN views INTEGER DEFAULT 0");
    }
    if (!in_array('video_url', $column_names)) {
        $db->exec("ALTER TABLE links ADD COLUMN video_url TEXT");
    }
    if (!in_array('author', $column_names)) {
        $db->exec("ALTER TABLE links ADD COLUMN author TEXT");
    }
    if (!in_array('pinned', $column_names)) {
        $db->exec("ALTER TABLE links ADD COLUMN pinned INTEGER DEFAULT 0");
    }
    if (!in_array('rating', $column_names)) {
        $db->exec("ALTER TABLE links ADD COLUMN rating REAL DEFAULT 0");
    }
    
    // مهاجرت برای جدول comments
    $comment_columns = $db->query("PRAGMA table_info(comments)")->fetchAll(PDO::FETCH_ASSOC);
    $comment_column_names = array_column($comment_columns, 'name');
    
    if (!in_array('user_name', $comment_column_names)) {
        $db->exec("ALTER TABLE comments ADD COLUMN user_name TEXT");
    }
    if (!in_array('user_id', $comment_column_names)) {
        $db->exec("ALTER TABLE comments ADD COLUMN user_id INTEGER");
    }
    if (!in_array('parent_id', $comment_column_names)) {
        $db->exec("ALTER TABLE comments ADD COLUMN parent_id INTEGER");
    }
    if (!in_array('likes', $comment_column_names)) {
        $db->exec("ALTER TABLE comments ADD COLUMN likes INTEGER DEFAULT 0");
    }
    if (!in_array('dislikes', $comment_column_names)) {
        $db->exec("ALTER TABLE comments ADD COLUMN dislikes INTEGER DEFAULT 0");
    }
} catch (PDOException $e) {
    die("خطا در اتصال به پایگاه داده: " . $e->getMessage());
}
?>