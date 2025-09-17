<?php
require_client_auth(); // Use helper function
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">  
    <link rel="stylesheet" href="/../../../public/css/client/booking.css">  

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <style>
        /* Custom scrollbar styling */
        select.custom-select {
            scrollbar-width: thin;
            scrollbar-color: #198754 #f0f0f0;
        }
        
        select.custom-select::-webkit-scrollbar {
            width: 8px;
        }
        
        select.custom-select::-webkit-scrollbar-track {
            background: #f0f0f0;
            border-radius: 10px;
        }
        
        select.custom-select::-webkit-scrollbar-thumb {
            background-color: #198754;
            border-radius: 10px;
            border: 2px solid #f0f0f0;
        }
        
        select.custom-select::-webkit-scrollbar-thumb:hover {
            background-color: #0f5132;
        }
        
        /* Custom dropdown styling */
        select.custom-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23198754' class='bi bi-caret-down-fill' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
            height: 38px;
            display: flex;
            align-items: center;
            line-height: 1;
        }

        /* Make options compact */
        select.custom-select option {
            padding: 2px 8px !important;
            font-size: 0.875rem;
            line-height: 1.2;
        }
        
        /* Target the dropdown list visual height in Chrome, Safari, Edge */
        select.custom-select option:checked,
        select.custom-select option:hover {
            background-color: #e8f5e9;
            color: #198754;
        }
        
        /* Adjust dropdown presentation */
        @supports (-moz-appearance:none) {
            /* Firefox-specific styles */
            select.custom-select {
                height: 38px;
                -moz-padding-start: 8px;
            }
        }

        @media screen and (-webkit-min-device-pixel-ratio:0) {
            /* Chrome/Safari specific styles */
            select.custom-select {
                height: 38px;
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
                padding-left: 1rem;
            }
        }
    </style>

    <title>Book a Trip</title>
