const savedAddress = document.querySelectorAll('input[name="saved-address"]');
savedAddress.forEach(function(address) {
    address.addEventListener("change", function() {
        document.querySelectorAll(".address-card").forEach(function(card) {
            card.classList.remove("selected");
        });

        address.closest(".address-card").classList.add("selected");
        let addressId = address.dataset.addressId;
        let FullName = address.dataset.fullName;
        let Number = address.dataset.phone;
        let Address_Line = address.dataset.address;
        let City = address.dataset.city;
        let State = address.dataset.state;
        let Pincode = address.dataset.pincode;
        document.querySelector("#selected-address-id").value = addressId;
        document.querySelector("#full-name").value = FullName;
        document.querySelector("#number").value = Number;
        document.querySelector("#address").value = Address_Line;
        document.querySelector("#city").value = City;
        document.querySelector("#state").value = State;
        document.querySelector("#pincode").value = Pincode;
    });
});