<?php

// Запускаем сессию
session_start();

// Подключаем файл с настройками базы данных
require_once("../sql.php");

// Проверяем, был ли отправлен POST-запрос
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Получаем логин и пароль из формы
    $login = $_POST['login'];
    $password = $_POST['password'];

    // SQL-запрос для получения хэшированного пароля пользователя по логину
    $sql = "SELECT password FROM `reg` WHERE `login` = '$login'";
    $result = $conn->query($sql);
    
    // Проверяем, есть ли результат и найден ли пользователь
    if ($result && $result->num_rows > 0) {
        // Получаем хэшированный пароль из базы данных
        $row = $result->fetch_assoc();
        $hashedPassword = $row['password'];

        // Проверяем, соответствует ли введенный пароль хэшированному паролю
        if (password_verify($password, $hashedPassword)) {
            // Успешный вход: сохраняем логин в сессии и перенаправляем на страницу профиля
            $_SESSION['login'] = $login;
            header("Location: http://MeetForEat/profile/profile.php");
            exit(); // Завершаем выполнение скрипта после перенаправления
        } else {
            // Если пароль неверный
            echo "Неверный пароль.";
        }
    } else {
        // Если пользователь не найден
        echo "Пользователь не найден.";
    }

    // Закрываем соединение с базой данных
    $conn->close();
} else {
    // Если запрос не является POST
    echo "Некорректный запрос";
}
?>