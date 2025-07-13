// script.js
// Particle.js
particlesJS('particles-js', {
    particles: {
        number: { value: 120, density: { enable: true, value_area: 800 } },
        color: { value: '#9b59b6' },
        shape: { type: 'circle' },
        opacity: { value: 0.6, random: true },
        size: { value: 4, random: true },
        line_linked: { enable: true, distance: 120, color: '#9b59b6', opacity: 0.5, width: 1.5 },
        move: { enable: true, speed: 4, direction: 'none', random: true, out_mode: 'out' }
    },
    interactivity: {
        detect_on: 'canvas',
        events: { onhover: { enable: true, mode: 'grab' }, onclick: { enable: true, mode: 'push' }, resize: true },
        modes: { grab: { distance: 150, line_linked: { opacity: 1 } }, push: { particles_nb: 5 } }
    }
});

// لودر صفحه
window.addEventListener('load', () => {
    document.querySelector('.loader').style.display = 'none';
});

// منوی همبرگری
document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    hamburger.addEventListener('click', () => {
        navMenu.classList.toggle('active');
    });
});

// حالت تیره/روشن
const themeToggle = document.querySelector('.theme-toggle');
themeToggle.addEventListener('click', () => {
    document.body.classList.toggle('light-mode');
    themeToggle.innerHTML = document.body.classList.contains('light-mode') ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
});

// چندزبانه
const languageToggle = document.querySelector('#language');
languageToggle.addEventListener('change', (e) => {
    if (e.target.value === 'en') {
        document.querySelectorAll('[data-en]').forEach(el => {
            el.textContent = el.dataset.en || el.textContent;
        });
    } else {
        document.querySelectorAll('[data-en]').forEach(el => {
            el.textContent = el.dataset.fa || el.textContent;
        });
    }
});

// حذف لینک
function deleteLink(id) {
    if (confirm('مطمئن هستید؟')) {
        window.location.href = `admin.php?delete=${id}`;
    }
}

// ویرایش لینک
function showEditForm(id, title, description, url, category_id, tags, video_url, author, pinned) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-title').value = title;
    document.getElementById('edit-description').value = description;
    document.getElementById('edit-url').value = url;
    document.getElementById('edit-category_id').value = category_id || '';
    document.getElementById('edit-tags').value = tags || '';
    document.getElementById('edit-video_url').value = video_url || '';
    document.getElementById('edit-author').value = author || '';
    document.getElementById('edit-pinned').checked = pinned == 1;
    document.getElementById('edit-form').style.display = 'block';
    suggestTags(document.getElementById('edit-tags'));
}

// پیش‌نمایش لینک
function previewLink() {
    const title = document.getElementById('title').value;
    const description = document.getElementById('description').value;
    const url = document.getElementById('url').value;
    const category_id = document.getElementById('category_id').value;
    const tags = document.getElementById('tags').value;
    const video_url = document.getElementById('video_url').value;
    const author = document.getElementById('author').value;
    const pinned = document.getElementById('pinned').checked;
    const images = document.getElementById('images').files;
    const preview = document.getElementById('preview');
    const previewTitle = document.getElementById('preview-title');
    const previewDescription = document.getElementById('preview-description');
    const previewUrl = document.getElementById('preview-url');
    const previewCategory = document.getElementById('preview-category');
    const previewTags = document.getElementById('preview-tags');
    const previewAuthor = document.getElementById('preview-author');
    const previewSlider = document.getElementById('preview-slider');
    const previewVideo = document.getElementById('preview-video');

    previewTitle.textContent = title || 'عنوان نمونه';
    previewDescription.textContent = description || 'توضیح نمونه';
    previewUrl.href = url || '#';
    previewUrl.textContent = url ? 'بخوانید' : 'بدون لینک';
    previewCategory.textContent = category_id ? document.querySelector(`#category_id option[value="${category_id}"]`).textContent : 'بدون دسته‌بندی';
    previewTags.textContent = tags || 'بدون تگ';
    previewAuthor.textContent = author || 'ناشناس';
    if (pinned) {
        preview.insertAdjacentHTML('afterbegin', '<span class="pinned"><i class="fas fa-thumbtack"></i> پین‌شده</span>');
    } else {
        const pinnedSpan = preview.querySelector('.pinned');
        if (pinnedSpan) pinnedSpan.remove();
    }
    previewSlider.innerHTML = '';
    if (video_url) {
        previewVideo.src = video_url;
        previewVideo.style.display = 'block';
    } else {
        previewVideo.style.display = 'none';
    }

    if (images.length) {
        Array.from(images).forEach((image, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = index === 0 ? 'active' : '';
                previewSlider.appendChild(img);
            };
            reader.readAsDataURL(image);
        });
    }

    preview.style.display = 'block';
    startSlider(previewSlider);
}

