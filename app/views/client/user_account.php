<?php
require_client_auth();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/../../../public/css/client/user_account.css">
    <title>My Account</title>
</head>
<body>
    <?php include_once __DIR__ . "/../assets/sidebar.php"; ?>

    <div class="content collapsed" id="content">
        <div class="container-fluid py-3 px-3 px-xl-4">
            <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap p-0 m-0">
                <div class="p-0">
                    <h3><i class="bi bi-person-check me-2 text-success"></i>My Account</h3>
                    <p class="text-muted">Manage your profile and account settings</p>
                </div>
                <?php include_once __DIR__ . "/../assets/user_profile.php"; ?>
            </div>
            <hr>

            <div class="container-fluid mt-4 mb-3">
                <div class="row">
                    <div class="col-lg-3 mb-4">
                       
                        <div class="user-account-container">
                            <div class="profile-header">
                                <div class="profile-avatar" id="profileAvatarContainer">
                                    <div id="avatarInitials"></div>
                                    <img id="profileAvatar" src="" alt="" style="display: none;">
                                    <div class="upload-overlay" id="uploadOverlay">
                                        <i class="bi bi-camera"></i> Change
                                    </div>
                                    <button type="button" id="removeProfileImage" class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle" style="display: none; width: 25px; height: 25px; font-size: 10px;">
                                        <i class="bi bi-x"></i>
                                    </button>
                                    <input type="file" id="avatarUpload" accept="image/*" style="display: none;">
                                </div>
                                <div class="profile-info">
                                    <h5 class="mb-1 text-capitalize" id="profileName"><?= $_SESSION["client_name"]; ?></h5>
                                    <p class="mb-0 text-muted" id="profileEmail"><?= $_SESSION["email"]; ?></p>
                                </div>
                            </div>
                            
                           
                            <div class="completion-meter">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small>Profile Completion</small>
                                    <small id="completionPercentage">70%</small>
                                </div>
                                <div class="progress mb-3">
                                    <div class="progress-bar" role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted" id="completionMessage">Complete your profile to improve your experience</small>
                            </div>
                            
                           
                            <div class="nav flex-column nav-pills mt-4" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                <button class="nav-link active mb-2" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile" type="button" role="tab" aria-controls="v-pills-profile" aria-selected="true">
                                    <i class="bi bi-person me-2"></i> Profile Information
                                </button>
                                <button class="nav-link mb-2" id="v-pills-security-tab" data-bs-toggle="pill" data-bs-target="#v-pills-security" type="button" role="tab" aria-controls="v-pills-security" aria-selected="false">
                                    <i class="bi bi-shield-lock me-2"></i> Security
                                </button>
                                <button class="nav-link mb-2 d-none" id="v-pills-bookings-tab" data-bs-toggle="pill" data-bs-target="#v-pills-bookings" type="button" role="tab" aria-controls="v-pills-bookings" aria-selected="false">
                                    <i class="bi bi-journal-check me-2"></i> Booking History
                                </button>
                                <button class="nav-link d-none" id="v-pills-preferences-tab" data-bs-toggle="pill" data-bs-target="#v-pills-preferences" type="button" role="tab" aria-controls="v-pills-preferences" aria-selected="false">
                                    <i class="bi bi-gear me-2"></i> Preferences
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-9">
                        <!-- Content Area -->
                        <div class="user-account-container">
                            <div class="tab-content" id="v-pills-tabContent">
                                <!-- Profile Information Tab -->
                                <div class="tab-pane fade show active" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                                    <h4 class="section-title">Profile Information</h4>
                                    <form id="userForm">
                                        <div class="row mb-3">
                                            <div class="col-md-6 mb-3 mb-md-0">
                                                <label for="firstName" class="form-label">First Name</label>
                                                <input type="text" id="firstName" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="lastName" class="form-label">Last Name</label>
                                                <input type="text" id="lastName" class="form-control" required>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label for="companyName" class="form-label">Company Name (Optional)</label>
                                                <input type="text" id="companyName" class="form-control" placeholder="Your company or organization name">
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6 mb-3 mb-md-0">
                                                <label for="email" class="form-label">Email Address</label>
                                                <input type="email" id="email" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="contactNumber" class="form-label">Phone Number</label>
                                                <input type="text" id="contactNumber" class="form-control" placeholder="+63 9XX XXX XXXX" maxlength="16" required>
                                                <small class="form-text text-muted">Format: +63 9XX XXX XXXX</small>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3 d-none">
                                            <div class="col-md-12">
                                                <label for="address" class="form-label">Home Address</label>
                                                <textarea id="address" class="form-control" rows="2"></textarea>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-success saveBtn">
                                            <span id="saveText">Save Changes</span>
                                            <span id="saveSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                                        </button>
                                        <div id="userMessage" class="mt-3"></div>
                                    </form>
                                </div>
                                
                                <!-- Security Tab -->
                                <div class="tab-pane fade" id="v-pills-security" role="tabpanel" aria-labelledby="v-pills-security-tab">
                                    <h4 class="section-title">Security Settings</h4>
                                    <form id="passwordForm">
                                        <div class="mb-3">
                                            <label for="currentPassword" class="form-label">Current Password</label>
                                            <input type="password" id="currentPassword" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="newPassword" class="form-label">New Password</label>
                                            <div class="input-group">
                                                <input type="password" id="newPassword" class="form-control" required>
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                            <input type="password" id="confirmPassword" class="form-control" required>
                                        </div>
                                        
                                        <ul class="password-requirements list-unstyled">
                                            <li id="length" class="invalid"><i class="bi bi-x-circle me-2"></i>At least 8 characters</li>
                                            <li id="uppercase" class="invalid"><i class="bi bi-x-circle me-2"></i>At least one uppercase letter</li>
                                            <li id="lowercase" class="invalid"><i class="bi bi-x-circle me-2"></i>At least one lowercase letter</li>
                                            <li id="number" class="invalid"><i class="bi bi-x-circle me-2"></i>At least one number</li>
                                            <li id="match" class="invalid"><i class="bi bi-x-circle me-2"></i>Passwords match</li>
                                        </ul>
                                        
                                        <button type="submit" class="btn btn-success mt-3">Update Password</button>
                                        <div id="passwordMessage" class="mt-3"></div>
                                    </form>
                                    
                                    <!-- <hr class="my-4">
                                    
                                    <h5 class="mb-3">Login Sessions</h5> -->
                                    <div class="card mb-0 d-none">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <h6 class="mb-1">Current Session</h6>
                                                    <small class="text-muted">
                                                        <i class="bi bi-laptop me-1"></i> 
                                                        <span id="deviceInfo">Windows • Chrome</span> • 
                                                        <span id="ipAddress">127.0.0.1</span>
                                                    </small>
                                                </div>
                                                <span class="badge bg-success p-2">Active Now</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Booking History Tab -->
                                <div class="tab-pane fade" id="v-pills-bookings" role="tabpanel" aria-labelledby="v-pills-bookings-tab">
                                    <h4 class="section-title">Booking History</h4>
                                    
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="card-stats text-center">
                                                <div class="icon"><i class="bi bi-calendar-check"></i></div>
                                                <div class="number" id="totalBookings">0</div>
                                                <div class="label">Total Bookings</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card-stats text-center">
                                                <div class="icon"><i class="bi bi-hourglass-split"></i></div>
                                                <div class="number" id="pendingBookings">0</div>
                                                <div class="label">Pending</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card-stats text-center">
                                                <div class="icon"><i class="bi bi-check2-circle"></i></div>
                                                <div class="number" id="completedBookings">0</div>
                                                <div class="label">Completed</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="booking-filter mb-4">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search bookings..." id="searchBookings">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Filter By</button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="#" data-filter="all">All Bookings</a></li>
                                                <li><a class="dropdown-item" href="#" data-filter="pending">Pending</a></li>
                                                <li><a class="dropdown-item" href="#" data-filter="confirmed">Confirmed</a></li>
                                                <li><a class="dropdown-item" href="#" data-filter="cancelled">Cancelled</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div id="bookingsList" class="booking-history-list">
                                        <!-- Booking items will be loaded here -->
                                        <div class="text-center text-muted py-5" id="noBookingsMessage">
                                            <i class="bi bi-calendar-x fs-1"></i>
                                            <p class="mt-3">No bookings found</p>
                                            <a href="/home/book" class="btn btn-outline-success btn-sm">Make Your First Booking</a>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Preferences Tab -->
                                <div class="tab-pane fade" id="v-pills-preferences" role="tabpanel" aria-labelledby="v-pills-preferences-tab">
                                    <h4 class="section-title">Preferences</h4>
                                    
                                    <form id="preferencesForm">
                                        <h5 class="mb-3">Notifications</h5>
                                        <div class="mb-3 form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                            <label class="form-check-label" for="emailNotifications">Email Notifications</label>
                                            <div class="form-text">Receive booking updates and important announcements via email</div>
                                        </div>
                                        
                                        <div class="mb-3 form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="smsNotifications">
                                            <label class="form-check-label" for="smsNotifications">SMS Notifications</label>
                                            <div class="form-text">Receive booking reminders and updates via SMS</div>
                                        </div>
                                        
                                        <hr class="my-4">
                                        
                                        <h5 class="mb-3">Display & Appearance</h5>
                                        <div class="mb-3">
                                            <label class="form-label">Theme Preference</label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="themePreference" id="themeLight" value="light" checked>
                                                    <label class="form-check-label" for="themeLight">Light</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="themePreference" id="themeDark" value="dark">
                                                    <label class="form-check-label" for="themeDark">Dark</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="themePreference" id="themeSystem" value="system">
                                                    <label class="form-check-label" for="themeSystem">System Default</label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-success mt-3">Save Preferences</button>
                                        <div id="preferencesMessage" class="mt-3"></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . '/chat_widget_core.php'; ?>

    <script>
        // Set user login status for chat widget
        var userLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script src="../../../public/js/client/user_account.js"></script>
    <script src="../../../public/js/assets/sidebar.js"></script>
    <script>
        // Format phone number as user types
       document.addEventListener('DOMContentLoaded', function () {
            const contactNumberInput = document.getElementById('contactNumber');

            contactNumberInput.addEventListener('input', function (e) {
                // Remove all non-digit characters
                let value = this.value.replace(/\D/g, '');

                // Convert starting '09' to '639'
                if (value.startsWith('09')) {
                    value = '63' + value.substring(1);
                }

                // Convert starting '9' (if no prefix) to '639'
                if (value.length >= 10 && value.startsWith('9')) {
                    value = '639' + value;
                }

                // Only format if it starts with 639 and has enough digits
                if (value.startsWith('639')) {
                    const part1 = value.substring(2, 5); // 917
                    const part2 = value.substring(5, 8); // 123
                    const part3 = value.substring(8, 12); // 4567

                    let formatted = '+63';
                    if (part1) formatted += ' ' + part1;
                    if (part2) formatted += ' ' + part2;
                    if (part3) formatted += ' ' + part3;

                    this.value = formatted.trim();
                } else {
                    this.value = value; // fallback (e.g., still typing)
                }
            });
        });
    </script>
</body>
</html>