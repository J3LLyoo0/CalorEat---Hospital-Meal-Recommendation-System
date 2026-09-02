<?php
require_once "session_check.php";
require_once "db_connect.php";

function h($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

$mealCategories = [
    "General Meals" => [
        [
            "name" => "Chicken Lemak Cili Api Pasta",
            "calories" => "500 - 700 kcal",
            "kcalValue" => 600,
            "image" => "images/food/chicken-lemak-cili-api-pasta.jpg",
            "ingredients" => "Pasta, chicken, coconut milk, chilli, herbs and vegetables.",
            "description" => "A general meal option with higher calories, suitable for patients without strict restrictions."
        ],
        [
            "name" => "Beringin Pandan Rice",
            "calories" => "250 - 300 kcal",
            "kcalValue" => 280,
            "image" => "images/food/beringin-pandan-rice.jpg",
            "ingredients" => "Pandan rice, vegetables, light sauce and seasoning.",
            "description" => "A lighter rice-based meal suitable for moderate calorie intake."
        ],
        [
            "name" => "Nasi Lemak Istana",
            "calories" => "350 - 450 kcal",
            "kcalValue" => 400,
            "image" => "images/food/nasi-lemak-istana.jpg",
            "ingredients" => "Rice, egg, cucumber, sambal and chicken.",
            "description" => "A Malaysian-style general meal with moderate to high calorie content."
        ],
    ],

    "Normal Meals" => [
        [
            "name" => "White Rice with Ginger Soy Chicken and Stir-Fried Sawi",
            "calories" => "450 - 550 kcal",
            "kcalValue" => 500,
            "image" => "images/food/WRwGSCaSTS.jpg",
            "ingredients" => "White rice, chicken, ginger, soy sauce and vegetables.",
            "description" => "A normal solid meal suitable for patients with standard meal requirements."
        ],
        [
            "name" => "Brown Rice with Ayam Bakar Kunyit",
            "calories" => "400 - 500 kcal",
            "kcalValue" => 450,
            "image" => "images/food/ayam-bakar-kunyit.jpg",
            "ingredients" => "Brown rice, grilled turmeric chicken and vegetables.",
            "description" => "A balanced meal with brown rice and grilled chicken."
        ],
        [
            "name" => "Bihun Soup with Scallions",
            "calories" => "300 - 400 kcal",
            "kcalValue" => 350,
            "image" => "images/food/bihun-soup.jpg",
            "ingredients" => "Bihun, soup broth, scallions and vegetables.",
            "description" => "A lighter noodle soup meal with moderate calories."
        ],
    ],

    "Semi-Liquid Meals" => [
        [
            "name" => "Chicken Porridge",
            "calories" => "250 - 350 kcal",
            "kcalValue" => 300,
            "image" => "images/food/chicken-porridge.jpg",
            "ingredients" => "Rice porridge, shredded chicken and light seasoning.",
            "description" => "A soft-texture meal for patients who require semi-liquid food."
        ],
        [
            "name" => "Pumpkin Soup",
            "calories" => "180 - 250 kcal",
            "kcalValue" => 220,
            "image" => "images/food/pumpkin-soup.jpg",
            "ingredients" => "Pumpkin, milk, light seasoning and vegetables.",
            "description" => "A smooth soup option for patients who need lighter meals."
        ],
        [
            "name" => "Oat Porridge",
            "calories" => "200 - 300 kcal",
            "kcalValue" => 250,
            "image" => "images/food/oat-porridge.jpg",
            "ingredients" => "Oats, milk and soft fruit topping.",
            "description" => "A simple semi-liquid meal with controlled calorie intake."
        ],
    ],

    "Low Fat Meals" => [
        [
            "name" => "Steamed Fish with Rice",
            "calories" => "300 - 420 kcal",
            "kcalValue" => 360,
            "image" => "images/food/steamed-fish-rice.jpg",
            "ingredients" => "Steamed fish, rice and boiled vegetables.",
            "description" => "A lower-fat meal suitable for patients who need healthier meal control."
        ],
        [
            "name" => "Vegetable Tofu Soup",
            "calories" => "200 - 300 kcal",
            "kcalValue" => 250,
            "image" => "images/food/tofu-soup.jpg",
            "ingredients" => "Tofu, vegetables and clear soup.",
            "description" => "A light low-fat meal option with vegetables and tofu."
        ],
        [
            "name" => "Grilled Chicken Salad",
            "calories" => "280 - 380 kcal",
            "kcalValue" => 330,
            "image" => "images/food/grilled-chicken-salad.jpg",
            "ingredients" => "Grilled chicken, lettuce, cucumber, tomato and light dressing.",
            "description" => "A low-fat meal with lean protein and vegetables."
        ],
    ],
];

$patients = [];

if ($_SESSION["role"] === "nutritionist" && isset($_SESSION["nutritionist_id"])) {
    $nutritionistId = (int)$_SESSION["nutritionist_id"];

    $patientSql = "SELECT p.patient_id, p.first_name, p.last_name, u.email, p.contact_number,
                          p.gender, p.height, p.weight, p.activity_level,
                          p.medical_conditions, p.allergies, p.daily_calorie_intake
                   FROM patients p
                   INNER JOIN users u ON p.user_id = u.user_id
                   WHERE p.nutritionist_id = ?
                   ORDER BY p.patient_id DESC";

    $patientStmt = mysqli_prepare($conn, $patientSql);
    mysqli_stmt_bind_param($patientStmt, "i", $nutritionistId);
    mysqli_stmt_execute($patientStmt);
    $patientResult = mysqli_stmt_get_result($patientStmt);

    while ($row = mysqli_fetch_assoc($patientResult)) {
        $patients[] = $row;
    }
} elseif ($_SESSION["role"] === "patient" && isset($_SESSION["patient_id"])) {
    $patientId = (int)$_SESSION["patient_id"];

    $patientSql = "SELECT p.patient_id, p.first_name, p.last_name, u.email, p.contact_number,
                          p.gender, p.height, p.weight, p.activity_level,
                          p.medical_conditions, p.allergies, p.daily_calorie_intake
                   FROM patients p
                   INNER JOIN users u ON p.user_id = u.user_id
                   WHERE p.patient_id = ?
                   LIMIT 1";

    $patientStmt = mysqli_prepare($conn, $patientSql);
    mysqli_stmt_bind_param($patientStmt, "i", $patientId);
    mysqli_stmt_execute($patientStmt);
    $patientResult = mysqli_stmt_get_result($patientStmt);

    if ($row = mysqli_fetch_assoc($patientResult)) {
        $patients[] = $row;
    }
}

?>