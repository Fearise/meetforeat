<?php
session_start();
require_once("../sql.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $description = trim($_POST['description']);
    $rating = (int)$_POST['rating'];

    // Валидация
    if (empty($name) || empty($email) || empty($description) || $rating < 1 || $rating > 5) {
        die("Ошибка: заполните все поля корректно.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Ошибка: некорректный email.");
    }

    // Подготовленный запрос
    $stmt = $conn->prepare("INSERT INTO support (name, email, description, rating, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssi", $name, $email, $description, $rating);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Спасибо за отзыв! Мы ценим ваше мнение.";
    } else {
        $_SESSION['message'] = "Ошибка при отправке. Попробуйте позже.";
    }

    $stmt->close();
    $conn->close();

    header("Location: support.php");
    exit();
}
?>
