<?php
session_start();
include '../sql.php';

if (!isset($_SESSION['admin'])) {
    header('Location: admin-login.php');
    exit;
}

$tab = $_GET['tab'] ?? 'products';

// === ОБРАБОТКА ДОБАВЛЕНИЯ ТОВАРА ===
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'products') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $available = isset($_POST['available']) ? 1 : 0;
    $discount = max(0, min(100, (int)($_POST['discount'] ?? 0)));

    if (empty($name) || empty($description) || empty($category) || $price <= 0) {
        $error = 'empty';
    } else {
        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = 'format';
            } else {
                $fileName = uniqid('food_') . '.' . $ext;
                $target = "../images/Блюда/" . $fileName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $imagePath = $fileName;
                } else {
                    $error = 'upload';
                }
            }
        } else {
            $error = 'nofile';
        }

        if (!$error) {
            $stmt = $conn->prepare("INSERT INTO menu_items (name, description, price, image, category, available, discount) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdssii", $name, $description, $price, $imagePath, $category, $available, $discount);
            if ($stmt->execute()) {
                $msg = "Блюдо «{$name}» успешно добавлено!";
            } else {
                $error = 'db';
            }
            $stmt->close();
        }
    }
}

// Удаление товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $id = (int)$_POST['delete_product'];
    $stmt = $conn->prepare("DELETE FROM menu_items WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $msg = 'Товар удалён.';
}

// Удаление пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $id = (int)$_POST['delete_user'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $msg = 'Пользователь удалён.';
}

// Удаление отзыва
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    $id = (int)$_POST['delete_review'];
    $stmt = $conn->prepare("DELETE FROM support WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $msg = 'Отзыв удалён.';
}

// Получение данных
$products = [];
$result = $conn->query("SELECT id, name, description, price, image, category, discount, available FROM menu_items ORDER BY category, name");
if ($result) $products = $result->fetch_all(MYSQLI_ASSOC);

$users = [];
$result = $conn->query("SELECT id, login, email, created_at FROM users ORDER BY created_at DESC");
if ($result) $users = $result->fetch_all(MYSQLI_ASSOC);

$reviews = [];
$result = $conn->query("SELECT id, name, email, description, created_at FROM support ORDER BY created_at DESC");
if ($result) $reviews = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админка – MeetForEat</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fc;
            color: #333;
        }
        .admin-header {
            background: #2c3e50;
            padding: 15px 30px;
        }
        .admin-nav {
            display: flex;
            gap: 20px;
            list-style: none;
        }
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.3s, color 0.3s;
        }
        .admin-nav a:hover,
        .admin-nav a[active] {
            background: #e94e77;
        }
        .admin-nav a.admin_logout {
            margin-left: auto;
            background: none;
            color: white;
            border: none; /* ✅ Убрана белая обводка */
        }
        .admin-nav a.admin_logout:hover {
            background: #e74c3c;
            color: white;
        }
        .admin-content {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        h2 {
            font-size: 1.8em;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }
        .alert {
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95em;
            text-align: center;
            background: #e8f5e8;
            color: #2e7d32;
        }
        .add-form {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.06);
            margin-bottom: 30px;
        }

        /* === ЕДИНЫЙ СТИЛЬ ДЛЯ ВСЕХ ПОЛЕЙ ВВОДА === */
        .add-form input[type="text"],
        .add-form input[type="number"],
        .add-form textarea,
        .select-wrapper input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        /* 🔴 РОЗОВАЯ ОБВОДКА ПРИ ФОКУСЕ ДЛЯ ВСЕХ ПОЛЕЙ */
        .add-form input[type="text"]:focus,
        .add-form input[type="number"]:focus,
        .add-form textarea:focus,
        .select-wrapper input:focus {
            outline: none;
            border-color: #e94e77;
            box-shadow: 0 0 0 2px rgba(233, 78, 119, 0.2);
        }

        .add-form textarea {
            min-height: 80px;
            resize: vertical;
        }

        .add-form .checkbox-container {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 12px 0;
        }
        .add-form .checkbox-container label {
            margin: 0;
            font-weight: 500;
            font-size: 1em;
        }

        .add-form .file-input {
            display: none;
        }
        .file-label {
            display: block;
            padding: 12px;
            background: #f0f0f0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            font-size: 1em;
        }
        .file-label:hover {
            background: #e0e0e0;
        }
        .file-name {
            display: block;
            text-align: center;
            font-size: 0.9em;
            color: #777;
            margin: 8px 0;
        }

        .add-form button {
            width: 100%;
            padding: 14px;
            background: #e94e77;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
        }
        .add-form button:hover {
            background: #d03a60;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.06);
        }
        .admin-table th {
            background: #f8f9fa;
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: #555;
            font-size: 1em;
        }
        .admin-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #eee;
            font-size: 0.95em;
        }
        .admin-table img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }
        .status-active {
            background: #27ae60;
            color: white;
        }
        .status-inactive {
            background: #95a5a6;
            color: white;
        }
        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
        }
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        .btn-delete:hover {
            background: #c0392b;
        }
        .admin-footer {
            text-align: center;
            padding: 30px;
            color: #999;
            font-size: 0.9em;
            margin-top: 50px;
        }

        /* === ПОЛЕ ВЫБОРА КАТЕГОРИИ === */
        .select-wrapper {
            position: relative;
            margin: 8px 0;
        }
        .select-wrapper input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            font-family: 'Poppins', sans-serif;
            background: white;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .select-wrapper input:focus {
            outline: none;
            border-color: #e94e77;
            box-shadow: 0 0 0 2px rgba(233, 78, 119, 0.2);
        }
        .select-arrow {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            font-size: 0.8em;
            color: #999;
            pointer-events: none;
            transition: color 0.3s;
        }
        .select-wrapper input:focus + .select-arrow {
            color: #e94e77;
        }
    </style>
