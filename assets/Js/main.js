function updateCartQuantity() {
    fetch("api/get-cart-count.php")
        .then(function(response) {
            return response.text();
        })
        .then(function(data) {
            document.querySelector(".itemQuantity").textContent = data;
        })
        .catch(function(error) {
            console.error(error);
        });
}
function updateWishlistQuantity() {
    fetch("api/get-wishlist-count.php")
        .then(function(response) {
            return response.text();
        })
        .then(function(data) {
            document.querySelector(".itemSavedQuantity").textContent = data;
        })
        .catch(function(error) {
            console.error(error);
        });
}
updateCartQuantity();
updateWishlistQuantity();