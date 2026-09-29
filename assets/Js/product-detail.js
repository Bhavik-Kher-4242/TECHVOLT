// For Product Detail Image Display

const mainImage = document.querySelector(".main-image");
const thumbnails = document.querySelectorAll(".thumbnail-container img");

thumbnails.forEach(function(thumbnail) {

    thumbnail.addEventListener("click", function() {

        mainImage.src = thumbnail.src;

        thumbnails.forEach(function(image) {
            image.classList.remove("active-image-product");
        });

        thumbnail.classList.add("active-image-product");
    });

});
let minusBtn = document.querySelector(".minus-btn");
let quntity = document.querySelector(".display-Qun");
let currentQuantity = Number(quntity.textContent);
minusBtn.addEventListener("click",function(){
    if(currentQuantity > 1){
        currentQuantity--;
    }
    quntity.innerHTML = currentQuantity;
});
let plusBtn = document.querySelector(".plus-btn");
plusBtn.addEventListener("click", function(){
    currentQuantity++;
    quntity.innerHTML = currentQuantity;
});
let addToCartBtn = document.querySelector(".add-to-cart");
addToCartBtn.addEventListener("click", function() {
    let product_id = addToCartBtn.dataset.productId;
    fetch("api/add-to-cart.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "product_id=" + product_id + "&quantity=" + currentQuantity
    }).then(function(response) {
        return response.text();
    })
    .then(function(data) {
        console.log(data);
        if (data == "success") {
            updateCartQuantity();
            Swal.fire({
            title: "Success!",
            text: "Product added to cart",
            theme: "dark",
            icon: "success"
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
            updateCartQuantity();
                Swal.fire({
                title: "Already in Cart!",
                text: "This product is already in your cart.",
                theme: "dark",
                icon: "info"
            });
        }
    });
});
const WishlistButton = document.querySelector(".add-to-wishlist");

WishlistButton.addEventListener("click", function(event) {

    event.preventDefault();
    event.stopPropagation();

    const productId = WishlistButton.dataset.productId;

    console.log("Product ID:", productId);

    const formData = new FormData();

    formData.append("product_id", productId);

    fetch("api/add-to-wishlist.php", {
        method: "POST",
        body: formData
    })
    .then(function(response) {
        return response.text();
    })
    .then(function(data) {

        console.log("Wishlist API Response:", data);

        if (data.trim() === "success") {
            updateWishlistQuantity();
            Swal.fire({
                title: "Added to Wishlist!",
                text: "Product has been added to your wishlist.",
                icon: "success",
                theme: "dark"
            });

        }
        
        else if (data.trim() === "already_exists") {
            updateWishlistQuantity();
            Swal.fire({
                title: "Already in Wishlist",
                text: "This product is already in your wishlist.",
                icon: "info",
                theme: "dark"
            });

        }
        else if (data.trim() === "login_required") {

            Swal.fire({
                title: "Login Required",
                text: "Please login to add products to your wishlist.",
                icon: "warning",
                theme: "dark"
            }).then(function() {

                window.location.href = "login.php";

            });

        }
        else {

            Swal.fire({
                title: "Something Went Wrong",
                text: "Unable to add the product to your wishlist.",
                icon: "error",
                theme: "dark"
            });

        }

    })
    .catch(function(error) {

        console.error("Wishlist Error:", error);

    });

});