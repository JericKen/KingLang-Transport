document.addEventListener('DOMContentLoaded', function() {
    const testimonialForm = document.getElementById('testimonialForm');
    const modal = document.getElementById('messageModal');
    const closeModal = document.querySelector('.close');
    const titleInput = document.getElementById('title');
    const contentTextarea = document.getElementById('contentText');
    const ratingInputs = document.querySelectorAll('input[name="rating"]');
    const ratingText = document.querySelector('.rating-text');

    // Rating text mapping
    const ratingTexts = {
        1: 'Poor - Very unsatisfied',
        2: 'Fair - Somewhat unsatisfied', 
        3: 'Good - Neutral experience',
        4: 'Very Good - Satisfied',
        5: 'Excellent - Very satisfied'
    };

    // Initialize character counters
    updateCharCount(titleInput);
    updateCharCount(contentTextarea);

    // Character count update
    function updateCharCount(element) {
        const charCountElement = element.parentNode.querySelector('.char-count');
        if (charCountElement) {
            const count = element.value.length;
            const maxLength = element.getAttribute('maxlength');
            charCountElement.textContent = `${count} / ${maxLength}`;
            
            // Change color based on usage
            const percentage = (count / maxLength) * 100;
            if (percentage >= 90) {
                charCountElement.style.color = '#dc3545';
            } else if (percentage >= 70) {
                charCountElement.style.color = '#ffc107';
            } else {
                charCountElement.style.color = '#666';
            }
        }
    }

    // Add event listeners for character counting
    if (titleInput) {
        titleInput.addEventListener('input', () => updateCharCount(titleInput));
    }

    if (contentTextarea) {
        contentTextarea.addEventListener('input', () => updateCharCount(contentTextarea));
    }

    // Rating change handler
    ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (ratingText) {
                ratingText.textContent = ratingTexts[this.value] || '';
            }
        });
    });

    // Form submission handler
    if (testimonialForm) {
        testimonialForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;

            // Disable form and show loading state
            setFormLoading(true, submitButton, originalButtonText);

            // Get form data
            const formData = new FormData(this);
            const data = {
                booking_id: formData.get('booking_id'),
                rating: formData.get('rating'),
                title: formData.get('title'),
                content: formData.get('content')
            };

            // Validate form data
            if (!validateForm(data)) {
                setFormLoading(false, submitButton, originalButtonText);
                return;
            }

            try {
                const response = await fetch('/home/testimonials/submit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showModal('success', 'Review Submitted!', result.message);
                    testimonialForm.reset();
                    updateCharCount(titleInput);
                    updateCharCount(contentTextarea);
                    ratingText.textContent = '';
                    
                    // Refresh page after successful submission to show updated testimonials
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showModal('error', 'Submission Failed', result.message);
                }
            } catch (error) {
                console.error('Error submitting testimonial:', error);
                showModal('error', 'Error', 'An error occurred while submitting your review. Please try again.');
            } finally {
                setFormLoading(false, submitButton, originalButtonText);
            }
        });
    }

    // Form validation
    function validateForm(data) {
        if (!data.booking_id) {
            showModal('error', 'Validation Error', 'Please select a trip to review.');
            return false;
        }

        if (!data.rating) {
            showModal('error', 'Validation Error', 'Please select a rating.');
            return false;
        }

        if (!data.title || data.title.trim().length === 0) {
            showModal('error', 'Validation Error', 'Please enter a review title.');
            return false;
        }

        if (data.title.length > 100) {
            showModal('error', 'Validation Error', 'Review title must be 100 characters or less.');
            return false;
        }

        if (!data.content || data.content.trim().length === 0) {
            showModal('error', 'Validation Error', 'Please enter your review content.');
            return false;
        }

        if (data.content.length > 1000) {
            showModal('error', 'Validation Error', 'Review content must be 1000 characters or less.');
            return false;
        }

        return true;
    }

    // Set form loading state
    function setFormLoading(isLoading, button, originalText) {
        if (isLoading) {
            button.disabled = true;
            button.classList.add('loading');
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            testimonialForm.classList.add('loading');
        } else {
            button.disabled = false;
            button.classList.remove('loading');
            button.innerHTML = originalText;
            testimonialForm.classList.remove('loading');
        }
    }

    // Show modal with message
    function showModal(type, title, message) {
        const modalIcon = document.getElementById('modalIcon');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');

        // Set icon based on type
        if (type === 'success') {
            modalIcon.innerHTML = '<i class="fas fa-check-circle success-icon"></i>';
        } else {
            modalIcon.innerHTML = '<i class="fas fa-exclamation-circle error-icon"></i>';
        }

        modalTitle.textContent = title;
        modalMessage.textContent = message;
        modal.style.display = 'block';
    }

    // Close modal handlers
    if (closeModal) {
        closeModal.onclick = function() {
            modal.style.display = 'none';
        };
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    };

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.style.display === 'block') {
            modal.style.display = 'none';
        }
    });

    // Star rating hover effects
    const stars = document.querySelectorAll('.rating-input .star');
    stars.forEach((star, index) => {
        star.addEventListener('mouseenter', function() {
            // Highlight stars up to the hovered one
            for (let i = stars.length - 1; i >= index; i--) {
                stars[i].style.color = '#f8b325';
            }
        });

        star.addEventListener('mouseleave', function() {
            // Reset to original state based on selection
            const checkedRating = document.querySelector('input[name="rating"]:checked');
            stars.forEach((s, i) => {
                if (checkedRating && i >= stars.length - checkedRating.value) {
                    s.style.color = '#f8b325';
                } else {
                    s.style.color = '#ddd';
                }
            });
        });
    });
});