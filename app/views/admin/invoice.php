<?php 
// Ensure admin authentication
if (!isset($_SESSION["role"]) || ($_SESSION["role"] !== "Super Admin" && $_SESSION["role"] !== "Admin")) {
    header("Location: /admin/login");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Invoice - Booking #<?php echo $booking['booking_id']; ?></title>
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/../../../public/css/bootstrap/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-green: #198754;
            --secondary-green: #28a745;
            --light-green: #d1f7c4;
            --hover-green: #20c997;
            --primary-yellow: #FDB913;
            --secondary-yellow: #FDD835;
            --primary-purple: #5B2C91;
        }
        
        body {
            font-family: 'Work Sans', sans-serif;
            background-color: #f8f9fa;
            padding: 15px;
            font-size: 0.85rem;
            line-height: 1.4;
            color: #333;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            padding: 0;
            animation: fadeIn 0.5s ease;
            position: relative;
            overflow: hidden;
        }
        
        /* Letterhead decorative elements */
        .letterhead-top-left {
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 0;
            border-top: 120px solid var(--primary-yellow);
            border-right: 120px solid transparent;
            z-index: 1;
        }
        
        .letterhead-top-right {
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            z-index: 1;
        }
        
        .letterhead-top-right::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 250px 180px 0;
            border-color: transparent var(--primary-yellow) transparent transparent;
        }
        
        .letterhead-top-right::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 150px 180px 0;
            border-color: transparent var(--primary-green) transparent transparent;
        }
        
        .letterhead-bottom-left {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 0;
            border-bottom: 100px solid var(--primary-yellow);
            border-right: 120px solid transparent;
            z-index: 1;
        }
        
        .letterhead-bottom-right {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 0 100px 120px;
            border-color: transparent transparent var(--primary-green) transparent;
            z-index: 1;
        }
        
        .invoice-content {
            position: relative;
            z-index: 2;
            padding: 25px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .invoice-logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        
        .invoice-logo {
            max-width: 100px;
            height: auto;
            object-fit: contain;
        }
        
        .company-info {
            margin-top: 5px;
        }
        
        .company-info p {
            margin-bottom: 3px;
            font-size: 0.85rem;
        }
        
        .title {
            font-size: 1.3rem;
            font-weight: 700;
            text-align: center;
            margin: 15px 0;
            text-transform: uppercase;
            color: var(--primary-green);
            padding-bottom: 10px;
            position: relative;
        }
        
        .title:after {
            content: "";
            position: absolute;
            width: 60px;
            height: 3px;
            background-color: var(--primary-green);
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .invoice-number {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-green);
        }
        
        .invoice-details {
            background-color: rgba(209, 247, 196, 0.2);
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-green);
        }
        
        .invoice-details p {
            margin-bottom: 6px;
        }
        
        .invoice-details strong {
            color: #333;
            font-weight: 600;
        }
        
        .section {
            margin: 20px 0;
        }
        
        .section-title {
            font-weight: 700;
            margin-bottom: 8px;
            text-transform: uppercase;
            color: var(--primary-green);
            font-size: 1rem;
            display: flex;
            align-items: center;
        }
        
        .section-title::before {
            content: "";
            display: inline-block;
            width: 5px;
            height: 18px;
            background-color: var(--primary-green);
            margin-right: 8px;
            border-radius: 3px;
        }
        
        .section-title i {
            margin-right: 5px;
            font-size: 0.9rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            font-size: 0.85rem;
        }
        
        .table-invoice th {
            background-color: var(--light-green);
            color: #333;
            font-weight: 600;
            text-align: left;
            padding: 10px;
        }
        
        .table-invoice td {
            padding: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .table-invoice tr:last-child td {
            border-bottom: none;
        }
        
        .table-invoice tr:hover td {
            background-color: rgba(209, 247, 196, 0.1);
        }
        
        .table-totals {
            width: 300px;
            margin-left: auto;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }
        
        .table-totals td {
            padding: 8px 12px;
            border-bottom: 1px solid #f2f2f2;
        }
        
        .table-totals tr:last-child {
            background-color: rgba(209, 247, 196, 0.3);
            font-weight: 700;
        }
        
        .table-totals tr:last-child td {
            border-bottom: none;
            padding: 10px 12px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #664d03;
        }
        
        .status-confirmed {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .status-processing {
            background-color: #cff4fc;
            color: #055160;
        }
        
        .status-canceled, .status-rejected {
            background-color: #f8d7da;
            color: #842029;
        }
        
        .status-completed {
            background-color: #c3e6cb;
            color: #155724;
        }
        
        .admin-actions {
            margin-top: 20px;
            background-color: rgba(209, 247, 196, 0.2);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-green);
        }
        
        .admin-actions h5 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--primary-green);
        }
        
        .admin-actions .btn {
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 0.85rem;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }
        
        .admin-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .print-btn {
            text-align: center;
            margin: 20px 0;
        }
        
        .print-btn .btn {
            border-radius: 8px;
            padding: 8px 15px;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .print-btn .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }
        
        .print-btn .btn-success {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
        }
        
        @media print {
            body {
                background-color: #fff;
                padding: 0;
                margin: 0;
                font-size: 12pt;
            }
            
            .invoice-container {
                box-shadow: none;
                max-width: 100%;
                padding: 15px;
                animation: none;
            }
            
            .print-btn, .admin-actions, .no-print {
                display: none !important;
            }
            
            .title:after {
                display: none;
            }
            
            table {
                page-break-inside: avoid;
            }
            
            .section {
                page-break-inside: avoid;
            }
            
            .table-invoice th {
                background-color: #f8f9fa !important;
                color: #333 !important;
            }
            
            /* Remove browser-added headers and footers */
            @page {
                size: auto;
                margin: 0mm;
            }
            
            /* Hide URL, date, etc when printing */
            @page {
                margin: 10mm 15mm 10mm 15mm;
            }
            
            html {
                background-color: #FFFFFF; 
            }
            
            /* Hide URL, title, date from the header */
            @page :first {
                margin-top: 10mm;
            }
            
            @page :left {
                margin-left: 15mm;
                margin-right: 15mm;
            }
            
            @page :right {
                margin-left: 15mm;
                margin-right: 15mm;
            }
            
            /* Add these rules to force hiding all headers and footers */
            html, body, .invoice-container {
                height: 100%;
                overflow: hidden;
                background: #fff;
                font-size: 12pt;
            }
            
            head, header, footer {
                display: none !important;
            }
        }
        
        @media (max-width: 768px) {
            .invoice-container {
                padding: 15px;
            }
            
            .invoice-details {
                padding: A10px;
            }
            
            .title {
                font-size: 1.2rem;
            }
            
            .print-btn {
                position: static;
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Letterhead Decorative Elements -->
        <div class="letterhead-top-left"></div>
        <div class="letterhead-top-right"></div>
        <div class="letterhead-bottom-left"></div>
        <div class="letterhead-bottom-right"></div>
        
        <!-- Invoice Content -->
        <div class="invoice-content">
        <div class="header">
            <div class="invoice-logo-container">
                <img src="/../../../public/images/logo.png" alt="Kinglang Bus Logo" class="invoice-logo">
            </div>
            <div class="company-info">
                <h3 class="mb-1" style="color: var(--primary-green); font-weight: 700; font-size: 1.4rem;">Kinglang Transport</h3>
                <p>295-B, Purok 4, M. L. Quezon Ave, Lower Bicutan, Taguig, 1632 Metro Manila</p>
                <p>Phone: (02) 123-4567 | Email: kinglang.transport@gmail.com</p>
            </div>
            <div class="mt-3">
                <h4 class="title">OFFICIAL INVOICE</h4>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="d-flex">
                        <div class="text-start me-2">
                            <strong><i class="bi bi-receipt text-success"></i> Invoice #:</strong> <br>
                            <strong><i class="bi bi-calendar-date text-success"></i> Booking Date:</strong>
                        </div>
                        
                        <div class="text-start">
                            <?php echo $booking['booking_id']; ?> <br>
                            <?php 
                                $booking_date = new DateTime($booking['booked_at']);
                                echo $booking_date->format('F j, Y') ?? "N/A"; 
                            ?> 
                        </div> 
                    </div>
                    <div>
                        <span class="status-badge status-<?php echo strtolower($booking['status']); ?>">
                            <i class="bi bi-tag-fill me-1"></i><?php echo $booking['status']; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="invoice-details">
            <div class="d-flex justify-content-between gap-2">    
                <div class="col-md-6">
                    <div class="section-title">Client Information</div>
                    <p><i class="bi bi-person-fill text-success me-1"></i><strong>Name:</strong> <?php echo $booking['client_name']; ?></p>
                    <p><i class="bi bi-envelope-fill text-success me-1"></i><strong>Email:</strong> <?php echo $booking['email']; ?></p>
                    <p><i class="bi bi-telephone-fill text-success me-1"></i><strong>Phone:</strong> <?php echo $booking['contact_number']; ?></p>
                </div>
                <div class="col-md-6">
                    <div class="section-title">Booking Details</div>
                                            <p><i class="bi bi-calendar-check text-success me-1"></i><strong>Booking Date:</strong> <?php echo convertToManilaTime($booking['booked_at'], 'F d, Y'); ?></p>
                                            <p><i class="bi bi-calendar-event text-success me-1"></i><strong>Tour Date:</strong> <?php echo convertToManilaTime($booking['date_of_tour'], 'M d, Y') . " to " . convertToManilaTime($booking['end_of_tour'], 'M d, Y'); ?></p>
                    <p><i class="bi bi-clock-fill text-success me-1"></i><strong>Duration:</strong> <?php echo $booking['number_of_days']; ?> day(s)</p>
                    <p><i class="bi bi-alarm text-success me-1"></i><strong>Pickup Time:</strong> <?php echo $booking['pickup_time']; ?></p>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title"><i class="bi bi-geo-alt"></i>Trip Details</div>
            <div class="d-flex justify-content-between gap-3">
                <div class="w-50">
                    <p><i class="bi bi-pin-map-fill text-success me-1"></i><strong>Pickup Point:</strong> <?php echo $booking['pickup_point']; ?></p>
                    <p><i class="bi bi-geo-alt-fill text-success me-1"></i><strong>Destination:</strong> <?php 
                        if (isset($stops) && !empty($stops)) {
                            foreach ($stops as $stop) {
                                echo $stop['location'] . "<i class='bi bi-arrow-right mx-1 text-danger'></i>";
                            }
                        }
                        echo $booking['destination']; 
                    ?>
                    </p>
                </div>
                <div class="w-50">
                    <p><i class="bi bi-bus-front-fill text-success me-1"></i><strong>Number of Buses:</strong> <?php echo $booking['number_of_buses']; ?></p>
                    <p><i class="bi bi-fuel-pump-fill text-success me-1"></i><strong>Current Diesel Price:</strong> ₱<?php echo number_format($booking['diesel_price'], 2); ?></p>
                    <p><i class="bi bi-currency-exchange text-success me-1"></i><strong>Base Rate:</strong> ₱<?php echo number_format($booking['base_rate'], 2); ?></p>
                    <p><i class="bi bi-map text-success me-1"></i><strong>Total Distance:</strong> <?php echo $booking['total_distance']; ?> km</p>
                </div>
            </div>
        </div>
        
        <?php if (!empty($payments)): ?>
        <div class="section">
            <div class="section-title"><i class="bi bi-credit-card"></i>Payment History</div>
            <table class="table table-bordered table-invoice table-sm">
                <thead>
                    <tr>
                        <th><i class="bi bi-calendar3 me-1"></i>Date</th>
                        <th><i class="bi bi-cash me-1"></i>Amount</th>
                        <th><i class="bi bi-wallet2 me-1"></i>Method</th>
                        <th><i class="bi bi-check-circle me-1"></i>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                    <?php if ($payment['is_canceled'] == 0): ?>
                    <tr>
                                                        <td><?php echo convertToManilaTime($payment['payment_date'], 'M d, Y'); ?></td>
                        <td>₱<?php echo number_format($payment['amount'], 2); ?></td>
                        <td><?php echo $payment['payment_method']; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($payment['status']); ?>">
                               <?php echo $payment['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
                        
        <div class="section">
            <div class="section-title"><i class="bi bi-cash-coin"></i>Cost Summary</div>
            <table class="table-totals">
                <tr>
                    <td><i class="bi bi-building text-success me-1"></i><strong>Base Cost:</strong></td>
                    <td class="text-end">₱<?php echo number_format($booking['base_cost'], 2); ?></td>
                </tr>
                <tr>
                    <td><i class="bi bi-fuel-pump text-success me-1"></i><strong>Diesel Cost:</strong></td>
                    <td class="text-end">₱<?php echo number_format($booking['diesel_cost'], 2); ?></td>
                </tr>
                <?php if (!empty($booking['gross_price']) && $booking['discount'] > 0): ?>
                <tr>
                    <td><i class="bi bi-tag text-success me-1"></i><strong>Original Price:</strong></td>
                    <td class="text-end">₱<?php echo number_format($booking['gross_price'], 2); ?></td>
                </tr>
                <tr>
                    <td><i class="bi bi-percent text-success me-1"></i><strong>Discount Rate:</strong></td>
                    <td class="text-end"><?php echo number_format($booking['discount'], 2); ?>%</td>
                </tr>
                <tr>
                    <td><i class="bi bi-piggy-bank text-success me-1"></i><strong>Discount Amount:</strong></td>
                    <td class="text-end">₱<?php echo number_format($booking['gross_price'] - $booking['total_cost'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td><i class="bi bi-cash-stack text-success me-1"></i><strong>Total Cost:</strong></td>
                    <td class="text-end">₱<?php echo number_format($booking['total_cost'], 2); ?></td>
                </tr>
                <tr>
                    <td><i class="bi bi-credit-card-2-front text-success me-1"></i><strong>Amount Paid:</strong></td>
                    <td class="text-end">₱<?php echo number_format($booking['total_cost'] - $booking['balance'], 2); ?></td>
                </tr>
                <tr>
                    <td><i class="bi bi-wallet text-success me-1"></i><strong>Balance:</strong></td>
                    <td class="text-end">₱<?php echo number_format($booking['balance'], 2); ?></td>
                </tr>
            </table>
        </div>
        
        <div class="mt-4 text-center">
            <p class="small"><i class="bi bi-info-circle-fill text-success me-1"></i><strong>Note:</strong> This is an official invoice from Kinglang Bus. Thank you for choosing our services!</p>
            <p class="small text-muted"><i class="bi bi-envelope-fill me-1"></i>For any inquiries, please contact us at: kinglang.transport@gmail.com</p>
        </div>
        
        <!-- Admin-only section -->
        <!-- <div class="admin-actions no-print">
            <h5><i class="bi bi-gear me-2"></i>Admin Actions</h5>
            <div class="d-flex flex-wrap g-2 mt-2">
                <div class="col-md-6 p-1">
                    <button class="btn btn-primary w-100" id="updatePaymentBtn">
                        <i class="bi bi-cash-coin me-1"></i> Record Payment
                    </button>
                </div>
                <div class="col-md-6 p-1">
                    <button class="btn btn-info w-100 text-white" id="emailInvoiceBtn">
                        <i class="bi bi-envelope me-1"></i> Email Invoice
                    </button>
                </div>
                <?php if ($booking['status'] == 'Pending'): ?>
                <div class="col-md-6 p-1">
                    <button class="btn btn-success w-100" id="confirmBookingBtn" data-booking-id="<?php echo $booking['booking_id']; ?>">
                        <i class="bi bi-check-circle me-1"></i> Confirm Booking
                    </button>
                </div>
                <div class="col-md-6 p-1">
                    <button class="btn btn-danger w-100" id="rejectBookingBtn" data-booking-id="<?php echo $booking['booking_id']; ?>">
                        <i class="bi bi-x-circle me-1"></i> Reject Booking
                    </button>
                </div>
                <?php endif; ?>
                <?php if ($booking['status'] != 'Canceled' && $booking['status'] != 'Rejected'): ?>
                <div class="col-md-6 p-1">
                    <button class="btn btn-outline-danger w-100" id="cancelBookingBtn" data-booking-id="<?php echo $booking['booking_id']; ?>">
                        <i class="bi bi-x-octagon me-1"></i> Cancel Booking
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div> -->
    </div>
    
    <!-- <div class="print-btn no-print">
        <button class="btn btn-success" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Invoice
        </button>
        <a href="/admin/booking-requests" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Bookings
        </a>
    </div> -->
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            // Automatically open print dialog when loaded from print link
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('print') === 'true') {
                window.print();
            }
        };
        
        // Connect to admin actions buttons
        document.addEventListener('DOMContentLoaded', function() {
            // Confirmation button
            const confirmBtn = document.getElementById('confirmBookingBtn');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function() {
                    const bookingId = this.getAttribute('data-booking-id');
                    
                    Swal.fire({
                        title: 'Enter Discount Rate',
                        text: 'Enter a discount percentage (0-100)',
                        input: 'number',
                        inputPlaceholder: 'e.g., 15 for 15%',
                        showCancelButton: true,
                        confirmButtonText: 'Confirm Booking',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        inputAttributes: {
                            min: 0,
                            max: 100,
                            step: 0.01
                        },
                        inputValidator: (value) => {
                            if (!value) {
                                return 'Please enter a discount rate';
                            }
                            const numValue = parseFloat(value);
                            if (isNaN(numValue) || numValue < 0 || numValue > 100) {
                                return 'Discount must be between 0 and 100';
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const discount = parseFloat(result.value || 0);
                            
                            fetch('/admin/confirm-booking', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    bookingId: bookingId,
                                    discount: discount
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: 'Booking confirmed successfully!',
                                        timer: 2000,
                                        timerProgressBar: true
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Error: ' + data.message,
                                        timer: 2000,
                                        timerProgressBar: true
                                    });
                                }
                            });
                        }
                    });
                });
            }
            
            // Reject booking button
            const rejectBtn = document.getElementById('rejectBookingBtn');
            if (rejectBtn) {
                rejectBtn.addEventListener('click', function() {
                    const bookingId = this.getAttribute('data-booking-id');
                    const reason = prompt('Please provide a reason for rejecting this booking:');
                    if (reason) {
                        fetch('/admin/reject-booking', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                bookingId: bookingId,
                                userId: <?php echo $booking['user_id']; ?>,
                                reason: reason
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Booking rejected successfully!');
                                location.reload();
                            } else {
                                alert('Error: ' + data.message);
                            }
                        });
                    }
                });
            }
            
            // Cancel booking button
            const cancelBtn = document.getElementById('cancelBookingBtn');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    const bookingId = this.getAttribute('data-booking-id');
                    if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
                        fetch('/admin/cancel-booking', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                bookingId: bookingId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Booking canceled successfully!');
                                location.reload();
                            } else {
                                alert('Error: ' + data.message);
                            }
                        });
                    }
                });
            }
            
            // Email invoice button functionality could be added here
            const emailBtn = document.getElementById('emailInvoiceBtn');
            if (emailBtn) {
                emailBtn.addEventListener('click', function() {
                    alert('This feature is coming soon!');
                });
            }
            
            // Record payment button - opens a modal or redirects to payment page
            const paymentBtn = document.getElementById('updatePaymentBtn');
            if (paymentBtn) {
                paymentBtn.addEventListener('click', function() {
                    window.location.href = `/admin/payment-management?booking_id=<?php echo $booking['booking_id']; ?>`;
                });
            }
        });
    </script>
        </div> <!-- End invoice-content -->
    </div> <!-- End invoice-container -->
</body>
</html> 