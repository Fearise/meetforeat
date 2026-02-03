<?php
session_start();
require_once("../sql.php");

if (!isset($_SESSION['login'])) {
    header("Location: ../login/login.php");
    exit();
}

$login = $_SESSION['login'];
$orders = [];

// Получаем заказы
$sql = "
    SELECT 
        id, name, phone, email, delivery_address,
        delivery_type, payment_method, order_notes,
        total_amount, status, created_at
    FROM orders 
    WHERE user_login = ? 
    ORDER BY created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $login);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

// Функция для цвета статуса
function getStatusColor($status) {
    switch ($status) {
        case 'новый': return '#007bff';
        case 'в обработке': return '#ffc107';
        case 'готов': return '#28a745';
        case 'доставляется': return '#17a2b8';
        case 'выполнен': return '#6c757d';
        case 'отменён': return '#dc3545';
        default: return '#adb5bd';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои заказы – MeetForEat</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/media-query.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
        main { padding: 80px 20px 60px; }
        .orders-container {
            max-width: 1200px; margin: 0 auto; padding: 0 20px;
        }
        .orders-container h1 {
            text-align: center; color: #e94e77; margin-bottom: 10px;
        }
        .orders-container p {
            text-align: center; color: #666; margin-bottom: 40px;
        }
        .order-card {
            background: white; border-radius: 16px; box-shadow: 0 6px 16px rgba(0,0,0,0.08);
            padding: 20px; margin-bottom: 20px; position: relative;
        }
        .order-status {
            position: absolute; top: 20px; right: 20px; padding: 6px 12px;
            border-radius: 20px; font-size: 0.85em; font-weight: 600; color: white;
        }
        .order-card p {
            margin: 8px 0; font-size: 1em; color: #555;
        }
        .order-card b {
            color: #2c3e50;
        }
        .no-orders {
            text-align: center; color: #888; font-size: 1.1em; margin: 60px 0;
        }
        .btn-back {
            display: block; width: fit-content; margin: 30px auto 0;
            padding: 12px 20px; background: #e94e77; color: white; text-decoration: none;
            border-radius: 8px; font-weight: 600;
        }
        .btn-back:hover { background: #d03a60; }
        @media (max-width: 600px) {
            .order-card { padding: 16px; }
            .order-status { position: static; display: inline-block; margin: 0 0 10px; }
        }
    </style>
</head>
<body>



<main>

<!-- Кнопка "Наверх" -->
<div class="butt-up">
    <a class="btn-up btn-up_hide" id="btn-up" href="#nav"><i class="fas fa-arrow-up"></i></a>
</div>

    <div class="orders-container">
        <h1>Мои заказы</h1>
        <p>История ваших заказов с деталями</p>

        <?php if (empty($orders)): ?>
            <p class="no-orders">Заказов пока нет.</p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <span class="order-status"
                          style="background: <?= getStatusColor($order['status']) ?>;">
                        <?= htmlspecialchars($order['status']) ?>
                    </span>

                    <p><b>№ Заказа:</b> <?= $order['id'] ?></p>
                    <p><b>Имя:</b> <?= htmlspecialchars($order['name']) ?></p>
                    <p><b>Телефон:</b> <?= htmlspecialchars($order['phone']) ?></p>
                    <p><b>Email:</b> <?= htmlspecialchars($order['email']) ?></p>

                    <p>
                        <b>Доставка:</b> 
                        <?= $order['delivery_type'] === 'доставка' ? 'Доставка' : 'Самовывоз' ?>
                        <?php if ($order['delivery_type'] === 'доставка'): ?>
                            <br><small style="margin-left: 20px;">Адрес: <?= htmlspecialchars($order['delivery_address']) ?></small>
                        <?php endif; ?>
                    </p>

                    <p><b>Оплата:</b> 
                        <?= $order['payment_method'] === 'наличными' ? 'Наличными' : 'Картой' ?>
                    </p>

                    <p><b>Сумма:</b> <b style="color: #e94e77;"><?= number_format($order['total_amount'], 2, ',', ' ') ?> ₽</b></p>

                    <?php if ($order['order_notes']): ?>
                        <p><b>Комментарий:</b> <em><?= htmlspecialchars($order['order_notes']) ?></em></p>
                    <?php endif; ?>

                    <p><b>Дата:</b> <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="../index.php" class="btn-back">На главную</a>
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

</script>
</body>
</html>
