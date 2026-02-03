<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Галерея блюд – MeetForEat</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/media-query.css">
     <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* === Общие стили === */
       body {
    font-family: 'Poppins', sans-serif;  /* ✅ Единый шрифт */
    background: #f8f9fa;
    margin: 0;
    padding: 0;
}


        /* Навигация */
.nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 40px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  position: fixed;
  top: 0;
  width: 100%;
  z-index: 1000;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
  transition: all 0.3s;
}


.nav.scrolled {
  padding: 10px 40px;
}

.logo img {
  height: 55px;
  transition: transform 0.3s;
}

.logo img:hover {
  transform: scale(1.05);
}

.nav-menu {
  display: flex;
  gap: 25px;
  
}



@keyframes pop {
    0% { transform: scale(0.5); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.nav-menu a {
  text-decoration: none;
  color: #333;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: color 0.3s;
}

.nav-menu a:hover {
  color: #e94e77;
}

        /* === Контейнер галереи === */
        .gallery-container {
            max-width: 1200px;
            margin: 90px auto 60px;
            padding: 20px;
        }

        .gallery-title {
            text-align: center;
            font-size: 2.2em;
            color: #2c3e50;
            margin-bottom: 40px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* === Сетка === */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            padding: 10px;
        }

        .gallery-item {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            background: white;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .gallery-item:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
        }

        .gallery-item img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.03);
        }

        .gallery-item-caption {
            padding: 18px;
            text-align: center;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .gallery-item-caption h3 {
            margin: 0 0 12px;
            font-size: 1.2em;
            color: #2c3e50;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gallery-item-caption p {
            margin: 0;
            font-size: 0.95em;
            color: #555;
            line-height: 1.5;
            text-align: left;
        }

        /* === Модальное окно === */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .modal.active {
            opacity: 1;
            display: flex;
        }

        /* Контейнер изображения и описания */
        .modal-box {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 90vw;
            max-height: 90vh;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        /* Изображение — плавно появляется и масштабируется */
        /* Контейнер изображения — с плавной анимацией */
        .modal-content {
            transform: scale(0.9);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px 12px 0 0;
            overflow: hidden;
            background: #000;
        }


        .modal.active .modal-content {
            transform: scale(1);
            opacity: 1;
        }

        .modal-content img {
            width: auto;
            height: auto;
            max-width: 90vw;
            max-height: 70vh;
            display: block;
            border-radius: 12px 12px 0 0;
        }

        /* Описание под изображением */
        .modal-description {
            width: 100%;
            padding: 18px 24px;
            background: #111;
            color: #ccc;
            font-size: 1em;
            line-height: 1.6;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
            text-align: center;
        }

        .modal-description h3 {
            margin: 0 0 8px;
            font-size: 1.3em;
            color: #fff;
            font-weight: 600;
        }

        .modal-description p {
            margin: 0;
            color: #aaa;
            font-size: 0.95em;
        }

        /* === Крестик — вверху справа, без сдвига === */
        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 44px;
            height: 44px;
            background: white;
            color: #333;
            border: none;
            border-radius: 50%;
            font-size: 1.8em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1001;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            background: #e94e77;
            color: white;
            transform: scale(1.1);
        }

        /* === Счётчик — вверху слева === */
        .modal-counter {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.95em;
            font-weight: 500;
            z-index: 1001;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* === Стрелки — не съезжают, просто растут === */
        .modal-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%) scale(1);
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            border: none;
            border-radius: 50%;
            font-size: 1.5em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1001;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal:hover .modal-arrow {
            opacity: 1;
            visibility: visible;
        }

        .modal-arrow.prev {
            left: 20px;
        }

        .modal-arrow.next {
            right: 20px;
        }

        .modal-arrow:hover {
            background: #e94e77;
            color: white;
            transform: translateY(-50%) scale(1.1);
        }

        /* === Кнопка скачивания === */
        .modal-download {
            margin-top: 12px;
            padding: 10px 20px;
            background: #e94e77;
            color: white;
            border: none;
            border-radius: 20px;
            font-size: 0.95em;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .modal-download:hover {
            background: #d03a60;
            transform: scale(1.05);
        }

        

        
    </style>
</head>
<body>

<!-- Навигация -->
<nav class="nav" id="nav">
    <div class="logo">
        <a href="../index.php">
            <img src="../images/Иконки и логотип/logo.png" alt="MeetForEat">
        </a>
    </div>
    <div class="nav-menu">
        <a class="nav_item" href="../catalog/catalog.php">Меню</a>
        <a class="nav_item" href="../gallery/gallery.php">Галерея</a>
        <a class="nav_item" href="../support/support.php">Поддержка</a>
        <a class="nav_item" href="../about/about.php">О нас</a>

        <?php if (isset($_SESSION['login'])): ?>
            <a class="nav_item" href="../profile/profile.php">Профиль</a>
            <p>
                <a class="nav_item" href="../cart/cart.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span id="cart-count" class="cart-counter">
                        <?= $_SESSION['cart_count'] ?? 0 ?>
                    </span>
                </a>
            </p>
        <?php else: ?>
            <a class="nav_item" href="../login/login.php">Войти</a>
            <a class="nav_item" href="../reg/reg.php">Регистрация</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Кнопка "Наверх" -->
<div class="butt-up">
    <a class="btn-up btn-up_hide" id="btn-up" href="#nav"><i class="fas fa-arrow-up"></i></a>
</div>

<main class="gallery-container">
    <h2 class="gallery-title">Наша галерея</h2>

    <div class="gallery-grid" id="galleryGrid">
        <?php
        require_once('../sql.php');
        if ($conn->connect_error) {
            die("Ошибка подключения: " . $conn->connect_error);
        }

        $sql = "SELECT name, description, image FROM menu_items WHERE available = 1 ORDER BY id";
        $result = $conn->query($sql);

        $items = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $name = htmlspecialchars($row['name']);
                $desc = htmlspecialchars($row['description']);
                $image = htmlspecialchars($row['image']);
                $items[] = ['name' => $name, 'description' => $desc, 'image' => $image];

                echo "
                <div class='gallery-item' onclick='openModal(\"$image\")'>
                    <img src='../images/Блюда/$image' alt='$name'>
                    <div class='gallery-item-caption'>
                        <h3>$name</h3>
                        <p>$desc</p>
                    </div>
                </div>";
            }
        } else {
            echo "<p style='grid-column: 1 / -1; text-align: center; color: #888;'>Нет доступных блюд.</p>";
        }
        $conn->close();
        ?>
    </div>
