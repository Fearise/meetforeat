<?php
session_start();
unset($_SESSION['cart']); // Удаление корзины из сессии
header("Location: http://MeetForEat/cart/cart.php"); // Перенаправление на главную
exit();
?>