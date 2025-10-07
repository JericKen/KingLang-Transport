<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../controllers/client/AuthController.php';
require_once __DIR__ . '/../../../config/settings.php';

if (is_client_authenticated()) {
    header("Location: /home/booking-requests");
    exit();
}

$company_contact = get_setting('company_contact', '0917-882-2727');
$company_email = get_setting('company_email');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="../../../public/css/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../../../public/css/login-signup.css">
    <link rel="stylesheet" href="../../../public/css/slideshow.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Sign Up</title>
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
        /* Password validation styling */
        .password-requirements {
            font-size: 0.75rem;
            line-height: 1.1;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.25rem;
        }
        .requirement {
            display: inline-flex;
            align-items: center;
            margin-right: 0.5rem;
        }
        .requirement i {
            font-size: 0.7rem;
            margin-right: 0.15rem;
        }
        .requirement i.bi-check-circle {
            color: #5db434 !important;
        }
    </style>
</head>
<body>
    <div class="header d-flex justify-content-between align-items-center px-4 border">
        <div class="logo">
            <img src="../../../public/images/logo.png" alt="">
        </div>
        <div class="d-flex gap-4 user-actions">
            <a href="avbar">
            <a href="/home">Home</a>
            <a href="#">About</a>
        </div>
        <div class="d-flex gap-2">
            <a href="/home/login" class="btn btn-outline-success btn-sm ">Log In</a>
            <a href="/home/signup" class="btn btn-success btn-sm">Sign up</a>
        </div>
    </div>

    <div class="content container-fluid d-flex p-0 m-0">
        <div class="form-container d-flex flex-column justify-content-center px-xl-3">
            <form action="" method="" id="signupForm" class="d-flex flex-column px-xl-4 mx-xl-4 px-md-3 mx-md-3 px-sm-1 mx-sm-1">
                <div class="mb-3">
                    <p class="welcome h3 text-success">Create an account</p>
                    <p class="sub-message text-warning">Already have an account? <a href="/home/login" class="link-warning link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Log In</a></p>
                    <p class="signup-text text-success"></p>
                    <p class="signup-error-text text-danger"></p>
                </div>
                <div class="row mb-3 g-2">
                    <div class="col">
                        <label for="firstName" class="form-label text-secondary">First Name</label>
                        <input type="text" name="firstName" id="firstName" class="form-control"> 
                    </div>
                    <div class="col">
                        <label for="lastName" class="form-label text-secondary">Last Name</label>
                        <input type="text" name="lastName" id="lastName" class="form-control">    
                    </div>   
                </div>
                <div class="mb-3">
                    <label for="companyName" class="form-label text-secondary">Company Name (Optional)</label>
                    <input type="text" name="companyName" id="companyName" class="form-control" placeholder="Your company or organization name">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label text-secondary">Email</label>
                    <input type="email" name="email" id="email" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="contactNumber" class="form-label text-secondary">Contact Number</label>
                    <input type="text" name="contactNumber" id="contactNumber" class="form-control" placeholder="+63 912 345 6789   " maxlength="16">
                    <small id="contactNumberHelp" class="form-text text-muted">Format: +63 XXX XXX XXXX</small>
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label text-secondary">Create password</label>
                    <div class="password-container">
                        <input type="password" name="new_password" id="password" class="form-control">
                        <span class="password-toggle" onclick="togglePasswordVisibility('password', 'togglePassword1')">
                            <i class="bi bi-eye" id="togglePassword1"></i>
                        </span>
                    </div>
                    <div id="passwordRequirements" class="password-requirements">
                        <small class="requirement" id="length-req"><i class="bi bi-x-circle text-danger"></i> 8+ chars</small>
                        <small class="requirement" id="uppercase-req"><i class="bi bi-x-circle text-danger"></i> 1 uppercase</small>
                        <small class="requirement" id="lowercase-req"><i class="bi bi-x-circle text-danger"></i> 1 lowercase</small>
                        <small class="requirement" id="number-req"><i class="bi bi-x-circle text-danger"></i> 1 number</small>
                        <small class="requirement" id="special-req"><i class="bi bi-x-circle text-danger"></i> 1 special</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label text-secondary">Confirm password</label>
                    <div class="password-container">
                        <input type="password" name="confirm_password" id="confirmPassword" class="form-control">
                        <span class="password-toggle" onclick="togglePasswordVisibility('confirmPassword', 'togglePassword2')">
                            <i class="bi bi-eye" id="togglePassword2"></i>
                        </span>
                    </div>
                    <small id="passwordMatch" class="form-text"></small>
                </div>
                <div class="mb-2">
                    <p class="sub-message small">By creating an account, you agree to our <a href="#" class="link-body-emphasis link-offset-2 link-underline-opacity-25 link-underline-opacity-75-hover">Terms of Use</a> and <a href="#" class="link-body-emphasis link-offset-2 link-underline-opacity-25 link-underline-opacity-75-hover">Privacy Policy</a></p>
                </div>
                <div class="button-message">
                    <button type="submit" name="signup" class="btn btn-success text-white w-100 rounded-pill">Create an account</button> 
                </div>
            </form>  
        </div>
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
                                    <span>📞 0917 882 2727 | 0933 862 4323</span>
                                </a>
                                <a href="mailto:bsmillamina@yahoo.com" class="contact-item">
                                    <span>✉️ bsmillamina@yahoo.com</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="../../../public/js/page-transition.js"></script>
    <script src="../../../public/js/client/signup.js"></script>
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
        
        function togglePasswordVisibility(inputId, toggleId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(toggleId);
            
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

        // Phone number formatting and validation
            document.addEventListener('DOMContentLoaded', function() {
                const contactNumberInput = document.getElementById('contactNumber');
                const contactNumberHelp = document.getElementById('contactNumberHelp');

                contactNumberInput.addEventListener('input', function () {
                    let value = this.value.replace(/\D/g, ''); // remove non-digits

                    // Convert "09" to "639"
                    if (value.startsWith('09')) {
                        value = '63' + value.substring(1);
                    }

                    // If it starts with 9 and has enough digits, assume mobile
                    if (value.length >= 10 && value.startsWith('9')) {
                        value = '63' + value;
                    }

                    // Validation: must start with 639
                    if (value.length >= 3 && !value.startsWith('639')) {
                        contactNumberHelp.classList.remove('text-muted');
                        contactNumberHelp.classList.add('text-danger');
                        contactNumberHelp.textContent = 'Phone number must start with 09 or 639';
                    } else {
                        contactNumberHelp.classList.remove('text-danger');
                        contactNumberHelp.classList.add('text-muted');
                        contactNumberHelp.textContent = 'Format: +63 917 123 4567';
                    }

                    // Format display: +63 XXX XXX XXXX
                    if (value.startsWith('639')) {
                        const part1 = value.substring(2, 5);
                        const part2 = value.substring(5, 8);
                        const part3 = value.substring(8, 12);

                        let formatted = '+63';
                        if (part1) formatted += ' ' + part1;
                        if (part2) formatted += ' ' + part2;
                        if (part3) formatted += ' ' + part3;

                        this.value = formatted.trim();
                    } else {
                        this.value = value;
                    }
                });
            
            // Validate on form submission
            const signupForm = document.getElementById('signupForm');
            signupForm.addEventListener('submit', function (e) {
                const rawValue = contactNumberInput.value;
                const digitsOnly = rawValue.replace(/\D/g, '');

                let isValid = false;

                // Accept either:
                // - 11 digits starting with '09'
                // - 12 digits starting with '639'
                if (
                    (digitsOnly.length === 11 && digitsOnly.startsWith('09')) ||
                    (digitsOnly.length === 12 && digitsOnly.startsWith('639'))
                ) {
                    isValid = true;
                }

                if (!isValid && digitsOnly.length > 0) {
                    e.preventDefault();
                    contactNumberHelp.classList.remove('text-muted');
                    contactNumberHelp.classList.add('text-danger');
                    contactNumberHelp.textContent =
                        'Invalid phone number. Must be 11 digits starting with 09, or 12 digits starting with 639';
                    return false;
                }
            });
        });

        // Password validation
        document.addEventListener('DOMContentLoaded', function() {
            // Password validation elements
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const passwordMatchMsg = document.getElementById('passwordMatch');
            
            // Password requirement elements
            const lengthReq = document.getElementById('length-req');
            const uppercaseReq = document.getElementById('uppercase-req');
            const lowercaseReq = document.getElementById('lowercase-req');
            const numberReq = document.getElementById('number-req');
            const specialReq = document.getElementById('special-req');
            
            // Password validation
            passwordInput.addEventListener('input', function() {
                const value = this.value;
                
                // Check length (at least 8 characters)
                if (value.length >= 8) {
                    updateRequirement(lengthReq, true);
                } else {
                    updateRequirement(lengthReq, false);
                }
                
                // Check uppercase letter
                if (/[A-Z]/.test(value)) {
                    updateRequirement(uppercaseReq, true);
                } else {
                    updateRequirement(uppercaseReq, false);
                }
                
                // Check lowercase letter
                if (/[a-z]/.test(value)) {
                    updateRequirement(lowercaseReq, true);
                } else {
                    updateRequirement(lowercaseReq, false);
                }
                
                // Check number
                if (/[0-9]/.test(value)) {
                    updateRequirement(numberReq, true);
                } else {
                    updateRequirement(numberReq, false);
                }
                
                // Check special character
                if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value)) {
                    updateRequirement(specialReq, true);
                } else {
                    updateRequirement(specialReq, false);
                }
                
                // Check if passwords match
                checkPasswordsMatch();
            });
            
            // Check if passwords match
            confirmPasswordInput.addEventListener('input', checkPasswordsMatch);
            
            function checkPasswordsMatch() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (confirmPassword === '') {
                    passwordMatchMsg.textContent = '';
                    passwordMatchMsg.className = 'form-text';
                } else if (password === confirmPassword) {
                    passwordMatchMsg.textContent = 'Passwords match';
                    passwordMatchMsg.className = 'form-text text-success';
                } else {
                    passwordMatchMsg.textContent = 'Passwords do not match';
                    passwordMatchMsg.className = 'form-text text-danger';
                }
            }
            
            function updateRequirement(element, isValid) {
                if (isValid) {
                    element.querySelector('i').classList.remove('bi-x-circle', 'text-danger');
                    element.querySelector('i').classList.add('bi-check-circle', 'text-success');
                } else {
                    element.querySelector('i').classList.remove('bi-check-circle', 'text-success');
                    element.querySelector('i').classList.add('bi-x-circle', 'text-danger');
                }
            }
            
            // Validate on form submission
            const signupForm = document.getElementById('signupForm');
            signupForm.addEventListener('submit', function(e) {
                // Check if all password requirements are met
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                const lengthValid = password.length >= 8;
                const uppercaseValid = /[A-Z]/.test(password);
                const lowercaseValid = /[a-z]/.test(password);
                const numberValid = /[0-9]/.test(password);
                const specialValid = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
                const passwordsMatch = password === confirmPassword;
                
                if (!(lengthValid && uppercaseValid && lowercaseValid && numberValid && specialValid)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Password Requirements',
                        text: 'Please meet all password requirements before submitting.'
                    });
                    return false;
                }
                
                if (!passwordsMatch) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Passwords Do Not Match',
                        text: 'Please make sure your passwords match.'
                    });
                    return false;
                }
            });
        });
    </script>
</body>
</html>