document.addEventListener("DOMContentLoaded", async () => {

    // Initialize UI components

    initializeUI();

    

    // Load user information

    await loadUserInformation();

    

    // Load booking history

    await loadBookingHistory();

    

    // Add event listeners

    setupEventListeners();

});



/**

 * Initialize UI components

 */

function initializeUI() {

    // Initialize profile avatar with initials

    initializeProfileAvatar();

    

    // Set device information

    document.getElementById('deviceInfo').textContent = getDeviceInfo();

    

    // For demo purposes, set IP address (in production this would come from backend)

    // In reality, fetch this from an endpoint

    document.getElementById('ipAddress').textContent = '192.168.1.1';

}



/**

 * Initialize profile avatar with user's initials

 */

function initializeProfileAvatar() {

    const fullName = document.getElementById('profileName').textContent;

    const initials = getInitials(fullName);

    

    // Set initials in avatar

    document.getElementById('avatarInitials').textContent = initials;

    

    // Generate random background color (would be consistent in production)

    const avatarContainer = document.getElementById('profileAvatarContainer');

    avatarContainer.style.backgroundColor = '#e9f5e9'; // Keep the green theme

}



/**

 * Get user's initials from full name

 */

function getInitials(name) {

    const names = name.trim().split(' ');

    if (names.length > 1) {

        return (names[0][0] + names[1][0]).toUpperCase();

    }

    return names[0][0].toUpperCase();

}



/**

 * Get user's device and browser information

 */

function getDeviceInfo() {

    const userAgent = navigator.userAgent;

    let device = 'Unknown';

    let browser = 'Unknown';



    // Detect device

    if (/(Win)/i.test(userAgent)) {

        device = 'Windows';

    } else if (/(Mac)/i.test(userAgent)) {

        device = 'Mac';

    } else if (/(Android)/i.test(userAgent)) {

        device = 'Android';

    } else if (/(iPhone|iPad|iPod)/i.test(userAgent)) {

        device = 'iOS';

    } else if (/(Linux)/i.test(userAgent)) {

        device = 'Linux';

    }



    // Detect browser

    if (/(Chrome)/i.test(userAgent) && !/(Edg)/i.test(userAgent)) {

        browser = 'Chrome';

    } else if (/(Firefox)/i.test(userAgent)) {

        browser = 'Firefox';

    } else if (/(Safari)/i.test(userAgent) && !/(Chrome)/i.test(userAgent)) {

        browser = 'Safari';

    } else if (/(Edg)/i.test(userAgent)) {

        browser = 'Edge';

    } else if (/(Opera|OPR)/i.test(userAgent)) {

        browser = 'Opera';

    }



    return `${device} • ${browser}`;

}



/**

 * Setup event listeners for all forms and interactive elements

 */

function setupEventListeners() {

    // Profile form submission

    const userForm = document.getElementById('userForm');

    if (userForm) {

        userForm.addEventListener('submit', handleProfileUpdate);

    }

    

    // Password form with validation

    const passwordForm = document.getElementById('passwordForm');

    if (passwordForm) {

        passwordForm.addEventListener('submit', handlePasswordUpdate);

        setupPasswordValidation();

    }

    

    // Toggle password visibility

    const togglePassword = document.getElementById('togglePassword');

    if (togglePassword) {

        togglePassword.addEventListener('click', () => {

            const passwordInput = document.getElementById('newPassword');

            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';

            passwordInput.setAttribute('type', type);

            togglePassword.innerHTML = type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';

        });

    }

    

    // Preferences form

    const preferencesForm = document.getElementById('preferencesForm');

    if (preferencesForm) {

        preferencesForm.addEventListener('submit', handlePreferencesUpdate);

    }

    

    // Booking search and filters

    const searchBookings = document.getElementById('searchBookings');

    if (searchBookings) {

        searchBookings.addEventListener('input', filterBookings);

    }

    

    // Booking filter links

    const filterLinks = document.querySelectorAll('[data-filter]');

    filterLinks.forEach(link => {

        link.addEventListener('click', (e) => {

            e.preventDefault();

            filterBookingsByStatus(link.dataset.filter);

        });

    });

    

    // Profile image upload

    const uploadOverlay = document.getElementById('uploadOverlay');

    const avatarUpload = document.getElementById('avatarUpload');

    const removeProfileImage = document.getElementById('removeProfileImage');

    

    if (uploadOverlay && avatarUpload) {

        uploadOverlay.addEventListener('click', () => {

            avatarUpload.click();

        });

        

        avatarUpload.addEventListener('change', handleProfileImageUpload);

    }

    if (removeProfileImage) {
        removeProfileImage.addEventListener('click', handleRemoveProfileImage);
    }

}



