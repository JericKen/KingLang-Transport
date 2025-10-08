<?php 
require_admin_auth(); // Use helper function

// Include the settings helper functions
require_once __DIR__ . '/../../../config/settings.php';

// Get company details from settings
$company_name = get_setting('company_name', 'KINGLANG TRANSPORT');
$company_address = get_setting('company_address', '295-B, Purok 4, M. L. Quezon Ave, Lower Bicutan, Taguig, 1632 Metro Manila');
$company_contact = get_setting('company_contact', '0917-882-2727 / 0932-882-2727');
$company_email = get_setting('company_email', 'bsmillamina@yahoo.com');

// Get bank details from settings
$bank_name = get_setting('bank_name', 'BPI Cainta Ortigas Extension Branch');
$bank_account_name = get_setting('bank_account_name', 'KINGLANG TOURS AND TRANSPORT SERVICES INC.');
$bank_account_number = get_setting('bank_account_number', '4091-0050-05');
$bank_swift_code = get_setting('bank_swift_code', 'BPOIPHMM');

// Debug output to see what data we have
// echo "<pre>"; print_r($booking); echo "</pre>";

// Process destinations list
$destinations = $booking['destination'] ?? 'N/A';
if (!empty($stops)) {
    $stopList = array_column($stops, 'location');
    if (!empty($stopList)) {
        $destinations = implode(' <i class="bi bi-arrow-right mx-1 text-danger"></i> ', $stopList) . ' <i class="bi bi-arrow-right mx-1 text-danger"></i> ' . $booking['destination'];
    }
}

// Format the date
function formatDate($date) {
    if (!$date) return 'N/A';
    $dateObj = new DateTime($date);
    return $dateObj->format('F j, Y');
}

// Calculate rates
$totalCost = (float)($booking['total_cost'] ?? 0);
$numberOfBuses = (int)($booking['number_of_buses'] ?? 1);
$unitCost = $numberOfBuses > 0 ? $totalCost / $numberOfBuses : 0;
// $regularRate = $unitCost * 1.4; // 40% markup for "regular" rate
$regularRate = (float)($booking['gross_price'] ?? $unitCost ?? 0); // Use gross_price if available

// Format currency
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2, '.', ',');
}

// Get contract date
$contract_date = isset($booking['confirmed_at']) && !empty($booking['confirmed_at']) 
    ? formatDate($booking['confirmed_at']) 
    : date('F j, Y');

