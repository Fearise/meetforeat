<?php
// Начинаем сессии для хранения данных о пользователе
session_start();

// Подключаемся к базе данных
require_once("../sql.php");

// Проверяем наличие ошибок подключения к базе данных
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Проверяем, был ли отправлен POST-запрос
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получаем данные из формы
    $login = $_POST['login'];
    $email = $_POST['email'];
    // Хэшируем пароль перед сохранением
    $password_hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Обработка фото
    $photo_value = "NULL"; // по умолчанию устанавливаем значение NULL
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        // Указываем директорию для загрузки фото
        $upload_dir = __DIR__ . '/../images/Фото профиля/';
        // Проверяем, существует ли директория, если нет - создаем
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        // Получаем временное имя файла и оригинальное имя
        $tmp_name = $_FILES['photo']['tmp_name'];
        $original_name = basename($_FILES['photo']['name']);
        // Генерируем уникальное имя для загружаемого файла
        $new_filename = uniqid() . '_' . $original_name;
        // Перемещаем загруженный файл в указанную директорию
        if (move_uploaded_file($tmp_name, $upload_dir . $new_filename)) {
            // Если загрузка прошла успешно, оборачиваем имя файла в кавычки для SQL-запроса
            $photo_value = "'" . $conn->real_escape_string($new_filename) . "'";
        }
    }

    // Подготавливаем SQL-запрос для вставки данных в таблицу
    $sql = "INSERT INTO `reg`(`login`, `email`, `password`, `photo`) VALUES (?, ?, ?, $photo_value)";
    $stmt = $conn->prepare($sql); // Подготавливаем запрос
    // Привязываем параметры к подготовленному запросу
    $stmt->bind_param("sss", $login, $email, $password_hashed);

    // Выполняем запрос и проверяем на успешность
    if ($stmt->execute()) {
        echo "Регистрация прошла успешно!";
        // Устанавливаем сессионные переменные для хранения информации о пользователе
        $_SESSION['registered'] = true;
        $_SESSION['login'] = $login;
        // Перенаправляем пользователя на страницу профиля
        header("Location: http://MeetForEat/profile/profile.php");
        exit(); // Завершаем скрипт
    } else {
        // Если произошла ошибка, выводим сообщение об ошибке
        echo "Произошла ошибка при регистрации: " . $stmt->error;
    }
}

// Закрываем соединение с базой данных
$conn->close();
?>