</head>
<body>

    <?php include_once __DIR__ . "/../assets/sidebar.php"; ?>

    <div class="content collapsed" id="content">
        <div class="container-fluid p-0 m-0">
            <div class="container-fluid d-flex justify-content-end align-items-center py-4 px-4 px-xl-5">
                <?php include_once __DIR__ . "/../assets/user_profile.php"; ?>
            </div>
            <div class="container-fluid d-flex justify-content-center gap-5 p-0 m-0">
                <form action="" id="bookingForm" class="border rounded p-3 height-auto align-self-start">
                    <input type="hidden" name="id" value="1">
                    <div id="firstInfo">
                        <h3 class="mb-3" id="bookingHeader">Book a Trip</h3>
                        <div class="mb-3 position-relative">
                            <i class="bi bi-geo-alt-fill location-icon"></i>
                            <input type="text" name="pickup_point" id="pickup_point" class="form-control text-truncate address py-2 px-4" autocomplete="off" placeholder="Pickup Location" required>
                            <ul id="pickupPointSuggestions" class="suggestions"></ul>
                        </div> 
                        <div class="mb-3 position-relative">
                            <i class="bi bi-geo-alt-fill location-icon"></i>
                            <i class="bi bi-plus-circle-fill add-icon" id="addStop" title="Add stop"></i>
                            <input type="text" name="destination" id="destination" class="form-control text-truncate address destination added-stop py-2 px-4" autocomplete="off" placeholder="Dropoff Location" required>
                            <ul id="destinationSuggestions" class="suggestions"></ul>
                        </div>

                        <div class="mb-3">
                            <button type="button" class="btn btn-success w-100" id="nextButton">Next</button>
                        </div>
                    </div>
                    
                    <div class="d-none" id="nextInfo">
                        <div class="mb-3">
                            <i class="bi bi-chevron-left fs-4" id="back"></i>
                        </div>
                        <div class="mb-3 position-relative">  
                            <i class="bi bi-calendar-fill calendar-icon"></i>
                            <input type="text" name="date_of_tour" id="date_of_tour" class="form-control py-2 px-4" placeholder="Pickup Date" required>
                        </div>   
                        <div class="mb-3 position-relative">
                            <i class="bi bi-clock-fill calendar-icon"></i>
                            <select name="pickup_time" id="pickup_time" class="form-select py-2 px-4 custom-select" required>
                                <option value="" disabled selected hidden>Select Pickup Time</option>
                                <option value="04:00:00">4:00 AM</option>
                                <option value="04:30:00">4:30 AM</option>
                                <option value="05:00:00">5:00 AM</option>
                                <option value="05:30:00">5:30 AM</option>
                                <option value="06:00:00">6:00 AM</option>
                                <option value="06:30:00">6:30 AM</option>
                                <option value="07:00:00">7:00 AM</option>
                                <option value="07:30:00">7:30 AM</option>
                                <option value="08:00:00">8:00 AM</option>
                                <option value="08:30:00">8:30 AM</option>
                                <option value="09:00:00">9:00 AM</option>
                                <option value="09:30:00">9:30 AM</option>
                                <option value="10:00:00">10:00 AM</option>
                                <option value="10:30:00">10:30 AM</option>
                                <option value="11:00:00">11:00 AM</option>
                                <option value="11:30:00">11:30 AM</option>
                                <option value="12:00:00">12:00 PM</option>
                            </select>
                        </div>      
                        <div class="mb-3 d-flex gap-3">
                            <div class="d-flex flex-column">
                                <p>Number of Days</p>
                                <p>Number of Buses</p>
                            </div>
                            
                            <div class="d-flex flex-column">
                                <div class="d-flex gap-3">
                                    <i class="bi bi-dash-square" id="decreaseDays" title="Decrease days"></i>
                                    <p id="number_of_days">0</p>
                                    <i class="bi bi-plus-square" id="increaseDays" title="Add days"></i>
                                </div>
                                <div class="d-flex gap-3">
                                    <i class="bi bi-dash-square" id="decreaseBuses"></i>
                                    <p id="number_of_buses">0</p>
                                    <i class="bi bi-plus-square" id="increaseBuses"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <p id="totalCost" class="fw-bold text-success"></p>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="agreeTerms" name="agreeTerms">
                            <label class="form-check-label" for="agreeTerms">I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">terms and conditions</a></label>
                        </div>

                        <div class="container-fluid d-flex justify-content-between align-items-center gap p-0">
                            <button type="submit" class="btn btn-success w-100" id="submitBooking">Request Booking</button>
                        </div>
                    </div>
                </form>
                <div class="border rounded" id="map">
                </div>
            </div>
        </div>
    </div>

    <script src="../../../public/js/jquery/jquery-3.6.4.min.js"></script>
    <script src="../../../public/js/client/booking.js"></script>
    <script src="../../../public/js/assets/sidebar.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyABxRtbMl6Yo1T3na9GbH3bW6GobHmZ_1Q&callback=initMap" async defer></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5>Booking Terms and Conditions</h5>
                    <p>By checking the "I agree to the terms and conditions" box, you acknowledge that you have read, understood, and agree to be bound by the following terms:</p>
                    
                    <ol>
                        <li><strong>Booking Confirmation:</strong> A trip is only considered confirmed once a <strong>50% deposit</strong> has been received.</li>
                        <li><strong>Payment Terms:</strong>
                            <ul>
                                <li>A <strong>50% down payment</strong> is required to confirm the booking.</li>
                                <li>The <strong>remaining balance</strong> must be settled before or during the trip date.</li>
                                <li>We accept payments via <strong>cash</strong> or <strong>bank transfer</strong>.</li>
                            </ul>
                        </li>
                        <li><strong>Cancellation Policy:</strong>
                            <ul>
                                <li>If canceled <strong>3 or more working days before</strong> the trip: <strong>50% deposit is forfeited</strong>.</li>
                                <li>If canceled <strong>within 24 hours</strong> of the trip: <strong>Full payment is required</strong>.</li>
                                <li><strong>No penalty</strong> for <strong>force majeure</strong> (e.g., typhoon).</li>
                            </ul>
                        </li>
                        <li><strong>Auto-Cancellation:</strong> Bookings with unpaid deposits after <strong>48 hours</strong> from reservation request will be <strong>automatically canceled</strong> without prior notice.</li>
                        <li><strong>Changes to Booking:</strong> Any changes to your booking must be made in <strong>writing</strong> and are subject to <strong>availability</strong> and additional charges.</li>
                        <li><strong>Liability:</strong> KingLang Booking is not liable for any <strong>loss</strong>, <strong>damage</strong>, <strong>delay</strong>, <strong>inconvenience</strong>, or <strong>direct or consequential loss</strong>, however caused, unless due to our employees' <strong>negligence</strong>.</li>
                        <li><strong>Insurance:</strong> We recommend that all passengers have <strong>travel insurance</strong> to cover incidents such as <strong>cancellation</strong>, <strong>personal effects</strong>, and any <strong>additional costs</strong>.</li>
                        <li><strong>Data Collection:</strong> When you agree to these terms, we will collect your <strong>IP address</strong> and <strong>timestamp</strong> for verification purposes.</li>
                    </ol>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="acceptTerms" data-bs-dismiss="modal">Accept</button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include_once __DIR__ . '/chat_widget_core.php'; ?>

    <script>
    // Set user login status for chat widget
    var userLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>

</body>
</html>