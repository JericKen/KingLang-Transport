document.addEventListener('DOMContentLoaded', function() {
    let slideIndex = 0;
    let slides = document.getElementsByClassName("slideshow-slide");
    let slideshowContainer = document.querySelector(".slideshow-container");
    let timer = null;
    let transitioning = false;
    
    // Preload images for smoother transitions
    preloadSlideImages();
    
    // Show the first slide immediately on page load
    if (slides.length > 0) {
        slides[0].classList.add("active-slide");
        // Trigger text animation for first slide
        setTimeout(() => {
            if (slides[0].querySelector('.slideshow-text')) {
                slides[0].querySelector('.slideshow-text').style.opacity = '1';
                slides[0].querySelector('.slideshow-text').style.transform = 'translateY(0)';
            }
            
            // Also trigger contact info animation for first slide
            if (slides[0].querySelector('.slideshow-contact-info')) {
                slides[0].querySelector('.slideshow-contact-info').style.opacity = '1';
                slides[0].querySelector('.slideshow-contact-info').style.transform = 'translateY(0)';
            }
        }, 500);
    }
    
    // Start the slideshow
    timer = setTimeout(showSlides, 5000); // Slightly longer initial delay

    function showSlides() {
        if (transitioning) return;
        transitioning = true;
        
        clearTimeout(timer); // Clear any existing timer
        
        // Calculate the next slide index
        let nextSlideIndex = slideIndex + 1;
        if (nextSlideIndex >= slides.length) {
            nextSlideIndex = 0;
        }
        
        // Prepare the next slide for transition
        const currentSlide = slides[slideIndex];
        const nextSlide = slides[nextSlideIndex];
        
        // Make sure next slide is ready for visibility before transition
        nextSlide.style.visibility = 'visible';
        
        // Add transition classes
        currentSlide.classList.add("slide-leave");
        nextSlide.classList.add("slide-enter");
        
        // Force reflow to ensure CSS transitions take effect
        void nextSlide.offsetWidth;
        
        // Make next slide active and begin transition
        nextSlide.classList.add("active-slide");
        nextSlide.classList.remove("slide-enter");
        
        // Short timeout to ensure proper CSS cascade before removing active class
        setTimeout(() => {
            // Now remove active class from current slide
            currentSlide.classList.remove("active-slide");
            
            // Update the slide index
            slideIndex = nextSlideIndex;
        }, 50);
        
        // Remove the leaving class after transition completes
        setTimeout(() => {
            currentSlide.classList.remove("slide-leave");
            
            // Hide all non-active slides
            Array.from(slides).forEach(slide => {
                if (!slide.classList.contains('active-slide')) {
                    slide.style.visibility = 'hidden';
                }
            });
            
            transitioning = false;
        }, 1500);
        
        // Set the next timer
        timer = setTimeout(showSlides, 6000); // Slightly longer delay for better viewing
    }

    // Function to change slide when dot is clicked (if navigation dots are added)
    window.currentSlide = function(n) {
        if (transitioning) return;
        
        clearTimeout(timer); // Stop the automatic slideshow when manually changing slides
        
        // Convert to zero-based index
        n = n - 1;
        
        // Don't do anything if clicking the current slide
        if (n === slideIndex) return;
        
        transitioning = true;
        
        // Prepare slides for transition
        const currentSlide = slides[slideIndex];
        const targetSlide = slides[n];
        
        // Make sure target slide is ready for visibility before transition
        targetSlide.style.visibility = 'visible';
        
        // Add transition classes
        currentSlide.classList.add("slide-leave");
        targetSlide.classList.add("slide-enter");
        
        // Force reflow to ensure CSS transitions take effect
        void targetSlide.offsetWidth;
        
        // Make target slide active and begin transition
        targetSlide.classList.add("active-slide");
        targetSlide.classList.remove("slide-enter");
        
        // Short timeout to ensure proper CSS cascade before removing active class
        setTimeout(() => {
            // Now remove active class from current slide
            currentSlide.classList.remove("active-slide");
            
            // Update the slide index
            slideIndex = n;
        }, 50);
        
        // Remove the leaving class after transition completes
        setTimeout(() => {
            currentSlide.classList.remove("slide-leave");
            
            // Hide all non-active slides
            Array.from(slides).forEach(slide => {
                if (!slide.classList.contains('active-slide')) {
                    slide.style.visibility = 'hidden';
                }
            });
            
            transitioning = false;
        }, 1500);
        
        // Restart the timer with a longer delay after manual interaction
        timer = setTimeout(showSlides, 7000);
    }
    
    // Preload all slideshow images for smoother transitions
    function preloadSlideImages() {
        if (!slideshowContainer) return;
        
        slideshowContainer.classList.add('loading');
        
        let imagePromises = [];
        Array.from(slides).forEach(slide => {
            const img = slide.querySelector('img');
            if (img && img.src) {
                const promise = new Promise((resolve, reject) => {
                    const newImg = new Image();
                    newImg.onload = resolve;
                    newImg.onerror = reject;
                    newImg.src = img.src;
                });
                imagePromises.push(promise);
            }
        });
        
        // When all images are loaded, remove loading class
        Promise.all(imagePromises)
            .then(() => {
                slideshowContainer.classList.remove('loading');
            })
            .catch(() => {
                slideshowContainer.classList.remove('loading');
            });
    }
}); 