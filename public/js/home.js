document.addEventListener('DOMContentLoaded', function() {

    const testimonialSlider = document.querySelector('.testimonial-slider');

    const prevBtn = document.querySelector('.prev-btn');

    const nextBtn = document.querySelector('.next-btn');

    const testimonialSummary = document.getElementById('testimonialSummary');



    const scrollAmount = 320;



    // Load dynamic testimonials

    loadTestimonials();



    if (prevBtn && nextBtn && testimonialSlider) {

        prevBtn.addEventListener('click', function() {

            testimonialSlider.scrollBy({

                left: -scrollAmount,

                behavior: 'smooth'

            });

        });



        nextBtn.addEventListener('click', function() {

            testimonialSlider.scrollBy({

                left: scrollAmount,

                behavior: 'smooth'

            });

        });

    }



    // Function to load testimonials from API

    async function loadTestimonials() {

        try {

            const response = await fetch('/home/testimonials/public?limit=10&featured=true');

            const data = await response.json();



            if (data.success && data.testimonials) {

                renderTestimonials(data.testimonials);

                updateTestimonialSummary(data.average_rating, data.total_reviews);

            } else {

                // Fallback to default message if no testimonials

                renderFallbackTestimonials();

            }

        } catch (error) {

            console.error('Error loading testimonials:', error);

            renderFallbackTestimonials();

        }

    }



    // Function to render testimonials

    function renderTestimonials(testimonials) {

        const slider = document.getElementById('testimonialSlider');

        if (!slider) return;



        slider.innerHTML = '';



        testimonials.forEach(testimonial => {

            const testimonialCard = createTestimonialCard(testimonial);

            slider.appendChild(testimonialCard);

        });



        // Trigger animation for new elements

        animateOnScroll();

    }



    // Function to create a testimonial card element

    function createTestimonialCard(testimonial) {

        const card = document.createElement('div');

        card.className = 'testimonial-card animate-on-scroll';



        // Create rating stars

        let starsHtml = '';

        for (let i = 1; i <= 5; i++) {

            if (i <= testimonial.rating) {

                starsHtml += '<i class="fas fa-star"></i>';

            } else {

                starsHtml += '<i class="far fa-star"></i>';

            }

        }



        // Format date

        const submittedDate = new Date(testimonial.created_at);

        const now = new Date();

        const timeDiff = now - submittedDate;

        const daysDiff = Math.floor(timeDiff / (1000 * 60 * 60 * 24));

        

        let timeAgo;

        if (daysDiff === 0) {

            const hoursDiff = Math.floor(timeDiff / (1000 * 60 * 60));

            timeAgo = hoursDiff <= 1 ? 'Just now' : `${hoursDiff} hours ago`;

        } else if (daysDiff === 1) {

            timeAgo = 'Yesterday';

        } else if (daysDiff < 7) {

            timeAgo = `${daysDiff} days ago`;

        } else {

            timeAgo = submittedDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });

        }



        // Create client name display

        const clientDisplay = testimonial.company_name ? 

            `${testimonial.client_name}, ${testimonial.company_name}` : 

            testimonial.client_name;



        card.innerHTML = `

            <div class="rating">

                ${starsHtml}

            </div>

            <h3>${escapeHtml(testimonial.title)}</h3>

            <p>${escapeHtml(testimonial.content)}</p>

            <div class="testimonial-author">

                <p>${escapeHtml(clientDisplay)}, <span>${timeAgo}</span></p>

            </div>

        `;



        return card;

    }



    // Function to update testimonial summary

    function updateTestimonialSummary(averageRating, totalReviews) {

        if (testimonialSummary) {

            if (totalReviews > 0) {

                testimonialSummary.innerHTML = `Rated <strong>${averageRating}</strong> / 5 based on <strong>${totalReviews.toLocaleString()}</strong> reviews. Showing our approved reviews.`;

            } else {

                testimonialSummary.innerHTML = 'No reviews yet. Be the first to share your experience!';

            }

        }

    }



    // Fallback testimonials when API fails or no data

    function renderFallbackTestimonials() {

        const slider = document.getElementById('testimonialSlider');

        if (!slider) return;



        const fallbackTestimonials = [

            {

                rating: 5,

                title: 'Excellent Service',

                content: 'KingLang Transport provides reliable and comfortable travel experiences for our group trips.',

                client_name: 'KingLang Transport',

                company_name: '',

                created_at: new Date().toISOString()

            }

        ];



        renderTestimonials(fallbackTestimonials);

        updateTestimonialSummary(5.0, 1);

    }



    // Utility function to escape HTML

    function escapeHtml(text) {

        const map = {

            '&': '&amp;',

            '<': '&lt;',

            '>': '&gt;',

            '"': '&quot;',

            "'": '&#039;'

        };

        return text.replace(/[&<>"']/g, function(m) { return map[m]; });

    }



    const mobileNavToggle = document.querySelector('.mobile-nav-toggle');

    const navigation = document.querySelector('.navigation');



    if (mobileNavToggle && navigation) {

        mobileNavToggle.addEventListener('click', function() {

            navigation.classList.toggle('active');

            mobileNavToggle.classList.toggle('active');

        });

    }



    // Header scroll behavior

    const header = document.querySelector('header');

    const scrollThreshold = 50; // Adjust this value as needed



    function toggleHeaderClass() {

        if (window.scrollY > scrollThreshold) {

            header.classList.add('header-scrolled');

        } else {

            header.classList.remove('header-scrolled');

        }

    }



    // Call on initial load and on scroll

    window.addEventListener('scroll', toggleHeaderClass);

    toggleHeaderClass();



    // Add animation to elements when they come into view

    const animateOnScroll = function() {

        const animatedElements = document.querySelectorAll('.animate-on-scroll');

        

        animatedElements.forEach(element => {

            const elementPosition = element.getBoundingClientRect().top;

            const screenPosition = window.innerHeight / 1.2;

            

            if (elementPosition < screenPosition) {

                element.classList.add('animated');

            }

        });

    };



    // Call on initial load and on scroll

    window.addEventListener('scroll', animateOnScroll);

    animateOnScroll();

    // Load Past Trips dynamically into destinations grid
    const destinationGrid = document.querySelector('.destination-grid');
    if (destinationGrid) {
        fetch('/api/past-trips/images')
            .then(r => r.json())
            .then(data => {
                if (data.success && Array.isArray(data.images) && data.images.length) {
                    destinationGrid.innerHTML = data.images.slice(0, 8).map(img => `
                        <div class="destination-card animate-on-scroll">
                            <div class="destination-image">
                                <img src="public/images/past-trips/${img.filename}" alt="${(img.title || 'Past Trip').replace(/"/g, '&quot;')}">
                                <h3>${(img.title || '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</h3>
                            </div>
                        </div>
                    `).join('');
                    animateOnScroll();
                }
            })
            .catch(() => {
                // keep static content if present
            });
    }
}); 