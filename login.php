<?php
session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once("../sql.php");
    $login = trim($_POST['login']);
    $password = $_POST['password'];

    // Ищем по логину или email
    $stmt = $conn->prepare("SELECT password FROM users WHERE login = ? OR email = ?");
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['login'] = $login;
            header("Location: ../profile/profile.php");
            exit();
        } else {
            $error = "Неверный пароль.";
        }
    } else {
        $error = "Пользователь не найден.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход – MeetForEat</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/media-query.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            color: #2c3e50;
        }

        main {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 100px 20px 60px;
        }

        .form-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        .form-container h2 {
            margin-bottom: 20px;
            font-size: 1.8em;
            color: #e94e77;
        }

        .error-list {
            background: #fee;
            color: #c32;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.9em;
            margin-bottom: 20px;
        }

        .form-container input[type="text"],
        .form-container input[type="password"] {
            width: 100%;
            padding: 14px;
            margin: 10px 0;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1em;
        }

        .form-container input:focus {
            outline: none;
            border-color: #e94e77;
        }

        .password-eye {
            position: relative;
        }

        .password-eye i {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            cursor: pointer;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #e94e77;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: #d03a60;
        }

        .not_account {
            margin-top: 20px;
            font-size: 0.95em;
            color: #666;
        }

        .not_account_link {
            color: #e94e77;
            text-decoration: none;
            font-weight: 600;
        }

        .not_account_link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<!-- Навигация -->
<nav class="nav" id="nav">
    <div class="logo">
        <a href="../index.php">
            <img src="../images/Иконки и логотип/logo.png" alt="MeetForEat">
        </a>
    </div>
    <div class="nav-menu">
        <p><a class="nav_item" href="../catalog/catalog.php">Меню</a></p>
        <p><a class="nav_item" href="../gallery/gallery.php">Галерея</a></p>
        <p><a class="nav_item" href="../support/support.php">Поддержка</a></p>
        <p><a class="nav_item" href="../about/about.php">О нас</a></p>
    </div>
</nav>

<!-- Кнопка "Наверх" -->
<div class="butt-up">
    <a class="btn-up btn-up_hide" id="btn-up" href="#nav"><i class="fas fa-arrow-up"></i></a>
</div>

<main>
    <div class="form-container">
        <h2>Вход</h2>

        <?php if ($error): ?>
            <div class="error-list"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="login" placeholder="Логин или Email" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" required>

            <div class="password-eye">
                <input type="password" name="password" id="password" placeholder="Пароль" required>
                <i class="fas fa-eye-slash" id="togglePassword"></i>
            </div>

            <button type="submit" class="btn-submit">Войти</button>
        </form>

        <p class="not_account">
            Нет аккаунта? <a href="../reg/reg.php" class="not_account_link">Зарегистрироваться</a>
        </p>
    </div>
</main>

<!-- Футер -->
<footer class="footer">
    <div class="footer-col">
        <h4>Меню</h4>
        <ul>
            <li><a href="../catalog/catalog.php?category=Бургеры">Бургеры</a></li>
            <li><a href="../catalog/catalog.php?category=Пицца">Пицца</a></li>
            <li><a href="../catalog/catalog.php?category=Суши">Суши</a></li>
            <li><a href="../catalog/catalog.php?category=Шаурма">Шаурма</a></li>
            <li><a href="../catalog/catalog.php?category=Выпечка">Выпечка</a></li>
        </ul>
    </div>
    <div class="footer-col">
        <h4>О нас</h4>
        <ul>
            <li><a href="../about/about.php">О компании</a></li>
            <li><a href="../gallery/gallery.php">Галерея</a></li>
            <li><a href="../support/support.php">Поддержка</a></li>
        </ul>
    </div>
    <div class="footer-col">
        <h4>Контакты</h4>
        <ul>
            <li><i class="fas fa-phone"></i> 8 800 555 35 35</li>
            <li><i class="fas fa-envelope"></i> info@meetforeat.ru</li>
            <li><i class="fas fa-map-marker-alt"></i> Москва, ул. Енисейская, 15</li>
        </ul>
    </div>
    <div class="footer-col">
        <h4>Мы в соцсетях</h4>
        <div class="social-icons">
            <a href="#"><i class="fab fa-vk"></i></a>
            <a href="#"><i class="fab fa-telegram"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pwd = document.getElementById('password');
        const toggle = document.getElementById('togglePassword');
        toggle.addEventListener('click', () => {
            pwd.type = pwd.type === 'password' ? 'text' : 'password';
            toggle.classList.toggle('fa-eye');
            toggle.classList.toggle('fa-eye-slash');
        });

        const btnUp = document.getElementById('btn-up');
        window.addEventListener('scroll', () => {
            btnUp.classList.toggle('btn-up_show', window.scrollY > 300);
        });
    });
</script>
</body>
</html>
