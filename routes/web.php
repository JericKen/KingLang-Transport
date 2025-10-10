<?php

// Define controller classes

$controllerClasses = [

    'client' => [

        'AuthController' => __DIR__ . "/../app/controllers/client/AuthController.php",

        'BookingController' => __DIR__ . "/../app/controllers/client/BookingController.php",

        'NotificationsController' => __DIR__ . "/../app/controllers/client/NotificationsController.php",

        'TestimonialController' => __DIR__ . "/../app/controllers/client/TestimonialController.php",

        'PayMongoController' => __DIR__ . "/../app/controllers/client/PayMongoController.php",

        'ChatController' => __DIR__ . "/../app/controllers/client/ChatController.php",

        'SlideshowController' => __DIR__ . "/../app/controllers/client/SlideshowController.php",
        'PastTripsController' => __DIR__ . "/../app/controllers/client/PastTripsController.php",

    ],

    'admin' => [

        'BookingManagementController' => __DIR__ . "/../app/controllers/admin/BookingManagementController.php",

        'AuthController' => __DIR__ . "/../app/controllers/admin/AuthController.php",   

        'PaymentManagementController' => __DIR__ . "/../app/controllers/admin/PaymentManagementController.php",

        'ReportController' => __DIR__ . "/../app/controllers/admin/ReportController.php",

        'UserManagementController' => __DIR__ . "/../app/controllers/admin/UserManagementController.php",

        'SettingsController' => __DIR__ . "/../app/controllers/admin/SettingsController.php",

        'NotificationsController' => __DIR__ . "/../app/controllers/admin/NotificationsController.php",

        'AuditTrailController' => __DIR__ . "/../app/controllers/admin/AuditTrailController.php",

        'BusManagementController' => __DIR__ . "/../app/controllers/admin/BusManagementController.php",

        'DriverManagementController' => __DIR__ . "/../app/controllers/admin/DriverManagementController.php",

        'BookingReviewReminderController' => __DIR__ . "/../app/controllers/admin/BookingReviewReminderController.php",

        'TestimonialManagementController' => __DIR__ . "/../app/controllers/admin/TestimonialManagementController.php",

        'SlideshowManagementController' => __DIR__ . "/../app/controllers/admin/SlideshowManagementController.php",
        'PastTripsController' => __DIR__ . "/../app/controllers/admin/PastTripsController.php",

        'AdminChatController' => __DIR__ . "/../app/controllers/admin/AdminChatController.php",

    ]

];



// Create lazy loading controllers

$controllers = [];



// Get current request

$request = $_SERVER["REQUEST_URI"];



// Remove query string for route matching

$requestPath = parse_url($request, PHP_URL_PATH);



// Special case for reset password

if (preg_match("/reset-password\/([a-zA-Z0-9]+)/", $requestPath, $matches)) {

    $token = $matches[1];

    require_once $controllerClasses['client']['AuthController'];

    $clientAuthController = new ClientAuthController();

    $clientAuthController->showResetForm($token);

    exit();

}



// Determine which controller we need for this request

$controllerType = null;

$controllerName = null;



if (strpos($requestPath, '/admin') === 0) {

    $controllerType = 'admin';

} else {

    $controllerType = 'client';

}



// Now handle the route

