-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 14, 2026 at 09:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `restaurant`
--

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `contact_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied','archived') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `name`, `email`, `phone`, `password`, `role`, `username`, `address`) VALUES
(2, 'ADMIN', 'admin@example.com', '0112223334', '$2y$10$BCIZHPUwS0yggDlIcjdoduqEw1c9k8xKCrtOYdWva1cLfe7uc5cze', 'manager', 'admin', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `item_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`item_id`, `name`, `price`, `category`, `image_path`, `description`, `is_available`, `created_at`) VALUES
(2, 'Brownies with Nuts', 800.00, 'Desserts', 'uploads/693f0ffdb9f93_Brownies_with_Nuts.jpeg', 'Decadent, fudgy brownies loaded with crunchy nuts, baked to perfection for a rich and satisfying treat in every bite.', 1, '2025-12-10 19:13:21'),
(3, 'Bruschetta', 800.00, 'Appetizers', 'uploads/693f0f0934161_Bruschetta.jpeg', 'Toasted slices of artisan bread topped with fresh tomatoes, basil, garlic, and a drizzle of olive oil—light, refreshing, and bursting with flavour.', 1, '2025-12-10 19:14:12'),
(4, 'Cheese Burger', 950.00, 'Main Course', 'uploads/693f1195bdcf2_Cheese_Burger.jpeg', 'Juicy beef patty topped with melted cheese, fresh lettuce, ripe tomatoes, and our signature sauce, all nestled in a soft, toasted bun—classic comfort in every bite.', 1, '2025-12-10 19:15:56'),
(5, 'Strawberry Cheesecake', 1400.00, 'Desserts', 'uploads/693f1108509ed_Strawberry_Cheesecake.jpeg', 'Creamy, velvety cheesecake layered on a buttery graham cracker crust and topped with fresh, sweet strawberries—a perfect balance of rich and fruity.', 1, '2025-12-10 19:17:14'),
(6, 'Chicken BBQ', 1350.00, 'Appetizers', 'uploads/693f0ecfee096_Chicken_BBQ.jpeg', 'Tender, juicy chicken grilled to perfection and glazed with smoky barbecue sauce—bursting with flavor in every bite.', 1, '2025-12-10 19:17:56'),
(7, 'Chicken Burger', 850.00, 'Main Course', 'uploads/693f119f5974a_Chicken_Burger.jpeg', 'Crispy or grilled chicken patty topped with fresh lettuce, juicy tomatoes, and creamy sauce, all tucked inside a soft, toasted bun—pure satisfaction in every bite.', 1, '2025-12-10 19:18:31'),
(8, 'Chicken Nuggets', 850.00, 'Appetizers', 'uploads/693f0f9034bef_Chicken_Nuggets.jpeg', 'Golden, crispy chicken bites with a tender, juicy interior—perfectly paired with your favorite dipping sauce for a delicious snack or meal.', 1, '2025-12-10 19:19:31'),
(9, 'Chocolate Cake', 449.00, 'Desserts', 'uploads/693f10beef972_Chocolate_Cake.jpeg', 'Rich, moist chocolate cake layered with creamy chocolate frosting—a decadent treat for every chocolate lover.', 1, '2025-12-10 19:20:11'),
(10, 'Chocolate Lava Cake', 900.00, 'Desserts', 'uploads/693f10cfbc544_Chocolate_Lava_Cake.jpeg', 'Warm, decadent chocolate cake with a molten, gooey center that oozes rich chocolate with every bite—pure indulgence for dessert lovers.', 1, '2025-12-10 19:20:54'),
(11, 'Coleslaw', 500.00, 'Sides', 'uploads/693f16797f039_Coleslaw.jpeg', 'Crisp, fresh cabbage and carrots tossed in a creamy, tangy dressing—a refreshing and crunchy side to complement any meal.', 1, '2025-12-10 19:21:49'),
(12, 'Corn on the Cob', 500.00, 'Sides', 'uploads/693f168b0eb59_Corn_on_the_Cob.jpeg', 'Sweet, tender corn grilled or boiled to perfection, served with butter and a hint of seasoning—a simple, classic treat everyone loves.', 1, '2025-12-10 19:22:24'),
(13, 'Egg Kottu', 650.00, 'Main Course', 'uploads/693f11ad8fce6_Egg_Kottu.jpeg', 'A Sri Lankan favorite! Stir-fried chopped roti mixed with spiced eggs, vegetables, and savory seasonings—flavorful, hearty, and utterly satisfying.', 1, '2025-12-10 19:23:18'),
(14, 'Faluda', 350.00, 'Drinks', 'uploads/693f112acd430_Faluda.jpeg', 'A refreshing and colorful dessert drink made with sweet basil seeds, vermicelli, rose syrup, and chilled milk—topped with ice cream for a creamy, indulgent treat.', 1, '2025-12-10 19:24:17'),
(15, 'French Fries', 500.00, 'Sides', 'uploads/693f16a55e5d2_French_Fries.jpeg', 'Crispy, golden fries seasoned to perfection—hot, crunchy, and irresistible with every bite.', 1, '2025-12-10 19:24:51'),
(16, 'Fried Pork', 1450.00, 'Sides', 'uploads/693f16b132070_Fried_Pork.jpeg', 'Crispy, golden-brown pork pieces with a juicy, flavorful interior—perfectly seasoned for a savory, satisfying bite every time.', 1, '2025-12-10 19:25:42'),
(17, 'Fruit Salad', 500.00, 'Desserts', 'uploads/693f10dbcff31_Fruit_Salad.jpeg', 'A refreshing mix of seasonal fruits, tossed together for a naturally sweet and colorful treat—light, healthy, and perfect for any time of day.', 1, '2025-12-10 19:26:23'),
(18, 'Garlic Bread', 600.00, 'Appetizers', 'uploads/693f0f9a36a7d_Garlic_Bread.jpeg', 'Toasted, buttery bread infused with roasted garlic and herbs—crispy on the outside, soft on the inside, and packed with irresistible flavor.', 1, '2025-12-10 19:27:13'),
(19, 'Grilled Chicken', 1250.00, 'Sides', 'uploads/693f16c3eeb88_Grilled_Chicken.jpeg', 'Juicy chicken grilled to perfection with a blend of herbs and spices—smoky, tender, and bursting with flavor in every bite.', 1, '2025-12-10 19:28:09'),
(20, 'Shrimp Rice', 1750.00, 'Main Course', 'uploads/693f14a3afe8f_Shrimp_Rice.jpeg', 'Fragrant rice stir-fried with succulent shrimp, fresh vegetables, and savory seasonings—delivering a perfect harmony of flavors in every bite.', 1, '2025-12-10 19:29:08'),
(21, 'Herbal Tea', 150.00, 'Drinks', 'uploads/693f113f8aa48_Herbal_Tea.jpeg', 'A soothing blend of natural herbs, carefully brewed to create a calming, aromatic tea—perfect for relaxation and a gentle boost of wellness.', 1, '2025-12-10 19:29:43'),
(22, 'Hot Chocolate', 500.00, 'Drinks', 'uploads/693f114bb9334_Hot_Chocolate.jpeg', 'Rich, velvety chocolate blended with warm milk and topped with a hint of whipped cream—comfort in a cup, perfect for any time of day.', 1, '2025-12-10 19:30:20'),
(23, 'Ice Coffee', 400.00, 'Drinks', 'uploads/693f1157dcb3c_Ice_Coffee.jpeg', 'Chilled, refreshing coffee served over ice, perfectly balanced with milk and a touch of sweetness—your cool pick-me-up any time of day.', 1, '2025-12-10 19:30:58'),
(24, 'Ice Cream Sundae', 750.00, 'Desserts', 'uploads/693f10f02368c_Ice_Cream_Sundae.jpeg', 'Creamy scoops of ice cream topped with rich chocolate or caramel sauce, whipped cream, and a cherry on top—an indulgent treat in every bite.', 1, '2025-12-10 19:31:38'),
(25, 'Iced Coffee', 350.00, 'Drinks', 'uploads/693f11624a903_Iced_Coffee.jpeg', 'Chilled coffee over ice, blended with milk and a touch of sweetness—refreshing, energizing, and perfect for a hot day.', 1, '2025-12-10 19:32:15'),
(26, 'Lasagna', 1450.00, 'Main Course', 'uploads/693f11c01c0df_Lasagna.jpeg', 'Layers of tender pasta, rich meat sauce, creamy béchamel, and melted cheese baked to perfection—comfort food at its finest.', 1, '2025-12-10 19:33:06'),
(27, 'Lime juice', 250.00, 'Drinks', 'uploads/693f11725d3c9_Lime_juice.jpeg', 'Freshly squeezed lime juice, chilled and lightly sweetened—a zesty, refreshing drink to quench your thirst and invigorate your senses.', 1, '2025-12-10 19:34:38'),
(28, 'Mashed Potatoes', 600.00, 'Sides', 'uploads/693f16dec3299_Mashed_Potatoes.jpeg', 'Creamy, buttery mashed potatoes whipped to perfection—smooth, comforting, and the perfect side for any meal.', 1, '2025-12-10 19:35:41'),
(29, 'Mojito', 450.00, 'Drinks', 'uploads/693f117e3b05f_Mojito.jpeg', 'A refreshing blend of fresh mint, zesty lime, a hint of sweetness, and sparkling soda—cool, crisp, and perfect for any occasion.', 1, '2025-12-10 19:36:16'),
(30, 'Mozzarella Sticks', 750.00, 'Appetizers', 'uploads/693f0fa8ea56e_Mozzarella_Sticks.jpeg', 'Golden, crispy on the outside and ooey-gooey cheesy on the inside, served with a side of rich marinara sauce—perfect for snacking or sharing.', 1, '2025-12-10 19:37:05'),
(31, 'Nasi Goreng', 1350.00, 'Main Course', 'uploads/693f148862505_Nasi_Goreng.jpeg', 'A flavorful Indonesian-style fried rice with aromatic spices, tender vegetables, and your choice of protein—savory, satisfying, and packed with flavor.', 1, '2025-12-10 19:38:13'),
(32, 'Onion Rings', 400.00, 'Appetizers', 'uploads/693f0fbe33381_Onion_Rings.jpeg', 'Crispy, golden-brown onion rings with a light, crunchy coating—perfectly seasoned and ideal as a snack or side.', 1, '2025-12-10 19:40:28'),
(33, 'Pizza', 1250.00, 'Main Course', 'uploads/693f149513aa9_Pizza.jpeg', 'Freshly baked pizza with a golden crust, rich tomato sauce, gooey melted cheese, and your choice of toppings—an all-time favorite for any appetite.', 1, '2025-12-10 19:41:23'),
(34, 'Pudding', 350.00, 'Desserts', 'uploads/693f10fee7cf8_Pudding.jpeg', 'Smooth, creamy, and perfectly sweet, this classic pudding is a comforting dessert that melts in your mouth with every bite.', 1, '2025-12-10 19:43:25'),
(35, 'Smoothies', 400.00, 'Drinks', 'uploads/693f118acf3db_Smoothies.jpeg', 'Fresh, blended fruits and creamy yogurt come together in a deliciously refreshing smoothie—healthy, vibrant, and perfect for a quick energy boost.', 1, '2025-12-10 19:44:10'),
(36, 'Spaghetti', 1250.00, 'Main Course', 'uploads/693f14b6980ef_Spaghetti.jpeg', 'Al dente pasta tossed in a rich, flavorful sauce with fresh herbs and your choice of meat or vegetables—classic, comforting, and utterly satisfying.', 1, '2025-12-10 19:44:50'),
(37, 'Spring Rolls', 600.00, 'Appetizers', 'uploads/693f0fde968df_Spring_Rolls.jpeg', 'Crispy, golden rolls filled with fresh vegetables and savory seasonings—light, crunchy, and perfect as a starter or snack.', 1, '2025-12-10 19:45:26'),
(38, 'Tiramisu', 1200.00, 'Desserts', 'uploads/693f111317ccf_Tiramisu.jpeg', 'Layers of espresso-soaked ladyfingers, creamy mascarpone, and a dusting of cocoa—an indulgent Italian dessert that’s light, rich, and utterly irresistible.', 1, '2025-12-10 19:46:10'),
(39, 'Vegetable Kottu', 650.00, 'Main Course', 'uploads/693f14dd74f77_Vegetable_Kottu.jpeg', 'Chopped roti stir-fried with fresh vegetables and aromatic spices—flavorful, hearty, and a true Sri Lankan favorite.', 1, '2025-12-10 19:46:51'),
(40, 'Vegetable Ratatouille', 1100.00, 'Main Course', 'uploads/693f14f4137c1_Vegetable_Ratatouille.jpeg', 'A colorful medley of fresh, tender vegetables slow-cooked with herbs and olive oil—light, flavorful, and perfect as a wholesome main or side dish.', 1, '2025-12-10 19:47:56'),
(41, 'Watalappan', 500.00, 'Desserts', 'uploads/693f111d59187_Watalappan.jpeg', 'A traditional Sri Lankan dessert made from coconut milk, jaggery, and aromatic spices—rich, creamy, and delightfully sweet in every bite.', 1, '2025-12-10 19:49:56'),
(42, 'Truffle Pasta', 1200.00, 'Main Course', 'uploads/693f14c74a7c7_Truffle_Pasta.jpeg', 'Creamy handmade pasta with black truffle and parmesan cheese, finished with fresh herbs', 1, '2025-12-10 19:54:39'),
(43, 'Grilled Salmon', 1000.00, 'Sides', 'uploads/693f16d0337d2_Grilled_Salmon.jpeg', 'Atlantic salmon with lemon butter sauce and seasonal vegetables, grilled to perfection.', 1, '2025-12-10 19:55:45'),
(44, 'Vegetable Risotto', 750.00, 'Main Course', 'uploads/693f1508dcd17_Vegetable_Risotto.jpeg', 'Creamy Arborio rice with fresh seasonal vegetables and Parmesan cheese.', 1, '2025-12-10 19:57:05'),
(48, 'Thai Fried Rice', 1600.00, 'Main Course', 'uploads/6958c34473248_Thai-fried-rice-with-sliced-cucumbers-thumbnaill-photo.jpg', 'An authentic recipe for Thai fried rice – just like you get in Thailand and at Thai restaurants! Make this with shrimp/prawns, chicken or any protein you wish.', 1, '2026-01-03 07:20:36');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_type` enum('dine_in','delivery','takeaway') NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `status` enum('pending','preparing','ready','delivered','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `special_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_logs`
--

CREATE TABLE `order_logs` (
  `log_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `guests` int(11) NOT NULL,
  `occasion` varchar(50) DEFAULT 'dinner',
  `special_requests` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservation_tables`
--

CREATE TABLE `reservation_tables` (
  `reservation_table_id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_tables`
--

CREATE TABLE `restaurant_tables` (
  `table_id` int(11) NOT NULL,
  `table_number` varchar(10) NOT NULL,
  `capacity` int(11) NOT NULL,
  `location` varchar(50) DEFAULT NULL COMMENT 'e.g., "window", "center", "private"',
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurant_tables`
--

