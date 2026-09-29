const NotFoundSection = document.querySelector("#not-found");
const SearchResultsSection = document.querySelector("#search-results");
const ProductContainer = document.querySelector(".all-products");
const ProductCount = document.querySelector("#product-count");
const SearchParams = new URLSearchParams(window.location.search);
const SearchValue = SearchParams.get("search") || "";
fetch("api/get-products.php")
    .then(response => response.json())
    .then(products => {
        const FilteredProducts = products.filter(function(product) {
            return product.product_name
                .toLowerCase()
                .includes(SearchValue.toLowerCase());
        });
        ProductCount.textContent = FilteredProducts.length;
            if (FilteredProducts.length === 0) {
                SearchResultsSection.style.display = "none";
                NotFoundSection.style.display = "block";
            }
            else {
                NotFoundSection.style.display = "none";
                SearchResultsSection.style.display = "block";
                displayProducts(FilteredProducts);
            }
    })
    .catch(error => {
        console.error(error);
    });
function displayProducts(products) {
    ProductContainer.innerHTML = "";
    products.forEach(function(product) {
        ProductContainer.innerHTML += `
            <div id="${product.product_id}" class="products">
                <img src="${product.image_path}" alt="${product.alt_text}">
                <h4 class="category">
                    ${product.category_name}
                </h4>
                <h3 class="product-name">
                    ${product.product_name}
                </h3>
                <div class="rating">
                    ${generateStar(product.rating)}
                    (${product.review_count})
                </div>
                <p class="product-short-discription">
                    ${product.short_description}
                </p>
                <div class="container-for-price-stock">
                    <p class="product-price">
                        Price: ₹${product.price}
                    </p>
                    <p class="product-stock">
                        &#9679; ${CheckStock(product.stock)}
                    </p>
                </div>
                <div class="container-for-buttons">
                    <button 
                        class="add-to-cart"
                        data-product-id="${product.product_id}">
                        Add to Cart
                    </button>
                    <button class="add-to-wishlist" data-product-id="${product.product_id}">
                        &#10084; Wishlist
                    </button>
                </div>
            </div>
        `;
    });
}
ProductContainer.addEventListener("click", function(event) {
    if (event.target.classList.contains("add-to-cart")) {
        event.stopPropagation();
        const productId =
            event.target.dataset.productId;
        const formData = new FormData();
        formData.append("product_id", productId);
        formData.append("quantity", 1);
        fetch("api/add-to-cart.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {
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
    if (event.target.classList.contains("add-to-wishlist")) {
    event.stopPropagation();

    const productId = event.target.dataset.productId;

    const formData = new FormData();
    formData.append("product_id", productId);

    fetch("api/add-to-wishlist.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log(data);
        
        if (data === "success") {
            updateWishlistQuantity();
            Swal.fire({
                title: "Added to Wishlist!",
                text: "Product has been added to your wishlist.",
                icon: "success",
                confirmButtonColor: "#0042FE"
            });
        }
        else if (data === "already_exists") {
            updateWishlistQuantity();
            Swal.fire({
                title: "Already in Wishlist",
                text: "This product is already in your wishlist.",
                icon: "info",
                confirmButtonColor: "#0042FE"
            });
        }
        else if (data === "login_required") {
            Swal.fire({
                title: "Login Required",
                text: "Please login to add products to your wishlist.",
                icon: "warning",
                confirmButtonColor: "#0042FE"
            }).then(() => {
                window.location.href = "login.php";
            });
        }
        else {
            Swal.fire({
                title: "Something Went Wrong",
                text: "Unable to add the product to your wishlist.",
                icon: "error",
                confirmButtonColor: "#0042FE"
            });
        }

    });

    return;
}
    const card = event.target.closest(".products");
    if (card) {
        window.location.href =
            `product-details.php?id=${card.id}`;
    }
});
function generateStar(rating) {
    let product_rating =
        Math.round(rating);
    let string_rating = "";
    for (let i = 0; i < 5; i++) {
        if (i < product_rating) {
            string_rating += "&starf;";
        }
        else {
            string_rating += "&star;";
        }
    }
    return string_rating;
}
function CheckStock(stock) {
    if (stock > 0) {
        return "In Stock";
    }
    else {
        return "Out of Stock";
    }
}