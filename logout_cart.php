<?php
session_start();
session_unset();
session_destroy();
header("Location: http://MeetForEat/cart/cart.php"); // Перенаправление на главную
exit();
?>