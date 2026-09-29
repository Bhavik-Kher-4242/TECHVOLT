function openStockModal(productName, currentStock, productId) {

    document.getElementById("stock-product-name").textContent = productName;

    document.getElementById("current-stock").textContent = currentStock;

    document.getElementById("new-stock").value = currentStock;

    document.getElementById("stock-product-id").value = productId;

    document.getElementById("stock-modal").classList.add("show");

    document.getElementById("new-stock").focus();
}

function closeStockModal() {
    document.getElementById("stock-modal").classList.remove("show");
}
function updateStock() {

    const productId = document.getElementById("stock-product-id").value;

    const newStock = document.getElementById("new-stock").value;


    if (productId === "") {

        Swal.fire({
            icon: "error",
            title: "Product Not Selected",
            text: "Please select a product first."
        });

        return;
    }


    if (newStock === "" || Number(newStock) < 0) {

        Swal.fire({
            icon: "error",
            title: "Invalid Stock",
            text: "Please enter a valid stock quantity."
        });

        return;
    }


    const form = document.createElement("form");

    form.method = "POST";

    form.action = "";


    const updateInput = document.createElement("input");

    updateInput.type = "hidden";

    updateInput.name = "update_stock";

    updateInput.value = "1";


    const productInput = document.createElement("input");

    productInput.type = "hidden";

    productInput.name = "product_id";

    productInput.value = productId;


    const stockInput = document.createElement("input");

    stockInput.type = "hidden";

    stockInput.name = "new_stock";

    stockInput.value = newStock;


    form.appendChild(updateInput);

    form.appendChild(productInput);

    form.appendChild(stockInput);


    document.body.appendChild(form);

    form.submit();
}

// =========================================================
// CUSTOMER VIEW MODAL
// =========================================================

function openUserModal(customer) {

    document.getElementById("user-modal-id").textContent =
        customer.id;

    document.getElementById("user-modal-name").textContent =
        customer.name;

    document.getElementById("user-modal-username").textContent =
        "@" + customer.username;

    document.getElementById("user-modal-email").textContent =
        customer.email;

    document.getElementById("user-modal-orders").textContent =
        customer.orders;

    document.getElementById("user-modal-joined").textContent =
        customer.joined;

    document.getElementById("user-modal-spent").textContent =
        customer.spent;

    document.getElementById("user-modal-avatar").textContent =
        customer.initials;

    document.getElementById("user-modal-status").textContent =
        customer.status;

    const avatar =
        document.getElementById("user-modal-avatar");

    const modalStatus =
        document.getElementById("user-modal-status");

    modalStatus.className =
        "user-modal-status " +
        customer.status.toLowerCase();

    if (customer.profile_image) {

        avatar.innerHTML = `
            <img
                src="../../${customer.profile_image}"
                alt="${customer.name}"
            >
        `;

    } else {

        avatar.textContent =
            customer.initials;

    }


    avatar.classList.add("has-image");

    document.getElementById("user-modal").classList.add(
        "show"
    );

    document.body.classList.add(
        "modal-open"
    );
}

function closeUserModal() {

    document.getElementById("user-modal").classList.remove(
        "show"
    );

    document.body.classList.remove(
        "modal-open"
    );
}

function openReturnModal(returnData) {

    /*
    |--------------------------------------------------------------------------
    | Return Information
    |--------------------------------------------------------------------------
    */

    document.getElementById("return-modal-id").textContent =
        returnData.id;

    document.getElementById("return-modal-date").textContent =
        returnData.return_date;

    document.getElementById("return-modal-order").textContent =
        returnData.order_id;


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    const statusElement =
        document.getElementById("return-modal-status");

    const status =
        returnData.status.toLowerCase();

    statusElement.textContent =
        status.charAt(0).toUpperCase() + status.slice(1);

    statusElement.className =
        "return-status " + status;


    /*
    |--------------------------------------------------------------------------
    | Customer Information
    |--------------------------------------------------------------------------
    */

    document.getElementById("return-modal-customer").textContent =
        returnData.customer_name;

    document.getElementById("return-modal-email").textContent =
        returnData.customer_email;


    /*
    |--------------------------------------------------------------------------
    | Customer Avatar
    |--------------------------------------------------------------------------
    */

    const avatar =
        document.getElementById("return-modal-avatar");


    avatar.innerHTML = "";


    if (returnData.profile_image) {

        const image =
            document.createElement("img");

        image.src =
            "../" + returnData.profile_image;

        image.alt =
            returnData.customer_name;

        avatar.appendChild(image);

    } else {

        avatar.textContent =
            returnData.initials;

    }


    /*
    |--------------------------------------------------------------------------
    | Product Information
    |--------------------------------------------------------------------------
    */

    document.getElementById("return-modal-product").textContent =
        returnData.product;

    document.getElementById("return-modal-quantity").textContent =
        returnData.quantity;


    const price =
        Number(returnData.price);


    document.getElementById("return-modal-price").textContent =
        "₹" + price.toLocaleString("en-IN", {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });


    /*
    |--------------------------------------------------------------------------
    | Return Request
    |--------------------------------------------------------------------------
    */

    document.getElementById("return-modal-reason").textContent =
        returnData.reason;

    document.getElementById("return-modal-description").textContent =
        returnData.description || "No description provided.";

        const rejectButton =
    document.getElementById("return-modal-reject");

const approveButton =
    document.getElementById("return-modal-approve");

    const returnId = returnData.return_id;
        
// Default: hide both buttons
rejectButton.style.display = "none";
approveButton.style.display = "none";


// Show actions only for pending returns
if (status === "requested") {

    rejectButton.style.display = "inline-flex";
    approveButton.style.display = "inline-flex";

    rejectButton.onclick = function () {
        updateReturnStatus(
            returnId,
            "rejected"
        );
    };

    approveButton.onclick = function () {
        updateReturnStatus(
            returnId,
            "approved"
        );
    };
}

    /*
    |--------------------------------------------------------------------------
    | Show Modal
    |--------------------------------------------------------------------------
    */

    document
        .getElementById("return-modal")
        .classList.add("show");

    document.body.classList.add("modal-open");
}