// Get client information
$client_name = $booking['client_name'] ?? $_SESSION['client_name'] ?? '';
$company_name_client = $booking['company_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transportation Agreement - #<?php echo $booking['booking_id'] ?? 'New'; ?></title>
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/../../../public/css/bootstrap/bootstrap.min.css">
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
        
        .contract-container {
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
        
        .contract-content {
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
        
        .header h3 {
            color: var(--primary-green);
            font-weight: 700;
            margin-bottom: 6px;
            font-size: 1.4rem;
        }
        
        .header p {
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
        
        .booking-details {
            background-color: rgba(209, 247, 196, 0.2);
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-green);
        }
        
        .booking-details p {
            margin-bottom: 6px;
        }
        
        .booking-details strong {
            color: #333;
            font-weight: 600;
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
        
        table th {
            background-color: var(--light-green);
            color: #333;
            font-weight: 600;
            text-align: left;
            padding: 10px;
        }
        
        table td {
            padding: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        table tr:last-child td {
            border-bottom: none;
        }
        
        table tr:hover td {
            background-color: rgba(209, 247, 196, 0.1);
        }
        
        .total {
            text-align: right;
            font-weight: 700;
            font-size: 1rem;
            padding: 12px 15px;
            margin-bottom: 20px;
            color: var(--primary-green);
            background-color: #f8f9fa;
            border-radius: 8px;
            border-right: 4px solid var(--primary-green);
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
        
        ul {
            padding-left: 18px;
            margin-bottom: 10px;
        }
        
        ul li {
            margin-bottom: 6px;
            position: relative;
        }
        
        ul li::marker {
            color: var(--primary-green);
        }
        
        .compact-list li {
            margin-bottom: 4px;
        }
        
        .agreement-section {
            background-color: rgba(209, 247, 196, 0.2);
            padding: 15px;
            border-radius: 8px;
            margin-top: 25px;
            border: 1px solid #e9ecef;
        }
        
        .agreement-section p {
            margin-bottom: 0;
            color: #333;
        }
        
        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 100;
            display: flex;
            gap: 8px;
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
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 15px;
        }
        
        .card-header {
            padding: 10px 15px;
            font-size: 0.9rem;
        }
        
        .card-body {
            padding: 12px 15px;
        }
        
        .list-unstyled li {
            margin-bottom: 5px;
        }
        
        .row-compact {
            margin-right: -5px;
            margin-left: -5px;
        }
        
        .row-compact > [class*="col-"] {
            padding-right: 5px;
            padding-left: 5px;
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
        
        @media print {
            body {
                background-color: #fff;
                padding: 0;
                margin: 0;
                font-size: 12pt;
            }
            .contract-container {
                box-shadow: none;
                max-width: 100%;
                padding: 15px;
                animation: none;
            }
            .no-print {
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
        }
        
        @media (max-width: 768px) {
            .contract-container {
                padding: 15px;
            }
            .booking-details {
                padding: 10px;
            }
            .title {
                font-size: 1.2rem;
            }
            .print-btn {
                bottom: 15px;
                right: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="contract-container">
        <!-- Letterhead Decorative Elements -->
        <div class="letterhead-top-left"></div>
        <div class="letterhead-top-right"></div>
        <div class="letterhead-bottom-left"></div>
        <div class="letterhead-bottom-right"></div>
        
        <!-- Contract Content -->
        <div class="contract-content">
        <div class="header">
            <div class="invoice-logo-container">
                <img src="/../../../public/images/logo.png" alt="Kinglang Bus Logo" class="invoice-logo">
            </div>
            <h3><?php echo $company_name; ?></h3>
            <p>Address: <?php echo $company_address; ?></p>
            <p>Contact: <?php echo $company_contact; ?> | Email: <?php echo $company_email; ?></p>
        </div>
        
        <!-- <?php
        echo "<pre>";
        print_r($booking); // Debug output to see what data we have
        echo "</pre>";
        ?> -->

        <div class="title">TRANSPORTATION RATE & AGREEMENT</div>
        
        <div class="booking-details">
            <div class="d-flex flex-wrap">
                <div class="w-50">
                    <p><strong><i class="bi bi-calendar-check me-1 text-success"></i>Contract Date:</strong> <?php echo $contract_date; ?></p>
                    <p><strong><i class="bi bi-hash me-1 text-success"></i>Booking Reference:</strong> #<?php echo $booking['booking_id'] ?? 'New'; ?></p>
                    <p><strong><i class="bi bi-person me-1 text-success"></i>Client:</strong> <?php echo $client_name; ?></p>
                </div>
                <div class="w-50">
                    <p><strong><i class="bi bi-calendar-event me-1 text-success"></i>Tour Date:</strong> <?php echo formatDate($booking['date_of_tour'] ?? ''); ?></p>
                    <p><strong><i class="bi bi-bus-front me-1 text-success"></i>Buses:</strong> <?php echo $booking['number_of_buses'] ?? 1; ?> bus<?php echo ((int)($booking['number_of_buses'] ?? 1) > 1) ? 'es' : ''; ?></p>
                    <p><strong><i class="bi bi-geo-alt me-1 text-success"></i>Pick-up:</strong> <?php echo $booking['pickup_point'] ?? 'N/A'; ?></p>
                </div>
            </div>
            <p class="mb-0"><strong><i class="bi bi-signpost-split me-1 text-success"></i>Destination:</strong> <?php echo $destinations; ?></p>
        </div>

        <!-- Driver and Bus Information Section -->
        <div class="section-title"><i class="bi bi-people-fill"></i>ASSIGNED RESOURCES</div>
        <div class="row">
            <!-- Driver Information -->
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <strong><i class="bi bi-person-badge me-1 text-success"></i>Driver Information</strong>
                    </div>
                    <div class="card-body p-2">
                        <?php if (!empty($drivers)): ?>
                            <?php foreach($drivers as $index => $driver): ?>
                                <div class="mb-2 <?php echo $index > 0 ? 'mt-3 pt-3 border-top' : ''; ?>">
                                    <p class="mb-1"><strong>Driver <?php echo $index + 1; ?>:</strong> <?php echo $driver['full_name'] ?? 'N/A'; ?></p>
                                    <p class="mb-1"><small><i class="bi bi-telephone me-1 text-success"></i><?php echo $driver['contact_number'] ?? 'N/A'; ?></small></p>
                                    <p class="mb-1"><small><i class="bi bi-card-text me-1 text-success"></i>License: <?php echo $driver['license_number'] ?? 'N/A'; ?></small></p>
                                    <!-- <p class="mb-0"><small><i class="bi bi-calendar-check me-1 text-success"></i>Experience: <?php echo $driver['years_experience'] ?? 'N/A'; ?> years</small></p> -->
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="mb-0 text-muted">Driver assignment pending</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Bus Information -->
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <strong><i class="bi bi-bus-front me-1 text-success"></i>Bus Information</strong>
                    </div>
                    <div class="card-body p-2">
                        <?php if (!empty($buses)): ?>
                            <?php foreach($buses as $index => $bus): ?>
                                <div class="mb-2 <?php echo $index > 0 ? 'mt-3 pt-3 border-top' : ''; ?>">
                                    <p class="mb-1"><strong>Bus <?php echo $index + 1; ?>:</strong> <?php echo $bus['name'] ?? 'N/A'; ?></p>
                                    <p class="mb-1"><small><i class="bi bi-tag me-1 text-success"></i>Plate #: <?php echo $bus['license_plate'] ?? 'N/A'; ?></small></p>
                                    <p class="mb-1"><small><i class="bi bi-people me-1 text-success"></i>Capacity: <?php echo $bus['capacity'] ?? 'N/A'; ?> seats</small></p>
                                    <!-- <p class="mb-0"><small><i class="bi bi-info-circle me-1 text-success"></i>Type: <?php echo $bus['type'] ?? 'N/A'; ?></small></p> -->
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="mb-0 text-muted">Bus assignment pending</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-title"><i class="bi bi-tag-fill"></i>RATES</div>
        <table>
            <tr>
                <th>Destination</th>
                <th>Regular Rate</th>
                <th>Discounted Rate</th>
                <th>Remarks</th>
            </tr>
            <tr>
                <td><?php echo $destinations; ?></td>
                <td><?php echo formatCurrency($regularRate); ?></td>
                <td><?php echo formatCurrency($unitCost); ?></td>
                <td><?php echo $booking['number_of_buses'] ?? 1; ?> bus<?php echo ((int)($booking['number_of_buses'] ?? 1) > 1) ? 'es' : ''; ?></td>
            </tr>
        </table>
        
        <div class="total">Total Amount Payable: <?php echo formatCurrency($totalCost); ?></div>

        <div class="d-flex">
            <div class="flex-fill me-3">
                <div class="section">
                    <div class="section-title"><i class="bi bi-info-circle-fill"></i>NOTES</div>
                    <ul class="compact-list">
                        <li>Rates based on current fuel prices; adjustments may apply</li>
                        <li>Quotation subject to vehicle availability</li>
                    </ul>
                </div>
                
                <div class="section">
                    <div class="section-title"><i class="bi bi-check-circle-fill"></i>INCLUSIONS</div>
                    <ul class="compact-list">
                        <li>Air-conditioned bus (49 seats) with entertainment system</li>
                        <li>Fuel and passenger insurance during transport</li>
                    </ul>
                </div>
                
                <div class="section">
                    <div class="section-title"><i class="bi bi-x-circle-fill"></i>EXCLUSIONS</div>
                    <ul class="compact-list">
                        <li>Driver's meals and accommodations (overnight)</li>
                        <li>Driver tips (optional)</li>
                        <li>Toll fees, parking, permits, and other fees</li>
                    </ul>
                </div>

                <div class="section">
                    <div class="section-title"><i class="bi bi-x-square-fill"></i>CANCELLATION POLICY</div>
                    <ul class="compact-list">
                        <li>Cancellations must be in writing</li>
                        <li>3+ days before: 50% deposit forfeited</li>
                        <li>Within 24 hours: full rate charged</li>
                        <li>"Acts of God" allow for rescheduling</li>
                    </ul>
                </div>
            </div>
            
            <div class="flex-fill">
                <div class="section">
                    <div class="section-title"><i class="bi bi-credit-card-fill"></i>PAYMENT TERMS</div>
                    <ul class="compact-list">
                        <li>50% down payment upon signing</li>
                        <li>Full payment by event day (cash/transfer)</li>
                        <li>Transfers required 7 days before event</li>
                    </ul>
                    
                    <div class="card mt-2 mb-2">
                        <div class="card-header bg-light p-2">
                            <strong><i class="bi bi-bank me-1"></i>Bank Details</strong>
                        </div>
                        <div class="card-body p-2">
                            <ul class="list-unstyled mb-0">
                                <li><i class="bi bi-building me-1 text-success"></i><strong>Name:</strong> <?php echo $bank_account_name; ?></li>
                                <li><i class="bi bi-bank2 me-1 text-success"></i><strong>Bank:</strong> <?php echo $bank_name; ?></li>
                                <li><i class="bi bi-credit-card me-1 text-success"></i><strong>Account:</strong> <?php echo $bank_account_number; ?></li>
                                <li><i class="bi bi-globe me-1 text-success"></i><strong>Swift:</strong> <?php echo $bank_swift_code; ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <div class="section-title"><i class="bi bi-shield-fill-check"></i>CONDUCT RULES</div>
                    <ul class="compact-list">
                        <li>No alcohol/illegal substances on board</li>
                        <li>Right to refuse transport to disruptive individuals</li>
                        <li>Damages to bus charged to client</li>
                        <li>Complaints must be submitted within 3 days</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="agreement-section">
            <div class="section-title mb-2"><i class="bi bi-file-earmark-text-fill"></i>AGREEMENT CONFIRMATION</div>
            <p>To confirm your booking, please sign and email this contract to <a href="mailto:<?php echo $company_email; ?>" class="text-success"><?php echo $company_email; ?></a>.</p>
            
            <div class="d-flex justify-content-between mt-4 mb-2">
                <div class="text-center" style="width: 45%; border: 1px solid #dee2e6; padding: 15px;">
                    <p class="fw-bold mb-4">BENJAMIN S. MILLAMINA</p>
                    <div style="border-bottom: 1px solid #000; margin-bottom: 10px;"></div>
                    <p class="mb-0">General Manager</p>
                </div>
                
                <div class="text-center" style="width: 45%; border: 1px solid #dee2e6; padding: 15px;">
                    <p class="fw-bold mb-4"><?= $client_name ?></p>
                    <div style="border-bottom: 1px solid #000; margin-bottom: 10px;"></div>
                    <p class="mb-0">Client</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- <div class="print-btn no-print">
        <button class="btn btn-success" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Contract
        </button>
        <a href="/home/booking-requests" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
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
    </script>
        </div> <!-- End contract-content -->
    </div> <!-- End contract-container -->
</body>
</html>     