</head>
<body>

<header class="admin-header">
    <nav>
        <ul class="admin-nav">
            <li><a href="?tab=products" <?= $tab === 'products' ? 'active' : '' ?>>Товары</a></li>
            <li><a href="?tab=users" <?= $tab === 'users' ? 'active' : '' ?>>Пользователи</a></li>
            <li><a href="?tab=support" <?= $tab === 'support' ? 'active' : '' ?>>Отзывы</a></li>
            <li><a href="admin-login.php" class="admin_logout">Выйти</a></li>
        </ul>
    </nav>
</header>

<main class="admin-content">

    <?php if ($msg): ?>
        <div class="alert">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <?php if ($tab === 'products'): ?>
        <h2>Добавить блюдо</h2>
        <form class="add-form" method="POST" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Название блюда" required>

            <textarea name="description" placeholder="Описание блюда" required></textarea>

            <input type="number" step="0.01" name="price" placeholder="Цена (₽)" min="0" required>

            <div class="select-wrapper">
                <input type="text" list="categories" name="category" placeholder="Категория (например: Бургеры)" required>
                <div class="select-arrow">▼</div>
            </div>
            <datalist id="categories">
                <option value="Бургеры">
                <option value="Пицца">
                <option value="Суши">
                <option value="Шаурма">
                <option value="Выпечка">
            </datalist>

            <div class="checkbox-container">
                <input type="checkbox" id="available" name="available" checked>
                <label for="available">Доступно в меню</label>
            </div>

            <div>
                <label>Скидка (%)</label>
                <input type="number" name="discount" min="0" max="100" value="0">
            </div>

            <label>Изображение</label>
            <input type="file" name="image" id="image" class="file-input" accept="image/*" required>
            <label for="image" class="file-label">Выберите фото блюда</label>
            <span class="file-name" id="file-name">Файл не выбран</span>

            <button type="submit">Добавить блюдо</button>
        </form>

        <h2>Все блюда</h2>
        <table class="admin-table">
            <thead>
            <tr>
                <th>Фото</th>
                <th>Название</th>
                <th>Описание</th>
                <th>Цена</th>
                <th>Категория</th>
                <th>Скидка</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><img src="../images/Блюда/<?= htmlspecialchars($p['image']) ?>" alt=""></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= htmlspecialchars(mb_strimwidth($p['description'], 0, 60, '...')) ?></td>
                    <td><?= number_format($p['price'], 2, ',', ' ') ?> ₽</td>
                    <td><b><?= htmlspecialchars($p['category']) ?></b></td>
                    <td><?= $p['discount'] > 0 ? '-' . $p['discount'] . '%' : '—' ?></td>
                    <td>
                        <span class="status <?= $p['available'] ? 'status-active' : 'status-inactive' ?>">
                            <?= $p['available'] ? 'Активно' : 'Скрыто' ?>
                        </span>
                    </td>
                    <td>
                        <form method="post" onsubmit="return confirm('Удалить это блюдо?')">
                            <input type="hidden" name="delete_product" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-delete">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    <?php elseif ($tab === 'users'): ?>
        <h2>Пользователи</h2>
        <table class="admin-table">
            <thead>
            <tr>
                <th>Логин</th>
                <th>Email</th>
                <th>Дата регистрации</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['login']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= $u['created_at'] ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Удалить пользователя?')">
                            <input type="hidden" name="delete_user" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn btn-delete">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    <?php elseif ($tab === 'support'): ?>
        <h2>Отзывы</h2>
        <table class="admin-table">
            <thead>
            <tr>
                <th>Имя</th>
                <th>Email</th>
                <th>Сообщение</th>
                <th>Дата</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($reviews as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['name']) ?></td>
                    <td><?= htmlspecialchars($r['email']) ?></td>
                    <td><?= htmlspecialchars(mb_strimwidth($r['description'], 0, 100, '...')) ?></td>
                    <td><?= $r['created_at'] ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Удалить отзыв?')">
                            <input type="hidden" name="delete_review" value="<?= $r['id'] ?>">
                            <button type="submit" class="btn btn-delete">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</main>

<footer class="admin-footer">
    Админ-панель &copy; <?= date('Y') ?> | MeetForEat
</footer>

<script>
    const fileInput = document.getElementById('image');
    const fileNameSpan = document.getElementById('file-name');
    fileInput.addEventListener('change', () => {
        fileNameSpan.textContent = fileInput.files.length ? 'Выбрано: ' + fileInput.files[0].name : 'Файл не выбран';
    });
</script>

</body>
</html>