// اسلایدر تصاویر
function startSlider(slider) {
    const images = slider.getElementsByTagName('img');
    if (images.length === 0) return;
    let current = 0;
    setInterval(() => {
        images[current].classList.remove('active');
        current = (current + 1) % images.length;
        images[current].classList.add('active');
    }, 3000);
}

// امتیازدهی
async function ratePost(linkId) {
    const rating = document.querySelector(`.rate-post[data-link-id="${linkId}"]`).value;
    if (rating < 1 || rating > 5) {
        Toastify({ text: 'امتیاز باید بین 1 تا 5 باشد!', duration: 3000, backgroundColor: '#e74c3c' }).showToast();
        return;
    }
    const response = await fetch('admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `rate=true&link_id=${linkId}&rating=${rating}`
    });
    if (response.ok) {
        Toastify({ text: 'امتیاز ثبت شد!', duration: 3000, backgroundColor: '#9b59b6' }).showToast();
        location.reload();
    }
}

// لایک/دیسلایک نظر
async function likeComment(commentId) {
    const response = await fetch('admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `like_comment=true&comment_id=${commentId}`
    });
    if (response.ok) {
        Toastify({ text: 'لایک ثبت شد!', duration: 3000, backgroundColor: '#9b59b6' }).showToast();
        location.reload();
    }
}

async function dislikeComment(commentId) {
    const response = await fetch('admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `dislike_comment=true&comment_id=${commentId}`
    });
    if (response.ok) {
        Toastify({ text: 'دیسلایک ثبت شد!', duration: 3000, backgroundColor: '#9b59b6' }).showToast();
        location.reload();
    }
}

// پاسخ به نظر
document.querySelectorAll('.reply-comment').forEach(button => {
    button.addEventListener('click', () => {
        const commentId = button.dataset.commentId;
        const replyForm = document.querySelector(`.reply-form[data-comment-id="${commentId}"]`);
        replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
    });
});

// ارسال نظر یا پاسخ
document.querySelectorAll('.comment-form, .reply-form').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const linkId = form.dataset.linkId;
        const commentId = form.dataset.commentId || '';
        const comment = form.querySelector('textarea').value;
        const userName = form.querySelector('.comment-name').value || 'ناشناس';
        const response = await fetch('admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `comment=true&link_id=${linkId}&comment=${encodeURIComponent(comment)}&user_name=${encodeURIComponent(userName)}&parent_id=${commentId}`
        });
        if (response.ok) {
            Toastify({ text: 'نظر ثبت شد!', duration: 3000, backgroundColor: '#9b59b6' }).showToast();
            form.reset();
            location.reload();
        }
    });
});

// جستجو و فیلتر
const searchInput = document.querySelector('#search');
const categoryFilter = document.querySelector('#category-filter');
const sortSelect = document.querySelector('#sort');
const dateFilter = document.querySelector('#date-filter');
const authorFilter = document.querySelector('#author-filter');
const tagCloud = document.querySelector('.tag-cloud');
let selectedTags = [];