function closeReturnModal() {

    document
        .getElementById("return-modal")
        .classList.remove("show");

    document.body.classList.remove("modal-open");
}

const removeImageButtons = document.querySelectorAll(".remove-image-btn");

if (removeImageButtons.length > 0) {
    removeImageButtons.forEach((button) => {
        button.addEventListener("click", function () {
            const imageId = this.dataset.imageId;

            Swal.fire({
                title: "Remove Image?",
                text: "This image will be removed from the product.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, Remove",
                cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log("Delete image ID:", imageId);
                    const formData = new FormData();

                    formData.append("image_id", imageId);

                    fetch("../../api/delete-product-image.php", {
                        method: "POST",
                        body: formData,
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                const imageCard = button.closest(".product-image-card");

                                imageCard.remove();

                                Swal.fire({
                                    icon: "success",
                                    title: "Image Removed",
                                    text: "Product image removed successfully.",
                                    confirmButtonText: "OK",
                                });
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Delete Failed",
                                    text: data.message,
                                    confirmButtonText: "OK",
                                });
                            }
                        })
                        .catch((error) => {
                            console.error("Error:", error);

                            Swal.fire({
                                icon: "error",
                                title: "Something went wrong",
                                text: "Unable to remove the image.",
                                confirmButtonText: "OK",
                            });
                        });
                }
            });
        });
    });
}

const setPrimaryButtons = document.querySelectorAll(".set-primary-btn");

