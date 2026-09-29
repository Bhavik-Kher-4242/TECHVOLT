const WishlistContainer = document.querySelector(".all-products");


/* =========================
   Wishlist Button Actions
========================= */

WishlistContainer.addEventListener("click", function(event) {
    if (event.target.classList.contains("add-to-cart")) {

        event.stopPropagation();

        const productId = event.target.dataset.productId;

        const formData = new FormData();

        formData.append("product_id", productId);
        formData.append("quantity", 1);


        fetch("api/add-to-cart.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {

            console.log(data);

            updateCartQuantity();
            if (data === "success") {

                Swal.fire({
                    title: "Added to Cart!",
                    text: "Product has been added to your cart.",
                    icon: "success",
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
            else if(data == "update"){
                
                Swal.fire({
                title: "Already in Cart!",
                text: "This product is already in your cart.",
                theme: "dark",
                icon: "info"
            });
            }
            else if (data === "login_required") {

                Swal.fire({
                    title: "Login Required",
                    text: "Please login to add products to your cart.",
                    icon: "warning",
                    theme: "dark",
                    confirmButtonColor: "#0042FE"
                }).then(() => {

                    window.location.href = "login.php";

                });

            }

            else {

                Swal.fire({
                    title: "Something Went Wrong",
                    text: "Unable to add the product to your cart.",
                    icon: "error",
                    theme: "dark",
                    confirmButtonColor: "#0042FE"
                });

            }

        })
        .catch(error => {

            console.error(error);

            Swal.fire({
                title: "Error",
                text: "Something went wrong. Please try again.",
                icon: "error",
                confirmButtonColor: "#0042FE"
            });

        });

        return;
    }
    


    /* =========================
       REMOVE FROM WISHLIST
    ========================= */

    if (event.target.classList.contains("wishlist-remove-btn")) {

        event.stopPropagation();

        const wishlistId =
            event.target.dataset.wishlistId;


        Swal.fire({
            title: "Remove Product?",
            text: "Are you sure you want to remove this product from your wishlist?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Remove",
            cancelButtonText: "Keep",
            theme: "dark",
            confirmButtonColor: "#DC2626",
            cancelButtonColor: "#0042FE"

        }).then((result) => {

            if (result.isConfirmed) {
                
                
                const formData = new FormData();

                formData.append("wishlist_id", wishlistId);


                fetch("api/remove-from-wishlist.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    console.log(data);
                    if (data === "success") {
                        updateWishlistQuantity();
                        Swal.fire({
                            title: "Removed!",
                            text: "Product has been removed from your wishlist.",
                            icon: "success",
                            theme: "dark",
                            confirmButtonColor: "#0042FE"
                        }).then(() => {

                            window.location.reload();

                        });

                    }

                    else if (data === "not_found") {
                        updateWishlistQuantity();
                        
                        Swal.fire({
                            title: "Product Not Found",
                            text: "This wishlist item could not be found.",
                            icon: "error",
                            theme: "dark",
                            confirmButtonColor: "#0042FE"
                        });

                    }

                    else if (data === "login_required") {

                        window.location.href = "login.php";

                    }

                    else {

                        Swal.fire({
                            title: "Something Went Wrong",
                            text: "Unable to remove the product.",
                            icon: "error",
                            confirmButtonColor: "#0042FE"
                        });

                    }

                })
                .catch(error => {

                    console.error(error);

                    Swal.fire({
                        title: "Error",
                        text: "Something went wrong. Please try again.",
                        icon: "error",
                        confirmButtonColor: "#0042FE"
                    });

                });

            }

        });

        return;
    }
    const card = event.target.closest(".products");

    if (card) {
        window.location.href = `product-details.php?id=${card.id}`;
    }

});