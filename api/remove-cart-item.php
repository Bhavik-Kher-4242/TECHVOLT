<?php

    session_start();
    if (!isset($_SESSION["user_id"])) {
        echo "login_required";
        exit;
    }

    require_once "../includes/database.php";

    $user_id = (int) $_SESSION["user_id"];
    $cart_item_id = (int) ($_POST["cart_item_id"] ?? 0);

    if ($cart_item_id <= 0) {
        echo "invalid_cart_item";
        exit;
    }

    /*
    * Remove cart item only if the cart belongs
    * to the currently logged-in user.
    */
    $sql = "DELETE ci
            FROM cart_items ci
            INNER JOIN carts c
                ON ci.cart_id = c.cart_id
            WHERE ci.cart_item_id = ?
            AND c.user_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        echo "error";
        exit;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $cart_item_id,
        $user_id
    );

    if (mysqli_stmt_execute($stmt)) {

        if (mysqli_stmt_affected_rows($stmt) === 1) {
            echo "removed";
        } else {
            echo "invalid_cart_item";
        }

    } else {
        echo "error";
    }

    mysqli_stmt_close($stmt);
?>