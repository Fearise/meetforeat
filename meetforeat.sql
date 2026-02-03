-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Дек 18 2025 г., 02:51
-- Версия сервера: 8.0.30
-- Версия PHP: 8.0.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `meetforeat`
--

-- --------------------------------------------------------

--
-- Структура таблицы `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'Разное',
  `views` int DEFAULT '0',
  `available` tinyint(1) DEFAULT '1',
  `discount` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `description`, `image`, `price`, `category`, `views`, `available`, `discount`) VALUES
(5, 'Унаги роял', 'Состав: Рис, нори, сливочный сыр, угорь, манго, лепестки арахиса, соус Унаги', 'food_69269e3adac10.jpg', '699.00', 'Суши', 42, 1, 20),
(6, 'Пицца Пепперони', 'В состав входят тесто, томатный соус, сыр моцарелла и сырокопчёная колбаса пепперони (салями пеперони).', 'food_6926a034b2a15.jpg', '349.00', 'Пицца', 47, 1, 50),
(12, 'Чизбургер', 'Чизбургер — это популярное блюдо из американской кухни, представляющее собой сэндвич, состоящий из мягкой булочки, внутри которой находится сочная котлета из говядины и плавленый сыр. ', 'food_69434071591d7.jpg', '1000.00', 'Бургеры', 2, 1, 0),
(13, 'Шаурма', 'Шаурма — это популярное блюдо восточной кухни, представляющее собой тонкий лаваш или pita, в который завернуты жареное мясо (обычно курица, говядина или баранина), свежие овощи, зелень и соусы.', 'food_6943410cde763.jpeg', '1500.00', 'Шаурма', 1, 1, 20),
(14, 'Самса с говядиной', 'Самса с говядиной — это традиционное блюдо центральноазиатской кухни, представляющее собой выпечку из тонкого теста, наполненную сочной говядиной и специями.', 'food_694341640e204.jpg', '450.00', 'Выпечка', 0, 1, 80);

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_login` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `delivery_address` text NOT NULL,
  `delivery_type` enum('доставка','самовывоз') DEFAULT 'доставка',
  `payment_method` enum('картой','наличными') DEFAULT 'наличными',
  `order_notes` text,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('новый','в обработке','готов','доставляется','выполнен','отменён') DEFAULT 'новый',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `user_login`, `name`, `phone`, `email`, `delivery_address`, `delivery_type`, `payment_method`, `order_notes`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES
(1, 'vladik', 'vladik', '+7 951 123 45 67', 'vladik@gmail.com', 'Степана Разина, 15, 45', 'доставка', 'картой', 'жду', '698.00', 'новый', '2025-11-28 06:34:07', '2025-11-28 06:34:07'),
(2, 'vlad', 'vlad', '89515553637', 'vlad@gmail.com', 'Ленина, 17, 3', 'доставка', 'наличными', '123', '4192.00', 'новый', '2025-12-16 17:20:51', '2025-12-16 17:20:51'),
(3, 'vlad', 'vlad', '89515553637', 'vlad@gmail.com', 'Ленина, 17, 3', 'доставка', 'наличными', '123', '8312.60', 'новый', '2025-12-16 20:23:37', '2025-12-16 20:23:37'),
(4, 'vlad', 'vlad', '89515553637', 'vlad@gmail.com', 'Ленина, 17, 3', 'доставка', 'наличными', '123', '17747.60', 'новый', '2025-12-16 21:09:51', '2025-12-16 21:09:51'),
(5, 'vlad', 'vlad', '89515553637', 'vlad@gmail.com', 'Ленина, 17, 3', 'доставка', 'наличными', '123', '5665.60', 'новый', '2025-12-17 23:49:17', '2025-12-17 23:49:17');

-- --------------------------------------------------------

--
-- Структура таблицы `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) GENERATED ALWAYS AS ((`quantity` * `price`)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `item_name`, `quantity`, `price`) VALUES
(1, 1, 'Пицца Пепперони', 2, '349.00'),
(2, 2, 'Унаги роял', 4, '699.00'),
(3, 2, 'Пицца Пепперони', 4, '349.00'),
(4, 3, 'Пицца Пепперони', 22, '174.50'),
(5, 3, 'Унаги роял', 8, '559.20'),
(6, 4, 'Пицца Пепперони', 28, '174.50'),
(7, 4, 'Унаги роял', 23, '559.20'),
(8, 5, 'Пицца Пепперони', 4, '174.50'),
(9, 5, 'Унаги роял', 3, '559.20'),
(10, 5, 'Чизбургер', 2, '1000.00'),
(11, 5, 'Шаурма', 1, '1200.00'),
(12, 5, 'Самса с говядиной', 1, '90.00');

-- --------------------------------------------------------

--
-- Структура таблицы `support`
--

CREATE TABLE `support` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `rating` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `support`
--

INSERT INTO `support` (`id`, `name`, `email`, `description`, `rating`, `created_at`) VALUES
(2, 'Владислав Мизеровский', 'vladikmizer@gmail.com', 'Лучший сайт, который я когда-либо видел!', 5, '2025-11-27 08:53:39'),
(3, 'Илсур Шангареев', 'suric_dev1ce@gmail.com', 'Доставка зачёт', 4, '2025-11-27 08:54:32');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `login` varchar(50) DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `login`, `email`, `password`, `photo`, `created_at`) VALUES
(1, 'vladik', 'vladik@gmail.com', '$2y$10$ZdAQp4/jCUatipwCf9ExNOfzxV3T0dPGOe0VnI9hWpBTf1k9tz7ti', 'img_69293b2e48e05_564x3181.webp', '2025-11-28 09:03:26'),
(2, 'vlad', 'vlad@gmail.com', '$2y$10$RJqDndJAoF3BBHKWmgiw1.zC7c4eInawz38jFc3UbXAhrgLUv3qIi', 'img_6942b19a9e8f7_profile.png', '2025-12-16 20:19:14');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Индексы таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Индексы таблицы `support`
--
ALTER TABLE `support`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `support`
--
ALTER TABLE `support`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
