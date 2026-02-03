<?php
session_start();

require_once("../sql.php");

// Подсчёт количества товаров в корзине
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
$_SESSION['cart_count'] = $cart_count; // Сохраняем в сессию

// Добавление в корзину
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = intval($_POST['product_id']);
    $stmt = $conn->prepare("SELECT name, price FROM menu_items WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if (!isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => 1,
            ];
        } else {
            $_SESSION['cart'][$productId]['quantity']++;
        }
    }
    header("Location: cart.php");
    exit();
}

// Очистка корзины
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    unset($_SESSION['cart']);
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Корзина – MeetForEat</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/media-query.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .cart-container {
            max-width: 800px;
            margin: 100px auto 60px;
            padding: 0 20px;
        }

        .cart-item {
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-clear {
            background: #c32;
            color: white;
        }

        .btn-order {
            background: #e94e77;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

<!-- nav -->

<main>
    <div class="cart-container">
        <h2>Корзина</h2>
        <?php if (empty($_SESSION['cart'])): ?>
            <p>Корзина пуста.</p>
        <?php else: ?>
            <?php $total = 0; ?>
            <?php foreach ($_SESSION['cart'] as $id => $item): 
                $sum = $item['price'] * $item['quantity'];
                $total += $sum;
            ?>
                <div class="cart-item">
                    <div>
                        <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                        <?= number_format($item['price'], 2, ',', ' ') ?> ₽ × <?= $item['quantity'] ?> = <b><?= number_format($sum, 2, ',', ' ') ?> ₽</b>
                    </div>
                </div>
            <?php endforeach; ?>
            <p><b>Итого: <?= number_format($total, 2, ',', ' ') ?> ₽</b></p>
            <div class="cart-actions">
                <a href="?action=clear" class="btn btn-clear">Очистить</a>
                <a href="../order/orders.php" class="btn btn-order">Оформить</a>
            </div>
        <?php endif; ?>
    </div>
</main>



</body>
</html>