/**

 * Load user information from the server

 */

async function loadUserInformation() {

    try {

        const response = await fetch("/get-client-information");

        const data = await response.json(); 



        if (data.success) {

            const client = data.client[0];

            

            // Set form values

            document.getElementById("firstName").value = client.first_name;

            document.getElementById("lastName").value = client.last_name;

            document.getElementById("contactNumber").value = client.contact_number;

            document.getElementById("email").value = client.email;

            

            // Set company name if available

            if (client.company_name) {

                document.getElementById("companyName").value = client.company_name;

            }

            

            // Set address if available

            const addressField = document.getElementById("address");

            if (addressField && client.address) {

                addressField.value = client.address;

            }

            

            // Set user's profile completion percentage

            calculateProfileCompletion(client);

            

            // Check if user has profile image

            if (client.profile_picture) {

                displayProfileImage(client.profile_picture);

            }

        }

    } catch (error) {

        console.error("Error fetching user data: ", error);

        showMessage("userMessage", "Failed to load your account information. Please try again later.", "danger");

    }

}



/**

 * Calculate and display user's profile completion percentage

 */

function calculateProfileCompletion(user) {

    const totalFields = 5; // first_name, last_name, email, contact_number, address, company_name

    let filledFields = 0;

    

    if (user.first_name) filledFields++;

    if (user.last_name) filledFields++;

    if (user.email) filledFields++;

    if (user.contact_number) filledFields++;    

    if (user.company_name) filledFields++;

    

    const percentage = Math.round((filledFields / totalFields) * 100);

    

    // Update UI

    document.getElementById('completionPercentage').textContent = `${percentage}%`;

    document.querySelector('.progress-bar').style.width = `${percentage}%`;

    document.querySelector('.progress-bar').setAttribute('aria-valuenow', percentage);

    

    // Update message based on completion

    const completionMessage = document.getElementById('completionMessage');

    if (percentage < 50) {

        completionMessage.textContent = 'Complete your profile to improve your booking experience';

    } else if (percentage < 100) {

        completionMessage.textContent = 'Your profile is looking good! Just a few more details to complete';

    } else {

        completionMessage.textContent = 'Your profile is complete! Thank you for providing all information';

    }

}



/**

 * Display user's profile image

 */

function displayProfileImage(imageUrl) {

    const avatarImg = document.getElementById('profileAvatar');

    const avatarInitials = document.getElementById('avatarInitials');

    

    avatarImg.src = imageUrl;

    avatarImg.style.display = 'block';

    avatarInitials.style.display = 'none';

}



/**

 * Handle profile form submission

 */

async function handleProfileUpdate(e) {

    e.preventDefault();

    

    // Show loading state

    const saveText = document.getElementById('saveText');

    const saveSpinner = document.getElementById('saveSpinner');

    saveText.textContent = 'Saving...';

    saveSpinner.classList.remove('d-none');

    

    const formData = {

        firstName: document.getElementById("firstName")?.value,

        lastName: document.getElementById("lastName")?.value,

        companyName: document.getElementById("companyName")?.value,

        contactNumber: formatPhoneNumberForDB(document.getElementById("contactNumber")?.value),

        email: document.getElementById("email")?.value,

        address: document.getElementById("address")?.value || ''

    };



    try {

        const response = await fetch("/update-client-information", {

            method: "POST",

            headers: { "Content-Type": "application/json" },

            body: JSON.stringify(formData)

        });

    

        const data = await response.json();

    

        if (data.success) {

            showMessage("userMessage", data.message, "success");

        } else {

            showMessage("userMessage", data.message, "danger");

        }

    } catch (error) {

        console.error("Error updating profile: ", error);

        showMessage("userMessage", "Failed to update your profile. Please try again later.", "danger");

    } finally {

        // Reset button state

        saveText.textContent = 'Save Changes';

        saveSpinner.classList.add('d-none');

    }

}



/**

 * Handle password update form submission

 */

