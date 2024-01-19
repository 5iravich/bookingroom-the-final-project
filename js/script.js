const menuToggle = document.querySelector('.menu-toggle');
const mobileMenu = document.querySelector('.mobile-menu');

menuToggle.addEventListener('click', () => {
    mobileMenu.classList.toggle('open');
    menuToggle.classList.toggle('change');
});


function showImagePopup(imageData) {
  var popup = document.getElementById("imagePopup");
  var popupImage = document.getElementById("popupImage");

  // Set the image source
  popupImage.src = "data:image/jpeg;base64," + imageData;

  // Show the popup
  popup.style.display = "block";
}

// Function to close the image popup
function closePopup() {
  var popup = document.getElementById("imagePopup");
  popup.style.display = "none";
}