switch ($requestPath) {

    // user

    case "/":

    case "/home":

        require_once __DIR__ . "/../public/home.php";

        break;

    case "/home/login":

        require_once $controllerClasses['client']['AuthController'];

        $controller = new ClientAuthController();

        $controller->loginForm();

        break;

    case "/home/signup":

        require_once $controllerClasses['client']['AuthController'];

        $controller = new ClientAuthController();

        $controller->signupForm();

        break;

    case "/client/login":

        require_once $controllerClasses['client']['AuthController'];

        $controller = new ClientAuthController();

        $controller->login();

        break;

    case "/client/google-login":

        require_once $controllerClasses['client']['AuthController'];

        $controller = new ClientAuthController();

        $controller->googleLogin();

        break;

    case "/client/signup":

        require_once $controllerClasses['client']['AuthController'];

        $controller = new ClientAuthController();

        $controller->signup();

        break;

    case "/client/home":

        require_once __DIR__ . "/../app/views/client/home.php";

        break;

    case "/home/my-account":

        require_once $controllerClasses['client']['AuthController'];

        $controller = new ClientAuthController();

        $controller->manageAccountForm();

        break;

    case "/get-client-information":

        require_once $controllerClasses['client']['AuthController'];

        $controller = new ClientAuthController();

        $controller->getClientInformation();

        break;

    case "/update-client-information":

        require_once $controllerClasses['client']['AuthController'];

        $controller = new ClientAuthController();

        $controller->updateClientInformation();

        break;

    case "/logout":

        require_once $controllerClasses['client']['AuthController'];

        $controller = new ClientAuthController();

        $controller->logout();

        break;

    case "/update-client-password":

        require_once $controllerClasses['client']['AuthController'];

        $controller = new ClientAuthController();

        $controller->updateClientPassword();

        break;

    case "/upload-profile-image":
        require_once $controllerClasses['client']['AuthController'];
        $controller = new ClientAuthController();
        $controller->uploadProfileImage();
        break;

    case "/remove-profile-image":
        require_once $controllerClasses['client']['AuthController'];
        $controller = new ClientAuthController();
        $controller->removeProfileImage();
        break;



    // testimonials

    case "/home/feedback":

        require_once $controllerClasses['client']['TestimonialController'];

        $controller = new TestimonialController();

        $controller->testimonialForm();

        break;

    case "/home/testimonials/submit":

        require_once $controllerClasses['client']['TestimonialController'];

        $controller = new TestimonialController();

        $controller->submitTestimonial();

        break;

    case "/home/testimonials/eligible-bookings":

        require_once $controllerClasses['client']['TestimonialController'];

        $controller = new TestimonialController();

        $controller->getEligibleBookings();

        break;

    case "/home/testimonials/my-reviews":

        require_once $controllerClasses['client']['TestimonialController'];

        $controller = new TestimonialController();

        $controller->getUserTestimonials();

        break;

    case "/home/testimonials/public":

        require_once $controllerClasses['client']['TestimonialController'];

        $controller = new TestimonialController();

        $controller->getApprovedTestimonials();

        break;

    // Slideshow API for frontend

    case "/api/slideshow/images":

        require_once $controllerClasses['client']['SlideshowController'];

        $controller = new SlideshowController();

        $controller->getActiveSlideshowImages();

        break;

    // Past Trips API for frontend
    case "/api/past-trips/images":
        require_once $controllerClasses['client']['PastTripsController'];
        $controller = new PastTripsController();
        $controller->getActivePastTrips();
        break;



    // forgot password

    case "/fogot-password":

        require_once $controllerClasses['client']['AuthController'];

        $controller = new ClientAuthController();

        $controller->showForgotForm();

        break;  

    case "/send-reset-link":

        require_once $controllerClasses['client']['AuthController'];

        $controller = new ClientAuthController();

        $controller->sendResetLink();

        break;

    case "/update-password":

        require_once $controllerClasses['client']['AuthController'];

        $controller = new ClientAuthController();

        $controller->resetPassword();

        break;



    // bookings

    case "/home/contact":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->clientInfoForm();

        break;

    case "/contact/submit":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->addClient();

        break;

    case "/get-available-buses":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->findAvailableBuses();

        break;

    case "/home/book":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->bookingForm();

        break;



    case "/get-address":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->getAddress();

        break;   

    case "/get-distance":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->getDistance();

        break;

    case "/get-route":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->processCoordinates();

        break;

    case "/get-bus-availability":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->getAvailableBuses();

        break;

    case "/get-driver-availability":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->getDriverAvailability();

        break;

    case "/get-total-cost":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->getTotalCost();

        break;

        

    case "/request-booking":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->requestBooking();

        break;

    case "/home/booking-requests":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->showBookingRequestTable();

        break;

    case "/home/booking-request":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->showBookingDetail();

        break;

    case "/home/get-booking-requests":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->getAllBookings();

        break;

    case "/request-rebooking":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->requestRebooking();

        break;

    case "/cancel-booking":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->cancelBooking();

        break;



    case "/get-booking":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->getBooking();

        break;



    case "/home/booking-statistics":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->getBookingStatistics();

        break;

        

    case "/home/get-booking-details":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->getBookingDetails();

        break;

    case "/home/get-payment-settings":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->getPaymentSettings();

        break;

    case "/home/calendar-events":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->getCalendarEvents();

        break;

        

    case "/home/export-bookings":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->exportBookings();

        break;

        

    case "/home/print-invoice":

    case preg_match('|^/home/print-invoice/([0-9]+)$|', $requestPath, $matches) ? $requestPath : "":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->printInvoice($matches[1] ?? null);

        break;



    case "/home/print-contract":

    case preg_match('|^/home/print-contract/([0-9]+)$|', $requestPath, $matches) ? $requestPath : "":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->printContract($matches[1] ?? null);

        break;



    case "/payment/process":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->addPayment();

        break;



    // PayMongo routes

    case "/paymongo/webhook":

        require_once $controllerClasses['client']['PayMongoController'];

        $controller = new PayMongoController();

        $controller->handleWebhook();

        break;

    case "/paymongo/success":

        require_once $controllerClasses['client']['PayMongoController'];

        $controller = new PayMongoController();

        $controller->handleSuccess();

        break;

    case "/paymongo/cancel":

        require_once $controllerClasses['client']['PayMongoController'];

        $controller = new PayMongoController();

        $controller->handleCancel();

        break;

    case "/paymongo/success-page":

        require_once $controllerClasses['client']['PayMongoController'];

        $controller = new PayMongoController();

        $controller->showSuccessPage();

        break;

    case "/paymongo/status":

        require_once $controllerClasses['client']['PayMongoController'];

        $controller = new PayMongoController();

        $controller->getPaymentStatus();

        break;



    // admin routes

    case "/admin/bookings":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $adminController = new BookingManagementController();

        $adminController->getAllBookings();

        

        require_once $controllerClasses['client']['BookingController'];

        $clientController = new BookingController();

        $clientController->updatePastBookings();

        break;

    case "/admin/login":

        require_once $controllerClasses['admin']['AuthController'];

        $controller = new AuthController();

        $controller->loginForm();

        break;

    case "/admin/submit-login":

        require_once $controllerClasses['admin']['AuthController'];

        $controller = new AuthController();

        $controller->login();

        break;

    case "/admin/logout":

        require_once $controllerClasses['admin']['AuthController'];

        $controller = new AuthController();

        $controller->logout();

        break;

    case "/admin/dashboard":

        require_once $controllerClasses['admin']['AuthController'];

        $controller = new AuthController();

        $controller->adminDashBoard();

        break;

    case "/admin/summary-metrics":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->summaryMetrics();

        break;

    case "/admin/payment-method-data":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->paymentMethodChart();

        break;



    case "/admin/monthly-booking-trends":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->monthlyBookingTrends();

        break;

        

    case "/admin/top-destinations":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->topDestinations();

        break;

        

    case "/admin/booking-status-distribution":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->bookingStatusDistribution();

        break;

        

    case "/admin/revenue-trends":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->revenueTrends();

        break;

    case "/admin/unpaid-bookings-data":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->unpaidBookingsData();

        break;

    case "/admin/peak-booking-periods-data":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->peakBookingPeriodsData();

        break;

    case "/admin/total-income-data":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->totalIncomeData();

        break;

    case "/admin/outstanding-balances-data":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->outstandingBalancesData();

        break;

    case "/admin/top-paying-clients-data":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->topPayingClientsData();

        break;

    case "/admin/discounts-given-data":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->discountsGivenData();

        break;

    case "/admin/cancellations-by-reason":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->cancellationsByReasonData();

        break;

    case "/admin/average-revenue-per-trip":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->averageRevenuePerTripData();

        break;

    case "/admin/bus-availability-data":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->busAvailabilityData();

        break;

    case "/admin/driver-assignments-per-day":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->driverAssignmentsPerDayData();

        break;

    case "/admin/average-trip-duration":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->averageTripDurationData();

        break;

    case "/admin/repeat-clients-data":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->repeatClientsData();

        break;

    case "/admin/new-clients-data":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->newClientsData();

        break;

    case "/admin/client-satisfaction-summary":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->clientSatisfactionSummaryData();

        break;



    case "/admin/booking-requests":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->showBookingTable();

        break;

    case "/admin/confirm-booking":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->confirmBooking();

        break;

    case "/admin/reject-booking":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->rejectBooking();

        break;

    case "/admin/reject-rebooking":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->rejectRebooking();

        break;

    case "/admin/cancel-booking":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->cancelBooking();

        break;

    case "/admin/rebooking-requests":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->showReschedRequestTable();

        break;

    case "/admin/get-rebooking-requests":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->getRebookingRequests();

        break;

    case "/admin/get-booking-audit-details":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->getBookingAuditDetails();

        break;

    case "/admin/confirm-rebooking-request":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->confirmRebookingRequest();

        break;

    case "/admin/booking-request":

    case "/admin/rebooking-request":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->showBookingDetail();

        break;

    case "/admin/get-booking":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->getBooking();

        break;

    case "/admin/get-booking-details":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->getBookingDetails();

        break;

    case "/admin/get-booking-assignments":
        require_once $controllerClasses['admin']['BookingManagementController'];
        $controller = new BookingManagementController();
        $controller->getBookingAssignments();
        break;
    case "/admin/get-available-resources-for-booking":
        require_once $controllerClasses['admin']['BookingManagementController'];
        $controller = new BookingManagementController();
        $controller->getAvailableResourcesForBooking();
        break;
    case "/admin/update-booking-assignments":
        require_once $controllerClasses['admin']['BookingManagementController'];
        $controller = new BookingManagementController();
        $controller->updateBookingAssignments();
        break;
    case "/admin/print-invoice":

    case preg_match('|^/admin/print-invoice/([0-9]+)$|', $requestPath, $matches) ? $requestPath : "":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->printInvoice($matches[1] ?? null);

        break;



    case "/admin/print-contract":

    case preg_match('|^/admin/print-contract/([0-9]+)$|', $requestPath, $matches) ? $requestPath : "":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->printContract($matches[1] ?? null);

        break;



    case "/admin/booking-stats":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->getBookingStats();

        break;



    case "/admin/calendar-bookings":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->getCalendarBookings();

        break;



    case "/admin/search-bookings":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->searchBookings();

        break;



    case "/admin/unpaid-bookings":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->getUnpaidBookings();

        break;



    case "/admin/partially-paid-bookings":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->getPartiallyPaidBookings();

        break;



    case "/admin/export-bookings":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->exportBookings();

        break;



    case "/admin/create-booking":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->showCreateBookingForm();

        break;

        

    case "/admin/submit-booking":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->createBooking();

        break;



    case "/admin/get-address":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->getAddress();

        break;   

    case "/admin/get-distance":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->getDistance();

        break;

    case "/admin/get-route":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->processCoordinates();

        break;

    case "/admin/get-total-cost":

        require_once $controllerClasses['admin']['BookingManagementController'];

        $controller = new BookingManagementController();

        $controller->getTotalCost();

        break;



    case "/getDieselPrice":

        require_once $controllerClasses['client']['BookingController'];

        $controller = new BookingController();

        $controller->getDieselPrice();

        break;



    case "/admin/get-users":

        require_once $controllerClasses['admin']['UserManagementController'];

        $controller = new UserManagementController();

        $controller->getUserListing();

        break;

        

    case "/admin/users":

        require_once $controllerClasses['admin']['UserManagementController'];

        $controller = new UserManagementController();

        $controller->showUserManagement();

        break;

        

    case "/admin/add-user":

        require_once $controllerClasses['admin']['UserManagementController'];

        $controller = new UserManagementController();

        $controller->addUser();

        break;

        

    case "/admin/update-user":

        require_once $controllerClasses['admin']['UserManagementController'];

        $controller = new UserManagementController();

        $controller->updateUser();

        break;

        

    case "/admin/delete-user":

        require_once $controllerClasses['admin']['UserManagementController'];

        $controller = new UserManagementController();

        $controller->deleteUser();

        break;



    case "/admin/restore-user":

        require_once $controllerClasses['admin']['UserManagementController'];

        $controller = new UserManagementController();

        $controller->restoreUser();

        break;



    case "/admin/get-user-details":

        require_once $controllerClasses['admin']['UserManagementController'];

        $controller = new UserManagementController();

        $controller->getUserDetails();

        break;



    case "/admin/get-user-stats":

        require_once $controllerClasses['admin']['UserManagementController'];

        $controller = new UserManagementController();

        $controller->getUserStats();

        break;



    case "/admin/payment-management":

        require_once $controllerClasses['admin']['PaymentManagementController'];

        $controller = new PaymentManagementController();

        $controller->index();

        break;

    case "/admin/payments/get":

        require_once $controllerClasses['admin']['PaymentManagementController'];

        $controller = new PaymentManagementController();

        $controller->getPayments();

        break;

    case "/admin/payments/stats":

        require_once $controllerClasses['admin']['PaymentManagementController'];

        $controller = new PaymentManagementController();

        $controller->getPaymentStats();

        break;

    case "/admin/payments/view-proof":

        require_once $controllerClasses['admin']['PaymentManagementController'];

        $controller = new PaymentManagementController();

        $controller->viewProof();

        break;

    case "/admin/payments/confirm":

        require_once $controllerClasses['admin']['PaymentManagementController'];

        $controller = new PaymentManagementController();

        $controller->confirmPayment();

        break;

    case "/admin/payments/reject":

        require_once $controllerClasses['admin']['PaymentManagementController'];

        $controller = new PaymentManagementController();

        $controller->rejectPayment();

        break;

    case "/admin/payments/record-manual":

        require_once $controllerClasses['admin']['PaymentManagementController'];

        $controller = new PaymentManagementController();

        $controller->recordManualPayment();

        break;

    case "/admin/payments/search-bookings":

        require_once $controllerClasses['admin']['PaymentManagementController'];

        $controller = new PaymentManagementController();

        $controller->searchBookings();

        break;

    case "/admin/payments/search-clients":

        require_once $controllerClasses['admin']['PaymentManagementController'];

        $controller = new PaymentManagementController();

        $controller->searchClients();

        break;

    case "/admin/payments/get-booking-details":

        require_once $controllerClasses['admin']['PaymentManagementController'];

        $controller = new PaymentManagementController();

        $controller->getBookingDetails();

        break;



    case "/admin/reports":

        require_once $controllerClasses['admin']['ReportController'];

        $controller = new ReportController();

        $controller->index();

        break;

    case "/admin/reports/booking-summary":

        require_once $controllerClasses['admin']['ReportController'];

        $controller = new ReportController();

        $controller->getBookingSummary();

        break;

    case "/admin/reports/monthly-trend":

        require_once $controllerClasses['admin']['ReportController'];

        $controller = new ReportController();

        $controller->getMonthlyBookingTrend();

        break;

    case "/admin/reports/top-destinations":

        require_once $controllerClasses['admin']['ReportController'];

        $controller = new ReportController();

        $controller->getTopDestinations();

        break;

    case "/admin/reports/payment-methods":

        require_once $controllerClasses['admin']['ReportController'];

        $controller = new ReportController();

        $controller->getPaymentMethodDistribution();

        break;

    case "/admin/reports/cancellations":

        require_once $controllerClasses['admin']['ReportController'];

        $controller = new ReportController();

        $controller->getCancellationReport();

        break;

    case "/admin/reports/detailed-bookings":

        require_once $controllerClasses['admin']['ReportController'];

        $controller = new ReportController();

        $controller->getDetailedBookingList();

        break;

    case "/admin/reports/financial-summary":

        require_once $controllerClasses['admin']['ReportController'];

        $controller = new ReportController();

        $controller->getFinancialSummary();

        break;

    case "/admin/reports/client-booking-history":

        require_once $controllerClasses['admin']['ReportController'];

        $controller = new ReportController();

        $controller->getClientBookingHistory();

        break;

    case "/admin/reports/export-bookings":

        require_once $controllerClasses['admin']['ReportController'];

        $controller = new ReportController();

        $controller->getDetailedBookingList();

        break;

    case "/admin/bus-management":

        require_once $controllerClasses['admin']['BusManagementController'];

        $controller = new BusManagementController();

        $controller->showBusManagement();

        break;

    case "/admin/get-all-buses":

        require_once $controllerClasses['admin']['BusManagementController'];

        $controller = new BusManagementController();

        $controller->getAllBuses();

        break;

    case "/admin/add-bus":

        require_once $controllerClasses['admin']['BusManagementController'];

        $controller = new BusManagementController();

        $controller->addBus();

        break;

    case "/admin/update-bus":

        require_once $controllerClasses['admin']['BusManagementController'];

        $controller = new BusManagementController();

        $controller->updateBus();

        break;

    case "/admin/delete-bus":

        require_once $controllerClasses['admin']['BusManagementController'];

        $controller = new BusManagementController();

        $controller->deleteBus();

        break;

    case "/admin/restore-bus":

        require_once $controllerClasses['admin']['BusManagementController'];

        $controller = new BusManagementController();

        $controller->restoreBus();

        break;

    case "/admin/get-bus-availability":

        require_once $controllerClasses['admin']['BusManagementController'];

        $controller = new BusManagementController();

        $controller->getBusAvailability();

        break;

    case "/admin/get-bus-schedule":

        require_once $controllerClasses['admin']['BusManagementController'];

        $controller = new BusManagementController();

        $controller->getBusSchedule();

        break;

    case "/admin/get-bus-stats":

        require_once $controllerClasses['admin']['BusManagementController'];

        $controller = new BusManagementController();

        $controller->getBusStats();

        break;



    case "/admin/driver-management":

        require_once $controllerClasses['admin']['DriverManagementController'];

        $controller = new DriverManagementController();

        $controller->showDriverManagement();

        break;

    case "/admin/api/drivers/all":

        require_once $controllerClasses['admin']['DriverManagementController'];

        $controller = new DriverManagementController();

        $controller->getAllDrivers();

        break;

    case "/admin/api/drivers/get":

        require_once $controllerClasses['admin']['DriverManagementController'];

        $controller = new DriverManagementController();

        $controller->getDriverById();

        break;

    case "/admin/api/drivers/add":

        require_once $controllerClasses['admin']['DriverManagementController'];

        $controller = new DriverManagementController();

        $controller->addDriver();

        break;

    case "/admin/api/drivers/update":

        require_once $controllerClasses['admin']['DriverManagementController'];

        $controller = new DriverManagementController();

        $controller->updateDriver();

        break;

    case "/admin/api/drivers/delete":

        require_once $controllerClasses['admin']['DriverManagementController'];

        $controller = new DriverManagementController();

        $controller->deleteDriver();

        break;

    case "/admin/api/drivers/restore":

        require_once $controllerClasses['admin']['DriverManagementController'];

        $controller = new DriverManagementController();

        $controller->restoreDriver();

        break;

    case "/admin/api/drivers/statistics":

        require_once $controllerClasses['admin']['DriverManagementController'];

        $controller = new DriverManagementController();

        $controller->getDriverStatistics();

        break;

    case "/admin/api/drivers/most-active":

        require_once $controllerClasses['admin']['DriverManagementController'];

        $controller = new DriverManagementController();

        $controller->getMostActiveDrivers();

        break;

    case "/admin/api/drivers/expiring-licenses":

        require_once $controllerClasses['admin']['DriverManagementController'];

        $controller = new DriverManagementController();

        $controller->getDriversWithExpiringLicenses();

        break;

    case "/admin/api/drivers/schedule":

        require_once $controllerClasses['admin']['DriverManagementController'];

        $controller = new DriverManagementController();

        $controller->getDriverSchedule();

        break;



    case "/admin/settings":

        require_once $controllerClasses['admin']['SettingsController'];

        $controller = new SettingsController();

        $controller->index();

        break;

    case "/admin/get-all-settings":

        require_once $controllerClasses['admin']['SettingsController'];

        $controller = new SettingsController();

        $controller->getAllSettings();

        break;

    case "/admin/get-settings-by-group":

        require_once $controllerClasses['admin']['SettingsController'];

        $controller = new SettingsController();

        $controller->getSettingsByGroup();

        break;

    case "/admin/update-settings":

        require_once $controllerClasses['admin']['SettingsController'];

        $controller = new SettingsController();

        $controller->updateSettings();

        break;

    case "/admin/add-setting":

        require_once $controllerClasses['admin']['SettingsController'];

        $controller = new SettingsController();

        $controller->addSetting();

        break;

    case "/admin/delete-setting":

        require_once $controllerClasses['admin']['SettingsController'];

        $controller = new SettingsController();

        $controller->deleteSetting();

        break;

        

    case "/admin/notifications":

        require_once $controllerClasses['admin']['NotificationsController'];

        $controller = new NotificationsController();

        $controller->index();

        break;

    case "/admin/notifications/mark-read":

        require_once $controllerClasses['admin']['NotificationsController'];

        $controller = new NotificationsController();

        $controller->markAsRead();

        break;

    case "/admin/notifications/mark-all-read":

        require_once $controllerClasses['admin']['NotificationsController'];

        $controller = new NotificationsController();

        $controller->markAllAsRead();

        break;

    case "/admin/notifications/get":

        require_once $controllerClasses['admin']['NotificationsController'];

        $controller = new NotificationsController();

        $controller->getNotifications();

        break;

        

    case "/client/notifications":

        require_once $controllerClasses['client']['NotificationsController'];

        $controller = new NotificationsController();

        $controller->index();

        break;

    case "/client/notifications/get":

        require_once $controllerClasses['client']['NotificationsController'];

        $controller = new NotificationsController();

        $controller->getNotifications();

        break;

    case "/client/notifications/mark-read":

        require_once $controllerClasses['client']['NotificationsController'];

        $controller = new NotificationsController();

        $controller->markAsRead();

        break;

    case "/client/notifications/mark-all-read":

        require_once $controllerClasses['client']['NotificationsController'];

        $controller = new NotificationsController();

        $controller->markAllAsRead();

        break;

    case "/client/notifications/add-test":

        require_once $controllerClasses['client']['NotificationsController'];

        $controller = new NotificationsController();

        $controller->addTestNotification();

        break;

        

    // Payment deadline check routes

    case "/admin/check-payment-deadlines":

        require_once __DIR__ . "/../app/controllers/admin/BookingDeadlineController.php";

        $controller = new BookingDeadlineController();

        $controller->checkDeadlines();

        break;



    // Booking completion check routes

    case "/admin/check-booking-completions":

        require_once __DIR__ . "/../app/controllers/admin/BookingCompletionController.php";

        $controller = new BookingCompletionController();

        $controller->checkCompletions();

        break;



    // Audit Trail Management

    case "/admin/audit-trail":

        require_once $controllerClasses['admin']['AuditTrailController'];

        $controller = new AuditTrailController();

        $controller->index();

        break;

    case "/admin/get-audit-trails":

        require_once $controllerClasses['admin']['AuditTrailController'];

        $controller = new AuditTrailController();

        $controller->getAuditTrails();

        break;

    case "/admin/get-audit-details":

        require_once $controllerClasses['admin']['AuditTrailController'];

        $controller = new AuditTrailController();

        $controller->getAuditDetails();

        break;



    // Testimonial Management

    case "/admin/testimonials":

        require_once $controllerClasses['admin']['TestimonialManagementController'];

        $controller = new TestimonialManagementController();

        $controller->testimonialManagement();

        break;

    case "/admin/testimonials/list":

        require_once $controllerClasses['admin']['TestimonialManagementController'];

        $controller = new TestimonialManagementController();

        $controller->getTestimonials();

        break;

    case "/admin/testimonials/stats":

        require_once $controllerClasses['admin']['TestimonialManagementController'];

        $controller = new TestimonialManagementController();

        $controller->getStats();

        break;

    case "/admin/testimonials/approve":

        require_once $controllerClasses['admin']['TestimonialManagementController'];

        $controller = new TestimonialManagementController();

        $controller->approveTestimonial();

        break;

    case "/admin/testimonials/reject":

        require_once $controllerClasses['admin']['TestimonialManagementController'];

        $controller = new TestimonialManagementController();

        $controller->rejectTestimonial();

        break;

    case "/admin/testimonials/toggle-featured":

        require_once $controllerClasses['admin']['TestimonialManagementController'];

        $controller = new TestimonialManagementController();

        $controller->toggleFeatured();

        break;

    case "/admin/testimonials/delete":

        require_once $controllerClasses['admin']['TestimonialManagementController'];

        $controller = new TestimonialManagementController();

        $controller->deleteTestimonial();

        break;

    case "/admin/testimonials/bulk-action":

        require_once $controllerClasses['admin']['TestimonialManagementController'];

        $controller = new TestimonialManagementController();

        $controller->bulkAction();

        break;

    case "/admin/testimonials/details":

        require_once $controllerClasses['admin']['TestimonialManagementController'];

        $controller = new TestimonialManagementController();

        $controller->getTestimonialDetails();

        break;

    // Slideshow Management

    case "/admin/slideshow":

        require_once $controllerClasses['admin']['SlideshowManagementController'];

        $controller = new SlideshowManagementController();

        $controller->slideshowManagement();

        break;

    case "/admin/slideshow/list":

        require_once $controllerClasses['admin']['SlideshowManagementController'];

        $controller = new SlideshowManagementController();

        $controller->getSlideshowImages();

        break;

    case "/admin/slideshow/upload":

        require_once $controllerClasses['admin']['SlideshowManagementController'];

        $controller = new SlideshowManagementController();

        $controller->uploadSlideshowImage();

        break;

    case "/admin/slideshow/update":

        require_once $controllerClasses['admin']['SlideshowManagementController'];

        $controller = new SlideshowManagementController();

        $controller->updateSlideshowImage();

        break;

    case "/admin/slideshow/delete":

        require_once $controllerClasses['admin']['SlideshowManagementController'];

        $controller = new SlideshowManagementController();

        $controller->deleteSlideshowImage();

        break;

    case "/admin/slideshow/toggle-status":

        require_once $controllerClasses['admin']['SlideshowManagementController'];

        $controller = new SlideshowManagementController();

        $controller->toggleSlideshowImageStatus();

        break;

    case "/admin/slideshow/update-order":

        require_once $controllerClasses['admin']['SlideshowManagementController'];

        $controller = new SlideshowManagementController();

        $controller->updateDisplayOrder();

        break;

    case "/admin/slideshow/stats":

        require_once $controllerClasses['admin']['SlideshowManagementController'];

        $controller = new SlideshowManagementController();

        $controller->getSlideshowStats();

        break;

    // Past Trips Management
    case "/admin/past-trips/list":
        require_once $controllerClasses['admin']['PastTripsController'];
        $controller = new PastTripsController();
        $controller->list();
        break;
    case "/admin/past-trips/upload":
        require_once $controllerClasses['admin']['PastTripsController'];
        $controller = new PastTripsController();
        $controller->upload();
        break;
    case "/admin/past-trips/update":
        require_once $controllerClasses['admin']['PastTripsController'];
        $controller = new PastTripsController();
        $controller->update();
        break;
    case "/admin/past-trips/delete":
        require_once $controllerClasses['admin']['PastTripsController'];
        $controller = new PastTripsController();
        $controller->delete();
        break;
    case "/admin/past-trips/toggle-status":
        require_once $controllerClasses['admin']['PastTripsController'];
        $controller = new PastTripsController();
        $controller->toggleStatus();
        break;
    case "/admin/past-trips/update-order":
        require_once $controllerClasses['admin']['PastTripsController'];
        $controller = new PastTripsController();
        $controller->updateOrder();
        break;

    case "/admin/get-entity-history":

        require_once $controllerClasses['admin']['AuditTrailController'];

        $controller = new AuditTrailController();

        $controller->getEntityHistory();

        break;

    case "/admin/export-audit-trails":

        require_once $controllerClasses['admin']['AuditTrailController'];

        $controller = new AuditTrailController();

        $controller->exportAuditTrails();

        break;



    // Urgent Review Reminder

    case "/admin/urgent-review-bookings":

        require_once $controllerClasses['admin']['BookingReviewReminderController'];

        $controller = new BookingReviewReminderController();

        $controller->showUrgentReviewBookings();

        break;

        

    // Manual Auto-Cancellation

    case "/admin/manual-auto-cancellation":

        require_once $controllerClasses['admin']['BookingReviewReminderController'];

        $controller = new BookingReviewReminderController();

        $controller->manualAutoCancellation();

        break;



    // Chat API Routes - Client

    case "/api/chat/conversation":

        require_once $controllerClasses['client']['ChatController'];

        $controller = new ChatController();

        $controller->getOrCreateConversation();

        break;

    case preg_match('|^/api/chat/messages/([0-9]+)$|', $requestPath, $matches) ? $requestPath : "":

        require_once $controllerClasses['client']['ChatController'];

        $controller = new ChatController();

        $controller->getMessages($matches[1]);

        break;

    case "/api/chat/send":

        require_once $controllerClasses['client']['ChatController'];

        $controller = new ChatController();

        $controller->sendMessage();

        break;

    case "/api/chat/request-human":

        require_once $controllerClasses['client']['ChatController'];

        $controller = new ChatController();

        $controller->requestHumanAssistance();

        break;

    case "/api/chat/end":

        require_once $controllerClasses['client']['ChatController'];

        $controller = new ChatController();

        $controller->endConversation();

        break;

    case preg_match('|^/api/chat/status/([0-9]+)$|', $requestPath, $matches) ? $requestPath : "":

        require_once $controllerClasses['client']['ChatController'];

        $controller = new ChatController();

        $controller->getConversationStatus($matches[1]);

        break;



    // Chat API Routes - Admin

    case "/admin/chat":

        require_once __DIR__ . "/../app/views/admin/chats.php";

        break;

    case "/api/admin/chat/dashboard":

        require_once $controllerClasses['admin']['AdminChatController'];

        $controller = new AdminChatController();

        $controller->getDashboard();

        break;

    case "/api/admin/chat/pending":

        require_once $controllerClasses['admin']['AdminChatController'];

        $controller = new AdminChatController();

        $controller->getPendingConversations();

        break;

    case "/api/admin/chat/active":

        require_once $controllerClasses['admin']['AdminChatController'];

        $controller = new AdminChatController();

        $controller->getActiveConversations();

        break;

    case "/api/admin/chat/ended":

        require_once $controllerClasses['admin']['AdminChatController'];

        $controller = new AdminChatController();

        $controller->getEndedConversations();

        break;

    case "/api/admin/chat/assign":

        require_once $controllerClasses['admin']['AdminChatController'];

        $controller = new AdminChatController();

        $controller->assignConversation();

        break;

    case "/api/admin/chat/send":

        require_once $controllerClasses['admin']['AdminChatController'];

        $controller = new AdminChatController();

        $controller->sendMessage();

        break;

    case preg_match('|^/api/admin/chat/messages/([0-9]+)$|', $requestPath, $matches) ? $requestPath : "":

        require_once $controllerClasses['admin']['AdminChatController'];

        $controller = new AdminChatController();

        $controller->getConversationMessages($matches[1]);

        break;

    case "/api/admin/chat/end":

        require_once $controllerClasses['admin']['AdminChatController'];

        $controller = new AdminChatController();

        $controller->endConversation();

        break;

    case preg_match('|^/api/admin/chat/view/([0-9]+)$|', $requestPath, $matches) ? $requestPath : "":

        require_once $controllerClasses['admin']['AdminChatController'];

        $controller = new AdminChatController();

        $controller->viewConversation($matches[1]);

        break;

    case "/api/admin/chat/bot-responses":

        require_once $controllerClasses['admin']['AdminChatController'];

        $controller = new AdminChatController();

        $controller->getBotResponses();

        break;

    case "/api/admin/chat/bot-responses/save":

        require_once $controllerClasses['admin']['AdminChatController'];

        $controller = new AdminChatController();

        $controller->saveBotResponse();

        break;

    case preg_match('|^/api/admin/chat/bot-responses/([0-9]+)/delete$|', $requestPath, $matches) ? $requestPath : "":

        require_once $controllerClasses['admin']['AdminChatController'];

        $controller = new AdminChatController();

        $controller->deleteBotResponse($matches[1]);

        break;

    case "/api/admin/chat/stats":

        require_once $controllerClasses['admin']['AdminChatController'];

        $controller = new AdminChatController();

        $controller->getStats();

        break;



    // Test route for chat debugging

    case "/test-chat":

        require_once __DIR__ . "/../test_chat.php";

        break;

        

    // Test route for chat statistics

    case "/test-chat-stats":

        require_once __DIR__ . "/../test_chat_stats.php";

        break;

        



        

    // Debug route for chat API

    case "/debug-chat":

        require_once __DIR__ . "/../debug_chat_api.php";

        break;

        

    // Test route for admin JS debugging

    case "/test-admin-js":

        echo '<!DOCTYPE html><html><head><title>Test Admin JS</title></head><body>';

        echo '<h1>Testing Admin JavaScript</h1>';

        echo '<div id="test-output">Loading...</div>';

        echo '<div id="auth-status">Checking authentication...</div>';

        echo '<script>console.log("Basic JS working"); document.getElementById("test-output").innerHTML = "Basic JavaScript works!";</script>';

        echo '<script>';

        echo 'fetch("/api/admin/chat/pending").then(r => r.text()).then(text => {';

        echo 'console.log("API Response:", text);';

        echo 'document.getElementById("auth-status").innerHTML = "API Response: " + text.substring(0, 200) + "...";';

        echo '}).catch(e => console.error("API Error:", e));';

        echo '</script>';

        echo '<script src="/public/js/admin-chat-complete.js?v=' . time() . '"></script>';

        echo '<script>setTimeout(() => { if (window.AdminChatManager) { document.getElementById("test-output").innerHTML += "<br>✅ AdminChatManager loaded successfully!"; } else { document.getElementById("test-output").innerHTML += "<br>❌ AdminChatManager failed to load"; } }, 500);</script>';

        echo '</body></html>';

        break;

    // Test route for admin session debugging
    case "/test-admin-session":
        header("Content-Type: application/json");
        echo json_encode([
            'session_data' => $_SESSION,
            'is_admin_authenticated' => is_admin_authenticated(),
            'role' => $_SESSION["role"] ?? "NOT SET",
            'admin_id' => $_SESSION["admin_id"] ?? "NOT SET"
        ]);
        break;
    // Chat API Routes - Visitors (Non-authenticated)
    case "/api/chat/visitor-conversation":
        require_once __DIR__ . "/../app/controllers/client/VisitorChatController.php";
        $controller = new VisitorChatController();
        $controller->getOrCreateVisitorConversation();
        break;
    case preg_match('|^/api/chat/visitor-messages/([0-9]+)$|', $requestPath, $matches) ? $requestPath : "":
        require_once __DIR__ . "/../app/controllers/client/VisitorChatController.php";
        $controller = new VisitorChatController();
        $controller->getVisitorMessages($matches[1]);
        break;
    case "/api/chat/visitor-send":
        require_once __DIR__ . "/../app/controllers/client/VisitorChatController.php";
        $controller = new VisitorChatController();
        $controller->sendVisitorMessage();
        break;
    case "/api/chat/visitor-request-human":
        require_once __DIR__ . "/../app/controllers/client/VisitorChatController.php";
        $controller = new VisitorChatController();
        $controller->requestVisitorHumanAssistance();
        break;
    case "/api/chat/visitor-end":
        require_once __DIR__ . "/../app/controllers/client/VisitorChatController.php";
        $controller = new VisitorChatController();
        $controller->endVisitorConversation();
        break;

    // Analytics API Routes
    case "/routes/analytics.php":
        require_once __DIR__ . "/../routes/analytics.php";
        break;

    default:

        // 404 Not Found

        header("HTTP/1.0 404 Not Found");

        require_once __DIR__ . "/../404.php";

        break;

}

?>