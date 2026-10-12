document.addEventListener("DOMContentLoaded", function () {
  const track = document.getElementById("carouselTrack");
  const slides = document.querySelectorAll(".carousel-img");
  let currentIndex = 0;
  const totalSlides = slides.length;
  function nextSlide() {
    currentIndex = (currentIndex + 1) % totalSlides;
    const offset = -currentIndex * 100;
    track.style.transform = `translateX(${offset}%)`;
  }
  if(track && totalSlides > 0) {
    setInterval(nextSlide, 3000);
  }
});