if (setPrimaryButtons.length > 0) {

    setPrimaryButtons.forEach(button => {

        button.addEventListener("click", function () {

            const imageId = this.dataset.imageId;

            Swal.fire({
                title: "Set as Primary?",
                text: "This image will become the primary product image.",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Yes, Set Primary",
                cancelButtonText: "Cancel"
            }).then((result) => {

                if (result.isConfirmed) {

                    const formData = new FormData();

                    formData.append("image_id", imageId);

                    fetch("../../api/set-primary-image.php", {
                        method: "POST",
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {

                            if (data.success) {

                                Swal.fire({
                                    icon: "success",
                                    title: "Primary Image Updated",
                                    text: "Product primary image has been updated successfully.",
                                    confirmButtonText: "OK"
                                }).then(() => {

                                    window.location.reload();

                                });

                            } else {

                                Swal.fire({
                                    icon: "error",
                                    title: "Update Failed",
                                    text: data.message,
                                    confirmButtonText: "OK"
                                });

                            }

                        })
                        .catch(error => {

                            console.error("Error:", error);

                            Swal.fire({
                                icon: "error",
                                title: "Something went wrong",
                                text: "Unable to update the primary image.",
                                confirmButtonText: "OK"
                            });

                        });

                }

            });

        });

    });

}

// =========================================================
// PRODUCT SPECIFICATION
// =========================================================

const addSpecButton = document.querySelector(".add-spec-btn");
const specificationContainer = document.querySelector(".specification-container");

if (addSpecButton && specificationContainer) {

    addSpecButton.addEventListener("click", function () {

        const newRow = document.createElement("div");

        newRow.classList.add("specification-row");

        newRow.innerHTML = `
            <div class="form-group">
                <label>Specification</label>
                <input
                    type="text"
                    name="spec_name[]"
                    placeholder="e.g. Voltage"
                >
            </div>

            <div class="form-group">
                <label>Value</label>
                <input
                    type="text"
                    name="spec_value[]"
                    placeholder="e.g. 5V"
                >
            </div>

            <button type="button" class="remove-spec-btn">×</button>
        `;

        specificationContainer.appendChild(newRow);
    });

    specificationContainer.addEventListener("click", function (event) {

        if (event.target.classList.contains("remove-spec-btn")) {

            event.target.closest(".specification-row").remove();

        }

    });
}
// =========================================================
// NEW PRODUCT IMAGE PREVIEW
// =========================================================

const productImageInput = document.getElementById("product-images");
const newImagePreviewContainer = document.getElementById(
    "new-image-preview-container"
);

if (productImageInput && newImagePreviewContainer) {

    productImageInput.addEventListener("change", function () {

        newImagePreviewContainer.innerHTML = "";

        const files = this.files;

        for (let i = 0; i < files.length; i++) {

            const file = files[i];

            if (!file.type.startsWith("image/")) {
                continue;
            }

            const reader = new FileReader();

            reader.onload = function (event) {

                const imageCard = document.createElement("div");

                imageCard.classList.add("product-image-card");

                imageCard.dataset.fileIndex = i;

                imageCard.innerHTML = `
                    <div class="product-image-preview">

                        <span class="new-image-badge">
                            New
                        </span>

                        <img
                            src="${event.target.result}"
                            alt="${file.name}">
                    </div>

                    <div class="product-image-card-footer">

                        <span class="product-image-name">
                            ${file.name}
                        </span>

                        <button
                            type="button"
                            class="new-image-primary-btn">
                            Set Primary
                        </button>
                        <button
                            type="button"
                            class="remove-new-image-btn">
                            ×
                        </button>

                    </div>
                `;

                newImagePreviewContainer.appendChild(imageCard);
            };

            reader.readAsDataURL(file);
        }
    });
    // =========================================================
    // NEW IMAGE - SET PRIMARY
    // =========================================================

    newImagePreviewContainer.addEventListener("click", function (event) {

        if (event.target.classList.contains("new-image-primary-btn")) {

            const selectedCard = event.target.closest(".product-image-card");

            // Remove primary state from other new image previews
            const newImageCards =
                newImagePreviewContainer.querySelectorAll(".product-image-card");

            newImageCards.forEach(function (card) {
                card.classList.remove("primary");

                const button = card.querySelector(".new-image-primary-btn");

                if (button) {
                    button.textContent = "Set Primary";
                }
            });

            // Set selected image as primary
            selectedCard.classList.add("primary");

            document.getElementById("primary-new-image").value = selectedCard.dataset.fileIndex;

            event.target.textContent = "★ Primary";
        }

        if (event.target.classList.contains("remove-new-image-btn")) {

            const selectedCard =
                event.target.closest(".product-image-card");

            const fileIndex =
                parseInt(selectedCard.dataset.fileIndex);

            const dataTransfer = new DataTransfer();

            Array.from(productImageInput.files).forEach(function (file, index) {

                if (index !== fileIndex) {
                    dataTransfer.items.add(file);
                }

            });

            productImageInput.files = dataTransfer.files;

            selectedCard.remove();

            newImagePreviewContainer
                .querySelectorAll(".product-image-card")
                .forEach(function (card, index) {

                    card.dataset.fileIndex = index;

                });
            if (fileIndex === parseInt(
                document.getElementById("primary-new-image").value
            )) {
                document.getElementById("primary-new-image").value = "";
            }
        }

    });
}
// ================= AUTO GENERATE SKU =================

const categorySelect = document.getElementById("category");
const skuInput = document.getElementById("sku");

if (categorySelect && skuInput) {

    categorySelect.addEventListener("change", function () {

        const categoryId = this.value;

        if (categoryId === "") {

            skuInput.value = "";

            return;
        }

        skuInput.value = "Generating...";

        fetch(`../../api/generate-sku.php?category_id=${categoryId}`)
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {

                if (data.success) {

                    skuInput.value = data.sku;

                } else {

                    skuInput.value = "";

                    Swal.fire({
                        icon: "error",
                        title: "SKU Generation Failed",
                        text: data.message,
                        background: "#111827",
                        color: "#ffffff"
                    });

                }

            })
            .catch(function (error) {

                console.error("SKU API Error:", error);

                skuInput.value = "";

                Swal.fire({
                    icon: "error",
                    title: "Something went wrong",
                    text: "Unable to generate SKU.",
                    background: "#111827",
                    color: "#ffffff"
                });

            });

    });

}

function updateReturnStatus(
    returnId,
    newStatus
) {

    const actionText =
        newStatus === "approved"
            ? "approve"
            : "reject";


    Swal.fire({

        title:
            newStatus === "approved"
                ? "Approve Return?"
                : "Reject Return?",

        text:
            `Are you sure you want to ${actionText} this return request?`,

        icon: "warning",

        showCancelButton: true,

        confirmButtonText:
            newStatus === "approved"
                ? "Yes, Approve"
                : "Yes, Reject",

        cancelButtonText:
            "Cancel",

        background: "#111827",

        color: "#ffffff"

    }).then((result) => {

        if (!result.isConfirmed) {
            return;
        }


        const form =
            document.createElement("form");

        form.method = "POST";
        form.action = "";


        const returnInput =
            document.createElement("input");

        returnInput.type = "hidden";
        returnInput.name = "return_id";
        returnInput.value = returnId;


        const statusInput =
            document.createElement("input");

        statusInput.type = "hidden";
        statusInput.name = "return_status";
        statusInput.value = newStatus;


        form.appendChild(returnInput);
        form.appendChild(statusInput);

        document.body.appendChild(form);

        form.submit();

    });
}