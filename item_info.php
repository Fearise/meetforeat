<?php

    session_start();

if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    die('<h3>Ошибка: не указан ID товара.</h3><a href="../catalog/catalog.php">← Вернуться к меню</a>');
}

require_once("../sql.php");
$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM menu_items WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('<h3>Товар не найден.</h3><a href="../catalog/catalog.php">← Вернуться к меню</a>');
}

$product_info = $result->fetch_assoc();

// Увеличиваем просмотры
$stmt_update = $conn->prepare("UPDATE menu_items SET views = views + 1 WHERE id = ?");
$stmt_update->bind_param("i", $id);
$stmt_update->execute();
$stmt_update->close();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product_info['name']); ?> – MeetForEat</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/media-query.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        <a class="nav_item" href="../catalog/catalog.php"><i class="fas fa-utensils"></i> Меню</a>
        <a class="nav_item" href="../gallery/gallery.php"><i class="fas fa-images"></i> Галерея</a>
        <a class="nav_item" href="../support/support.php"><i class="fas fa-headset"></i> Поддержка</a>
        <a class="nav_item" href="../about/about.php"><i class="fas fa-info-circle"></i> О нас</a>

        <?php if (isset($_SESSION['login'])): ?>
            <a class="nav_item" href="../profile/profile.php"><i class="fas fa-user"></i> Профиль</a>
            <p>
    <a class="nav_item" href="../cart/cart.php">
        <i class="fas fa-shopping-cart"></i>
        <span id="cart-count" class="cart-counter">
            <?= $_SESSION['cart_count'] ?? 0 ?>
        </span>
    </a>
</p>

        <?php else: ?>
            <a class="nav_item" href="../login/login.php"><i class="fas fa-sign-in-alt"></i> Войти</a>
            <a class="nav_item" href="../reg/reg.php"><i class="fas fa-user-plus"></i> Регистрация</a>
        <?php endif; ?>
    </div>
</nav>



<main class="product-page-info">
    <div class="product-container-info">
        <div class="product-image-info">
            <img src="../images/Блюда/<?php echo htmlspecialchars($product_info['image']); ?>" alt="<?php echo htmlspecialchars($product_info['name']); ?>">
            <div class="product-views-info">
                <i class="fas fa-eye"></i> <?php echo (int)$product_info['views'] + 1; ?> просмотров
            </div>
            <?php if ($product_info['discount'] > 0): ?>
                <div class="product-discount-badge-info">
                    -<?php echo (int)$product_info['discount']; ?>%
                </div>
            <?php endif; ?>
        </div>

        <div class="product-info-details">
            <h1 class="product-title-info"><?php echo htmlspecialchars($product_info['name']); ?></h1>

            <div class="product-price-info">
                <?php
                $price = (float)$product_info['price'];
                $discount = (int)($product_info['discount'] ?? 0);
                $discounted_price = $discount > 0 ? $price * (1 - $discount / 100) : $price;
                $price_formatted = number_format($price, 2, ',', ' ');
                $discounted_formatted = number_format($discounted_price, 2, ',', ' ');
                ?>
                <?php if ($discount > 0): ?>
                    <span class="old-price-info"><?php echo $price_formatted; ?> ₽</span>
                    <span class="current-price-info"><?php echo $discounted_formatted; ?> ₽</span>
                <?php else: ?>
                    <span class="current-price-info"><?php echo $discounted_formatted; ?> ₽</span>
                <?php endif; ?>
            </div>

            <p class="product-description-info">
                <?php echo htmlspecialchars($product_info['description']); ?>
            </p>

                <button type="button" class="btn-add-to-cart-info" 
                    data-id="<?php echo (int)$product_info['id']; ?>"
                    data-name="<?php echo htmlspecialchars($product_info['name'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-price="<?php echo number_format($discounted_price, 2, '.', ''); ?>">
                    Добавить в корзину
                </button> <br>


            <div class="product-share-info">
                <span>Поделиться:</span>
                <a href="#" class="share-icon-info"><i class="fab fa-vk"></i></a>
                <a href="#" class="share-icon-info"><i class="fab fa-telegram"></i></a>
                <a href="#" class="share-icon-info"><i class="fab fa-instagram"></i></a>
            </div>

            <a href="../main.php" class="btn-back-info">
                Вернуться к меню
            </a>
        </div>
    </div>

    
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addToCartBtn = document.querySelector('.btn-add-to-cart-info');
        const cartCounter = document.getElementById('cart-count');

        addToCartBtn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const price = this.getAttribute('data-price');

            fetch('/cart/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${id}&name=${encodeURIComponent(name)}&price=${price}`
            })
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Обновляем счётчик
                    if (cartCounter) {
                        cartCounter.textContent = data.count;
                        // Анимация
                        cartCounter.style.animation = 'none';
                        setTimeout(() => {
                            cartCounter.style.animation = 'pop 0.3s ease';
                        }, 10);
                    }
                    // Без alert — тихо обновляем
                } else {
                    alert('Ошибка: ' + (data.message || 'Не удалось добавить'));
                }
            })
            .catch(err => {
                console.error('Ошибка:', err);
                alert('Не удалось добавить товар в корзину.');
            });
        });

        // Обновление счётчика при загрузке (на случай, если изменилось)
        function updateCartCount() {
            const count = <?= $_SESSION['cart_count'] ?? 0 ?>;
            if (cartCounter) {
                cartCounter.textContent = count;
            }
        }
        updateCartCount();
    });
</script>


</body>
</html>
