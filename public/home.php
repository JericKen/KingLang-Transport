<?php
if (is_client_authenticated()) {
    header("Location: /home/booking-requests");
    exit();
} 

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/visitor-chat-widget.php';

$site_name = get_setting('site_name', 'KingLang Transport');
$company_contact = get_setting('company_contact', '0917-882-2727');
$comapny_address = get_setting('company_address', '295 Manuel L. Quezon Ave, Lower Bicutan, Taguig City Lower Bicutan, Philippines');
$company_email = get_setting('company_email');  
$company_facebook_link = get_setting('company_facebook_link', 'https://www.facebook.com/KingLangTransport/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $site_name ?> - Go far. Go together.</title>
    <link rel="icon" href="public/images/main-logo-icon.png" type="">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Original+Surfer&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="public/css/home.css">
    <link rel="stylesheet" href="public/css/chat-widget.css">
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="/home">
                        <img src="public/images/hero-images/logo.png" alt="KingLang Transport">
                    </a>
                </div>
                <nav class="navigation">
                    <ul>
                        <li><a href="/home" class="active"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="#destinations"><i class="fas fa-bus"></i> Trips</a></li>
                        <li><a href="#find-us"><i class="fas fa-map-marker-alt"></i> Find us</a></li>
                    </ul>
                </nav>
                <button class="mobile-nav-toggle" aria-label="Toggle navigation" aria-expanded="false">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <div class="user-actions">
                    <a href="/home/login" class="btn btn-secondary">Log In</a>
                    <a href="/home/signup" class="btn btn-primary">Sign Up</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text animate-on-scroll">
                    <h1>Go far. Go together.<br>Go with <span class="highlight">KingLang</span></h1>
                    <p>Trusted since 2016 for smooth, safe, and unforgettable </br> group travel — wherever you're headed, we'll get you </br> there in comfort.</p>
                    <a href="#" class="btn btn-primary btn-large">Book now</a>
                </div>
                <div class="hero-image animate-on-scroll">
                    <img src="public/images/hero-images/hero-bus.png" alt="KingLang Bus">
                </div>
            </div>
            <div class="accreditations animate-on-scroll">
                <img src="public/images/permit-logos/LTFRB_Seal.svg.png" alt="LTFRB">
                <img src="public/images/permit-logos/Land_Transportation_Office.svg.png" alt="LTO">
                <!-- <img src="public/images/permit-logos/Department_of_Tourism_(DOT).svg.png" alt="DOT"> -->
                <img src="public/images/permit-logos/Department_of_Transportation_(Philippines).svg.png" alt="DOTr">
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <h2 class="animate-on-scroll">Our service, their words.</h2>
            <div class="testimonial-slider" id="testimonialSlider">
                <!-- Dynamic testimonials will be loaded here -->
            </div>
            <div class="testimonial-summary animate-on-scroll">
                <p id="testimonialSummary">Loading reviews...</p>
                <div class="testimonial-controls">
                    <button class="prev-btn"><i class="fas fa-chevron-left"></i></button>
                    <button class="next-btn"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </section>

    <!-- Destinations Section -->
    <section class="destinations" id="destinations">
        <div class="container">
            <h2 class="animate-on-scroll">Plan your next adventure with KingLang Transport</h2>
            <div class="destination-grid">
                <div class="destination-card animate-on-scroll">
                    <div class="destination-image">
                        <img src="public/images/past-trips/anawagin-cove.jpg" alt="Anawagin Cove">
                        <h3>Anawagin Cove</h3>
                    </div>
                </div>
                <div class="destination-card animate-on-scroll">
                    <div class="destination-image">
                        <img src="public/images/past-trips/bataan.jpg" alt="Bataan">
                        <h3>Bataan</h3>
                    </div>
                </div>
                <div class="destination-card animate-on-scroll">
                    <div class="destination-image">
                        <img src="public/images/past-trips/bataan.jpg" alt="Bauang">
                        <h3>Bauang</h3>
                    </div>
                </div>
                <div class="destination-card animate-on-scroll">
                    <div class="destination-image">
                        <img src="public/images/past-trips/bauang.jpg" alt="Bauang">
                        <h3>Bauang</h3>
                    </div>
                </div>
                <div class="destination-card animate-on-scroll">
                    <div class="destination-image">
                        <img src="public/images/past-trips/calatagan-batangas.jpg" alt="Calatagan Batangas">
                        <h3>Calatagan Batangas</h3>
                    </div>
                </div>
                <div class="destination-card animate-on-scroll">
                    <div class="destination-image">
                        <img src="public/images/past-trips/laiya.jpg" alt="Laiya">
                        <h3>Laiya</h3>
                    </div>
                </div>
                <div class="destination-card animate-on-scroll">
                    <div class="destination-image">
                        <img src="public/images/past-trips/nasugbu.jpeg" alt="Nasugbu">
                        <h3>Nasugbu</h3>
                    </div>
                </div>
                <div class="destination-card animate-on-scroll">
                    <div class="destination-image">
                        <img src="public/images/past-trips/olongapo.jpg" alt="Olongapo">
                        <h3>Olongapo</h3>
                    </div>
                </div>
            </div>
            <div class="see-all-container animate-on-scroll">
                <a href="#" class="btn btn-secondary">See All Trips</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="find-us">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="public/images/hero-images/logo.png" alt="KingLang Transport">
                </div>
                <div class="footer-links">
                    <div class="footer-section">
                        <h3 class="account">SOCIALS</h3>
                        <div class="social-icons">
                            <a href="<?= $company_facebook_link ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        </div>
                    </div>
                    <div class="footer-section">
                        <h3 class="account">CONTACT US</h3>
                        <ul class="footers">
                            <li><?= $company_contact ?></li>
                            <li><?= $company_email ?></li>
                        </ul>
                    </div>
                    <div class="footer-section">
                        <h3 class="account">FIND US</h3>
                        <p class="footers"><?= $comapny_address ?></p>
                        <p class="footer-hours">Everyday from 8:00 am to 11:00 pm</p>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 — Copyright</p>
                <p><a href="#">Privacy</a></p>
            </div>
        </div>
     </footer>
    <script src="public/js/home.js"></script>
    <!-- <script src="public/js/chat-widget-visitor.js"></script> -->
    <script>


        // Initialize chat widget for visitors
        document.addEventListener('DOMContentLoaded', function() {
            // Set userLoggedIn to false for visitors
            window.userLoggedIn = false;
            
            // The visitor chat widget will initialize automatically
        });
    </script>
</body>
</html> 