INSERT INTO `restaurant_tables` (`table_id`, `table_number`, `capacity`, `location`, `is_available`) VALUES
(1, 'T01', 2, 'window', 1),
(2, 'T02', 2, 'center', 1),
(3, 'T03', 4, 'window', 1),
(4, 'T04', 4, 'center', 1),
(5, 'T05', 6, 'center', 1),
(6, 'T06', 6, 'private', 1),
(7, 'T07', 8, 'private', 1),
(8, 'T08', 2, 'window', 1),
(9, 'T09', 4, 'center', 1),
(10, 'T10', 6, 'window', 1),
(11, 'Hall-1', 50, 'private', 0),
(12, 'Hall-2', 20, 'private', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`contact_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Indexes for table `order_logs`
--
ALTER TABLE `order_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_date` (`reservation_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_date_time` (`reservation_date`,`reservation_time`);

--
-- Indexes for table `reservation_tables`
--
ALTER TABLE `reservation_tables`
  ADD PRIMARY KEY (`reservation_table_id`),
  ADD UNIQUE KEY `unique_reservation_table` (`reservation_id`,`table_id`),
  ADD KEY `table_id` (`table_id`);

--
-- Indexes for table `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  ADD PRIMARY KEY (`table_id`),
  ADD UNIQUE KEY `unique_table_number` (`table_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_logs`
--
ALTER TABLE `order_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reservation_tables`
--
ALTER TABLE `reservation_tables`
  MODIFY `reservation_table_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  MODIFY `table_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_logs`
--
ALTER TABLE `order_logs`
  ADD CONSTRAINT `order_logs_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `reservation_tables`
--
ALTER TABLE `reservation_tables`
  ADD CONSTRAINT `reservation_tables_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservation_tables_ibfk_2` FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables` (`table_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
