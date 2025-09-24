<head>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>

    /* Booking details popup styling */

    .booking-details-popup {

        box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.15) !important;

        border: 1px solid rgba(0, 0, 0, 0.08);

        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);

        border-radius: 0.75rem;

    }

    

    /* Notification styling */

    .notification-dropdown {

        border-radius: 0.75rem !important;

        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;

        min-width: 360px;

        max-width: 100vw;

        padding: 0 !important;

        border: none;

        overflow: hidden;

        animation: dropdownFadeIn 0.3s ease-out forwards;

    }

    

    @keyframes dropdownFadeIn {

        from { opacity: 0; transform: translateY(10px); }

        to { opacity: 1; transform: translateY(0); }

    }

    

    .notification-header, .notification-footer {

        background: #f8f9fa;

        padding: 14px 18px;

        border-bottom: 1px solid rgba(0, 0, 0, 0.05);

    }

    

    .notification-header {

        display: flex;

        justify-content: space-between;

        align-items: center;

    }

    

    .notification-header h6 {

        font-weight: 600;

        color: #198754;

        margin: 0;

        display: flex;

        align-items: center;

    }

    

    .notification-header h6 i {

        margin-right: 8px;

    }

    

    .notification-footer {

        border-top: 1px solid rgba(0, 0, 0, 0.05);

        border-bottom: none;

        text-align: center;

        padding: 12px;

    }

    

    .notification-footer a {

        color: #198754;

        font-weight: 500;

        transition: all 0.2s ease;

    }

    

    .notification-footer a:hover {

        color: #0d6a3e;

        text-decoration: none;

    }

    

    .notification-list {

        max-height: 400px;

        overflow-y: auto;

        background: #fff;

        scrollbar-width: thin;

        scrollbar-color: rgba(25, 135, 84, 0.3) rgba(0, 0, 0, 0.05);

    }

    

    .notification-list::-webkit-scrollbar {

        width: 6px;

    }

    

    .notification-list::-webkit-scrollbar-track {

        background: rgba(0, 0, 0, 0.05);

    }

    

    .notification-list::-webkit-scrollbar-thumb {

        background-color: rgba(25, 135, 84, 0.3);

        border-radius: 10px;

    }

    

    .notification-item {

        display: flex;

        align-items: flex-start;

        gap: 12px;

        padding: 16px 18px;

        border-bottom: 1px solid rgba(0, 0, 0, 0.05);

        transition: all 0.3s ease;

        cursor: pointer;

        text-decoration: none;

        color: inherit;

        position: relative;

        animation: itemFadeIn 0.5s ease-out forwards;

    }

    

    @keyframes itemFadeIn {

        from { opacity: 0; transform: translateY(8px); }

        to { opacity: 1; transform: translateY(0); }

    }

    

    .notification-item:last-child {

        border-bottom: none;

    }

    

    .notification-item:hover, .notification-item.bg-light {

        background: rgba(25, 135, 84, 0.05) !important;

        transform: translateY(-2px);

    }

    

    .notification-item.bg-light::after {

        content: '';

        position: absolute;

        top: 12px;

        right: 12px;

        width: 8px;

        height: 8px;

        border-radius: 50%;

        background-color: #198754;

    }

    

    .notification-icon {

        flex-shrink: 0;

        font-size: 1.5rem;

        margin-top: 2px;

        width: 40px;

        height: 40px;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 50%;

        background-color: rgba(25, 135, 84, 0.1);

        transition: all 0.3s ease;

    }

    

    .notification-item:hover .notification-icon {

        transform: scale(1.1);

    }

    

    .notification-content {

        flex: 1;

        min-width: 0;

    }

    

    .notification-message {

        font-weight: 500;

        font-size: 0.95rem;

        line-height: 1.4;

        white-space: nowrap;

        overflow: hidden;

        text-overflow: ellipsis;

        max-width: 260px;

        margin-bottom: 4px;

        color: #333;

    }

    

    .notification-time {

        font-size: 0.8rem;

        color: #6c757d;

        display: flex;

        align-items: center;

        gap: 4px;

    }

    

    .notification-time i {

        font-size: 0.75rem;

    }

    

    .notification-badge {

        /* position: absolute;

        top: -8px;

        right: -1px;

        font-size: 0.65rem;

        padding: 0.25rem 0.4rem;

        box-shadow: 0 2px 5px rgba(0,0,0,0.2); */

    }

    

    .no-notifications {

        padding: 40px 0;

        color: #adb5bd;

        font-size: 0.95rem;

        text-align: center;

    }

    

    .no-notifications i {

        font-size: 2.5rem;

        margin-bottom: 1rem;

        opacity: 0.5;

    }

    

    .mark-all-read {

        color: #198754;

        font-weight: 500;

        font-size: 0.8rem;

        transition: all 0.2s ease;

    }

    

    .mark-all-read:hover { 

        color: #0d6a3e;

        text-decoration: none;

    }
    
    /* Profile dropdown styling */
    .profile-dropdown {
        animation: dropdownFadeIn 0.3s ease-out forwards;
    }
    
    .profile-toggle {
        transition: all 0.2s ease;
        border-radius: 0.5rem;
        padding: 0.25rem;
    }
    
    .profile-toggle:hover {
        background-color: rgba(25, 135, 84, 0.05);
    }
    
    .profile-dropdown .dropdown-item {
        transition: all 0.2s ease;
        border-radius: 0.25rem;
        margin: 0 0.25rem;
    }
    
    .profile-dropdown .dropdown-item:hover {
        background-color: rgba(25, 135, 84, 0.05);
    }
    
    .profile-dropdown .dropdown-item.text-danger:hover {
        background-color: rgba(220, 53, 69, 0.05);
    }
    
    /* SweetAlert2 custom styling */
    .swal2-popup-custom {
        border-radius: 0.75rem !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
    }
    
    .swal2-popup-custom .swal2-title {
        color: #198754 !important;
        font-weight: 600 !important;
    }
    
    .swal2-popup-custom .swal2-html-container {
        color: #6c757d !important;
    }
    
    .swal2-popup-custom .swal2-confirm {
        background-color: #198754 !important;
        border-color: #198754 !important;
        border-radius: 0.5rem !important;
        font-weight: 500 !important;
        padding: 0.5rem 1.5rem !important;
    }
    
    .swal2-popup-custom .swal2-confirm:hover {
        background-color: #0d6a3e !important;
        border-color: #0d6a3e !important;
    }
    
    .swal2-popup-custom .swal2-cancel {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
        border-radius: 0.5rem !important;
        font-weight: 500 !important;
        padding: 0.5rem 1.5rem !important;
    }
    
    .swal2-popup-custom .swal2-cancel:hover {
        background-color: #5a6268 !important;
        border-color: #5a6268 !important;
    }

