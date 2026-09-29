let minusBtn = document.querySelectorAll(".minus-btn");
let plusBtn = document.querySelectorAll(".plus-btn");
let itemQuantity = Number(document.querySelector(".item-count").textContent);
let subtotalAmount = document.querySelector(".subtotal-amount");
let gstRate = 0.07;
let deliveryChargeTotal = 50;
minusBtn.forEach(function (btn) {
    btn.addEventListener("click", function () {
        let cartProduct = btn.closest(".cart-product");
        let currentQuantity = Number(cartProduct.querySelector(".Quantity-in-cart").textContent);
        let cart_item_id = cartProduct.dataset.cartItemId;
        
        if (currentQuantity > 1) {
            currentQuantity--;
        }
        cartProduct.querySelector(".Quantity-in-cart").textContent = currentQuantity;
        updateSubtotal();
        fetch("api/update-cart.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: "cart_item_id=" + cart_item_id + "&quantity=" + currentQuantity,
        })
        .then(function (response) {
            return response.text();
        })
        .then(function (data) {
                updateCartQuantity();
                console.log(data);
            });
    });
});
plusBtn.forEach(function (btn) {
    btn.addEventListener("click", function () {

        let cartProduct = btn.closest(".cart-product");

        let cart_item_id = cartProduct.dataset.cartItemId;
        let availableStock = Number(cartProduct.dataset.stock);

        let quantityElement =
            cartProduct.querySelector(".Quantity-in-cart");

        let oldQuantity = Number(quantityElement.textContent);
        let newQuantity = oldQuantity + 1;

        // Stop before sending an invalid quantity to server
        if (newQuantity > availableStock) {

            Swal.fire({
                icon: "warning",
                title: "Stock Limit Reached",
                text: "Only " + availableStock + " item(s) are available in stock.",
                theme: "dark"
            });

            return;
        }

        quantityElement.textContent = newQuantity;

        updateSubtotal();

        fetch("api/update-cart.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body:
                "cart_item_id=" +
                encodeURIComponent(cart_item_id) +
                "&quantity=" +
                encodeURIComponent(newQuantity),
        })
        .then(function (response) {
            return response.text();
        })
        .then(function (data) {

            console.log("Update cart response:", data);

            if (data === "quantity") {

                updateCartQuantity();

            } else {

                // Backend rejected it
                quantityElement.textContent = oldQuantity;
                updateSubtotal();

                if (data === "out_of_stock") {

                    Swal.fire({
                        icon: "warning",
                        title: "Out of Stock",
                        text: "You cannot add more than the available stock.",
                        theme: "dark"
                    });

                } else {

                    Swal.fire({
                        icon: "error",
                        title: "Unable to Update Cart",
                        text: "Something went wrong while updating your cart.",
                        theme: "dark"
                    });
                }
            }
        })
        .catch(function () {

            quantityElement.textContent = oldQuantity;
            updateSubtotal();

            Swal.fire({
                icon: "error",
                title: "Connection Error",
                text: "Unable to update your cart.",
                theme: "dark"
            });
        });
    });
});
let removeBtn = document.querySelectorAll(".cart-remove-btn");
removeBtn.forEach(function (btn, index) {
    btn.addEventListener("click", function () {
        let cartProduct = btn.closest(".cart-product");
        let cart_item_id = cartProduct.dataset.cartItemId;
        Swal.fire({
            title: "Remove Product?",
            text: "Are you sure you want to remove this product from your cart?",
            icon: "warning",
            theme: "dark",
            showCancelButton: true,
            confirmButtonText: "Yes, Remove",
            cancelButtonText: "Cancel",
        }).then(function (result) {
            if (result.isConfirmed) {
                fetch("api/remove-cart-item.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: "cart_item_id=" + cart_item_id,
                })
                    .then(function (response) {
                        return response.text();
                    })
                    .then(function (data) {
                        if (data == "removed") {
                            cartProduct.remove();
                            updateSubtotal();
                            updateCartQuantity();
                            itemQuantity--;
                            document.querySelector(".item-count").textContent = itemQuantity;
                            if(itemQuantity == 0){
                                document.querySelector(".container-of-deliveryOptions").style.display = "none";
                                document.querySelector(".container-of-orderSummery").style.display = "none";
                                document.querySelector(".all-products-title").style.display = "none";
                                document.querySelector(".container-of-product").classList.add("empty-cart-product");
                                document.querySelector(".empty-cart").classList.remove("hide-empty-cart");
                            }
                        }
                    });
            }
        });
    });
});

function updateSubtotal() {
    let subtotal = 0;
    let cartProduct = document.querySelectorAll(".cart-product");
    cartProduct.forEach(function(product){
        let price = Number(product.dataset.price);
        let quantity = product.querySelector(".Quantity-in-cart");
        let currentQuantity = Number(quantity.textContent);
        subtotal += price * currentQuantity;
    })
    subtotalAmount.innerHTML = "₹" + subtotal;
    let gstAmount = subtotal * gstRate;
    document.querySelector(".gst-amount").innerHTML = "₹" + gstAmount.toFixed(2);
    document.querySelector(".grand-total-amount").innerHTML = "₹" + (subtotal + deliveryChargeTotal + gstAmount).toFixed(2);
}
let deliveryOptions = document.querySelectorAll(".options");

deliveryOptions.forEach(function(option){
    option.addEventListener("click", function(){
        let radio = option.querySelector(".radio-for-delivery");
        radio.checked = true;
        let priceElement = option.querySelector(".delivery-price");
        let deliveryCharge = Number(priceElement.dataset.price);
        deliveryChargeTotal = deliveryCharge;
        updateSubtotal();
        document.querySelector(".delivery-amount").innerHTML = "₹" + deliveryCharge;
        document.querySelector(".deliveryChargesAmount").value = deliveryCharge;
    })
})
updateSubtotal();