async function handlePasswordUpdate(e) {

    e.preventDefault();

    

    const currentPassword = document.getElementById('currentPassword').value;

    const newPassword = document.getElementById('newPassword').value;

    const confirmPassword = document.getElementById('confirmPassword').value;

    

    // Client-side validation

    if (!validatePassword(newPassword, confirmPassword)) {

        showMessage("passwordMessage", "Please correct the password issues before submitting", "danger");

        return;

    }

    

    const formData = {

        currentPassword,

        newPassword,

        confirmPassword

    };

    

    try {

        // In production, implement this endpoint

        const response = await fetch("/update-client-password", {

            method: "POST",

            headers: { "Content-Type": "application/json" },

            body: JSON.stringify(formData)

        });

    

        const data = await response.json();

    

        if (data.success) {

            showMessage("passwordMessage", data.message, "success");

            document.getElementById('passwordForm').reset();

        } else {

            showMessage("passwordMessage", data.message, "danger");

        }

    } catch (error) {

        console.error("Error updating password: ", error);

        showMessage("passwordMessage", "Failed to update your password. Please try again later.", "danger");

    }

}



/**

 * Setup password validation

 */

function setupPasswordValidation() {

    const newPassword = document.getElementById('newPassword');

    const confirmPassword = document.getElementById('confirmPassword');

    

    const validateInputs = () => {

        const password = newPassword.value;

        const confirm = confirmPassword.value;

        

        // Length validation

        const lengthItem = document.getElementById('length');

        if (password.length >= 8) {

            lengthItem.classList.remove('invalid');

            lengthItem.classList.add('valid');

            lengthItem.innerHTML = '<i class="bi bi-check-circle me-2"></i>At least 8 characters';

        } else {

            lengthItem.classList.remove('valid');

            lengthItem.classList.add('invalid');

            lengthItem.innerHTML = '<i class="bi bi-x-circle me-2"></i>At least 8 characters';

        }

        

        // Uppercase validation

        const uppercaseItem = document.getElementById('uppercase');

        if (/[A-Z]/.test(password)) {

            uppercaseItem.classList.remove('invalid');

            uppercaseItem.classList.add('valid');

            uppercaseItem.innerHTML = '<i class="bi bi-check-circle me-2"></i>At least one uppercase letter';

        } else {

            uppercaseItem.classList.remove('valid');

            uppercaseItem.classList.add('invalid');

            uppercaseItem.innerHTML = '<i class="bi bi-x-circle me-2"></i>At least one uppercase letter';

        }

        

        // Lowercase validation

        const lowercaseItem = document.getElementById('lowercase');

        if (/[a-z]/.test(password)) {

            lowercaseItem.classList.remove('invalid');

            lowercaseItem.classList.add('valid');

            lowercaseItem.innerHTML = '<i class="bi bi-check-circle me-2"></i>At least one lowercase letter';

        } else {

            lowercaseItem.classList.remove('valid');

            lowercaseItem.classList.add('invalid');

            lowercaseItem.innerHTML = '<i class="bi bi-x-circle me-2"></i>At least one lowercase letter';

        }

        

        // Number validation

        const numberItem = document.getElementById('number');

        if (/[0-9]/.test(password)) {

            numberItem.classList.remove('invalid');

            numberItem.classList.add('valid');

            numberItem.innerHTML = '<i class="bi bi-check-circle me-2"></i>At least one number';

        } else {

            numberItem.classList.remove('valid');

            numberItem.classList.add('invalid');

            numberItem.innerHTML = '<i class="bi bi-x-circle me-2"></i>At least one number';

        }

        

        // Match validation

        const matchItem = document.getElementById('match');

        if (password === confirm && password !== '') {

            matchItem.classList.remove('invalid');

            matchItem.classList.add('valid');

            matchItem.innerHTML = '<i class="bi bi-check-circle me-2"></i>Passwords match';

        } else {

            matchItem.classList.remove('valid');

            matchItem.classList.add('invalid');

            matchItem.innerHTML = '<i class="bi bi-x-circle me-2"></i>Passwords match';

        }

    };

    

    newPassword.addEventListener('input', validateInputs);

    confirmPassword.addEventListener('input', validateInputs);

}



/**

 * Validate password meets requirements

 */

function validatePassword(password, confirmPassword) {

    if (password.length < 8) {

        return false;

    }

    

    if (!/[A-Z]/.test(password)) {

        return false;

    }

    

    if (!/[a-z]/.test(password)) {

        return false;

    }

    

    if (!/[0-9]/.test(password)) {

        return false;

    }

    

    if (password !== confirmPassword) {

        return false;

    }

    

    return true;

}



/**

 * Handle preferences form submission

 */