</style>

</head>



<div class="p-2 d-flex align-items-center gap-2">

    <a href="#" class="text-success"><i class="bi bi-plus-square-fill me-2 fs-5"></i></a>

    

    <!-- Notification Bell with Badge -->

    <div class="dropdown">

        <a href="#" class="position-relative text-success notification-toggle" id="notificationToggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">

            <i class="bi bi-bell-fill me-2 fs-5"></i>

            <span class="position-absolute top-0 translate-middle badge rounded-pill bg-danger notification-badge" style="display: none; left: 1.2rem; box-shadow: 0 2px 5px rgba(0,0,0,0.2); font-size: 0.65rem; padding: 0.25rem 0.4rem;">

                <span class="notification-count text-center">0</span>

                <span class="visually-hidden">unread notifications</span>

            </span>

        </a>

        

        <!-- Notification Dropdown -->

        <div class="dropdown-menu dropdown-menu-end notification-dropdown" id="notificationDropdownMenu">

            <div class="notification-header">

                <h6><i class="bi bi-bell-fill"></i>Notifications</h6>

                <a href="javascript:void(0)" class="text-decoration-none small mark-all-read" style="display: none;">Mark all as read</a>

            </div>

            <div class="notification-list">

                <!-- Notifications will be loaded here dynamically -->

                <div class="no-notifications text-center">No notifications</div>

            </div>

            <div class="notification-footer">

                <a href="/admin/notifications" class="text-decoration-none small d-none">View all notifications</a>

            </div>

        </div>

    </div>

    

    <!-- Notification Details Popup -->

    <div class="notification-details-popup position-fixed bg-white rounded-4 shadow-lg p-0" style="display: none; width: 350px; z-index: 1060; right: auto; left: auto;">

        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">

            <h6 class="m-0 fw-semibold d-flex align-items-center">

                <i class="bi bi-bell-fill me-2 text-success"></i>Notification Details

            </h6>

            <button type="button" class="btn-close close-notification-details" aria-label="Close"></button>

        </div>

        <div class="notification-detail-content p-3">

            <!-- Notification details will be loaded here -->

        </div>

        <div class="p-3 border-top d-flex justify-content-end d-none">

            <a href="#" class="btn btn-sm btn-success view-related-content" style="display: none;">

                <i class="bi bi-arrow-right me-1"></i>View Details

            </a>

        </div>

    </div>

    

    <!-- Profile Dropdown -->
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center gap-2 text-decoration-none profile-toggle" id="profileToggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="../../../public/images/profile.jpg" alt="profile" height="35px" class="rounded-circle">
            <div class="text-sm">
                <div class="name text-success fw-bold" style="font-size: 12px"><?= $_SESSION["admin_name"]; ?></div>
                <div class="role" style="font-size: 10px"><?= $_SESSION["role"]; ?></div>
            </div>
            <i class="bi bi-chevron-down text-success" style="font-size: 10px;"></i>
        </a>
        
        <!-- Profile Dropdown Menu -->
        <div class="dropdown-menu dropdown-menu-end profile-dropdown" id="profileDropdownMenu" style="min-width: 200px; border-radius: 0.75rem; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15); border: none; padding: 0.5rem 0;">
            <div class="px-3 py-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <img src="../../../public/images/profile.jpg" alt="profile" height="40px" class="rounded-circle">
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 14px;"><?= $_SESSION["admin_name"]; ?></div>
                        <div class="text-muted" style="font-size: 12px;"><?= $_SESSION["role"]; ?></div>
                    </div>
                </div>
            </div>
            <div class="py-1">
                <a href="" id="logoutLink" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 text-danger" style="font-size: 14px;">
                    <i class="bi bi-box-arrow-left"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>