</main>

<!-- Модальное окно -->
<div id="imageModal" class="modal">
    <button class="modal-close" onclick="closeModal()">&times;</button>
    <div class="modal-counter" id="modalCounter">1 из 1</div>
    <button class="modal-arrow prev" onclick="prevImage()">&#10094;</button>
    <button class="modal-arrow next" onclick="nextImage()">&#10095;</button>

    <div class="modal-box">
        <div class="modal-content">
            <img id="modalImage" src="" alt="Увеличенное изображение">
        </div>

        <div class="modal-description" id="modalDescription">
            <h3>Название блюда</h3>
            <p>Описание загрузится здесь...</p>
            <button class="modal-download" onclick="downloadImage()">Скачать</button>
        </div>
    </div>
</div>

<!-- Футер -->
<footer class="footer">
    <div class="footer-col">
        <h4>Меню</h4>
        <ul>
            <li><a href="../catalog/catalog.php?category=Бургеры">Бургеры</a></li>
            <li><a href="../catalog/catalog.php?category=Пицца">Пицца</a></li>
            <li><a href="../catalog/catalog.php?category=Суши">Суши</a></li>
            <li><a href="../catalog/catalog.php?category=Шаурма">Шаурма</a></li>
            <li><a href="../catalog/catalog.php?category=Выпечка">Выпечка</a></li>
        </ul>
    </div>
    <div class="footer-col">
        <h4>О нас</h4>
        <ul>
            <li><a href="../about/about.php">О компании</a></li>
            <li><a href="../gallery/gallery.php">Галерея</a></li>
            <li><a href="../support/support.php">Поддержка</a></li>
        </ul>
    </div>
    <div class="footer-col">
        <h4>Контакты</h4>
        <ul>
            <li><i class="fas fa-phone"></i> 8 800 555 35 35</li>
            <li><i class="fas fa-envelope"></i> info@meetforeat.ru</li>
            <li><i class="fas fa-map-marker-alt"></i> Москва, ул. Енисейская, 15</li>
        </ul>
    </div>
    <div class="footer-col">
        <h4>Мы в соцсетях</h4>
        <div class="social-icons">
            <a href="#"><i class="fab fa-vk"></i></a>
            <a href="#"><i class="fab fa-telegram"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
    </div>
