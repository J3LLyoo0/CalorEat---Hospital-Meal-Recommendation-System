-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 22, 2026 at 10:52 AM
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
-- Database: `caloreat_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `user_id`, `first_name`, `last_name`, `contact_number`, `created_at`) VALUES
(1, 10, 'System', 'Admin', NULL, '2026-07-19 14:22:09');

-- --------------------------------------------------------

--
-- Table structure for table `custom_meals`
--

CREATE TABLE `custom_meals` (
  `custom_meal_id` int(11) NOT NULL,
  `food_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `nutritionist_id` int(11) NOT NULL,
  `base_food_id` int(11) NOT NULL,
  `meal_time` varchar(30) NOT NULL,
  `removed_ingredients` text DEFAULT NULL,
  `added_ingredients` text DEFAULT NULL,
  `preparation_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `custom_meals`
--

INSERT INTO `custom_meals` (`custom_meal_id`, `food_id`, `patient_id`, `nutritionist_id`, `base_food_id`, `meal_time`, `removed_ingredients`, `added_ingredients`, `preparation_notes`, `created_at`) VALUES
(1, 19, 3, 1, 1, 'Lunch', '', '', '', '2026-07-14 06:14:12');

-- --------------------------------------------------------

--
-- Table structure for table `food_items`
--

CREATE TABLE `food_items` (
  `food_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `food_name` varchar(150) NOT NULL,
  `ingredients` text DEFAULT NULL,
  `portion_size` varchar(50) DEFAULT NULL,
  `calories` int(11) NOT NULL,
  `protein` decimal(6,2) DEFAULT NULL,
  `carbohydrates` decimal(6,2) DEFAULT NULL,
  `fat` decimal(6,2) DEFAULT NULL,
  `sodium` decimal(6,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_items`
--

INSERT INTO `food_items` (`food_id`, `category_id`, `food_name`, `ingredients`, `portion_size`, `calories`, `protein`, `carbohydrates`, `fat`, `sodium`, `image`, `status`, `created_at`) VALUES
(1, 1, 'Chicken Lemak Chili Api Pasta', 'Spaghetti, Smoked Chicken Slices, Turmeric Coconut Cream Sauce, Turmeric, Bird\'s Eye Chilli, Lemongrass, Garlic', '1 serving', 600, 0.00, 0.00, 0.00, 0.00, 'images/food/chicken-lemak-cili-api-pasta.jpg', 'active', '2026-07-08 14:27:10'),
(2, 1, 'Beringin Pandan Rice', 'Basmati Rice, Pandan Leaves, Lemongrass, Santan or Low Fat Evaporated Milk, Ghee or Butter, Ginger, Shallots', '1 serving', 275, 0.00, 0.00, 0.00, 0.00, 'images/food/beringin-pandan-rice.jpg', 'active', '2026-07-08 14:27:10'),
(3, 1, 'Nasi Lemak Istana', 'Pandan-infused Coconut Milk Rice, Chicken or Beef, Hard Boiled Egg, Deep-Fried Anchovies, Roasted Peanuts, Cucumber Slices, Sambal', '1 serving', 400, 0.00, 0.00, 0.00, 0.00, 'images/food/nasi-lemak-istana.jpg', 'active', '2026-07-08 14:27:10'),
(4, 1, 'Soup La Doraisamy', 'Lean Chicken or Beef, Sup Bunjut Soup Spice Pouch, Ginger, Garlic, Shallots, Potatoes, Carrots, Water or Light Broth', '1 serving', 500, 0.00, 0.00, 0.00, 0.00, 'images/food/soupaladoraisamywithrotibakarandsaltedbutter.jpg', 'active', '2026-07-08 14:27:10'),
(5, 1, 'Kampung Chicken Vermicelli Noodle Soup', 'Kampung Chicken, Rice Vermicelli, Clear Broth, Fresh Ginger, Salt', '1 serving', 360, 0.00, 0.00, 0.00, 0.00, 'images/food/vermicelli-noodle-soup.jpg', 'active', '2026-07-08 14:27:10'),
(6, 2, 'White Rice with Ginger Soy Chicken and Stir-Fried Sawi', 'Chicken, Sawi Choi Sum, White Rice, Ginger, Soy Sauce, Garlic', '1 serving', 475, 0.00, 0.00, 0.00, 0.00, 'images/food/wrwgscasfs.jpg', 'active', '2026-07-08 14:27:10'),
(7, 2, 'Brown Rice with Ayam Bakar Kunyit and Sautéed Cabbage', 'Brown Rice, Skinless Chicken, Turmeric Powder, Garlic, Ginger, Cabbage', '1 serving', 450, 0.00, 0.00, 0.00, 0.00, 'images/food/brwabkasc.png', 'active', '2026-07-08 14:27:10'),
(8, 2, 'Bihun Soup Ikan with Scallions', 'White Fish Fillet, Bihun Rice Vermicelli, Ginger, Scallions', '1 serving', 288, 0.00, 0.00, 0.00, 0.00, 'images/food/bihun-soup-ikan-with-scallions.jpg', 'active', '2026-07-08 14:27:10'),
(9, 2, 'Sup Ayam Bunjut', 'Skinless Chicken Breast, Ginger, Garlic, Coriander, Fennel, Black Pepper, Cinnamon, Potatoes, Carrots', '1 serving', 118, 0.00, 0.00, 0.00, 0.00, 'images/food/supayambunjut.jpg', 'active', '2026-07-08 14:27:10'),
(10, 3, 'Minced Chicken Ginger Porridge with White Pepper', 'White Rice, Chicken Broth, Minced Chicken, Ginger, White Pepper', '1 serving', 215, 0.00, 0.00, 0.00, 0.00, 'images/food/mcgpwwp.jpg', 'active', '2026-07-08 14:27:10'),
(11, 3, 'Soft Kuey Teow Soup with Minced Beef', 'Kuey Teow, Minced Beef, Egg Whites, Ginger', '1 serving', 375, 0.00, 0.00, 0.00, 0.00, 'images/food/sktswmb.png', 'active', '2026-07-08 14:27:10'),
(12, 3, 'Mashed Pumpkin Rice and Steamed Silken Tofu with Minced Chicken Sauce', 'White Rice, Pumpkin, Silken Tofu, Minced Chicken, Ginger, Garlic', '1 serving', 390, 0.00, 0.00, 0.00, 0.00, 'images/food/mprasstwmcs.jpg', 'active', '2026-07-08 14:27:10'),
(13, 4, 'Plain Porridge', 'White Rice', '1 serving', 120, 0.00, 0.00, 0.00, 0.00, 'images/food/plain-porridge.jpg', 'active', '2026-07-08 14:27:10'),
(14, 4, 'Yam and Chicken Cereal Blend', 'To be updated', '1 serving', 0, 0.00, 0.00, 0.00, 0.00, 'images/food/yam-and-chicken-cereal-blend.jpg', 'active', '2026-07-08 14:27:10'),
(15, 5, 'Ikan Masak Singgang with Brown Rice', 'Brown Rice, Mackerel, Garlic, Shallots, Turmeric, Lemongrass, Tamarind Slices', '1 serving', 330, 0.00, 0.00, 0.00, 0.00, 'images/food/ikan-masak-singgang-with-brown-rice.jpg', 'active', '2026-07-08 14:27:10'),
(16, 5, 'Skinless Lemongrass Grilled Chicken Served with Blanched Spinach', 'Boneless Chicken, Spinach, Lemongrass, Garlic, Shallots, Lime Juice', '1 serving', 250, 0.00, 0.00, 0.00, 0.00, 'images/food/slgcswbs.jpg', 'active', '2026-07-08 14:27:10'),
(17, 6, 'Baked Masala Fish Fillet and Stir-Fried Long Beans with Oats and Rice Blend', 'Fish Fillet, Long Beans, Mix of Rolled Oats and Brown Rice, Garlic, Ginger, Lemin Juice, Turmeric Powder, Garam Masala', '1 serving', 440, 0.00, 0.00, 0.00, 0.00, 'images/food/bmffasflbwoarb.jpg', 'active', '2026-07-08 14:27:10'),
(18, 6, 'Steamed Pomfret with Ginger and Scallions with Stir-Fried Kailan and Wholemeal Bun', 'To be updated', '1 serving', 0, 0.00, 0.00, 0.00, 0.00, 'images/food/spwg&swsfkawb.jpg', 'active', '2026-07-08 14:27:10'),
(19, 1, 'Customized Chicken Lemak Chili Api Pasta', 'Spaghetti, Smoked Chicken Slices, Turmeric Coconut Cream Sauce, Turmeric, Bird\'s Eye Chilli, Lemongrass, Garlic', '1 serving', 600, 0.00, 0.00, 0.00, 0.00, 'images/food/chicken-lemak-cili-api-pasta.jpg', 'active', '2026-07-14 06:14:12');

-- --------------------------------------------------------

--
-- Table structure for table `meal_categories`
--

CREATE TABLE `meal_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_categories`
--

INSERT INTO `meal_categories` (`category_id`, `category_name`, `description`, `status`, `created_at`) VALUES
(1, 'General Meals', 'General meal options displayed on the main page.', 'active', '2026-07-08 05:47:51'),
(2, 'Normal Meals', 'Standard balanced meals suitable for patients without special requirements.', 'active', '2026-07-08 05:47:51'),
(3, 'Soft Meals', 'Soft texture meals for patient who have difficulty chewing.', 'active', '2026-07-08 05:47:51'),
(4, 'Semi-Liquid Meals', 'Semi-liquid meals for patients who require easier swallowing and digestion.', 'active', '2026-07-08 05:47:51'),
(5, 'Low Sodium & Low Fat Meals', 'Meals prepared with reduced sodium and lower fat content.', 'active', '2026-07-08 05:47:51'),
(6, 'Low Sugar Meals', 'Meal suitable for patients who need controlled sugar intake', 'active', '2026-07-08 05:47:51');

-- --------------------------------------------------------

--
-- Table structure for table `meal_recommendations`
--

CREATE TABLE `meal_recommendations` (
  `recommendation_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `food_id` int(11) NOT NULL,
  `nutritionist_id` int(11) NOT NULL,
  `meal_time` varchar(30) NOT NULL,
  `recommendation_type` enum('manual','automatic') NOT NULL DEFAULT 'manual',
  `target_calories` int(11) NOT NULL,
  `meal_calories` int(11) NOT NULL,
  `evaluation_result` varchar(30) NOT NULL,
  `recommendation_notes` text DEFAULT NULL,
  `recommended_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_recommendations`
--

INSERT INTO `meal_recommendations` (`recommendation_id`, `patient_id`, `food_id`, `nutritionist_id`, `meal_time`, `recommendation_type`, `target_calories`, `meal_calories`, `evaluation_result`, `recommendation_notes`, `recommended_at`) VALUES
(1, 3, 1, 1, 'Lunch', 'manual', 564, 600, 'recommended', 'The meal calories are within the selected meal-time range. No recorded allergy conflict was detected. No category-specific medical-condition rule was detected.', '2026-07-14 03:31:54'),
(2, 3, 1, 1, 'Lunch', 'manual', 564, 600, 'recommended', 'The meal calories are within the selected meal-time range. No recorded allergy conflict was detected. No category-specific medical-condition rule was detected.', '2026-07-14 03:52:08'),
(3, 3, 6, 1, 'Dinner', 'automatic', 470, 475, 'recommended', 'The meal calories are within the selected meal-time range. No recorded allergy conflict was detected. No category-specific medical-condition rule was detected.', '2026-07-14 04:17:31'),
(4, 3, 19, 1, 'Lunch', 'manual', 564, 600, 'recommended', 'Customized from Chicken Lemak Chili Api Pasta. ', '2026-07-14 06:14:12');

-- --------------------------------------------------------

--
-- Table structure for table `nutritionists`
--

CREATE TABLE `nutritionists` (
  `nutritionist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `gender` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nutritionists`
--

INSERT INTO `nutritionists` (`nutritionist_id`, `user_id`, `first_name`, `last_name`, `contact_number`, `created_at`, `gender`, `profile_image`) VALUES
(1, 1, 'Demo', 'Nutritionist', '9876543210', '2026-07-07 14:35:20', 'Male', 'images/profile/nutritionist_1_1783927056.png'),
(2, 4, 'Jeff', 'Demo', '000000000', '2026-07-12 13:25:21', NULL, NULL),
(3, 8, 'donovan', '', '', '2026-07-12 18:08:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nutritionist_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `ic_number` varchar(30) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `activity_level` varchar(30) NOT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `nutritionist_gender_edit_used` tinyint(1) NOT NULL DEFAULT 0,
  `allergies` text DEFAULT NULL,
  `medical_conditions` text DEFAULT NULL,
  `daily_calorie_intake` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `user_id`, `nutritionist_id`, `first_name`, `last_name`, `contact_number`, `ic_number`, `date_of_birth`, `height`, `weight`, `activity_level`, `gender`, `nutritionist_gender_edit_used`, `allergies`, `medical_conditions`, `daily_calorie_intake`, `created_at`) VALUES
(1, 2, 1, 'Heng', 'Zhen Yee', '0108291622', '050622140653', '2005-06-22', 179.00, 99.00, 'Low', 'Male', 0, 'None', 'None', 2210, '2026-07-08 04:00:02'),
(2, 3, 1, 'John', 'Doe', '123456789', '987654321', '2026-07-01', 200.00, 99.00, 'High', 'Male', 0, 'no', 'no', 5000, '2026-07-08 04:52:19'),
(3, 9, 1, 'Test', 'Patient', '0123456789', '960713101234', '1996-07-13', 170.00, 65.00, 'Sedentary', 'Male', 0, 'none', 'none', 1881, '2026-07-13 14:10:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','nutritionist','patient') NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `profile_image`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'demo_nutritionist', 'nutritionist@caloreat.test', 'images/profile/nutritionist_1_1783927056.png', '$2y$10$LumZxdzdrqKY7WbtbI6T4.8IexxtjweeufmrgFz0m7Z6zsUO/OAnC', 'nutritionist', 'active', '2026-07-07 14:35:20'),
(2, 'jeffreyheng612', 'jeffreyheng612@gmail.com', NULL, '$2y$10$a4fGmJbpEzYiB8ds0naF1uryVOC.GmjiIc56DP0QBsflsNEuEzpGW', 'patient', 'active', '2026-07-08 04:00:02'),
(3, 'johndoe', 'johndo3@gmail.com', NULL, '$2y$10$VXmuh9lWDT.AhdcNdIj13uEb80R9O0pE3wB7Q6Rf61dZAoIk2/7WK', 'patient', 'active', '2026-07-08 04:52:19'),
(4, 'jeff_demo', 'j3ffh3ng@gmail.com', NULL, '$2y$10$Ma6ggrKgbUpIljVFjGeJR..t.T/7EXBbS8lJWCvWYpDWucSv90C9C', 'nutritionist', 'active', '2026-07-12 13:25:21'),
(8, 'donovan', 'jeffreyheng10@gmail.com', NULL, '$2y$10$T1aV7jVsUd1ITvCczg8BmuPGctfcHWYP/HdCpUJSmyejSEL4qEqKK', 'nutritionist', 'active', '2026-07-12 18:08:00'),
(9, 'testpatient', NULL, NULL, '$2y$10$MD004Xmk7SF/KlT5Yskbxu.EODly0PmxiHITCkRajmOFngMRcsipm', 'patient', 'active', '2026-07-13 14:10:43'),
(10, 'admin', 'admin@caloreat.test', NULL, '$2y$12$EclA4UQ8pHbBfT0DnZ8kkeM41ONhqBrAL45.ks9VsfPPGRsePHeOm', 'admin', 'active', '2026-07-19 14:22:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `custom_meals`
--
ALTER TABLE `custom_meals`
  ADD PRIMARY KEY (`custom_meal_id`),
  ADD UNIQUE KEY `food_id` (`food_id`),
  ADD KEY `idx_custom_meal_patient` (`patient_id`),
  ADD KEY `idx_custom_meal_nutritionist` (`nutritionist_id`),
  ADD KEY `idx_custom_meal_base_food` (`base_food_id`);

--
-- Indexes for table `food_items`
--
ALTER TABLE `food_items`
  ADD PRIMARY KEY (`food_id`),
  ADD KEY `fk_food_category` (`category_id`);

--
-- Indexes for table `meal_categories`
--
ALTER TABLE `meal_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `meal_recommendations`
--
ALTER TABLE `meal_recommendations`
  ADD PRIMARY KEY (`recommendation_id`),
  ADD KEY `idx_recommendation_patient` (`patient_id`),
  ADD KEY `idx_recommendation_food` (`food_id`),
  ADD KEY `idx_recommendation_nutritionist` (`nutritionist_id`);

--
-- Indexes for table `nutritionists`
--
ALTER TABLE `nutritionists`
  ADD PRIMARY KEY (`nutritionist_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `fk_patients_nutritionist` (`nutritionist_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_user_email` (`email`),
  ADD UNIQUE KEY `unique_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `custom_meals`
--
ALTER TABLE `custom_meals`
  MODIFY `custom_meal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `food_items`
--
ALTER TABLE `food_items`
  MODIFY `food_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `meal_categories`
--
ALTER TABLE `meal_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `meal_recommendations`
--
ALTER TABLE `meal_recommendations`
  MODIFY `recommendation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `nutritionists`
--
ALTER TABLE `nutritionists`
  MODIFY `nutritionist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `fk_admins_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `food_items`
--
ALTER TABLE `food_items`
  ADD CONSTRAINT `fk_food_category` FOREIGN KEY (`category_id`) REFERENCES `meal_categories` (`category_id`) ON UPDATE CASCADE;

--
-- Constraints for table `meal_recommendations`
--
ALTER TABLE `meal_recommendations`
  ADD CONSTRAINT `fk_recommendation_food` FOREIGN KEY (`food_id`) REFERENCES `food_items` (`food_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_recommendation_nutritionist` FOREIGN KEY (`nutritionist_id`) REFERENCES `nutritionists` (`nutritionist_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_recommendation_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE;

--
-- Constraints for table `nutritionists`
--
ALTER TABLE `nutritionists`
  ADD CONSTRAINT `fk_nutritionists_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `fk_patients_nutritionist` FOREIGN KEY (`nutritionist_id`) REFERENCES `nutritionists` (`nutritionist_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_patients_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
