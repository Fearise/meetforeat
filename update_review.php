<?php
session_start();
require_once("../sql.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $review_id = $_POST['review_id'] ?? null;
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $description = trim($_POST['description']);
    $rating = (int)$_POST['rating'];

    // Валидация
    if (empty($name) || empty($email) || empty($description) || $rating < 1 || $rating > 5) {
        $_SESSION['message'] = "Ошибка: заполните все поля корректно.";
        header("Location: support.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Ошибка: некорректный email.";
        header("Location: support.php");
        exit();
    }

    // Сохраняем в сессию
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;

    if ($review_id) {
        // Обновляем существующий отзыв
        $stmt = $conn->prepare("UPDATE support SET description = ?, rating = ? WHERE id = ?");
        $stmt->bind_param("sii", $description, $rating, $review_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Ваш отзыв успешно обновлён!";
        }
    } else {
        // Добавляем новый
        $stmt = $conn->prepare("INSERT INTO support (name, email, description, rating) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $email, $description, $rating);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Спасибо за отзыв!";
        }
    }

    $stmt->close();
    $conn->close();

    header("Location: support.php");
    exit();
}
?>