async function handlePreferencesUpdate(e) {

    e.preventDefault();

    

    const emailNotifications = document.getElementById('emailNotifications').checked;

    const smsNotifications = document.getElementById('smsNotifications').checked;

    const themePreference = document.querySelector('input[name="themePreference"]:checked').value;

    

    const formData = {

        emailNotifications,

        smsNotifications,

        themePreference

    };

    

    try {

        // In production, implement this endpoint

        const response = await fetch("/update-client-preferences", {

            method: "POST",

            headers: { "Content-Type": "application/json" },

            body: JSON.stringify(formData)

        });

    

        const data = await response.json();

    

        if (data.success) {

            showMessage("preferencesMessage", data.message, "success");

            

            // Apply theme if changed (for demo)

            applyTheme(themePreference);

        } else {

            showMessage("preferencesMessage", data.message, "danger");

        }

    } catch (error) {

        console.error("Error updating preferences: ", error);

        showMessage("preferencesMessage", "Failed to update your preferences. Please try again later.", "danger");

    }

}



/**

 * Apply selected theme

 */

function applyTheme(theme) {

    // This would be implemented to change the UI theme

    console.log(`Theme set to: ${theme}`);

}



/**

 * Load booking history

 */

async function loadBookingHistory() {

    try {

        // In production, implement this endpoint

        const response = await fetch("/get-client-bookings");

        const data = await response.json();

        

        if (data.success) {

            renderBookingStats(data.bookings);

            renderBookingList(data.bookings);

        }

    } catch (error) {

        console.error("Error loading booking history: ", error);

        

        // For demo purposes, show sample data

        renderSampleBookingData();

    }

}



/**

 * Render booking statistics

 */

function renderBookingStats(bookings = []) {

    if (bookings.length === 0) {

        return;

    }

    

    const totalBookings = bookings.length;

    const pendingBookings = bookings.filter(b => b.status === 'pending').length;

    const completedBookings = bookings.filter(b => b.status === 'confirmed').length;

    

    document.getElementById('totalBookings').textContent = totalBookings;

    document.getElementById('pendingBookings').textContent = pendingBookings;

    document.getElementById('completedBookings').textContent = completedBookings;

}



/**

 * Render booking list

 */

function renderBookingList(bookings = []) {

    const bookingsList = document.getElementById('bookingsList');

    const noBookingsMessage = document.getElementById('noBookingsMessage');

    

    if (bookings.length === 0) {

        noBookingsMessage.style.display = 'block';

        return;

    }

    

    noBookingsMessage.style.display = 'none';

    bookingsList.innerHTML = '';

    

    bookings.forEach(booking => {

        const bookingItem = document.createElement('div');

        bookingItem.className = 'booking-history-item';

        bookingItem.dataset.status = booking.status;

        

        let statusClass = '';

        switch(booking.status) {

            case 'confirmed':

                statusClass = 'status-confirmed';

                break;

            case 'pending':

                statusClass = 'status-pending';

                break;

            case 'cancelled':

                statusClass = 'status-cancelled';

                break;

        }

        

        bookingItem.innerHTML = `

            <div class="d-flex justify-content-between align-items-start">

                <div>

                    <h6 class="mb-1">Booking #${booking.booking_id}</h6>

                    <p class="mb-1 text-muted">${booking.booking_date} • ${booking.bus_type}</p>

                    <p class="mb-0"><small>${booking.origin} to ${booking.destination}</small></p>

                </div>

                <span class="status ${statusClass}">${booking.status}</span>

            </div>

            <div class="mt-2">

                <a href="/home/booking-details/${booking.booking_id}" class="btn btn-sm btn-outline-success">View Details</a>

            </div>

        `;

        

        bookingsList.appendChild(bookingItem);

    });

}



/**

 * Render sample booking data (for demo)

 */

function renderSampleBookingData() {

    const sampleBookings = [

        {

            booking_id: 'BK1001',

            status: 'confirmed',

            booking_date: '2023-10-15',

            bus_type: 'Premium Bus',

            origin: 'Manila',

            destination: 'Baguio'

        },

        {

            booking_id: 'BK1002',

            status: 'pending',

            booking_date: '2023-11-05',

            bus_type: 'Economy Bus',

            origin: 'Manila',

            destination: 'Batangas'

        },

        {

            booking_id: 'BK1003',

            status: 'cancelled',

            booking_date: '2023-09-20',

            bus_type: 'Deluxe Bus',

            origin: 'Manila',

            destination: 'Tagaytay'

        }

    ];

    

    renderBookingStats(sampleBookings);

    renderBookingList(sampleBookings);

}



/**

 * Filter bookings by text search

 */

