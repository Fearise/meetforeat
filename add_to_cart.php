<?php
// Отключаем вывод ошибок в ответе (но ошибки будут в логах)
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);

session_start();

// Очищаем буфер (на случай, если что-то уже выведено)
if (ob_get_level()) ob_clean();

header('Content-Type: application/json; charset=utf-8');

require_once('../sql.php');

// Очистка битых товаров
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = array_filter($_SESSION['cart'], function ($item) {
        return is_array($item) && 
               isset($item['id']) && 
               isset($item['name']) && 
               isset($item['price']) && 
               isset($item['quantity']);
    });
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['product_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);

    if ($id <= 0 || empty($name) || $price < 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Некорректные данные'
        ]);
        exit;
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $id) {
            $item['quantity']++;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $id,
            'name' => $name,
            'price' => $price,
            'quantity' => 1
        ];
    }

    $_SESSION['cart_count'] = array_sum(array_column($_SESSION['cart'], 'quantity'));

    echo json_encode([
        'success' => true,
        'message' => 'Товар добавлен в корзину!',
        'count' => $_SESSION['cart_count']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Метод не поддерживается'
    ]);
}
?>
