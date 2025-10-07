<?php
// Debug session
// error_log("Admin login session data: " . json_encode($_SESSION ?? []));

// Redirect if already logged in
if (is_admin_authenticated()) {
    header("Location: /admin/dashboard");
    exit();
}

require_once __DIR__ . '/../../../config/settings.php';

$company_contact = get_setting('company_contact', '0917-882-2727');
$company_email = get_setting('company_email');
?>

<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="../../../public/css/login-signup.css">
    <link rel="stylesheet" href="../../../public/css/slideshow.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Log In</title>
    <style>
        /* More compact form styling */
        .form-container .form-label {
            margin-bottom: 0.25rem;
        }
        .form-container .mb-3 {
            margin-bottom: 0.75rem !important;
        }
        .welcome {
            margin-bottom: 0.25rem;
        }
        .sub-message {
            margin-bottom: 0.5rem;
        }
        /* Password field styling */
        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 10;
        }
    </style>
</head>
<body>

    <div class="header d-flex justify-content-between align-items-center px-4 border">
        <div class="logo">
            <img src="../../../public/images/logo.png" alt="">
        </div>
        <div class="d-flex gap-3 user-actions">
            <a href="/home" class="text-dark">Home</a>
            <a href="#" class="text-dark">About</a>
        </div>
        <div class="">
            <a href="/admin/login" class="btn btn-success btn-sm">Log In</a>
        </div>
    </div>

    <div class="content container-fluid p-0 m-0 d-flex flex-wrap">
        <div class="image-container">
            <div class="slideshow-container" id="slideshow-container">
                <?php
                // Load slideshow images directly from PHP
                require_once __DIR__ . '/../../../app/models/admin/SlideshowManagementModel.php';
                $slideshowModel = new SlideshowManagementModel();
                $activeImages = $slideshowModel->getActiveSlideshowImages();
                
                if ($activeImages && count($activeImages) > 0) {
                    foreach ($activeImages as $index => $image) {
                        $isActive = $index === 0 ? 'active-slide' : '';
                        $visibility = $index === 0 ? 'visible' : 'hidden';
                        ?>
                        <div class="slideshow-slide <?= $isActive ?>" style="visibility: <?= $visibility ?>;">
                            <img src="../../../public/images/slideshow/<?= htmlspecialchars($image['filename']) ?>" alt="<?= htmlspecialchars($image['title'] ?? 'Slideshow Image') ?>">
                            <?php if ($image['title']): ?>
                                <div class="slideshow-text"><?= htmlspecialchars($image['title']) ?></div>
                            <?php endif; ?>
                            <div class="slideshow-contact-info">
                                <div class="slideshow-contact-details">
                                    <a href="tel:0917-8822727" class="contact-item">
                                        <span><i class="bi bi-telephone-fill"></i> <?= $company_contact ?></span>
                                    </a>
                                    <a href="mailto:bsmillamina@yahoo.com" class="contact-item">
                                        <span><i class="bi bi-envelope-fill"></i> <?= $company_email ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    // Fallback to default images if no database images
                    ?>
                    <div class="slideshow-slide active-slide">
                        <img src="../../../public/images/slideshow/slide2.jpg" alt="Experience Comfort and Luxury">
                        <div class="slideshow-text">EXPERIENCE COMFORT AND LUXURY</div>
                        <div class="slideshow-contact-info">
                            <div class="slideshow-contact-details">
                                <a href="tel:0917-8822727" class="contact-item">
                                    <span><i class="bi bi-telephone-fill"></i> <?= $company_contact ?></span>
                                </a>
                                <a href="mailto:bsmillamina@yahoo.com" class="contact-item">
                                    <span><i class="bi bi-envelope-fill"></i> <?= $company_email ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <div class="form-container d-flex flex-column justify-content-center">
            <form action="" method="" id="loginForm" class="d-flex flex-column p-lg-4 m-lg-4">
                <div class="mb-3">
                    <p class="welcome h3 text-success">Welcome back Admin!</p>
                    <p class="sub-message text-warning">Please login to continue to your account.</p>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label text-secondary">Email Address</label>
                    <input type="email" name="username" value="" id="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label text-secondary">Password</label>
                    <div class="password-container">
                        <input type="password" name="password" id="password" class="form-control" required>
                        <span class="password-toggle" onclick="togglePasswordVisibility()">
                            <i class="bi bi-eye" id="togglePassword"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" name="login" class="btn btn-success w-100 text-white fw-bold rounded-pill p-2">Log In</button>    
                
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="../../../public/js/jquery/jquery-3.6.4.min.js"></script>
    <script src="../../../public/js/admin/login.js"></script>
    <script>
        // Simple slideshow functionality
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slideshow-slide');
        
        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.style.visibility = i === index ? 'visible' : 'hidden';
                slide.classList.toggle('active-slide', i === index);
            });
        }
        
        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }
        
        // Auto-advance slides every 5 seconds
        if (slides.length > 1) {
            setInterval(nextSlide, 5000);
        }
        
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePassword');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>