<?php
session_start();
require_once("../sql.php");

if (!isset($_SESSION['login'])) {
    header("Location: ../Вход/login.php");
    exit();
}

$login = $_SESSION['login'];
$edit_mode = false;
$message = $error = null;
$user = null;
$orders = [];

// Получение профиля
$stmt = $conn->prepare("SELECT * FROM users WHERE login = ?");
$stmt->bind_param("s", $login);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    session_destroy();
    header("Location: ../Вход/login.php");
    exit();
}

// Получение заказов пользователя
$stmt = $conn->prepare("
    SELECT id, name, phone, delivery_address, created_at 
    FROM orders 
    WHERE email = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->bind_param("s", $user['email']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'edit') {
        $edit_mode = true;
    } elseif ($action === 'cancel') {
        $edit_mode = false;
    } elseif ($action === 'delete') {
        if ($user['photo']) @unlink(__DIR__ . '/../images/Фото профиля/' . $user['photo']);
        $stmt = $conn->prepare("DELETE FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        if ($stmt->execute()) {
            session_destroy();
            header("Location: ../main.php");
            exit();
        } else {
            $error = "Ошибка при удалении аккаунта.";
        }
    } elseif ($action === 'save') {
        $new_login = trim($_POST['login']);
        $new_email = trim($_POST['email']);
        $errors = [];

        if (empty($new_login)) $errors[] = "Логин не может быть пустым.";
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Некорректный email.";

        // Проверка уникальности
        $stmt = $conn->prepare("SELECT id FROM users WHERE (login = ? OR email = ?) AND login != ?");
        $stmt->bind_param("sss", $new_login, $new_email, $login);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $existing = $conn->prepare("SELECT login FROM users WHERE id = ?");
            $existing->bind_param("i", $row['id']);
            $existing->execute();
            $existing = $existing->get_result()->fetch_assoc();
            if ($existing['login'] === $new_login) {
                $errors[] = "Этот логин уже занят.";
            } else {
                $errors[] = "Этот email уже используется.";
            }
        }

        $photo_filename = $user['photo'];
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../images/Фото профиля/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $tmp_name = $_FILES['photo']['tmp_name'];
            $original_name = basename($_FILES['photo']['name']);
            $new_filename = uniqid('img_') . '_' . preg_replace('/[^A-Za-z0-9.]/', '', $original_name);
            $target = $upload_dir . $new_filename;
            if (move_uploaded_file($tmp_name, $target)) {
                if ($photo_filename && file_exists($upload_dir . $photo_filename)) {
                    @unlink($upload_dir . $photo_filename);
                }
                $photo_filename = $new_filename;
            } else {
                $errors[] = "Ошибка загрузки фото.";
            }
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("UPDATE users SET login = ?, email = ?, photo = ? WHERE login = ?");
            $stmt->bind_param("ssss", $new_login, $new_email, $photo_filename, $login);
            if ($stmt->execute()) {
                $_SESSION['login'] = $new_login;
                $user['login'] = $new_login;
                $user['photo'] = $photo_filename;
                $message = "Профиль успешно обновлён!";
            } else {
                $error = "Ошибка базы данных.";
            }
        } else {
            $error = "" . implode('<br>', $errors);
            $edit_mode = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль – MeetForEat</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/media-query.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            margin: 0;
            color: #2c3e50;
        }

        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
            padding: 80px 20px 60px;
        }

        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }

        .profile-header {
            background: linear-gradient(135deg, #e94e77, #f57c98);
            color: white;
            padding: 30px 20px;
            font-size: 1.6em;
            font-weight: 600;
            text-align: center;
        }

        .profile-content {
            padding: 30px;
        }

        .profile-photo-wrapper {
            position: relative;
            width: 140px;
            height: 140px;
            margin: -60px auto 20px;
            border-radius: 50%;
            overflow: hidden;
            border: 5px solid white;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }

        .profile-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5em;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            background: linear-gradient(45deg, #ff9a9e, #fecfef);
        }

        .upload-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            cursor: pointer;
            font-size: 1.2em;
        }

        .profile-photo-wrapper:hover .upload-overlay {
            opacity: 1;
        }

        .profile-info {
            margin: 20px 0;
            text-align: left;
        }

        .profile-info label {
            display: block;
            font-weight: 600;
            color: #34495e;
            margin-bottom: 6px;
            font-size: 0.95em;
        }

        .profile-info span,
        .profile-info input {
            display: block;
            width: 100%;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1em;
            color: #2c3e50;
            background: #fdfdfd;
        }

        .profile-info input:focus {
            outline: none;
            border-color: #e94e77;
            box-shadow: 0 0 0 3px rgba(233, 78, 119, 0.1);
        }

        /* Заказы */
        .orders-section {
            margin-top: 40px;
            border-top: 1px dashed #e0e0e0;
            padding-top: 20px;
        }

        .orders-section h3 {
            margin-bottom: 15px;
            color: #2c3e50;
            font-size: 1.2em;
        }

        .order-item {
            background: #fdfdfd;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 10px;
            font-size: 0.95em;
            border: 1px solid #eee;
        }

        .order-item b {
            color: #2c3e50;
        }

        .no-orders {
            color: #999;
            font-style: italic;
        }

        /* Кнопки */
        .profile-btns {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 25px;
            justify-content: center;
        }

        .profile-btn {
            padding: 14px 22px;
            border: none;
            border-radius: 10px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            min-width: 140px;
            text-decoration: none;
            text-align: center;
        }

        .profile-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-save { background: #e94e77; color: white; }
        .btn-cancel { background: #bdc3c7; color: #2c3e50; }
        .btn-delete { background: #c0392b; color: white; }
        .btn-orders { background: #3498db; color: white; }
        .btn-logout { background: #7f8c8d; color: white; }
        .btn-back { background: #95a5a6; color: white; }

        .message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95em;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95em;
            white-space: pre-line;
        }

        .btn-up {
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .profile-header { font-size: 1.4em; }
            .profile-photo-wrapper { width: 120px; height: 120px; }
            .profile-btn { min-width: 120px; font-size: 0.95em; padding: 12px 18px; }
        }
    </style>
</head>
<body>
<div class="page-wrapper">

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
</nav> <br> <br> <br>

    <!-- Кнопка "Наверх" -->
    <div class="butt-up">
        <a class="btn-up btn-up_hide" id="btn-up" href="#nav"><i class="fas fa-arrow-up"></i></a>
    </div>

    <main>
        <div class="profile-container">
            <div class="profile-header">Профиль пользователя</div>
            <div class="profile-content">

                <?php if ($message): ?>
                    <div class="message"><?= $message ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="error"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="profile-photo-wrapper">
                        <?php if ($user['photo']): ?>
                            <img src="../images/Фото профиля/<?= $user['photo'] ?>" alt="Фото" class="profile-img">
                        <?php else: ?>
                            <div class="avatar-fallback"
                                 style="background: <?= getGradientColor($user['login']) ?>;">
                                <?= mb_strtoupper(mb_substr($user['login'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($edit_mode): ?>
                            <label for="photo-upload" class="upload-overlay">Загрузить</label>
                            <input type="file" name="photo" id="photo-upload" accept="image/*" style="display: none;">
                        <?php endif; ?>
                    </div>

                    <?php if (!$edit_mode): ?>
                        <div class="profile-info">
                            <label>Логин:</label>
                            <span><?= htmlspecialchars($user['login']) ?></span>
                        </div>
                        <div class="profile-info">
                            <label>Email:</label>
                            <span><?= htmlspecialchars($user['email']) ?></span>
                        </div>
                        <div class="profile-btns">
                            <button type="submit" name="action" value="edit" class="profile-btn btn-save">Редактировать</button>
                        </div>
                    <?php else: ?>
                        <div class="profile-info">
                            <label>Логин:</label>
                            <input type="text" name="login" value="<?= htmlspecialchars($user['login']) ?>" required>
                        </div>
                        <div class="profile-info">
                            <label>Email:</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="profile-btns">
                            <button type="submit" name="action" value="save" class="profile-btn btn-save">Сохранить</button>
                            <button type="submit" name="action" value="cancel" class="profile-btn btn-cancel">Отмена</button>
                        </div>
                    <?php endif; ?>
                </form>

                <!-- История заказов -->
                <div class="orders-section">
                    <h3>Последние заказы</h3>
                    <?php if (empty($orders)): ?>
                        <p class="no-orders">Заказов пока нет.</p>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <div class="order-item">
                                <b>ID:</b> <?= $order['id'] ?> | 
                                <b>Дата:</b> <?= date('d.m.Y', strtotime($order['created_at'])) ?><br>
                                <b>Адрес:</b> <?= htmlspecialchars($order['delivery_address']) ?><br>
                                <b>Телефон:</b> <?= htmlspecialchars($order['phone']) ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Кнопки действий -->
                <form method="POST" onsubmit="return confirm('Удалить аккаунт? Это нельзя отменить.')">
                    <div class="profile-btns">
                        <button type="submit" name="action" value="delete" class="profile-btn btn-delete">Удалить аккаунт</button>
                    </div>
                </form>

                <div class="profile-btns">
                    <a href="../order/orders_catalog.php" class="profile-btn btn-orders">Все заказы</a>
                    <a href="logout_profile.php" class="profile-btn btn-logout">Выйти</a>
                    <a href="../index.php" class="profile-btn btn-back">На главную</a>
                </div>
            </div>
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

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnUp = document.getElementById('btn-up');
        if (btnUp) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 300) {
                    btnUp.classList.add('btn-up_show');
                } else {
                    btnUp.classList.remove('btn-up_show');
                }
            });
            btnUp.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        const updateCartCount = () => {
            const count = <?= $_SESSION['cart_count'] ?? 0 ?>;
            const counter = document.getElementById('cart-count');
            if (counter) {
                counter.textContent = count;
                counter.style.animation = 'none';
                setTimeout(() => { counter.style.animation = 'pop 0.3s ease'; }, 10);
            }
        };
        updateCartCount();
    });

</script>
</body>
</html>

<?php
// Функция для генерации градиента по логину
function getGradientColor($str) {
    $colors = [
        'linear-gradient(45deg, #ff9a9e, #fecfef)',
        'linear-gradient(45deg, #a8edea, #fed6e3)',
        'linear-gradient(45deg, #ffecd2, #fcb69f)',
        'linear-gradient(45deg, #c3cfe2, #c3cfe2)',
        'linear-gradient(45deg, #fdcbf1, #e6dee9)',
        'linear-gradient(45deg, #ff9a9e, #fecfef)',
        'linear-gradient(45deg, #667eea, #764ba2)',
        'linear-gradient(45deg, #f093fb, #f5576c)'
    ];
    $hash = 0;
    for ($i = 0; $i < strlen($str); $i++) {
        $hash = $str[$i] + ($hash << 6) + ($hash << 16) - $hash;
    }
    $index = abs($hash) % count($colors);
    return $colors[$index];
}
?>
