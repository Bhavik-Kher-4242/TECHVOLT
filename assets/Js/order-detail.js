const cancelButton = document.querySelector(".cancel-order-btn");
if (cancelButton) {
    cancelButton.addEventListener("click", function () {
        Swal.fire({
            title: "Cancel Order?",
            text: "Are you sure you want to cancel this order?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Cancel Order",
            cancelButtonText: "Keep Order",
            confirmButtonColor: "#DC2626",
            cancelButtonColor: "#0042FE"
        }).then((result) => {
            if (result.isConfirmed) {
                const orderId = cancelButton.dataset.orderId;
                    fetch("api/cancel-order.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "order_id=" + encodeURIComponent(orderId)
                })
                .then(response => response.text())
                .then(data => {
                    if (data === "success") {
                        Swal.fire({
                            title: "Order Cancelled",
                            text: "Your order has been cancelled successfully.",
                            icon: "success",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#0042FE"
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                    else if (data === "cannot_cancel") {
                        Swal.fire({
                            title: "Cannot Cancel",
                            text: "This order can no longer be cancelled.",
                            icon: "error",
                            confirmButtonColor: "#0042FE"
                        });
                    }
                    else if (data === "order_not_found") {
                        Swal.fire({
                            title: "Order Not Found",
                            text: "The requested order was not found.",
                            icon: "error",
                            confirmButtonColor: "#0042FE"
                        });
                    }
                    else {
                        Swal.fire({
                            title: "Something Went Wrong",
                            text: "Unable to cancel the order.",
                            icon: "error",
                            confirmButtonColor: "#0042FE"
                        });
                    }
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire({
                        title: "Error",
                        text: "Something went wrong while cancelling the order.",
                        icon: "error",
                        confirmButtonColor: "#0042FE"
                    });
                });
            }
        });
    });
}
const returnButtons = document.querySelectorAll(".return-order-btn");
const returnModal = document.querySelector(".return-modal");
const returnOrderItemId = document.querySelector("#return-order-item-id");
returnButtons.forEach(function(button) {
    button.addEventListener("click", function() {
        const orderItemId = button.dataset.orderItemId;
        returnOrderItemId.value = orderItemId;
        returnModal.style.display = "flex";
    });
});
const closeReturnButton = document.querySelector(".close-return-form");
if (closeReturnButton) {
    closeReturnButton.addEventListener("click", function() {
        returnModal.style.display = "none";
    });
}
const cancelReturnButton = document.querySelector(".cancel-return-btn");
if (cancelReturnButton) {
    cancelReturnButton.addEventListener("click", function() {
        returnModal.style.display = "none";
    });
}
// ==============================
// SUBMIT RETURN REQUEST
// ==============================
const returnForm = document.querySelector(".return-form form");
if (returnForm) {
    returnForm.addEventListener("submit", function(event) {
        event.preventDefault();
        const formData = new FormData(returnForm);
        Swal.fire({
            title: "Submit Return Request?",
            text: "Are you sure you want to submit this return request?",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Yes, Submit",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#0042FE",
            cancelButtonColor: "#6B7280"
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("api/return-item.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    if (data === "success") {
                        Swal.fire({
                            title: "Return Request Submitted",
                            text: "Your return request has been submitted successfully.",
                            icon: "success",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#0042FE"
                        }).then(() => {
                            returnModal.style.display = "none";
                            returnForm.reset();
                        });
                    }
                    else if (data === "already_returned") {
                        Swal.fire({
                            title: "Already Requested",
                            text: "A return request already exists for this item.",
                            icon: "info",
                            confirmButtonColor: "#0042FE"
                        });
                    }
                    else if (data === "return_not_allowed") {
                        Swal.fire({
                            title: "Return Not Allowed",
                            text: "This item cannot be returned at this stage.",
                            icon: "error",
                            confirmButtonColor: "#0042FE"
                        });
                    }
                    else if (data === "item_not_found") {
                        Swal.fire({
                            title: "Item Not Found",
                            text: "The requested order item was not found.",
                            icon: "error",
                            confirmButtonColor: "#0042FE"
                        });
                    }
                    else if (data === "invalid_data") {

                        Swal.fire({
                            title: "Missing Information",
                            text: "Please select a return reason.",
                            icon: "warning",
                            confirmButtonColor: "#0042FE"
                        });
                    }
                    else {
                        Swal.fire({
                            title: "Something Went Wrong",
                            text: "Unable to submit the return request.",
                            icon: "error",
                            confirmButtonColor: "#0042FE"
                        });
                    }
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire({
                        title: "Error",
                        text: "Something went wrong while submitting the return request.",
                        icon: "error",
                        confirmButtonColor: "#0042FE"
                    });
                });
            }
        });
    });
}