function filterBookings() {

    const searchTerm = document.getElementById('searchBookings').value.toLowerCase();

    const bookingItems = document.querySelectorAll('.booking-history-item');

    

    bookingItems.forEach(item => {

        const text = item.textContent.toLowerCase();

        if (text.includes(searchTerm)) {

            item.style.display = 'block';

        } else {

            item.style.display = 'none';

        }

    });

}



/**

 * Filter bookings by status

 */

function filterBookingsByStatus(status) {

    const bookingItems = document.querySelectorAll('.booking-history-item');

    

    bookingItems.forEach(item => {

        if (status === 'all' || item.dataset.status === status) {

            item.style.display = 'block';

        } else {

            item.style.display = 'none';

        }

    });

}



/**

 * Handle profile image upload

 */

async function handleProfileImageUpload(e) {

    const file = e.target.files[0];

    

    if (!file) return;

    

    if (!file.type.match('image.*')) {

        showMessage("userMessage", "Please select an image file", "danger");

        return;

    }

    

    const reader = new FileReader();

    reader.onload = function (e) {

        const avatarImg = document.getElementById('profileAvatar');

        const avatarInitials = document.getElementById('avatarInitials');

        

        avatarImg.src = e.target.result;

        avatarImg.style.display = 'block';

        avatarInitials.style.display = 'none';

        

        // In production, implement uploading to server

        uploadProfileImage(file);

    };

    reader.readAsDataURL(file);

}



/**

 * Upload profile image to server

 */

async function uploadProfileImage(file) {

    const formData = new FormData();

    formData.append('profileImage', file);

    

    try {

        const response = await fetch("/upload-profile-image", {

            method: "POST",

            body: formData

        });

        

        const data = await response.json();

        

        if (data.success) {

            showMessage("userMessage", "Profile image updated successfully", "success");

            // Refresh profile display in navigation

            refreshProfileDisplay(data.image_url);

        } else {

            showMessage("userMessage", data.message || "Failed to update profile image", "danger");

        }

    } catch (error) {

        console.error("Error uploading image: ", error);

        showMessage("userMessage", "Failed to upload image. Please try again later.", "danger");

    }

}



/**

 * Handle remove profile image

 */

async function handleRemoveProfileImage() {

    const removeProfileImage = document.getElementById('removeProfileImage');

    const avatarImg = document.getElementById('profileAvatar');

    const avatarInitials = document.getElementById('avatarInitials');

    

    if (removeProfileImage) {

        removeProfileImage.style.display = 'none';

    }

    

    if (avatarImg) {

        avatarImg.src = ''; // Clear image source

        avatarImg.style.display = 'none';

    }

    

    if (avatarInitials) {

        avatarInitials.style.display = 'block';

    }

    

    try {

        const response = await fetch("/remove-profile-image", {

            method: "POST"

        });

        

        const data = await response.json();

        

        if (data.success) {

            showMessage("userMessage", "Profile image removed successfully", "success");

            // Refresh profile display in navigation

            refreshProfileDisplay("../../../public/images/profile.png");

        } else {

            showMessage("userMessage", data.message || "Failed to remove profile image", "danger");

        }

    } catch (error) {

        console.error("Error removing profile image: ", error);

        showMessage("userMessage", "Failed to remove profile image. Please try again later.", "danger");

    }

}



/**

 * Refresh profile display in navigation

 */

function refreshProfileDisplay(imageUrl) {

    // Update profile images in the navigation

    const profileImages = document.querySelectorAll('.profile-toggle img, .profile-dropdown img');

    profileImages.forEach(img => {

        img.src = imageUrl;

    });

}



/**

 * Display message to user

 */

function showMessage(elementId, message, type = "success") {

    const messageElement = document.getElementById(elementId);

    messageElement.innerHTML = message;

    messageElement.className = `save-message alert alert-${type} mt-3`;

    

    // Auto-hide after 3 seconds

    setTimeout(() => {

        messageElement.innerHTML = '';

        messageElement.className = '';

    }, 3000);

}



/**

 * Format phone number for database

 */

function formatPhoneNumberForDB(value) {

    if (!value || value.trim() === '') return '';

    

    // Remove all non-digits

    const digits = value.replace(/\D/g, '');



    // Expecting a Philippine mobile number starting with 09 and 11 digits total

    // if (digits.length !== 11 || !digits.startsWith('09')) {

    //     return digits;

    // }



    // Convert 09XX to +63 9XX and format as +63 917 123 4567

    return `+63 ${digits.substring(1, 4)} ${digits.substring(4, 7)} ${digits.substring(7, 11)}`;

}

