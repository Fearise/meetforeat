<?php
session_start();
require_once "../sql.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    if ($login === "admin" && $password === "123") {
        $_SESSION['admin'] = true;
        header("Location: index.php");
        exit;
    }
    $error = true;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход | Админка</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6f9;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 350px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: #2c3e50;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #e94e77;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
        }
        button:hover {
            background: #d03a60;
        }
        .error {
            color: #e74c3c;
            font-size: 0.9em;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Админка</h2>
        <form method="POST">
            <input type="text" name="login" placeholder="Логин" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Войти</button>
        </form>
        <?php if (isset($error)): ?>
            <p class="error">❌ Неверный логин или пароль</p>
        <?php endif; ?>
    </div>
</body>
</html>
