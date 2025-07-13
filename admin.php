<?php
// admin.php
session_start();
require 'db.php';

// بررسی لاگین
$logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
if (!$logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $captcha = $_POST['captcha'];
    if ($captcha !== $_SESSION['captcha']) {
        $error = "کپچا اشتباه است!";
    } else {
        $stmt = $db->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $logged_in = true;
        } else {
            $error = "نام کاربری یا رمز عبور اشتباه است!";
        }
    }
}

// ثبت‌نام کاربر
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $password]);
    echo "<script>Toastify({text: 'ثبت‌نام با موفقیت انجام شد!', duration: 3000, backgroundColor: '#9b59b6'}).showToast();</script>";
}

// افزودن لینک
if ($logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_link'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $url = $_POST['url'];
    $category_id = $_POST['category_id'] ?: null;
    $tags = isset($_POST['tags']) ? $_POST['tags'] : '';
    $video_url = $_POST['video_url'];
    $author = $_POST['author'];
    $pinned = isset($_POST['pinned']) ? 1 : 0;
    $stmt = $db->prepare("INSERT INTO links (title, description, url, category_id, tags, video_url, author, pinned) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $url, $category_id, $tags, $video_url, $author, $pinned]);
    $link_id = $db->lastInsertId();
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $index => $tmp_name) {
            if ($_FILES['images']['error'][$index] === UPLOAD_ERR_OK) {
                $image = time() . '_' . $index . '_' . basename($_FILES['images']['name'][$index]);
                if (move_uploaded_file($tmp_name, 'uploads/' . $image)) {
                    $stmt = $db->prepare("INSERT INTO images (link_id, image_path) VALUES (?, ?)");
                    $stmt->execute([$link_id, $image]);
                } else {
                    $error = "خطا در آپلود تصویر!";
                }
            }
        }
    }
    if (!$error) {
        echo "<script>Toastify({text: 'پست با موفقیت اضافه شد!', duration: 3000, backgroundColor: '#9b59b6'}).showToast();</script>";
    }
}

// حذف لینک
if ($logged_in && isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $db->prepare("SELECT image_path FROM images WHERE link_id = ?");
    $stmt->execute([$id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($images as $image) {
        if (file_exists('uploads/' . $image['image_path'])) {
            unlink('uploads/' . $image['image_path']);
        }
    }
    $db->prepare("DELETE FROM images WHERE link_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM comments WHERE link_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM links WHERE id = ?")->execute([$id]);
    echo "<script>Toastify({text: 'پست حذف شد!', duration: 3000, backgroundColor: '#9b59b6'}).showToast();</script>";
}

// ویرایش لینک
if ($logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_link'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $url = $_POST['url'];
    $category_id = $_POST['category_id'] ?: null;
    $tags = isset($_POST['tags']) ? $_POST['tags'] : '';
    $video_url = $_POST['video_url'];
    $author = $_POST['author'];
    $pinned = isset($_POST['pinned']) ? 1 : 0;
    $stmt = $db->prepare("UPDATE links SET title = ?, description = ?, url = ?, category_id = ?, tags = ?, video_url = ?, author = ?, pinned = ? WHERE id = ?");
    $stmt->execute([$title, $description, $url, $category_id, $tags, $video_url, $author, $pinned, $id]);
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $stmt = $db->prepare("SELECT image_path FROM images WHERE link_id = ?");
        $stmt->execute([$id]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($images as $image) {
            if (file_exists('uploads/' . $image['image_path'])) {
                unlink('uploads/' . $image['image_path']);
            }
        }
        $db->prepare("DELETE FROM images WHERE link_id = ?")->execute([$id]);
        foreach ($_FILES['images']['tmp_name'] as $index => $tmp_name) {
            if ($_FILES['images']['error'][$index] === UPLOAD_ERR_OK) {
                $image = time() . '_' . $index . '_' . basename($_FILES['images']['name'][$index]);
                if (move_uploaded_file($tmp_name, 'uploads/' . $image)) {
                    $stmt = $db->prepare("INSERT INTO images (link_id, image_path) VALUES (?, ?)");
                    $stmt->execute([$id, $image]);
                } else {
                    $error = "خطا در آپلود تصویر!";
                }
            }
        }
    }
    if (!$error) {
        echo "<script>Toastify({text: 'پست ویرایش شد!', duration: 3000, backgroundColor: '#9b59b6'}).showToast();</script>";
    }
}

// افزودن دسته‌بندی
if ($logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = $_POST['category_name'];
    $stmt = $db->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->execute([$name]);
    echo "<script>Toastify({text: 'دسته‌بندی اضافه شد!', duration: 3000, backgroundColor: '#9b59b6'}).showToast();</script>";
}

// افزودن نظر
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $link_id = $_POST['link_id'];
    $comment = $_POST['comment'];
    $user_name = $_POST['user_name'];
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $parent_id = $_POST['parent_id'] ?: null;
    $stmt = $db->prepare("INSERT INTO comments (link_id, comment, user_name, user_id, parent_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$link_id, $comment, $user_name, $user_id, $parent_id]);
    if ($user_id) {
        $db->prepare("UPDATE users SET points = points + 10 WHERE id = ?")->execute([$user_id]);
    }
    echo "<script>Toastify({text: 'نظر اضافه شد!', duration: 3000, backgroundColor: '#9b59b6'}).showToast();</script>";
}

// امتیازدهی
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rate'])) {
    $link_id = $_POST['link_id'];
    $rating = $_POST['rating'];
    $stmt = $db->prepare("UPDATE links SET rating = ((rating * views) + ?) / (views + 1), views = views + 1 WHERE id = ?");
    $stmt->execute([$rating, $link_id]);
    if (isset($_SESSION['user_id'])) {
        $db->prepare("UPDATE users SET points = points + 5 WHERE id = ?")->execute([$_SESSION['user_id']]);
    }
    echo "<script>Toastify({text: 'امتیاز ثبت شد!', duration: 3000, backgroundColor: '#9b59b6'}).showToast();</script>";
}

// لایک/دیسلایک نظر
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_comment'])) {
    $comment_id = $_POST['comment_id'];
    $db->prepare("UPDATE comments SET likes = likes + 1 WHERE id = ?")->execute([$comment_id]);
    if (isset($_SESSION['user_id'])) {
        $db->prepare("UPDATE users SET points = points + 2 WHERE id = ?")->execute([$_SESSION['user_id']]);
    }
    echo "<script>Toastify({text: 'لایک ثبت شد!', duration: 3000, backgroundColor: '#9b59b6'}).showToast();</script>";
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dislike_comment'])) {
    $comment_id = $_POST['comment_id'];
    $db->prepare("UPDATE comments SET dislikes = dislikes + 1 WHERE id = ?")->execute([$comment_id]);
    echo "<script>Toastify({text: 'دیسلایک ثبت شد!', duration: 3000, backgroundColor: '#9b59b6'}).showToast();</script>";
}

