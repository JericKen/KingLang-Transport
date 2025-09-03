<?php
// Debug session
error_log("Session data: " . json_encode($_SESSION ?? []));

// Redirect if already logged in
if (is_client_authenticated()) {
    header("Location: /home/booking-requests");
    exit();
}

// Include Google OAuth configuration
require_once __DIR__ . '/../../../config/google_auth.php';
require_once __DIR__ . '/../../../config/settings.php';

$company_contact = get_setting('company_contact', '0917-882-2727');
$company_email = get_setting('company_email');
?>
<!DOCTYPE html>
<html lang="en" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google" content="notranslate">
    <meta http-equiv="Accept-Language" content="en">
    <meta name="language" content="en">
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="../../../public/css/login-signup.css">
    <link rel="stylesheet" href="../../../public/css/slideshow.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://accounts.google.com/gsi/client?hl=en" async></script>
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
        /* Google Sign-In button styling */
        .google-btn-wrapper {
            width: 100%;
            display: block;
            margin-bottom: 10px;
        }
        .g_id_signin {
            width: 100% !important;
            display: flex !important;
            justify-content: center !important;
        }
        .g_id_signin > div,
        .g_id_signin iframe {
            width: 100% !important;
            max-width: 100% !important;
        }
        /* Force English language for Google button */
        .g_id_signin * {
            font-family: 'Roboto', sans-serif !important;
        }
        /* Force English text for Google Sign-In */
        .g_id_signin iframe {
            font-family: 'Roboto', sans-serif !important;
        }
        /* Override any language-specific styling */
        [data-lang="en"] .g_id_signin,
        [lang="en"] .g_id_signin {
            font-family: 'Roboto', sans-serif !important;
        }
        .or-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 15px 0 10px;
        }
        .or-divider::before, .or-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #ddd;
        }
        .or-divider::before {
            margin-right: .5em;
        }
        .or-divider::after {
            margin-left: .5em;
        }
    </style>
</head>
<body>
   
    <div class="header d-flex justify-content-between align-items-center px-4 border">
        <div class="logo">
            <img src="../../../public/images/logo.png" alt="">
        </div>
        <div class="d-flex gap-4 user-actions">
            <a href="/home">Home</a>
            <a href="#">About</a>
        </div>
        <div class="d-flex gap-2">
            <a href="/home/login" class="btn btn-success btn-sm">Log In</a>
            <a href="/home/signup" class="btn btn-outline-success btn-sm">Sign Up</a>
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
                    <p class="welcome h3 text-success">Welcome Back!</p>
                    <p class="sub-message text-warning">Please login to continue to your account.</p>
                    <p class="login-message text-danger"></p>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label text-secondary">Email</label>
                    <input type="email" name="username" value="" id="email" class="form-control" required autocomplete="username">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label text-secondary">Password</label>
                    <div class="password-container">
                        <input type="password" name="password" id="password" class="form-control" required autocomplete="current-password">
                        <span class="password-toggle" onclick="togglePasswordVisibility()">
                            <i class="bi bi-eye" id="togglePassword"></i>
                        </span>
                    </div>
                    <a href="/fogot-password" class="link-body-emphasis link-offset-2 link-underline-opacity-25 link-underline-opacity-75-hover small">Forgot password?</a>
                </div>
                <div class="login-button mb-3 d-flex gap-2 flex-column">
                    <button type="submit" name="login" class="btn btn-success w-100 text-white fw-bold rounded p-2">Log In</button>
                    
                    <div class="or-divider">or</div>
                    
                    <div id="g_id_onload"
                         data-client_id="<?php echo GOOGLE_CLIENT_ID; ?>"
                         data-context="signin"
                         data-ux_mode="popup"
                         data-callback="handleGoogleSignIn"
                         data-auto_prompt="false"
                         data-locale="en"
                         data-lang="en"
                         data-hl="en">
                    </div>

                    <div class="google-btn-wrapper rounded-pill">
                        <div id="google-signin-button"
                             class="g_id_signin"
                             data-type="standard"
                             data-shape="rectangular"
                             data-theme="outline"
                             data-text="signin_with"
                             data-size="large"
                             data-logo_alignment="left"
                             data-width="100%"
                             data-lang="en"
                             data-hl="en"
                             lang="en">
                        </div>
                    </div>
                    
                    <p class="small mb-0">Need an account? <a href="/home/signup" class="link-body-emphasis link-offset-2 link-underline-opacity-25 link-underline-opacity-75-hover">Create one</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script src="../../../public/js/page-transition.js"></script>
    <script src="../../../public/js/client/login.js"></script>
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
        
        // Set Google button language to English
        document.documentElement.lang = 'en';
        
        // Initialize Google Sign-In with forced English
        function initializeGoogleSignIn() {
            if (window.google && window.google.accounts) {
                window.google.accounts.id.initialize({
                    client_id: '<?php echo GOOGLE_CLIENT_ID; ?>',
                    callback: handleGoogleSignIn,
                    auto_select: false,
                    cancel_on_tap_outside: true,
                    locale: 'en',
                    hl: 'en'
                });
                
                // Render the Google Sign-In button with English settings
                const buttonElement = document.getElementById('google-signin-button');
                if (buttonElement) {
                    window.google.accounts.id.renderButton(buttonElement, {
                        type: 'standard',
                        theme: 'outline',
                        size: 'large',
                        text: 'signin_with',
                        shape: 'rectangular',
                        logo_alignment: 'left',
                        width: '100%',
                        locale: 'en',
                        hl: 'en'
                    });
                }
            }
        }
        
        // Wait for Google API to load
        window.onload = function() {
            // Set page language to English
            document.documentElement.lang = 'en';
            document.documentElement.setAttribute('data-lang', 'en');
            
            // Check if Google API is already loaded
            if (window.google && window.google.accounts) {
                initializeGoogleSignIn();
            } else {
                // Wait for Google API to load
                let checkCount = 0;
                const checkGoogleAPI = setInterval(function() {
                    checkCount++;
                    if (window.google && window.google.accounts) {
                        clearInterval(checkGoogleAPI);
                        initializeGoogleSignIn();
                    } else if (checkCount > 50) { // 5 seconds timeout
                        clearInterval(checkGoogleAPI);
                        console.log('Google API failed to load');
                    }
                }, 100);
            }
        };
        
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
        
        function handleGoogleSignIn(response) {
            // Send the ID token to the server
            const credential = response.credential;
            
            fetch('/client/google-login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    credential: credential
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.body.style.transition = 'opacity 0.5s ease';
                    document.body.style.opacity = '0';
                    
                    setTimeout(() => {
                        window.location.href = data.redirect || '/home/booking-requests';
                    }, 300);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: data.message || 'Google authentication failed. Please try again.',
                        timer: 1500,
                        timerProgressBar: true,
                        confirmButtonColor: '#28a745'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Something went wrong',
                    text: 'Please try again later.',
                    confirmButtonColor: '#28a745'
                });
            });
        }
    </script>
</body>
</html>