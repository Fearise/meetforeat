<?php
session_start();
require_once("../sql.php");

$message = '';
$error = '';

// Проверка авторизации
if (!isset($_SESSION['login'])) {
    header("Location: ../login/login.php");
    exit();
}

$login = $_SESSION['login'];
$stmt = $conn->prepare("SELECT email FROM users WHERE login = ?");
$stmt->bind_param("s", $login);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $email = $user['email'];
} else {
    $error = "Пользователь не найден.";
}

// Проверка корзины
if (empty($_SESSION['cart'])) {
    $error = "Ваша корзина пуста.";
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $delivery_type = $_POST['delivery_type'] ?? 'доставка';
    $payment_method = $_POST['payment_method'] ?? 'наличными';
    $order_notes = trim($_POST['notes'] ?? '');

    // Валидация
    if (empty($name)) $error = "Введите имя.";
    if (empty($phone)) $error = "Введите телефон.";
    if ($delivery_type === 'доставка' && empty($address)) {
        $error = "Введите адрес доставки.";
    }

    if (!$error) {
        // Считаем сумму
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        // Вставка заказа
        $stmt = $conn->prepare("
            INSERT INTO orders (
                user_login, name, phone, email, delivery_address, 
                delivery_type, payment_method, order_notes, total_amount
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "ssssssssd",
            $login, $name, $phone, $email, $address,
            $delivery_type, $payment_method, $order_notes, $total
        );

        if ($stmt->execute()) {
            $order_id = $conn->insert_id;

            // Вставка позиций
            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, item_name, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($_SESSION['cart'] as $item) {
                $stmt_item->bind_param("isid", $order_id, $item['name'], $item['quantity'], $item['price']);
                $stmt_item->execute();
            }
            $stmt_item->close();

            // Очистка корзины
            unset($_SESSION['cart']);

            // Успех
            $_SESSION['order_success'] = "Ваш заказ №$order_id успешно оформлен!";
            header("Location: ../order/orders_catalog.php");
            exit();
        } else {
            $error = "Ошибка при оформлении заказа.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оформление заказа – MeetForEat</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/media-query.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
        main { padding: 80px 20px 60px; }
        .order-container {
            max-width: 700px; margin: 0 auto; background: white; border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); padding: 30px; text-align: center;
        }
        .cart-preview {
            background: #fdfdfd; border-radius: 12px; padding: 15px; margin-bottom: 20px;
            text-align: left; border: 1px solid #eee;
        }
        .cart-item { display: flex; justify-content: space-between; margin: 6px 0; }
        .cart-total { font-weight: 700; color: #e94e77; text-align: right; margin-top: 10px; }
        .form-group {
            margin-bottom: 18px; text-align: left;
        }
        .form-group label {
            display: block; font-weight: 600; margin-bottom: 6px; color: #34495e;
        }
        .form-group select, .form-group input, .form-group textarea {
            width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 10px;
            font-size: 1em;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none; border-color: #e94e77; box-shadow: 0 0 0 3px rgba(233,78,119,0.1);
        }
        .btn-submit {
            width: 100%; padding: 14px; background: #e94e77; color: white; border: none;
            border-radius: 10px; font-size: 1.1em; font-weight: 600; cursor: pointer;
            margin-top: 10px;

        }

       

        .btn-submit:hover { background: #d03a60; }
        .btn-back { color: #000; text-decoration: none; display: inline-block; margin-top: 15px; }
        .btn-back:hover { color: #4d4d4dff; }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .no-cart { color: #888; font-style: italic; }
        .delivery-note { font-size: 0.9em; color: #666; margin-top: -10px; }
    </style>
</head>
<body>


<main>

<!-- Кнопка "Наверх" -->
<div class="butt-up">
    <a class="btn-up btn-up_hide" id="btn-up" href="#nav"><i class="fas fa-arrow-up"></i></a>
</div>

    <div class="order-container">
        <h1>Оформление заказа</h1>
        <p>Заполните данные для доставки и оплаты</p>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (empty($error) && !empty($_SESSION['cart'])): ?>
            <div class="cart-preview">
                <h3>Ваш заказ:</h3>
                <?php $total = 0; ?>
                <?php foreach ($_SESSION['cart'] as $item):
                    $sum = $item['price'] * $item['quantity'];
                    $total += $sum;
                ?>
                    <div class="cart-item">
                        <span><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></span>
                        <span><?= number_format($sum, 2, ',', ' ') ?> ₽</span>
                    </div>
                <?php endforeach; ?>
                <div class="cart-total">Итого: <?= number_format($total, 2, ',', ' ') ?> ₽</div>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Имя:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? $login) ?>" required>
                </div>
                <div class="form-group">
                    <label>Телефон:</label>
                    <input type="tel" name="phone" placeholder="+7 (999) 123-45-67" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" value="<?= htmlspecialchars($email) ?>" disabled>
                </div>
                <div class="form-group">
                    <label>Способ доставки:</label>
                    <select name="delivery_type" id="delivery_type" required>
                        <option value="доставка">Доставка</option>
                        <option value="самовывоз">Самовывоз</option>
                    </select>
                    <div class="delivery-note" id="delivery_note">
                        Введите адрес для доставки.
                    </div>
                </div>
                <div class="form-group">
                    <label>Адрес доставки:</label>
                    <textarea name="address" rows="2" placeholder="Улица, дом, квартира" required></textarea>
                </div>
                <div class="form-group">
                    <label>Способ оплаты:</label>
                    <select name="payment_method" required>
                        <option value="наличными">Наличными</option>
                        <option value="картой">Картой при получении</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Комментарий к заказу (опционально):</label>
                    <textarea name="notes" rows="2" placeholder="Ножи, без лука, звонить за 10 минут..."></textarea>
                </div>
                <button type="submit" class="btn-submit">Оформить заказ</button>
            </form>
            <a href="../catalog/catalog.php" class="btn-back">Вернуться в меню</a>
        <?php else: ?>
            <p class="no-cart">Ваша корзина пуста.</p>
            <a href="../catalog/catalog.php" class="btn-back">Перейти в меню</a>
        <?php endif; ?>
    </div>
</main>



<script>
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


        const deliveryType = document.getElementById('delivery_type');
        const addressField = document.querySelector('textarea[name="address"]');
        const deliveryNote = document.getElementById('delivery_note');

        function updateFields() {
            if (deliveryType.value === 'самовывоз') {
                addressField.required = false;
                addressField.value = "Самовывоз";
                deliveryNote.textContent = "Адрес не требуется — вы получите заказ в точке выдачи.";
            } else {
                addressField.required = true;
                addressField.value = "";
                deliveryNote.textContent = "Введите полный адрес для доставки.";
            }
        }

        deliveryType.addEventListener('change', updateFields);
        updateFields(); // При загрузке
    });
</script>
</body>
</html>
