<?php
// index.php
require 'db.php';
session_start();
$categories = $db->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$links = $db->query("SELECT l.*, c.name as category_name FROM links l LEFT JOIN categories c ON l.category_id = c.id ORDER BY l.pinned DESC, l.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$tags = [];
foreach ($links as &$link) {
    $stmt = $db->prepare("SELECT image_path FROM images WHERE link_id = ?");
    $stmt->execute([$link['id']]);
    $link['images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $db->prepare("SELECT id, comment, user_name, user_id, parent_id, likes, dislikes FROM comments WHERE link_id = ?");
    $stmt->execute([$link['id']]);
    $link['comments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $db->prepare("UPDATE links SET views = views + 1 WHERE id = ?")->execute([$link['id']]);
    if ($link['tags']) {
        $tags = array_merge($tags, array_map('trim', explode(',', $link['tags'])));
    }
}
$tags = array_unique($tags);
$authors = array_unique(array_column($links, 'author'));
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="داستان‌ها و مقاله‌های Veksa">
    <meta name="keywords" content="داستان, مقاله, شعر, Veksa">
    <title>Veksa | داستان‌ها و مقاله‌ها</title>
    <link href="https://cdn.fontcdn.ir/Font/Persian/Vazir/Vazir.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
    <link rel="manifest" href="/manifest.json">
</head>
<body>
    <div id="particles-js"></div>
    <div class="scroll-progress"></div>
    <div class="loader">
        <div class="spinner"></div>
    </div>
    <header>
    <div class="header-container">
        <div class="logo">
            <span class="typing">Veksa</span>
        </div>
        <nav class="nav-menu">
            <div class="hamburger"><i class="fas fa-bars"></i></div>
            <ul>
                <li><a href="#home">خانه</a></li>
                <li><a href="#categories">دسته‌بندی‌ها</a></li>
                <li><a href="admin.php">مدیریت</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="?logout">خروج</a></li>
                <?php else: ?>
                    <li><a href="#login">ورود/ثبت‌نام</a></li>
                <?php endif; ?>
                <li><a href="https://instagram.com/YOUR_INSTAGRAM" target="_blank"><i class="fab fa-instagram"></i></a></li>
            </ul>
        </nav>
        <div class="theme-toggle">
            <i class="fas fa-sun"></i>
        </div>
        <div class="language-toggle">
            <select id="language">
                <option value="fa">فارسی</option>
                <option value="en">English</option>
            </select>
        </div>
    </div>
    <div class="search-container">
        <div class="search-bar">
            <input type="text" id="search" placeholder="جستجو...">
            <select id="category-filter">
                <option value="all">همه</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <select id="sort">
                <option value="date-desc">جدید</option>
                <option value="date-asc">قدیمی</option>
                <option value="title-asc">عنوان</option>
                <option value="views-desc">محبوب</option>
            </select>
            <select id="date-filter">
                <option value="all">همه تاریخ‌ها</option>
                <option value="week">هفته</option>
                <option value="month">ماه</option>
            </select>
            <select id="author-filter">
                <option value="all">همه نویسندگان</option>
                <?php foreach ($authors as $author): ?>
                    <?php if ($author): ?>
                        <option value="<?php echo htmlspecialchars($author); ?>"><?php echo htmlspecialchars($author); ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="tag-cloud">
            <?php foreach ($tags as $tag): ?>
                <span class="tag" data-tag="<?php echo htmlspecialchars($tag); ?>"><?php echo htmlspecialchars($tag); ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</header>
    <main>
        <div id="login-section" style="display: none;">
            <h2>ورود/ثبت‌نام</h2>
            <form id="auth-form">
                <label>نام کاربری:</label>
                <input type="text" name="username" required>
                <label>ایمیل:</label>
                <input type="email" name="email" required>
                <label>رمز عبور:</label>
                <input type="password" name="password" required>
                <button type="submit" name="register">ثبت‌نام</button>
                <button type="submit" name="login">ورود</button>
            </form>
        </div>
        <div class="links-container" id="links-container">
            <?php if (empty($links)): ?>
                <p style="text-align: center; font-size: 1.2rem; color: #ecf0f1;">هنوز محتوایی اضافه نشده است!</p>
            <?php else: ?>
                <?php foreach ($links as $link): ?>
                    <div class="link-card" data-title="<?php echo htmlspecialchars($link['title']); ?>" 
                         data-description="<?php echo htmlspecialchars($link['description']); ?>" 
                         data-tags="<?php echo htmlspecialchars($link['tags']); ?>" 
                         data-category="<?php echo $link['category_id']; ?>" 
                         data-created-at="<?php echo $link['created_at']; ?>" 
                         data-author="<?php echo htmlspecialchars($link['author']); ?>">
                        <?php if ($link['pinned']): ?>
                            <span class="pinned"><i class="fas fa-thumbtack"></i> پین‌شده</span>
                        <?php endif; ?>
                        <?php if (!empty($link['images'])): ?>
                            <div class="image-slider">
                                <?php foreach ($link['images'] as $image): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($image['image_path']); ?>" alt="<?php echo htmlspecialchars($link['title']); ?>" loading="lazy">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($link['video_url']): ?>
                            <video controls class="video-player" onmouseover="this.play()" onmouseout="this.pause()">
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
                        <div class="share-buttons">
                            <a href="https://www.instagram.com/share?url=<?php echo urlencode($link['url']); ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                            <a href="https://t.me/share/url?url=<?php echo urlencode($link['url']); ?>" target="_blank"><i class="fab fa-telegram"></i></a>
                            <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($link['url']); ?>" target="_blank"><i class="fab fa-whatsapp"></i></a>
                            <button class="copy-link" data-url="<?php echo htmlspecialchars($link['url']); ?>"><i class="fas fa-copy"></i></button>
                        </div>
                        <div class="rating">
                            <input type="number" min="1" max="5" step="1" class="rate-post" data-link-id="<?php echo $link['id']; ?>">
                            <button onclick="ratePost(<?php echo $link['id']; ?>)">امتیاز دهید</button>
                        </div>
                        <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" class="read-btn">بخوانید</a>
                        <button class="reading-mode" data-link-id="<?php echo $link['id']; ?>">حالت مطالعه</button>
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
                        <div class="related-posts">
                            <h3>پست‌های مرتبط</h3>
                            <!-- در JS پر می‌شود -->
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="pagination"></div>
    </main>
    <footer>
        <p>© 2025 Veksa. همه حقوق محفوظه.</p>
    </footer>
    <button class="back-to-top"><i class="fas fa-arrow-up"></i></button>
    <div class="chat-box">
        <div class="chat-header">چت زنده</div>
        <div class="chat-messages"></div>
        <input type="text" class="chat-input" placeholder="پیام خود را بنویسید...">
        <button class="chat-send">ارسال</button>
    </div>
    <audio id="background-music" loop>
        <source src="assets/background-music.mp3" type="audio/mp3">
    </audio>
    <button class="music-toggle"><i class="fas fa-music"></i></button>
    <script src="particles.min.js"></script>
    <script src="https://unpkg.com/scrollreveal@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="script.js"></script>
</body>
</html>