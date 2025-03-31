const images = document.querySelectorAll('.image-container img');
const imageContainer = document.getElementById('imageContainer');

let currentIndex = 0;

imageContainer.addEventListener('wheel', (event) => {
    event.preventDefault(); // Prevent default scroll behavior
    if (event.deltaY < 0) {
        // Scrolling up (show previous image)
        currentIndex = (currentIndex === 0) ? images.length - 1 : currentIndex - 1;
    } else {
        // Scrolling down (show next image)
        currentIndex = (currentIndex === images.length - 1) ? 0 : currentIndex + 1;
    }
    updateImageView();
});

function updateImageView() {
    images.forEach((img, index) => {
        img.classList.remove('active');
        if (index === currentIndex) {
            img.classList.add('active');
        }
    });
}