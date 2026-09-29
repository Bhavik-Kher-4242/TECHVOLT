const UpdateProfileForm = document.querySelector(".update-profile-form form");
const UpdateProfileButton = document.querySelector(".update-profile");
const UpdateProfileModal = document.querySelector(".update-profile-modal");
const CloseUpdateProfile = document.querySelector(".close-update-profile");
const CancelUpdateProfile = document.querySelector(".cancel-update-profile");


UpdateProfileButton.addEventListener("click", function () {

    UpdateProfileModal.style.display = "flex";

});
CloseUpdateProfile.addEventListener("click", function () {

    UpdateProfileModal.style.display = "none";

});


CancelUpdateProfile.addEventListener("click", function () {

    UpdateProfileModal.style.display = "none";

});
UpdateProfileForm.addEventListener("submit", function(event) {
    event.preventDefault();
    const formData = new FormData(UpdateProfileForm);
    fetch("api/update-profile.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log(data);
        if (data === "success") {
            Swal.fire({
                title: "Profile Updated!",
                text: "Your profile information has been updated successfully.",
                icon: "success",
                theme: "dark",
                confirmButtonColor: "#0042FE"
            }).then(() => {
                window.location.reload();
            });
        }
        else if (data === "empty_fields") {
            Swal.fire({
                title: "Missing Information",
                text: "Please fill in all fields.",
                icon: "warning",
                theme: "dark",
                confirmButtonColor: "#0042FE"
            });
        }
        else if (data === "invalid_email") {
            Swal.fire({
                title: "Invalid Email",
                text: "Please Enter Valid Email.",
                icon: "warning",
                theme: "dark",
                confirmButtonColor: "#0042FE"
            });
        }
        else if (data === "already_exists") {
            Swal.fire({
                title: "Already Exists",
                text: "Username or email is already being used.",
                icon: "warning",
                theme: "dark",
                confirmButtonColor: "#0042FE"
            });
        }
        else {
            Swal.fire({
                title: "Update Failed",
                text: "Unable to update your profile. Please try again.",
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
            theme: "dark",
            confirmButtonColor: "#0042FE"
        });
    });
});
const LogoutButton = document.querySelector(".logout-btn");
LogoutButton.addEventListener("click", function () {
    Swal.fire({
        title: "Logout?",
        text: "Are you sure you want to logout?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, Logout",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#DC2626",
        cancelButtonColor: "#0042FE"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "logout.php";
        }
    });
});
const ChangeProfilePic = document.querySelector(".change-profile-pic");
const ProfileImageInput = document.querySelector("#profile-image-input");
const UploadProfilePic = document.querySelector(".upload-profile-pic");
ChangeProfilePic.addEventListener("click", function(){
    ProfileImageInput.click();
});
ProfileImageInput.addEventListener("change", function () {

    const file = ProfileImageInput.files[0];

    if (file) {
        const imageURL = URL.createObjectURL(file);
        document.querySelector(".profile-image").src = imageURL;
        UploadProfilePic.style.display = "block";
    }

});

UploadProfilePic.addEventListener("click", function () {

    const file = ProfileImageInput.files[0];

    if (!file) {
        return;
    }

    const formData = new FormData();

    formData.append("profile_image", file);

    fetch("api/update-profile-picture.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {

        console.log(data);

        if (data === "success") {

            Swal.fire({
                title: "Profile Picture Updated!",
                text: "Your profile picture has been updated successfully.",
                icon: "success",
                confirmButtonColor: "#0042FE"
            }).then(() => {
                window.location.reload();
            });

        }

        else if (data === "file_too_large") {

            Swal.fire({
                title: "File Too Large",
                text: "Please select an image smaller than 2 MB.",
                icon: "warning",
                confirmButtonColor: "#0042FE"
            });

        }

        else if (data === "invalid_image" || data === "invalid_type") {

            Swal.fire({
                title: "Invalid Image",
                text: "Please select a valid JPG, PNG, or WEBP image.",
                icon: "warning",
                confirmButtonColor: "#0042FE"
            });

        }

        else if (data === "login_required") {

            window.location.href = "login.php";

        }

        else {

            Swal.fire({
                title: "Upload Failed",
                text: "Unable to update your profile picture.",
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

});