function filterLinks() {
    const search = searchInput.value.toLowerCase();
    const category = categoryFilter.value;
    const sort = sortSelect.value;
    const date = dateFilter.value;
    const author = authorFilter.value;
    const now = new Date();
    let filteredLinks = document.querySelectorAll('.link-card');

    filteredLinks.forEach(link => {
        const title = link.dataset.title.toLowerCase();
        const description = link.dataset.description.toLowerCase();
        const tags = link.dataset.tags.toLowerCase();
        const linkCategory = link.dataset.category;
        const createdAt = new Date(link.dataset.createdAt);
        const linkAuthor = link.dataset.author;

        let show = true;
        if (search && !title.includes(search) && !description.includes(search) && !tags.includes(search)) {
            show = false;
        }
        if (category !== 'all' && linkCategory !== category) {
            show = false;
        }
        if (selectedTags.length > 0 && !selectedTags.every(tag => tags.includes(tag.toLowerCase()))) {
            show = false;
        }
        if (date === 'week' && (now - createdAt) > 7 * 24 * 60 * 60 * 1000) {
            show = false;
        }
        if (date === 'month' && (now - createdAt) > 30 * 24 * 60 * 60 * 1000) {
            show = false;
        }
        if (author !== 'all' && linkAuthor !== author) {
            show = false;
        }

        link.style.display = show ? 'block' : 'none';
    });

    sortLinks(sort, filteredLinks);
}

function sortLinks(sort, links) {
    const container = document.querySelector('.links-container');
    const sortedLinks = Array.from(links).sort((a, b) => {
        if (sort === 'date-desc') {
            return new Date(b.dataset.createdAt) - new Date(a.dataset.createdAt);
        } else if (sort === 'date-asc') {
            return new Date(a.dataset.createdAt) - new Date(b.dataset.createdAt);
        } else if (sort === 'title-asc') {
            return a.dataset.title.localeCompare(b.dataset.title);
        } else if (sort === 'views-desc') {
            return parseInt(b.querySelector('.stats span:first-child').textContent) - parseInt(a.querySelector('.stats span:first-child').textContent);
        }
    });
    container.innerHTML = '';
    sortedLinks.forEach(link => container.appendChild(link));
}

searchInput.addEventListener('input', filterLinks);
categoryFilter.addEventListener('change', filterLinks);
sortSelect.addEventListener('change', filterLinks);
dateFilter.addEventListener('change', filterLinks);
authorFilter.addEventListener('change', filterLinks);

tagCloud.addEventListener('click', (e) => {
    if (e.target.classList.contains('tag')) {
        const tag = e.target.dataset.tag;
        if (selectedTags.includes(tag)) {
            selectedTags = selectedTags.filter(t => t !== tag);
            e.target.style.background = '#9b59b6';
        } else {
            selectedTags.push(tag);
            e.target.style.background = '#e74c3c';
        }
        filterLinks();
    }
});

// پیشنهاد تگ‌ها
function suggestTags(input) {
    const tags = Array.from(document.querySelectorAll('.tag')).map(tag => tag.dataset.tag);
    const suggestions = document.querySelector(input.id === 'tags' ? '.tag-suggestions' : '.tag-suggestions:last-child');
    suggestions.innerHTML = '';
    input.addEventListener('input', () => {
        const value = input.value.toLowerCase();
        suggestions.innerHTML = '';
        tags.filter(tag => tag.toLowerCase().includes(value) && !input.value.includes(tag))
            .forEach(tag => {
                const span = document.createElement('span');
                span.textContent = tag;
                span.addEventListener('click', () => {
                    input.value = input.value ? `${input.value}, ${tag}` : tag;
                    suggestions.innerHTML = '';
                });
                suggestions.appendChild(span);
            });
    });
}

if (document.getElementById('tags')) {
    suggestTags(document.getElementById('tags'));
}

// کپی لینک
document.querySelectorAll('.copy-link').forEach(button => {
    button.addEventListener('click', () => {
        navigator.clipboard.writeText(button.dataset.url).then(() => {
            Toastify({ text: 'لینک کپی شد!', duration: 3000, backgroundColor: '#9b59b6' }).showToast();
        });
    });
});

