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



const menuBar = document.querySelector('#content nav .bx.bx-menu');
const sideBar = document.querySelector('#sidebar');

menuBar.addEventListener('click', () => {
    sideBar.classList.toggle('closemain');
    const isSidebarClosed = sideBar.classList.contains('closemain');
    localStorage.setItem('sidebarState', isSidebarClosed ? 'closed' : 'open');
});

document.addEventListener('DOMContentLoaded', () => {
  const savedSidebarState = localStorage.getItem('sidebarState');
  if (savedSidebarState === 'closed') {
    sideBar.classList.add('closemain');
  }
  else{
    sideBar.classList.add('open');
  }
});

