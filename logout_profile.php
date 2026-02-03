<?php
// Начинаем сессию
session_start();

// Удаляем все переменные сессии
session_unset();

// Уничтожаем сессию
session_destroy();

// Перенаправляем пользователя на главную страницу автосалона
header("Location: http://MeetForEat/index.php");
exit(); // Завершаем выполнение скрипта
?>