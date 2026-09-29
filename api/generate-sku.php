<?php

session_start();

require_once "../includes/database.php";

header("Content-Type: application/json");

if (!isset($_SESSION["admin_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access."
    ]);
    exit;
}

$category_id = $_GET["category_id"] ?? "";

if (empty($category_id)) {
    echo json_encode([
        "success" => false,
        "message" => "Category ID is required."
    ]);
    exit;
}


/* ================= CATEGORY CODE ================= */

$categorySql = "SELECT category_name
                FROM categories
                WHERE category_id = ? AND status = 'active'";

$categoryStmt = mysqli_prepare($conn, $categorySql);

if (!$categoryStmt) {
    echo json_encode([
        "success" => false,
        "message" => "Category query failed."
    ]);
    exit;
}

mysqli_stmt_bind_param(
    $categoryStmt,
    "i",
    $category_id
);

mysqli_stmt_execute($categoryStmt);

$categoryResult = mysqli_stmt_get_result($categoryStmt);

$category = mysqli_fetch_assoc($categoryResult);

if (!$category) {
    echo json_encode([
        "success" => false,
        "message" => "Category not found."
    ]);
    exit;
}


/* ================= CATEGORY CODE MAP ================= */

/* ================= CATEGORY CODE ================= */

$categoryName = $category["category_name"];

/*
 * Existing categories keep their original SKU codes.
 * New categories automatically get the first 3 letters.
 */
$categoryCodes = [
    "Accessories" => "ACC",
    "Displays" => "DIS",
    "ICs" => "IC",
    "Microcontrollers" => "MCU",
    "Motors & Actuators" => "MOT",
    "Passive Components" => "PAS",
    "Power Modules" => "PWR",
    "Single Board Computers" => "SBC",
    "Sensors" => "SEN"
];

if (isset($categoryCodes[$categoryName])) {

    // Existing category
    $categoryCode = $categoryCodes[$categoryName];

} else {

    // New category → take first 3 alphabetic characters
    $lettersOnly = preg_replace("/[^a-zA-Z]/", "", $categoryName);

    if (strlen($lettersOnly) < 3) {
        echo json_encode([
            "success" => false,
            "message" => "Category name must contain at least 3 letters for SKU generation."
        ]);
        exit;
    }

    $categoryCode = strtoupper(substr($lettersOnly, 0, 3));

    /*
     * Check whether this automatically generated code
     * is already being used by another category's SKU.
     */
    $codeSql = "
        SELECT sku
        FROM products
        WHERE sku LIKE ?
        LIMIT 1
    ";

    $codeStmt = mysqli_prepare($conn, $codeSql);

    if (!$codeStmt) {
        echo json_encode([
            "success" => false,
            "message" => "SKU code check failed."
        ]);
        exit;
    }

    $baseCode = $categoryCode;
    $codeNumber = 1;

    while (true) {

        $testCode = ($codeNumber === 1)
            ? $baseCode
            : $baseCode . $codeNumber;

        $testPrefix = "TV-" . $testCode . "-";
        $testPattern = $testPrefix . "%";

        mysqli_stmt_bind_param(
            $codeStmt,
            "s",
            $testPattern
        );

        mysqli_stmt_execute($codeStmt);

        $testResult = mysqli_stmt_get_result($codeStmt);

        if (mysqli_num_rows($testResult) === 0) {
            $categoryCode = $testCode;
            break;
        }

        $codeNumber++;
    }

    mysqli_stmt_close($codeStmt);
}


/* ================= FIND NEXT NUMBER ================= */

$skuPrefix = "TV-" . $categoryCode . "-";

$skuSql = "SELECT sku
           FROM products
           WHERE sku LIKE ?
           ORDER BY sku DESC";

$skuStmt = mysqli_prepare($conn, $skuSql);

if (!$skuStmt) {
    echo json_encode([
        "success" => false,
        "message" => "SKU query failed."
    ]);
    exit;
}

$skuPattern = $skuPrefix . "%";

mysqli_stmt_bind_param(
    $skuStmt,
    "s",
    $skuPattern
);

mysqli_stmt_execute($skuStmt);

$skuResult = mysqli_stmt_get_result($skuStmt);

$highestNumber = 0;

while ($row = mysqli_fetch_assoc($skuResult)) {

    $sku = $row["sku"];

    $parts = explode("-", $sku);

    if (count($parts) === 3 && is_numeric($parts[2])) {

        $number = (int)$parts[2];

        if ($number > $highestNumber) {
            $highestNumber = $number;
        }
    }
}


/* ================= GENERATE NEW SKU ================= */

$nextNumber = $highestNumber + 1;

$skuNumber = str_pad(
    $nextNumber,
    3,
    "0",
    STR_PAD_LEFT
);

$newSku = $skuPrefix . $skuNumber;


/* ================= RESPONSE ================= */

echo json_encode([
    "success" => true,
    "sku" => $newSku
]);

?>