// اشتراک خبرنامه
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter'])) {
    $email = $_POST['email'];
    $stmt = $db->prepare("INSERT INTO newsletter (email) VALUES (?)");
    $stmt->execute([$email]);
    echo "<script>Toastify({text: 'در خبرنامه ثبت شد!', duration: 3000, backgroundColor: '#9b59b6'}).showToast();</script>";
}

// بکاپ دیتابیس
if ($logged_in && isset($_GET['backup'])) {
    $backup_file = '/home/veksair/backup_' . time() . '.sqlite';
    copy('/home/veksair/database.sqlite', $backup_file);
    echo "<script>Toastify({text: 'بکاپ با موفقیت ایجاد شد!', duration: 3000, backgroundColor: '#9b59b6'}).showToast();</script>";
}

$links = $db->query("SELECT l.*, c.name as category_name FROM links l LEFT JOIN categories c ON l.category_id = c.id ORDER BY l.pinned DESC, l.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$categories = $db->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
foreach ($links as &$link) {
    $stmt = $db->prepare("SELECT image_path FROM images WHERE link_id = ?");
    $stmt->execute([$link['id']]);
    $link['images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $db->prepare("SELECT id, comment, user_name, user_id, parent_id, likes, dislikes FROM comments WHERE link_id = ?");
    $stmt->execute([$link['id']]);
    $link['comments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// کپچا
$_SESSION['captcha'] = rand(1000, 9999);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت Veksa</title>
    <link href="https://cdn.fontcdn.ir/Font/Persian/Vazir/Vazir.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
</head>
<body>
    <div id="particles-js"></div>
    <div class="loader">
        <div class="spinner"></div>
    </div>
    <header>
        <div class="logo">
            <span class="typing">پنل مدیریت Veksa</span>
        </div>
        <?php if ($logged_in): ?>
            <a href="?logout">خروج</a>
            <a href="?backup">بکاپ دیتابیس</a>
        <?php endif; ?>
    </header>
    <main>
        <?php if (!$logged_in): ?>
            <div id="login-section">
                <h2>ورود</h2>
                <form method="post">
                    <label>نام کاربری:</label>
                    <input type="text" name="username" value="admin" required>
                    <label>رمز عبور:</label>
                    <input type="password" name="password" required>
                    <label>کپچا: <?php echo $_SESSION['captcha']; ?></label>
                    <input type="text" name="captcha" required>
                    <button type="submit" name="login">ورود</button>
                    <?php if (isset($error)): ?>
                        <p style="color: red;"><?php echo $error; ?></p>
                    <?php endif; ?>
                </form>
            </div>
        <?php else: ?>
            <div id="admin-section">
                <h2>افزودن لینک جدید</h2>
                <form method="post" enctype="multipart/form-data" id="add-link-form">
                    <label>عنوان:</label>
                    <input type="text" name="title" id="title" required>
                    <label>توضیح:</label>
                    <textarea name="description" id="description"></textarea>
                    <label>لینک:</label>
                    <input type="url" name="url" id="url" required>
                    <label>لینک ویدیو (اختیاری):</label>
                    <input type="url" name="video_url" id="video_url">
                    <label>نویسنده:</label>
                    <input type="text" name="author" id="author">
                    <label>دسته‌بندی:</label>
                    <select name="category_id" id="category_id">
                        <option value="">بدون دسته‌بندی</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>تگ‌ها (با کاما جدا کنید):</label>
                    <input type="text" name="tags" id="tags" placeholder="مثال: داستان, عاشقانه">
                    <div class="tag-suggestions"></div>
                    <label>تصاویر (چندین تصویر):</label>
                    <input type="file" name="images[]" id="images" accept="image/*,.gif,.webp" multiple>
                    <label><input type="checkbox" name="pinned" id="pinned"> پین کردن پست</label>
                    <button type="button" onclick="previewLink()">پیش‌نمایش</button>
                    <button type="submit" name="add_link">افزودن</button>
                    <?php if (isset($error)): ?>
                        <p style="color: red;"><?php echo $error; ?></p>
                    <?php endif; ?>
                </form>
                <h2>افزودن دسته‌بندی جدید</h2>
                <form method="post">
                    <label>نام دسته‌بندی:</label>
                    <input type="text" name="category_name" required>
                    <button type="submit" name="add_category">افزودن دسته‌بندی</button>
                </form>
                <h2>تحلیل آماری</h2>
                <div class="stats">
                    <p>تعداد پست‌ها: <?php echo count($links); ?></p>
                    <p>تعداد دسته‌بندی‌ها: <?php echo count($categories); ?></p>
                    <p>کل بازدیدها: <?php echo $db->query("SELECT SUM(views) FROM links")->fetchColumn(); ?></p>
                    <p>تعداد کاربران: <?php echo $db->query("SELECT COUNT(*) FROM users")->fetchColumn(); ?></p>
                    <canvas id="stats-chart"></canvas>
                </div>
                <h2>لیست لینک‌ها</h2>
                <form method="post" id="bulk-actions">
                    <label><input type="checkbox" id="select-all"> انتخاب همه</label>
                    <button type="submit" name="bulk_delete">حذف گروهی</button>
                </form>
                <div class="links-container">
                    <?php if (empty($links)): ?>
                        <p style="text-align: center; font-size: 1.2rem; color: #ecf0f1;">هنوز لینکی اضافه نشده است!</p>
                    <?php else: ?>
                        <?php foreach ($links as $link): ?>
                            <div class="link-card">
                                <input type="checkbox" name="bulk_ids[]" value="<?php echo $link['id']; ?>">
                                <?php if ($link['pinned']): ?>
                                    <span class="pinned"><i class="fas fa-thumbtack"></i> پین‌شده</span>
                                <?php endif; ?>
                                <?php if (!empty($link['images'])): ?>
                                    <div class="image-slider">
                                        <?php foreach ($link['images'] as $image): ?>
                                            <img src="uploads/<?php echo htmlspecialchars($image['image_path']); ?>" alt="<?php echo htmlspecialchars($link['title']); ?>">
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($link['video_url']): ?>
                                    <video controls class="video-player">
                                        <source src="<?php echo htmlspecialchars($link['video_url']); ?>" type="video/mp4">
                                    </video>
                                <?php endif; ?>
                                <h2><?php echo htmlspecialchars($link['title']); ?></h2>
                                <p><?php echo htmlspecialchars($link['description']); ?></p>
                                <span class="category"><?php echo htmlspecialchars($link['category_name'] ?: 'بدون دسته‌بندی'); ?></span>
                                <span class="tags"><?php echo htmlspecialchars($link['tags'] ?: 'بدون تگ'); ?></span>
                                <span class="author"><?php echo htmlspecialchars($link['author'] ?: 'ناشناس'); ?></span>
                                <div class="stats">
                                    <span><i class="fas fa-eye"></i> <?php echo $link['views']; ?> بازدید</span>
                                    <span><i class="fas fa-comment"></i> <?php echo count($link['comments']); ?> نظر</span>
                                    <span><i class="fas fa-star"></i> <?php echo number_format($link['rating'], 1); ?> امتیاز</span>
                                </div>
                                <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank">مشاهده</a>
                                <button onclick="deleteLink(<?php echo $link['id']; ?>)">حذف</button>
                                <button onclick="showEditForm(<?php echo $link['id']; ?>, '<?php echo htmlspecialchars(str_replace("'", "\\'", $link['title'])); ?>', '<?php echo htmlspecialchars(str_replace("'", "\\'", $link['description'])); ?>', '<?php echo htmlspecialchars($link['url']); ?>', '<?php echo $link['category_id'] ?: ''; ?>', '<?php echo htmlspecialchars(str_replace("'", "\\'", $link['tags'])); ?>', '<?php echo htmlspecialchars($link['video_url']); ?>', '<?php echo htmlspecialchars(str_replace("'", "\\'", $link['author'])); ?>', <?php echo $link['pinned']; ?>)">ویرایش</button>
                                <div class="comments">
                                    <h3>نظرات</h3>
                                    <?php foreach ($link['comments'] as $comment): ?>
                                        <div class="comment" data-comment-id="<?php echo $comment['id']; ?>">
                                            <p><strong><?php echo htmlspecialchars($comment['user_name'] ?: 'ناشناس'); ?>:</strong> <?php echo htmlspecialchars($comment['comment']); ?></p>
                                            <div class="comment-actions">
                                                <button class="like-comment" data-comment-id="<?php echo $comment['id']; ?>"><i class="fas fa-thumbs-up"></i> <?php echo $comment['likes']; ?></button>
                                                <button class="dislike-comment" data-comment-id="<?php echo $comment['id']; ?>"><i class="fas fa-thumbs-down"></i> <?php echo $comment['dislikes']; ?></button>
                                                <button class="reply-comment" data-comment-id="<?php echo $comment['id']; ?>">پاسخ</button>
                                            </div>
                                            <form class="reply-form" style="display: none;" data-comment-id="<?php echo $comment['id']; ?>">
                                                <input type="text" placeholder="نام شما..." class="comment-name">
                                                <textarea placeholder="پاسخ خود را بنویسید..." required></textarea>
                                                <button type="submit">ارسال پاسخ</button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                    <form class="comment-form" data-link-id="<?php echo $link['id']; ?>">
                                        <input type="text" placeholder="نام شما..." class="comment-name">
                                        <textarea placeholder="نظر خود را بنویسید..." required></textarea>
                                        <button type="submit">ارسال نظر</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div id="edit-form" style="display: none;">
                    <h2>ویرایش لینک</h2>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="edit-id">
                        <label>عنوان:</label>
                        <input type="text" name="title" id="edit-title" required>
                        <label>توضیح:</label>
                        <textarea name="description" id="edit-description"></textarea>
                        <label>لینک:</label>
                        <input type="url" name="url" id="edit-url" required>
                        <label>لینک ویدیو (اختیاری):</label>
                        <input type="url" name="video_url" id="edit-video_url">
                        <label>نویسنده:</label>
                        <input type="text" name="author" id="edit-author">
                        <label>دسته‌بندی:</label>
                        <select name="category_id" id="edit-category_id">
                            <option value="">بدون دسته‌بندی</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>تگ‌ها (با کاما جدا کنید):</label>
                        <input type="text" name="tags" id="edit-tags" placeholder="مثال: داستان, عاشقانه">
                        <div class="tag-suggestions"></div>
                        <label>تصاویر جدید (اختیاری):</label>
                        <input type="file" name="images[]" accept="image/*,.gif,.webp" multiple>
                        <label><input type="checkbox" name="pinned" id="edit-pinned"> پین کردن پست</label>
                        <button type="submit" name="edit_link">ذخیره</button>
                        <button type="button" onclick="document.getElementById('edit-form').style.display='none'">لغو</button>
                    </form>
                </div>
                <div id="preview" style="display: none;" class="link-card">
                    <div id="preview-slider" class="image-slider"></div>
                    <video id="preview-video" controls style="display: none;"></video>
                    <h2 id="preview-title"></h2>
                    <p id="preview-description"></p>
                    <span id="preview-category" class="category"></span>
                    <span id="preview-tags" class="tags"></span>
                    <span id="preview-author" class="author"></span>
                    <a id="preview-url" href="#" target="_blank">بخوانید</a>
                </div>
            </div>
        <?php endif; ?>
    </main>
    <script src="particles.min.js"></script>
    <script src="https://unpkg.com/scrollreveal@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
</body>
</html>