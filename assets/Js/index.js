// ========================================
// Product Card Click
// ========================================

const productCards = document.querySelectorAll(".products");

productCards.forEach(function (product) {

    product.addEventListener("click", function () {

        const productId = product.dataset.productId;

        window.location.href = "product-details.php?id=" + productId;

    });

});


// ========================================
// Add To Cart
// ========================================

const addToCartButtons = document.querySelectorAll(".add-to-cart");

addToCartButtons.forEach(function (button) {

    button.addEventListener("click", function (event) {

        // Prevent product card click
        event.stopPropagation();

        const productId = button.dataset.productId;

        const formData = new FormData();

        formData.append("product_id", productId);
        formData.append("quantity", 1);

        fetch("api/add-to-cart.php", {
            method: "POST",
            body: formData
        })
        .then(function (response) {
            return response.text();
        })
        .then(function (data) {

            console.log(data);

            if (data === "success") {

                updateCartQuantity();

                Swal.fire({
                    title: "Added to Cart!",
                    text: "Product has been added to your cart.",
                    icon: "success",
                    theme: "dark",
                    confirmButtonColor: "#0042FE"
                });

            }

            else if (data === "update") {

                updateCartQuantity();

                Swal.fire({
                    title: "Already in Cart!",
                    text: "This product is already in your cart.",
                    icon: "info",
                    theme: "dark",
                    confirmButtonColor: "#0042FE"
                });

            }

            else if (data === "out_of_stock") {

                Swal.fire({
                    title: "Out of Stock!",
                    text: "Sorry, this product is currently out of stock.",
                    icon: "warning",
                    theme: "dark",
                    confirmButtonColor: "#0042FE"
                });

            }

            else if (data === "product_not_found") {

                Swal.fire({
                    title: "Product Not Found!",
                    text: "This product is no longer available.",
                    icon: "error",
                    theme: "dark",
                    confirmButtonColor: "#0042FE"
                });

            }

            else if (data === "login_required") {

                Swal.fire({
                    title: "Login Required!",
                    text: "Please login to add products to your cart.",
                    icon: "warning",
                    theme: "dark",
                    confirmButtonColor: "#0042FE"
                }).then(function () {

                    window.location.href = "login.php";

                });

            }

            else {

                Swal.fire({
                    title: "Something Went Wrong!",
                    text: "Unable to add this product to your cart.",
                    icon: "error",
                    theme: "dark",
                    confirmButtonColor: "#0042FE"
                });

            }

        })
        .catch(function (error) {

            console.error(error);

            Swal.fire({
                title: "Error!",
                text: "Something went wrong. Please try again.",
                icon: "error",
                theme: "dark",
                confirmButtonColor: "#0042FE"
            });

        });

    });

});


// ========================================
// Add To Wishlist
// ========================================

const wishlistButtons = document.querySelectorAll(".add-to-wishlist");

wishlistButtons.forEach(function (button) {

    button.addEventListener("click", function (event) {

        // Prevent product card click
        event.stopPropagation();

        const productId = button.dataset.productId;

        const formData = new FormData();

        formData.append("product_id", productId);

        fetch("api/add-to-wishlist.php", {
            method: "POST",
            body: formData
        })
        .then(function (response) {
            return response.text();
        })
        .then(function (data) {

            console.log(data);

            if (data === "success") {

                updateWishlistQuantity();

                Swal.fire({
                    title: "Added to Wishlist!",
                    text: "Product has been added to your wishlist.",
                    icon: "success",
                    theme: "dark",
                    confirmButtonColor: "#0042FE"
                });

            }

            else if (data === "already_exists") {

                Swal.fire({
                    title: "Already in Wishlist!",
                    text: "This product is already in your wishlist.",
                    icon: "info",
                    theme: "dark",
                    confirmButtonColor: "#0042FE"
                });

            }

            else if (data === "login_required") {

                Swal.fire({
                    title: "Login Required!",
                    text: "Please login to add products to your wishlist.",
                    icon: "warning",
                    theme: "dark",
                    confirmButtonColor: "#0042FE"
                }).then(function () {

                    window.location.href = "login.php";

                });

            }

            else {

                Swal.fire({
                    title: "Something Went Wrong!",
                    text: "Unable to add this product to your wishlist.",
                    icon: "error",
                    theme: "dark",
                    confirmButtonColor: "#0042FE"
                });

            }

        })
        .catch(function (error) {

            console.error(error);

            Swal.fire({
                title: "Error!",
                text: "Something went wrong. Please try again.",
                icon: "error",
                theme: "dark",
                confirmButtonColor: "#0042FE"
            });

        });

    });

});