</div>



<!-- Add notification scripts - will fetch notifications via AJAX -->

<script>

document.addEventListener('DOMContentLoaded', function() {

    const notificationToggle = document.getElementById('notificationToggle');

    const notificationDropdown = document.getElementById('notificationDropdownMenu');

    const notificationBadge = document.querySelector('.notification-badge');

    const notificationCount = document.querySelector('.notification-count');

    const notificationList = document.querySelector('.notification-list');

    const noNotifications = document.querySelector('.no-notifications');

    const markAllReadBtn = document.querySelector('.mark-all-read');

    const notificationDetailsPopup = document.querySelector('.notification-details-popup');

    const closeNotificationDetailsBtn = document.querySelector('.close-notification-details');

    const viewRelatedContentBtn = document.querySelector('.view-related-content');

    

    // Function to load notifications

    async function loadNotifications() {

        try {

            const response = await fetch('/admin/notifications/get', {

                method: 'GET',

                headers: {

                    'Accept': 'application/json'

                }

            });

            

            if (!response.ok) {

                throw new Error(`HTTP error! status: ${response.status}`);

            }

            

            const data = await response.json();

            

            if (data.success) {

                if (data.unreadCount > 0) {

                    notificationBadge.style.display = 'block';

                    notificationCount.textContent = (data.unreadCount > 9) ? '9+' : data.unreadCount;

                } else {

                    notificationBadge.style.display = 'none';

                }

                // Show the "Mark all as read" button only when unread > 1
                if (markAllReadBtn) {
                    markAllReadBtn.style.display = (data.unreadCount > 1) ? 'inline' : 'none';
                }

                

                // Render notifications

                if (data.notifications && data.notifications.length > 0) {

                    noNotifications.style.display = 'none';

                    notificationList.innerHTML = '';

                    

                    data.notifications.forEach(notification => {

                        const notificationItem = document.createElement('a');

                        notificationItem.href = getNotificationLink(notification);

                        notificationItem.className = `dropdown-item p-2 border-bottom notification-item ${!notification.is_read ? 'bg-light' : ''}`;

                        notificationItem.setAttribute('data-id', notification.notification_id);

                        notificationItem.setAttribute('data-reference-id', notification.reference_id);

                        notificationItem.setAttribute('data-type', notification.type);

                        

                        // Icon based on notification type

                        let iconClass = 'bi-info-circle-fill text-primary';

                        if (notification.type.includes('booking_confirmed')) {

                            iconClass = 'bi-check-circle-fill text-success';

                        } else if (notification.type.includes('booking_rejected') || notification.type.includes('booking_canceled') || notification.type.includes('booking_cancelled_by_client')) {

                            iconClass = 'bi-x-circle-fill text-danger';

                        } else if (notification.type.includes('payment_submitted')) {

                            iconClass = 'bi-cash-coin text-primary';

                        } else if (notification.type.includes('rebooking')) {

                            iconClass = 'bi-arrow-repeat text-warning';

                        }

                        

                        const date = new Date(notification.created_at);

                        const formattedDate = date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

                        

                        notificationItem.innerHTML = `

                            <div class="notification-icon">

                                <i class="bi ${iconClass}"></i>

                            </div>

                            <div class="notification-content">

                                <div class="notification-message">${notification.message}</div>

                                <div class="notification-time">

                                    <i class="bi bi-clock"></i>${formattedDate}

                                    ${!notification.is_read ? '<span class="badge bg-success rounded-pill ms-2" style="font-size: 0.7rem; padding: 0.25em 0.6em;">New</span>' : ''}

                                </div>

                            </div>

                        `;

                        

                        notificationItem.querySelector('.notification-message').setAttribute('title', notification.message);

                        

                        notificationList.appendChild(notificationItem);

                    });

                } else {

                    noNotifications.style.display = 'block';

                    notificationList.innerHTML = '';

                    notificationList.appendChild(noNotifications);

                }

            }

        } catch (error) {

            console.error('Error loading notifications:', error);

        }

    }

    

    // Function to get notification link based on type

    function getNotificationLink(notification) {

        if (notification.type.includes('booking_confirmed') || 

            notification.type.includes('booking_rejected') || 

            notification.type.includes('booking_canceled') || 

            notification.type.includes('booking_cancelled_by_client')) {

            return `/admin/booking/view/${notification.reference_id}`;

        } else if (notification.type.includes('payment_submitted')) {

            return `/admin/payment-management?booking_id=${notification.reference_id}`;

        } else {

            return 'javascript:void(0)';

        }

    }

    

    // Mark notification as read when clicked

    document.addEventListener('click', async function(e) {

        const notificationItem = e.target.closest('.notification-item');

        if (notificationItem) {

            e.preventDefault(); // Prevent navigation

            const notificationId = notificationItem.getAttribute('data-id');

            const referenceId = notificationItem.getAttribute('data-reference-id');

            const notificationType = notificationItem.getAttribute('data-type');

            

            // Create a safe notification object with default values

            const notification = {

                notification_id: notificationId || '',

                reference_id: referenceId || '',

                type: notificationType || 'information',

                message: '',

                created_at: new Date().toISOString(),

                is_read: false

            };

            

            // Try to extract message and time from the notification item

            try {

                const messageElement = notificationItem.querySelector('.notification-message');

                if (messageElement) {

                    notification.message = messageElement.textContent;

                }

                

                const timeElement = notificationItem.querySelector('.notification-time');

                if (timeElement) {

                    // Extract only the date/time text, not including any child elements

                    const timeText = Array.from(timeElement.childNodes)

                        .filter(node => node.nodeType === Node.TEXT_NODE)

                        .map(node => node.textContent.trim())

                        .join('');

                    

                    if (timeText) {

                        notification.created_at = timeText;

                    }

                }

            } catch (error) {

                console.error('Error extracting notification details:', error);

            }

            

            // Only show notification details if we have a valid notification type

            if (notification.type) {

                // Function to show notification details popup

                async function showNotificationDetails(notification, notificationItem) {

                    if (!notification) return;

                    

                    // Position the popup next to the notification item

                    const rect = notificationItem.getBoundingClientRect();



                    // Calculate position to keep popup within viewport

                    const viewportWidth = window.innerWidth;

                    const viewportHeight = window.innerHeight;

                    const popupWidth = 350; // Width of the popup



                    // Check if there's enough space to the right

                    let left;

                    if (rect.right + popupWidth + 60 < viewportWidth) {

                        // Position to the right of the notification

                        left = rect.right + 600;

                    } else {

                        // Position to the left of the notification

                        left = Math.max(20, rect.left - popupWidth - 20);

                    }



                    // Add 20px extra space from the top

                    const top = Math.min(rect.top + 80, viewportHeight - 400);

                    

                    // Apply the calculated position

                    notificationDetailsPopup.style.top = `${top}px`;

                    notificationDetailsPopup.style.left = `${left}px`;

                    notificationDetailsPopup.style.display = 'block';

                    

                    // Determine icon class and background based on notification type

                    let iconClass = 'bi-info-circle-fill text-primary';

                    let iconBg = 'info';

                    let statusClass = 'bg-primary';

                    let statusText = 'Information';

                    

                    if (notification.type.includes('booking_confirmed')) {

                        iconClass = 'bi-check-circle-fill text-success';

                        iconBg = 'success';

                        statusClass = 'bg-success';

                        statusText = 'Confirmed';

                    } else if (notification.type.includes('booking_rejected') || notification.type.includes('booking_canceled') || notification.type.includes('booking_cancelled_by_client')) {

                        iconClass = 'bi-x-circle-fill text-danger';

                        iconBg = 'danger';

                        statusClass = 'bg-danger';

                        statusText = notification.type.includes('rejected') ? 'Rejected' : 'Canceled';

                    } else if (notification.type.includes('payment_submitted')) {

                        iconClass = 'bi-cash-coin text-primary';

                        iconBg = 'info';

                        statusClass = 'bg-info';

                        statusText = 'Payment';

                    } else if (notification.type.includes('rebooking')) {

                        iconClass = 'bi-arrow-repeat text-warning';

                        iconBg = 'warning';

                        statusClass = 'bg-warning';

                        statusText = 'Rebooking';

                    }

                    

                    // Format date

                    const date = new Date(notification.created_at);

                    const formattedDate = date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

                    

                    // Set the view related content link

                    const viewRelatedBtn = document.querySelector('.view-related-content');

                    if (notification.reference_id) {

                        if (notification.type.includes('booking_confirmed') || 

                            notification.type.includes('booking_rejected') || 

                            notification.type.includes('booking_canceled') || 

                            notification.type.includes('booking_cancelled_by_client')) {

                            viewRelatedBtn.href = `/admin/booking/view/${notification.reference_id}`;

                            viewRelatedBtn.style.display = 'block';

                        } else if (notification.type.includes('payment_submitted')) {

                            viewRelatedBtn.href = `/admin/payment-management?booking_id=${notification.reference_id}`;

                            viewRelatedBtn.style.display = 'block';

                        } else {

                            viewRelatedBtn.style.display = 'none';

                        }

                    } else {

                        viewRelatedBtn.style.display = 'none';

                    }

                    

                    // Load notification details

                    const detailContent = notificationDetailsPopup.querySelector('.notification-detail-content');

                    

                    detailContent.innerHTML = `

                        <div class="mb-3 d-flex align-items-center">

                            <div class="notification-icon ${iconBg} me-3" style="width: 48px; height: 48px;">

                                <i class="bi ${iconClass} fs-4"></i>

                            </div>

                            <div>

                                <span class="badge ${statusClass} mb-2">${statusText}</span>

                                <h6 class="fw-bold mb-0">${notification.type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</h6>

                            </div>

                        </div>

                        

                        <div class="mb-3">

                            <label class="form-label fw-medium">Message</label>

                            <p class="mb-0">${notification.message}</p>

                        </div>

                        

                        <div class="mb-3">

                            <label class="form-label fw-medium">Date & Time</label>

                            <p class="mb-0"><i class="bi bi-clock me-1"></i>${formattedDate}</p>

                        </div>

                        

                        ${notification.reference_id ? `

                        <div class="mb-3">

                            <label class="form-label fw-medium">Reference ID</label>

                            <p class="mb-0">${notification.reference_id}</p>

                        </div>

                        ` : ''}

                        

                        <div class="mb-0 d-none">

                            <label class="form-label fw-medium">Status</label>

                            <p class="mb-0">

                                <span class="badge ${notification.is_read ? 'bg-secondary' : 'bg-success'} rounded-pill">

                                    ${notification.is_read ? 'Read' : 'Unread'}

                                </span>

                            </p>

                        </div>

                    `;

                }

                

                // Show notification details popup

                showNotificationDetails(notification, notificationItem);

            }

            

            try {

                const response = await fetch('/admin/notifications/mark-read', {

                    method: 'POST',

                    headers: {

                        'Content-Type': 'application/json',

                        'Accept': 'application/json'

                    },

                    body: JSON.stringify({ notification_id: notificationId })

                });

                

                if (!response.ok) {

                    throw new Error(`HTTP error! Status: ${response.status}`);

                }

                

                const data = await response.json();

                

                if (data.success) {

                    // Update UI

                    notificationItem.classList.remove('bg-light');

                    const badge = notificationItem.querySelector('.badge');

                    if (badge) badge.remove();

                    

                    // Reload notification count

                    loadNotifications();

                }

            } catch (error) {

                console.error('Error marking notification as read:', error);

            }

        }

        

        // Close notification details popup when clicking outside or when notification dropdown is closed

        if (!notificationDetailsPopup.contains(e.target) && 

            !e.target.closest('.notification-item') &&

            notificationDetailsPopup.style.display === 'block') {

            notificationDetailsPopup.style.display = 'none';

        }

    });

    

    // Close notification details popup when close button is clicked

    if (closeNotificationDetailsBtn) {

        closeNotificationDetailsBtn.addEventListener('click', function() {

            notificationDetailsPopup.style.display = 'none';

        });

    }

    

    // Handle bootstrap dropdown events to close notification details popup

    notificationToggle.addEventListener('hidden.bs.dropdown', function() {

        if (notificationDetailsPopup.style.display === 'block') {

            notificationDetailsPopup.style.display = 'none';

        }

    });

    

    // Mark all as read

    if (markAllReadBtn) {

        markAllReadBtn.addEventListener('click', async function(e) {

            e.preventDefault();

            

            try {

                const response = await fetch('/admin/notifications/mark-all-read', {

                    method: 'POST',

                    headers: {

                        'Content-Type': 'application/json',

                        'Accept': 'application/json'

                    },

                    body: JSON.stringify({})

                });

                

                if (!response.ok) {

                    throw new Error(`HTTP error! Status: ${response.status}`);

                }

                

                const data = await response.json();

                

                if (data.success) {

                    loadNotifications();

                }

            } catch (error) {

                console.error('Error marking all notifications as read:', error);

            }

        });

    }

    

    // Load notifications when the page loads

    loadNotifications();

    

    // Refresh notifications every 30 seconds

    // setInterval(loadNotifications, 30000);
    
    // Profile dropdown functionality
    const profileToggle = document.getElementById('profileToggle');
    const profileDropdown = document.getElementById('profileDropdownMenu');
    
    // Close profile dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!profileToggle.contains(e.target) && !profileDropdown.contains(e.target)) {
            const dropdown = bootstrap.Dropdown.getInstance(profileToggle);
            if (dropdown) {
                dropdown.hide();
            }
        }
    });
    
    // Handle logout confirmation
    const logoutLink = document.getElementById('logoutLink');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();         
            Swal.fire({
                 title: 'Logout Confirmation',
                 text: 'Are you sure you want to logout?',
                 icon: 'question',
                 showCancelButton: true,
                 confirmButtonColor: '#198754',
                 cancelButtonColor: '#6c757d',
                 confirmButtonText: 'Yes, Logout',
                 cancelButtonText: 'Cancel',
                 reverseButtons: true,
                 customClass: {
                     popup: 'swal2-popup-custom',
                     confirmButton: 'btn btn-success',
                     cancelButton: 'btn btn-secondary'
                 }
            }).then((result) => {
                 if (result.isConfirmed) {
                     // Show loading state
                     Swal.fire({
                         title: 'Logging out...',
                         text: 'Please wait while we log you out.',
                         allowOutsideClick: false,
                         allowEscapeKey: false,
                         showConfirmButton: false,
                         didOpen: () => {
                             Swal.showLoading();
                         }
                     });
              
                     // Redirect to logout
                     setTimeout(() => {
                         window.location.href = '/admin/logout';
                     }, 1000);
                 }
            });
        });
    }
});

</script>