</footer>

<script>
   const galleryItems = <?= json_encode($items) ?>;
let currentIndex = 0;
const modal = document.getElementById('imageModal');
const modalImg = document.getElementById('modalImage');
const modalCounter = document.getElementById('modalCounter');
const modalDescription = document.getElementById('modalDescription');

// Элемент с анимацией
const modalContent = document.querySelector('.modal-content');

function openModal(image) {
    const itemIndex = galleryItems.findIndex(item => item.image === image);
    if (itemIndex === -1) return;

    currentIndex = itemIndex;
    updateModal();

    document.body.style.overflow = 'hidden';
    modal.classList.add('active');

    // Плавное появление
    setTimeout(() => {
        modalContent.style.opacity = 1;
        modalContent.style.transform = 'scale(1)';
    }, 10);

    preloadNeighborImages();
}

function updateModal() {
    const item = galleryItems[currentIndex];

    // Скрываем изображение с анимацией
    modalContent.style.opacity = 0;
    modalContent.style.transform = 'scale(0.95)';

    // Через полсекунды — меняем и плавно показываем
    setTimeout(() => {
        modalImg.src = "../images/Блюда/" + item.image;
        modalImg.alt = item.name;

        modalCounter.textContent = `${currentIndex + 1} из ${galleryItems.length}`;
        modalDescription.querySelector('h3').textContent = item.name;
        modalDescription.querySelector('p').textContent = item.description;

        // Плавно показываем новое
        modalContent.style.opacity = 1;
        modalContent.style.transform = 'scale(1)';
    }, 300);
}

function closeModal() {
    modalContent.style.opacity = 0;
    modalContent.style.transform = 'scale(0.9)';

    setTimeout(() => {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }, 300);
}

function prevImage() {
    currentIndex = (currentIndex - 1 + galleryItems.length) % galleryItems.length;
    updateModal();
}

function nextImage() {
    currentIndex = (currentIndex + 1) % galleryItems.length;
    updateModal();
}

function downloadImage() {
    const item = galleryItems[currentIndex];
    const link = document.createElement('a');
    link.href = "../images/Блюда/" + item.image;
    link.download = item.image;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function preloadNeighborImages() {
    const nextIndex = (currentIndex + 1) % galleryItems.length;
    const prevIndex = (currentIndex - 1 + galleryItems.length) % galleryItems.length;

    new Image().src = "../images/Блюда/" + galleryItems[nextIndex].image;
    new Image().src = "../images/Блюда/" + galleryItems[prevIndex].image;
}

// Клавиши
document.addEventListener('keydown', (e) => {
    if (!modal.classList.contains('active')) return;
    if (e.key === 'Escape') closeModal();
    if (e.key === 'ArrowLeft') prevImage();
    if (e.key === 'ArrowRight') nextImage();
});

// Клик вне — закрыть
modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
});

    document.addEventListener('DOMContentLoaded', function () {
        const btnUp = document.getElementById('btn-up');
        if (btnUp) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 300) {
                    btnUp.classList.add('btn-up_show');
                } else {
                    btnUp.classList.remove('btn-up_show');
                }
            });
            btnUp.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        const updateCartCount = () => {
            const count = <?= $_SESSION['cart_count'] ?? 0 ?>;
            const counter = document.getElementById('cart-count');
            if (counter) {
                counter.textContent = count;
                counter.style.animation = 'none';
                setTimeout(() => { counter.style.animation = 'pop 0.3s ease'; }, 10);
            }
        };
        updateCartCount();
    });


</script>
</body>
</html>
