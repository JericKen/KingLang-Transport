// Dynamic Slideshow Loader
// This script loads slideshow images dynamically from the database

class DynamicSlideshow {
    constructor(containerSelector, options = {}) {
        this.container = document.querySelector(containerSelector);
        this.options = {
            autoPlay: options.autoPlay !== false,
            interval: options.interval || 5000,
            showText: options.showText !== false,
            showContactInfo: options.showContactInfo !== false,
            ...options
        };
        
        this.images = [];
        this.currentIndex = 0;
        this.timer = null;
        this.isTransitioning = false;
        
        this.init();
    }
    
    async init() {
        if (!this.container) {
            console.error('Slideshow container not found');
            return;
        }
        
        await this.loadImages();
        this.renderSlideshow();
        
        if (this.options.autoPlay && this.images.length > 1) {
            this.startAutoPlay();
        }
    }
    
    async loadImages() {
        try {
            const response = await fetch('/api/slideshow/images');
            const data = await response.json();
            
            if (data.success && data.images) {
                this.images = data.images;
            } else {
                console.warn('No slideshow images found or failed to load');
                // Fallback to default images if API fails
                this.images = this.getDefaultImages();
            }
        } catch (error) {
            console.error('Error loading slideshow images:', error);
            // Fallback to default images if API fails
            this.images = this.getDefaultImages();
        }
    }
    
    getDefaultImages() {
        // Fallback default images if API fails
        return [
            {
                filename: 'slide2.jpg',
                title: 'Experience Comfort and Luxury',
                description: 'Luxury bus transportation for your comfort'
            },
            {
                filename: 'slide3.jpg',
                title: 'Travel with Style and Safety',
                description: 'Safe and stylish travel experience'
            },
            {
                filename: 'slide4.jpg',
                title: 'Your On-The-Go Tourist Bus Rental',
                description: 'Professional tourist bus rental services'
            }
        ];
    }
    
    renderSlideshow() {
        if (this.images.length === 0) {
            this.container.innerHTML = '<p class="text-center text-muted">No slideshow images available</p>';
            return;
        }
        
        this.container.innerHTML = this.images.map((image, index) => {
            const isActive = index === 0 ? 'active-slide' : '';
            const visibility = index === 0 ? 'visible' : 'hidden';
            
            return `
                <div class="slideshow-slide ${isActive}" style="visibility: ${visibility};">
                    <img src="public/images/slideshow/${image.filename}" alt="${image.title || 'Slideshow Image'}">
                    ${this.options.showText ? `
                        <div class="slideshow-text">${image.title || ''}</div>
                    ` : ''}
                    ${this.options.showContactInfo ? `
                        <div class="slideshow-contact-info">
                            <div class="slideshow-contact-details">
                                <a href="tel:0917-8822727" class="contact-item">
                                    <span>📞 0917 882 2727 | 0933 862 4323</span>
                                </a>
                                <a href="mailto:bsmillamina@yahoo.com" class="contact-item">
                                    <span>✉️ bsmillamina@yahoo.com</span>
                                </a>
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;
        }).join('');
        
        // Add navigation dots if multiple images
        if (this.images.length > 1) {
            this.addNavigationDots();
        }
    }
    
    addNavigationDots() {
        const dotsContainer = document.createElement('div');
        dotsContainer.className = 'slideshow-dots';
        dotsContainer.style.cssText = `
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        `;
        
        this.images.forEach((_, index) => {
            const dot = document.createElement('button');
            dot.className = `slideshow-dot ${index === 0 ? 'active' : ''}`;
            dot.style.cssText = `
                width: 12px;
                height: 12px;
                border-radius: 50%;
                border: 2px solid white;
                background: ${index === 0 ? 'white' : 'transparent'};
                cursor: pointer;
                transition: all 0.3s ease;
            `;
            
            dot.addEventListener('click', () => {
                this.goToSlide(index);
            });
            
            dotsContainer.appendChild(dot);
        });
        
        this.container.appendChild(dotsContainer);
    }
    
    goToSlide(index) {
        if (this.isTransitioning || index === this.currentIndex) return;
        
        this.isTransitioning = true;
        this.stopAutoPlay();
        
        const currentSlide = this.container.querySelector('.active-slide');
        const targetSlide = this.container.querySelectorAll('.slideshow-slide')[index];
        const dots = this.container.querySelectorAll('.slideshow-dot');
        
        if (!currentSlide || !targetSlide) return;
        
        // Update dots
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });
        
        // Transition to new slide
        currentSlide.classList.add('slide-leave');
        targetSlide.style.visibility = 'visible';
        targetSlide.classList.add('slide-enter');
        
        // Force reflow
        void targetSlide.offsetWidth;
        
        targetSlide.classList.add('active-slide');
        targetSlide.classList.remove('slide-enter');
        
        setTimeout(() => {
            currentSlide.classList.remove('active-slide');
            this.currentIndex = index;
        }, 50);
        
        setTimeout(() => {
            currentSlide.classList.remove('slide-leave');
            currentSlide.style.visibility = 'hidden';
            this.isTransitioning = false;
            
            // Restart auto-play
            if (this.options.autoPlay) {
                this.startAutoPlay();
            }
        }, 1500);
    }
    
    nextSlide() {
        const nextIndex = (this.currentIndex + 1) % this.images.length;
        this.goToSlide(nextIndex);
    }
    
    previousSlide() {
        const prevIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
        this.goToSlide(prevIndex);
    }
    
    startAutoPlay() {
        if (this.timer) {
            clearTimeout(this.timer);
        }
        
        this.timer = setTimeout(() => {
            this.nextSlide();
        }, this.options.interval);
    }
    
    stopAutoPlay() {
        if (this.timer) {
            clearTimeout(this.timer);
            this.timer = null;
        }
    }
    
    // Public method to refresh slideshow
    async refresh() {
        await this.loadImages();
        this.renderSlideshow();
        
        if (this.options.autoPlay && this.images.length > 1) {
            this.startAutoPlay();
        }
    }
}

// Initialize slideshow when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize slideshow for pages that have slideshow containers
    const slideshowContainers = document.querySelectorAll('.slideshow-container');
    
    slideshowContainers.forEach(container => {
        // Check if this is a login/signup page or home page
        const isAuthPage = window.location.pathname.includes('/login') || window.location.pathname.includes('/signup');
        const isHomePage = window.location.pathname === '/home' || window.location.pathname === '/';
        
        if (isAuthPage) {
            // For login/signup pages, show text and contact info
            new DynamicSlideshow(container, {
                showText: true,
                showContactInfo: true,
                autoPlay: true,
                interval: 5000
            });
        } else if (isHomePage) {
            // For home page, show text but no contact info
            new DynamicSlideshow(container, {
                showText: true,
                showContactInfo: false,
                autoPlay: true,
                interval: 6000
            });
        }
    });
});
