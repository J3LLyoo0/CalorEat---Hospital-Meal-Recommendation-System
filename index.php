<?php
require_once "session_check.php";
require_once "db_connect.php";

if (($_SESSION["role"] ?? "") === "admin") {
    header("Location: admin_dashboard.php");
    exit();
}

if (!function_exists('h')) {
    function h($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$patients = [];
$currentUserId = $_SESSION["user_id"] ?? 0;
$currentRole = $_SESSION["role"] ?? "";
$defaultProfileImage = "images/user.png";
$accountProfileImage = $defaultProfileImage;

$stmt = mysqli_prepare($conn, "SELECT profile_image FROM users WHERE user_id = ? LIMIT 1");

mysqli_stmt_bind_param($stmt, "i", $currentUserId);
mysqli_stmt_execute($stmt);

$accountImageResult = mysqli_stmt_get_result($stmt);
$accountImageUser = mysqli_fetch_assoc($accountImageResult);

$storedProfileImage = trim(
    (string) ($accountImageUser["profile_image"] ?? "")
);

if ($storedProfileImage !== "" && is_file(__DIR__ . "/" . $storedProfileImage))
    {
        $accountProfileImage = $storedProfileImage;
    }

$accountProfileImageUrl = $accountProfileImage;
$accountProfileImageFile = __DIR__ . "/" . $accountProfileImage;

if (is_file($accountProfileImageFile)) {
    $accountProfileImageUrl .= "?v=" . filemtime($accountProfileImageFile);
}

/* =========================
   LOAD PATIENTS FROM DATABASE
========================= */

if ($currentRole === "nutritionist") {
    $nutritionistId = null;

    $stmt = mysqli_prepare($conn, "SELECT nutritionist_id FROM nutritionists WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $currentUserId);
    mysqli_stmt_execute($stmt);
    $nutritionistResult = mysqli_stmt_get_result($stmt);

    if ($nutritionist = mysqli_fetch_assoc($nutritionistResult)) {
        $nutritionistId = $nutritionist["nutritionist_id"];
    }

    if ($nutritionistId !== null) {
        $stmt = mysqli_prepare($conn, "
            SELECT 
                p.*,
                u.email
            FROM patients p
            INNER JOIN users u ON p.user_id = u.user_id
            WHERE p.nutritionist_id = ?
            ORDER BY p.patient_id DESC
        ");

        mysqli_stmt_bind_param($stmt, "i", $nutritionistId);
        mysqli_stmt_execute($stmt);
        $patientResult = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($patientResult)) {
            $patients[] = $row;
        }
    }
} elseif ($currentRole === "patient") {
    $stmt = mysqli_prepare($conn, "
        SELECT 
            p.*,
            u.email
        FROM patients p
        INNER JOIN users u ON p.user_id = u.user_id
        WHERE p.user_id = ?
        LIMIT 1
    ");

    mysqli_stmt_bind_param($stmt, "i", $currentUserId);
    mysqli_stmt_execute($stmt);
    $patientResult = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($patientResult)) {
        $patients[] = $row;
    }
} else {
    $patientSql = "
        SELECT 
            p.*,
            u.email
        FROM patients p
        INNER JOIN users u ON p.user_id = u.user_id
        ORDER BY p.patient_id DESC
    ";

    $patientResult = mysqli_query($conn, $patientSql);

    if ($patientResult) {
        while ($row = mysqli_fetch_assoc($patientResult)) {
            $patients[] = $row;
        }
    }
}

/* =========================
   LOAD MEAL CATEGORIES + FOOD ITEMS FROM DATABASE
========================= */

$mealCategories = [];

$mealSql = "
    SELECT 
        mc.category_id,
        mc.category_name,
        fi.food_id,
        fi.food_name,
        fi.ingredients,
        fi.portion_size,
        fi.calories,
        fi.protein,
        fi.carbohydrates,
        fi.fat,
        fi.sodium,
        fi.image
    FROM meal_categories mc
    INNER JOIN food_items fi ON mc.category_id = fi.category_id
    LEFT JOIN custom_meals cm ON fi.food_id = cm.food_id
    WHERE cm.custom_meal_id IS NULL
      AND fi.status = 'active'
    ORDER BY mc.category_id ASC, fi.food_id ASC
";

$mealResult = mysqli_query($conn, $mealSql);

if ($mealResult) {
    while ($row = mysqli_fetch_assoc($mealResult)) {
        $categoryName = $row["category_name"];

        $imagePath = $row["image"];

        if ($imagePath !== "" && strpos($imagePath, "/") === false) {
            $imagePath = "images/" . $imagePath;
        }

        $mealCategories[$categoryName][] = [
            "food_id" => (int)$row["food_id"],
            "name" => $row["food_name"],
            "calories" => $row["calories"] . " kcal",
            "kcalValue" => (int)$row["calories"],
            "image" => $imagePath,
            "ingredients" => $row["ingredients"],
            "description" =>
                "Portion: " . ($row["portion_size"] ?: "Not stated") .
                ". Protein: " . ($row["protein"] ?: "0") . "g" .
                ", Carbohydrates: " . ($row["carbohydrates"] ?: "0") . "g" .
                ", Fat: " . ($row["fat"] ?: "0") . "g" .
                ", Sodium: " . ($row["sodium"] ?: "0") . "mg."
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CalorEat Main Page</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/register-patient-modal.css">
<link rel="stylesheet" href="assets/css/patient-details-modal.css?v=4">
<link rel="stylesheet" href="assets/css/meal-details-modal.css?v=3">
<link rel="stylesheet" href="assets/css/meal-customization-modal.css?v=<?php echo filemtime(__DIR__ . "/assets/css/meal-customization-modal.css"); ?>">
</head>

<body>

<div class="app-frame">

    <header class="top-divider">
        <div class="brand">
            <div class="logo-box">
                <img src="images/logo.png" alt="CalorEat Logo">
            </div>
            <h1>CalorEat</h1>
        </div>

        <button class="account-button" id="accountBtn" type="button" title="Account" onclick="window.location.href='account.php'">
            <img src="<?php echo h($accountProfileImageUrl); ?>" alt="Account Profile Image">
        </button>
    </header>

    <main class="main-area">

        <section class="section-box patient-section">
            <h2>Patients</h2>

            <div id="patientPanel" class="patient-panel <?php echo count($patients) === 0 ? 'patient-empty' : 'patient-filled'; ?>">
                <?php if (count($patients) === 0): ?>
                    <?php if ($_SESSION["role"] === "nutritionist"): ?>
                        <button class="register-main-btn js-register-btn" type="button">
                            Register New Patient
                        </button>
                    <?php else: ?>
                        <p>No patient profile found.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="patient-row">
                        <?php foreach ($patients as $patient): ?>
                            <?php
                                $firstName = trim((string)($patient["first_name"] ?? ""));
                                $lastName = trim((string)($patient["last_name"] ?? ""));
                                $fullName = trim($firstName . " " . $lastName);

                                if ($fullName === "") {
                                    $fullName = "Unamed Patient";
                                }

                                $initials = strtoupper(substr($firstName, 0, 1)) . strtoupper(substr($lastName, 0 ,1));

                                if ($initials === "") {
                                    $initials = "P";
                                }

                                $medicalCondition = trim((string)($patient["medical_conditions"] ?? ""));

                                if ($medicalCondition === "") {
                                    $medicalCondition = "None Recorded";
                                }

                                $allergies = trim((string)($patient["allergies"] ?? ""));

                                if ($allergies === "") {
                                    $allergies = "None Recorded";
                                }

                                $email = trim((string)($patient["email"] ?? ""));

                                if ($email === "") {
                                    $email = "Not Provided";
                                }

                                $dailyCalories = (int)($patient["daily_calorie_intake"] ?? 0);

                                $dailyCaloriesDisplay = $dailyCalories > 0 ? number_format($dailyCalories) . "kcal/day" : "Not calculated";
                            ?>

                            <button type="button" class="patient-card" 
                                data-patient-id="<?php echo h($patient["patient_id"] ?? ""); ?>"
                                data-name="<?php echo h($fullName); ?>"
                                data-email="<?php echo h($email); ?>"
                                data-contact="<?php echo h($patient["contact_number"] ?? ""); ?>"
                                data-ic-number="<?php echo h($patient["ic_number"] ?? ""); ?>"
                                data-date-of-birth="<?php echo h($patient["date_of_birth"] ?? ""); ?>"
                                data-gender="<?php echo h($patient["gender"] ?? ""); ?>"
                                data-gender-edit-used="<?php echo h($patient["nutritionist_gender_edit_used"] ?? 0);?>"
                                data-height="<?php echo h($patient["height"] ?? ""); ?>"
                                data-weight="<?php echo h($patient["weight"] ?? ""); ?>"
                                data-activity-level="<?php echo h($patient["activity_level"] ?? ""); ?>"
                                data-condition="<?php echo h($medicalCondition); ?>"
                                data-allergy="<?php echo h($allergies); ?>"
                                data-calories="<?php echo h($dailyCalories); ?>"
                            >
                                <div class="patient-card-header">
                                    <span class="patient-initials"><?php echo h($initials); ?></span>

                                    <div class="patient-card-name">
                                        <h3><?php echo h($fullName); ?></h3>
                                        <span>View Details →</span>
                                    </div>
                                </div>

                                <div class="patient-card-summary">
                                    <div class="patient-summary-item">
                                        <span>Daily Intake</span>

                                        <strong><?php echo h($dailyCaloriesDisplay); ?></strong>
                                    </div>

                                    <div class="patient-summary-item">
                                        <span>Medical Condition</span>

                                        <strong><?php echo h($medicalCondition); ?></strong>
                                    </div>
                                </div>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($_SESSION["role"] === "nutritionist"): ?>
                        <button class="register-corner-btn js-register-btn" type="button">
                            Register New Patient
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>

        <?php foreach ($mealCategories as $categoryName => $meals) { ?>
            <section class="section-box meal-section">
                <h2><?php echo h($categoryName); ?></h2>

                <div class="meal-row">
                    <?php foreach ($meals as $meal) { ?>
                        <button
                            type="button" class="meal-card"
                            
                            data-food-id="<?php echo h($meal["food_id"]); ?>"
                            data-category="<?php echo h($categoryName); ?>"
                            data-name="<?php echo h($meal['name']); ?>"
                            data-calories="<?php echo h($meal['calories']); ?>"
                            data-kcal="<?php echo h($meal['kcalValue']); ?>"
                            data-image="<?php echo h($meal['image']); ?>"
                            data-ingredients="<?php echo h($meal['ingredients']); ?>"
                            data-description="<?php echo h($meal['description']); ?>"
                        >
                            <div class="meal-image-box">
                                <img
                                    src="<?php echo h($meal['image']); ?>"
                                    alt="<?php echo h($meal['name']); ?>"
                                    onerror="this.style.display='none'; this.parentElement.classList.add('missing-img');"
                                >
                            </div>

                            <div class="meal-info">
                                <h3><?php echo h($meal['name']); ?></h3>
                                <p><?php echo h($meal['calories']); ?></p>
                            </div>
                        </button>
                    <?php } ?>
                </div>
            </section>
        <?php } ?>

    </main>

    <button class="floating-customize" id="customizeOpenBtn" type="button">
        +
        <span class="floating-tooltip">Patient Meal Customization</span>
    </button>

</div>

<!-- Meal Details Modal -->
<?php require __DIR__ . "/components/meals_details_modal.php"?>

<!-- Modal Insert Register Patient and Patient Details -->
<?php if ($currentRole === "nutritionist"): ?>
    <?php require  __DIR__ . "/components/register_patient_modal.php"; ?>
    <?php require __DIR__ . "/components/patient_details_modal.php"; ?>
<?php endif; ?>

<!-- Meal Customization Modal -->
<?php if ($currentRole === "nutritionist"): ?>
    <?php require __DIR__ . "/components/meal_customization_modal.php"; ?>
<?php endif; ?>

<script>
    window.CALOREAT_MEALS = <?php echo json_encode($mealCategories, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    window.CALOREAT_PATIENTS = <?php echo json_encode($patients, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
</script>
<script src="assets/js/main.js?v=<?php echo filemtime(__DIR__ . "/assets/js/main.js"); ?>"></script>
<script src="assets/js/register-patient-modal.js"></script>
<script src="assets/js/patient-details-modal.js?v=<?php echo filemtime(__DIR__ . "/assets/js/patient-details-modal.js"); ?>"></script>
<script src="assets/js/meal-details-modal.js?v=<?php echo filemtime(__DIR__ . "/assets/js/meal-details-modal.js"); ?>"></script>
<script src="assets/js/meal-customization-modal.js?v=<?php echo filemtime(__DIR__ . "/assets/js/meal-customization-modal.js"); ?>"></script>

</body>
</html>