// دکمه بازگشت به بالا
const backToTop = document.querySelector('.back-to-top');
window.addEventListener('scroll', () => {
    backToTop.style.display = window.scrollY > 300 ? 'block' : 'none';
});
backToTop.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// چت زنده
const chatBox = document.querySelector('.chat-box');
const chatHeader = document.querySelector('.chat-header');
const chatInput = document.querySelector('.chat-input');
const chatSend = document.querySelector('.chat-send');
const chatMessages = document.querySelector('.chat-messages');

chatHeader.addEventListener('click', () => {
    chatBox.classList.toggle('active');
});

chatSend.addEventListener('click', () => {
    const message = chatInput.value.trim();
    if (message) {
        const msgElement = document.createElement('div');
        msgElement.textContent = message;
        chatMessages.appendChild(msgElement);
        chatInput.value = '';
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
});

// موسیقی پس‌زمینه
const musicToggle = document.querySelector('.music-toggle');
const backgroundMusic = document.getElementById('background-music');
musicToggle.addEventListener('click', () => {
    if (backgroundMusic.paused) {
        backgroundMusic.play();
        musicToggle.innerHTML = '<i class="fas fa-pause"></i>';
    } else {
        backgroundMusic.pause();
        musicToggle.innerHTML = '<i class="fas fa-music"></i>';
    }
});

// حالت مطالعه
document.querySelectorAll('.reading-mode').forEach(button => {
    button.addEventListener('click', () => {
        const linkId = button.dataset.linkId;
        const linkCard = button.closest('.link-card');
        const content = linkCard.querySelector('p').textContent;
        const modal = document.createElement('div');
        modal.className = 'reading-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2>${linkCard.querySelector('h2').textContent}</h2>
                <p>${content}</p>
            </div>
        `;
        document.body.appendChild(modal);
        modal.querySelector('.close-modal').addEventListener('click', () => modal.remove());
    });
});

// نوار پیشرفت اسکرول
window.addEventListener('scroll', () => {
    const scrollProgress = document.querySelector('.scroll-progress');
    const scrollTop = window.scrollY;
    const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
    const scrolled = (scrollTop / scrollHeight) * 100;
    scrollProgress.style.width = `${scrolled}%`;
});

// انیمیشن‌های GSAP
gsap.from('.link-card', {
    opacity: 0,
    y: 50,
    stagger: 0.2,
    duration: 1,
    ease: 'power2.out'
});

gsap.from('header', {
    y: -100,
    duration: 1,
    ease: 'bounce.out'
});

// گالری تمام‌صفحه
document.querySelectorAll('.image-slider img').forEach(img => {
    img.addEventListener('click', () => {
        const modal = document.createElement('div');
        modal.className = 'gallery-modal';
        modal.innerHTML = `<div class="modal-content"><img src="${img.src}"><span class="close-modal">&times;</span></div>`;
        document.body.appendChild(modal);
        modal.querySelector('.close-modal').addEventListener('click', () => modal.remove());
    });
});

// پیش‌نمایش PDF
document.querySelectorAll('.read-btn').forEach(button => {
    button.addEventListener('click', async (e) => {
        const url = button.href;
        if (url.endsWith('.pdf')) {
            e.preventDefault();
            const modal = document.createElement('div');
            modal.className = 'pdf-modal';
            modal.innerHTML = `<div class="modal-content"><span class="close-modal">&times;</span><canvas id="pdf-canvas"></canvas></div>`;
            document.body.appendChild(modal);
            const pdf = await pdfjsLib.getDocument(url).promise;
            const page = await pdf.getPage(1);
            const canvas = document.getElementById('pdf-canvas');
            const context = canvas.getContext('2d');
            const viewport = page.getViewport({ scale: 1.5 });
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            page.render({ canvasContext: context, viewport }).promise;
            modal.querySelector('.close-modal').addEventListener('click', () => modal.remove());
        }
    });
});

// صفحه‌بندی
function setupPagination() {
    const links = document.querySelectorAll('.link-card');
    const perPage = 6;
    const totalPages = Math.ceil(links.length / perPage);
    const pagination = document.querySelector('.pagination');
    pagination.innerHTML = '';
    for (let i = 1; i <= totalPages; i++) {
        const button = document.createElement('button');
        button.textContent = i;
        button.addEventListener('click', () => {
            links.forEach((link, index) => {
                link.style.display = index >= (i - 1) * perPage && index < i * perPage ? 'block' : 'none';
            });
            pagination.querySelectorAll('button').forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
        });
        pagination.appendChild(button);
    }
    if (totalPages > 0) pagination.querySelector('button').click();
}

setupPagination();

// پست‌های مرتبط
document.querySelectorAll('.related-posts').forEach(container => {
    const linkCard = container.closest('.link-card');
    const tags = linkCard.dataset.tags.split(',').map(t => t.trim().toLowerCase());
    const relatedLinks = Array.from(document.querySelectorAll('.link-card')).filter(link => {
        if (link === linkCard) return false;
        const linkTags = link.dataset.tags.split(',').map(t => t.trim().toLowerCase());
        return tags.some(tag => linkTags.includes(tag));
    }).slice(0, 3);
    container.innerHTML = '<h3>پست‌های مرتبط</h3>';
    relatedLinks.forEach(link => {
        const relatedCard = document.createElement('div');
        relatedCard.className = 'link-card related';
        relatedCard.innerHTML = `
            <h2>${link.querySelector('h2').textContent}</h2>
            <p>${link.querySelector('p').textContent}</p>
            <a href="${link.querySelector('.read-btn').href}" target="_blank">بخوانید</a>
        `;
        container.appendChild(relatedCard);
    });
});

// اعلان‌های Push
if ('Notification' in window && navigator.serviceWorker) {
    Notification.requestPermission().then(permission => {
        if (permission === 'granted') {
            console.log('اعلان‌ها فعال شدند');
        }
    });
}

// PWA
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').then(() => {
        console.log('Service Worker ثبت شد');
    });
}

// نمودار آماری
if (document.getElementById('stats-chart')) {
    const ctx = document.getElementById('stats-chart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['پست‌ها', 'دسته‌بندی‌ها', 'بازدیدها', 'کاربران'],
            datasets: [{
                label: 'آمار',
                data: [
                    document.querySelector('.stats p:nth-child(1)').textContent.match(/\d+/)[0],
                    document.querySelector('.stats p:nth-child(2)').textContent.match(/\d+/)[0],
                    document.querySelector('.stats p:nth-child(3)').textContent.match(/\d+/)[0],
                    document.querySelector('.stats p:nth-child(4)').textContent.match(/\d+/)[0]
                ],
                backgroundColor: '#9b59b6'
            }]
        },
        options: {
            scales: { y: { beginAtZero: true } }
        }
    });
}

// انتخاب گروهی
const selectAll = document.getElementById('select-all');
if (selectAll) {
    selectAll.addEventListener('change', () => {
        document.querySelectorAll('input[name="bulk_ids[]"]').forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    });
}

document.getElementById('bulk-actions')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const ids = Array.from(document.querySelectorAll('input[name="bulk_ids[]"]:checked')).map(cb => cb.value);
    if (ids.length && confirm('مطمئن هستید که می‌خواهید این موارد را حذف کنید؟')) {
        ids.forEach(id => deleteLink(id));
    }
});

// اشتراک خبرنامه
document.querySelector('.newsletter-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = e.target.querySelector('input[type="email"]').value;
    const response = await fetch('admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `newsletter=true&email=${encodeURIComponent(email)}`
    });
    if (response.ok) {
        Toastify({ text: 'در خبرنامه ثبت شد!', duration: 3000, backgroundColor: '#9b59b6' }).showToast();
        e.target.reset();
    }
});

// ورود/ثبت‌نام
document.getElementById('auth-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const action = e.target.querySelector('button[type="submit"]:focus').name;
    const response = await fetch('admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `${action}=true&username=${encodeURIComponent(formData.get('username'))}&email=${encodeURIComponent(formData.get('email'))}&password=${encodeURIComponent(formData.get('password'))}`
    });
    if (response.ok) {
        Toastify({ text: action === 'login' ? 'ورود موفق!' : 'ثبت‌نام موفق!', duration: 3000, backgroundColor: '#9b59b6' }).showToast();
        location.reload();
    }
});