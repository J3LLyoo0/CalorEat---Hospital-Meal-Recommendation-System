<?php
require_once "session_check.php";
require_once "db_connect.php";

header("Content-Type: application/json; charset=UTF-8");

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);

function sendJson($success, $message, $data = [])
{
    echo json_encode(
        [
            "success" => $success,
            "message" => $message,
            "recommendations" => $data
        ]
    );

    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);

    sendJson(
        false,
        "Invalid request method."
    );
}

$currentRole =
    $_SESSION["role"] ?? "";

$currentUserId =
    (int)($_SESSION["user_id"] ?? 0);

$patientId =
    (int)($_GET["patient_id"] ?? 0);

if ($patientId <= 0) {
    http_response_code(400);

    sendJson(
        false,
        "Invalid patient ID."
    );
}

try {
    /*
     * Nutritionists may only view recommendations
     * belonging to their assigned patients.
     *
     * Admins may view any patient.
     */
    if ($currentRole === "nutritionist") {
        $accessStatement = mysqli_prepare(
            $conn,
            "
                SELECT p.patient_id
                FROM patients p
                INNER JOIN nutritionists n
                    ON p.nutritionist_id = n.nutritionist_id
                WHERE p.patient_id = ?
                AND n.user_id = ?
                LIMIT 1
            "
        );

        mysqli_stmt_bind_param(
            $accessStatement,
            "ii",
            $patientId,
            $currentUserId
        );

        mysqli_stmt_execute(
            $accessStatement
        );

        $accessResult =
            mysqli_stmt_get_result(
                $accessStatement
            );

        if (!mysqli_fetch_assoc($accessResult)) {
            http_response_code(403);

            sendJson(
                false,
                "You cannot view recommendations for this patient."
            );
        }

    } elseif ($currentRole === "admin") {
        $accessStatement = mysqli_prepare(
            $conn,
            "
                SELECT patient_id
                FROM patients
                WHERE patient_id = ?
                LIMIT 1
            "
        );

        mysqli_stmt_bind_param(
            $accessStatement,
            "i",
            $patientId
        );

        mysqli_stmt_execute(
            $accessStatement
        );

        $accessResult =
            mysqli_stmt_get_result(
                $accessStatement
            );

        if (!mysqli_fetch_assoc($accessResult)) {
            http_response_code(404);

            sendJson(
                false,
                "Patient could not be found."
            );
        }

    } else {
        http_response_code(403);

        sendJson(
            false,
            "You do not have permission to view these recommendations."
        );
    }

    $recommendationStatement =
        mysqli_prepare(
            $conn,
            "
                SELECT
                    mr.recommendation_id,
                    mr.meal_time,
                    mr.recommendation_type,
                    mr.target_calories,
                    mr.meal_calories,
                    mr.evaluation_result,
                    mr.recommendation_notes,
                    mr.recommended_at,

                    fi.food_id,
                    fi.food_name,
                    fi.image,

                    mc.category_name

                FROM meal_recommendations mr

                INNER JOIN food_items fi
                    ON mr.food_id = fi.food_id

                LEFT JOIN meal_categories mc
                    ON fi.category_id = mc.category_id

                WHERE mr.patient_id = ?
                  AND fi.status = 'active'

                ORDER BY
                    mr.recommended_at DESC,
                    mr.recommendation_id DESC
            "
        );

    mysqli_stmt_bind_param(
        $recommendationStatement,
        "i",
        $patientId
    );

    mysqli_stmt_execute(
        $recommendationStatement
    );

    $recommendationResult =
        mysqli_stmt_get_result(
            $recommendationStatement
        );

    $recommendations = [];

    while (
        $row =
        mysqli_fetch_assoc(
            $recommendationResult
        )
    ) {
        $imagePath =
            trim((string)($row["image"] ?? ""));

        if (
            $imagePath !== "" &&
            strpos($imagePath, "/") === false
        ) {
            $imagePath =
                "images/" . $imagePath;
        }

        $recommendations[] = [
            "recommendation_id" =>
                (int)$row["recommendation_id"],

            "food_id" =>
                (int)$row["food_id"],

            "food_name" =>
                $row["food_name"],

            "image" =>
                $imagePath,

            "category_name" =>
                $row["category_name"] ??
                "Uncategorized",

            "meal_time" =>
                $row["meal_time"],

            "recommendation_type" =>
                $row["recommendation_type"],

            "target_calories" =>
                (int)$row["target_calories"],

            "meal_calories" =>
                (int)$row["meal_calories"],

            "evaluation_result" =>
                $row["evaluation_result"],

            "recommendation_notes" =>
                $row["recommendation_notes"],

            "recommended_at" =>
                $row["recommended_at"]
        ];
    }

    sendJson(
        true,
        "Recommendations loaded.",
        $recommendations
    );

} catch (mysqli_sql_exception $error) {
    error_log(
        "Patient recommendation loading error: " .
        $error->getMessage()
    );

    http_response_code(500);

    sendJson(
        false,
        "Recommended meals could not be loaded."
    );
}