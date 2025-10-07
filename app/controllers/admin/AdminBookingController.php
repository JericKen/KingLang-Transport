<?php
require_once __DIR__ . '/../../models/admin/AdminBookingModel.php';
require_once __DIR__ . '/../../models/admin/NotificationModel.php';

class AdminBookingController {  
    private $bookingModel;
    private $notificationModel;

    public function __construct() {
        $this->bookingModel = new AdminBookingModel();
        $this->notificationModel = new NotificationModel();
        
        // Check if the user is logged in and is an admin
        $requestUri = $_SERVER['REQUEST_URI'];
        if (strpos($requestUri, '/admin') === 0 && 
            strpos($requestUri, '/admin/login') === false && 
            strpos($requestUri, '/admin/submit-login') === false) {
            
            if (!isset($_SESSION["role"]) || ($_SESSION["role"] !== "Super Admin" && $_SESSION["role"] !== "Admin")) {
                header("Location: /admin/login");
                exit();
            }
        }
    }

    public function showBookingForm() {
        require_once __DIR__ . "/../../views/admin/create_booking.php";
    }

    public function getAddress() {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json");

        $apiKey = "AIzaSyASHotkPROmUL_mheV_L9zXarFIuRAIMRs";

        $input = json_decode(file_get_contents("php://input"), true);

        if (empty($input["address"])) {
            echo json_encode(["error" => "Input is required"]);
            return;
        }

        $address = urlencode($input["address"]);
        $country = "PH"; // Philippines

        // Check if we have a cached result
        $cacheFile = __DIR__ . "/../../cache/address_" . md5($address) . ".json";
        $cacheExpiry = 60 * 60 * 24; // 24 hours in seconds
        
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheExpiry)) {
            // Return cached result
            echo file_get_contents($cacheFile);
            return;
        }

        // Modified URL to include more location types and session token for better results
        $sessionToken = md5(uniqid(rand(), true));
        $url = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input=$address&key=$apiKey&components=country:$country&sessiontoken=$sessionToken";

        // Use cURL instead of file_get_contents for better performance
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second timeout
        $response = curl_exec($ch);
        curl_close($ch);

        // Cache the result
        if (!is_dir(__DIR__ . "/../../cache")) {
            mkdir(__DIR__ . "/../../cache", 0755, true);
        }
        file_put_contents($cacheFile, $response);

        echo $response;
    }

    public function getDistance() {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json");
    
        $apiKey = "AIzaSyASHotkPROmUL_mheV_L9zXarFIuRAIMRs";
    
        $input = json_decode(file_get_contents("php://input"), true);
        $stops = $input["stops"] ?? [];
    
        if (count($stops) < 2) {
            echo json_encode(["error" => "At least two stops are required"]);
            return;
        }
    
        // Prepare all legs of the trip
        $origins = array_slice($stops, 0, -1);
        $destinations = array_slice($stops, 1);

        // Add the final leg to return to origin (round trip)
        $origins[] = end($stops);   // last stop
        $destinations[] = $stops[0]; // back to origin

        // Create URL-safe strings
        $originStr = implode("|", array_map("urlencode", $origins));
        $destinationStr = implode("|", array_map("urlencode", $destinations));
    
        // Check if we have a cached result
        $cacheKey = md5($originStr . $destinationStr);
        $cacheFile = __DIR__ . "/../../cache/distance_" . $cacheKey . ".json";
        $cacheExpiry = 60 * 60 * 24; // 24 hours in seconds
        
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheExpiry)) {
            // Return cached result
            echo file_get_contents($cacheFile);
            return;
        }
    
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$originStr&destinations=$destinationStr&key=$apiKey";
    
        // Use cURL instead of file_get_contents for better performance
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second timeout
        $response = curl_exec($ch);
        curl_close($ch);
        
        // Cache the result
        if (!is_dir(__DIR__ . "/../../cache")) {
            mkdir(__DIR__ . "/../../cache", 0755, true);
        }
        file_put_contents($cacheFile, $response);
        
        echo $response;
    }

    public function getCoordinates($address, $apiKey) {
        // Check if we have a cached result
        $cacheKey = md5($address);
        $cacheFile = __DIR__ . "/../../cache/coordinates_" . $cacheKey . ".json";
        $cacheExpiry = 60 * 60 * 24; // 24 hours in seconds
        
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheExpiry)) {
            // Return cached result
            $cachedData = json_decode(file_get_contents($cacheFile), true);
            return $cachedData;
        }
        
        $address = urlencode($address);
        $geoUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=$apiKey";

        // Use cURL instead of file_get_contents for better performance
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $geoUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second timeout
        $geoResponse = curl_exec($ch);
        curl_close($ch);
        
        $geoData = json_decode($geoResponse, true);

        if ($geoData["status"] === "OK") {
            $latitude = $geoData["results"][0]["geometry"]["location"]["lat"];
            $longitude = $geoData["results"][0]["geometry"]["location"]["lng"];
            $result = ["lat" => $latitude, "lng" => $longitude];
            
            // Cache the result
            if (!is_dir(__DIR__ . "/../../cache")) {
                mkdir(__DIR__ . "/../../cache", 0755, true);
            }
            file_put_contents($cacheFile, json_encode($result));
            
            return $result;
        } 
        return null;
    }

    public function processCoordinates() {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json");

        $apiKey = "AIzaSyASHotkPROmUL_mheV_L9zXarFIuRAIMRs";

        $input = json_decode(file_get_contents("php://input"), true);

        if (empty($input["pickupPoint"]) || empty($input["destination"])) {
            echo json_encode(["error" => "Both pickup and destination points are required"]);
            return;
        }

        // Check if we have a cached result for the entire route
        $cacheKey = md5($input["pickupPoint"] . $input["destination"] . json_encode($input["stops"] ?? []));
        $cacheFile = __DIR__ . "/../../cache/route_" . $cacheKey . ".json";
        $cacheExpiry = 60 * 60 * 24; // 24 hours in seconds
        
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheExpiry)) {
            // Return cached result
            echo file_get_contents($cacheFile);
            return;
        }

        // Get coordinates for pickup point
        $pickup_point = $this->getCoordinates($input["pickupPoint"], $apiKey);
        if (!$pickup_point) {
            echo json_encode(["error" => "Could not find coordinates for pickup point: " . $input["pickupPoint"]]);
            return;
        }

        // Get coordinates for destination
        $destination = $this->getCoordinates($input["destination"], $apiKey);
        if (!$destination) {
            echo json_encode(["error" => "Could not find coordinates for destination: " . $input["destination"]]);
            return;
        }

        // Get coordinates for any intermediate stops
        $stopCoordinates = [];
        if (!empty($input["stops"]) && is_array($input["stops"])) {
            foreach ($input["stops"] as $stop) {
                $stopCoord = $this->getCoordinates($stop, $apiKey);
                if (!$stopCoord) {
                    echo json_encode(["error" => "Could not find coordinates for stop: " . $stop]);
                    return;
                }
                $stopCoordinates[] = $stopCoord;
            }
        }

        $result = [
            "pickupPoint" => $pickup_point,
            "destination" => $destination,
            "stops" => $stopCoordinates
        ];

        // Cache the result
        if (!is_dir(__DIR__ . "/../../cache")) {
            mkdir(__DIR__ . "/../../cache", 0755, true);
        }
        file_put_contents($cacheFile, json_encode($result));

        echo json_encode($result);
    }

    public function getDieselPrice() {
        header("Content-Type: application/json");
        require_once __DIR__ . "/../../models/admin/Settings.php";
        $settings = new Settings();
        $currentDieselPrice = $settings->getSetting('diesel_price');
        if (!$currentDieselPrice) {
            $currentDieselPrice = 65.00; // Default fallback if setting is not found
        }
        echo json_encode(["price" => (float)$currentDieselPrice]);
    }

    public function getTotalCost() {
        header("Content-Type: application/json");
        
        $input = json_decode(file_get_contents("php://input"), true);
        
        $pickupPoint = $input["pickupPoint"];
        $destination = $input["destination"];
        $stops = $input["stops"] ?? [];
        $days = intval($input["days"]);
        $buses = intval($input["buses"]);
        
        // Combine all locations to determine region
        $locations = array_merge([$pickupPoint, $destination], $stops);
        $region = $this->determineRegionFromLocations($locations);
        
        // Calculate total cost based on the region
        $totalCost = $this->calculateTotalCost($region, $days, $buses);
        
        // Add distance-based costs
        $estimatedDistanceKm = 150 * $days; // Default estimate
        $fuelEfficiency = 3; // km per liter
        
        // Get diesel price from settings
        require_once __DIR__ . "/../../models/admin/Settings.php";
        $settings = new Settings();
        $dieselPrice = (float)$settings->getSetting('diesel_price');
        if (!$dieselPrice) {
            $dieselPrice = 65.00; // Default fallback if setting is not found
        }
        
        $estimatedFuelLiters = $estimatedDistanceKm / $fuelEfficiency;
        $fuelCost = $estimatedFuelLiters * $dieselPrice * $buses;
        
        $finalCost = $totalCost + $fuelCost;
        
        $response = [
            "success" => true,
            "regionUsed" => $region,
            "baseCost" => $totalCost,
            "dieselCost" => $fuelCost,
            "totalCost" => $finalCost,
            "breakdown" => [
                "region" => $region,
                "days" => $days,
                "buses" => $buses,
                "estimatedDistanceKm" => $estimatedDistanceKm,
                "fuelEfficiencyKmPerLiter" => $fuelEfficiency,
                "dieselPricePerLiter" => $dieselPrice,
                "totalFuelLiters" => $estimatedFuelLiters
            ]
        ];
        
        echo json_encode($response);
    }

    private function determineRegionFromLocations($locations) {
        // Default region if we can't determine
        $defaultRegion = "Region 4A";
        
        // Define keywords for each region
        $regionKeywords = [
            "NCR" => ["metro manila", "ncr", "manila", "quezon city", "makati", "pasig", "taguig", "mandaluyong", "pasay", "bgc", "bonifacio global city", "ortigas", "muntinlupa", "las piñas", "parañaque", "marikina", "san juan", "caloocan", "malabon", "navotas", "pateros", "valenzuela"],
            "CAR" => ["cordillera", "car", "baguio", "benguet", "mountain province", "mt. province", "ifugao", "abra", "kalinga", "apayao"],
            "Region 1" => ["ilocos", "region 1", "la union", "pangasinan", "laoag", "vigan", "san fernando", "dagupan", "ilocos norte", "ilocos sur"],
            "Region 2" => ["cagayan valley", "region 2", "cagayan", "isabela", "nueva vizcaya", "quirino", "batanes", "tuguegarao"],
            "Region 3" => ["central luzon", "region 3", "bulacan", "pampanga", "tarlac", "zambales", "nueva ecija", "bataan", "angeles", "clark", "malolos", "cabanatuan", "olongapo", "subic", "san fernando"],
            "Region 4A" => ["calabarzon", "region 4a", "cavite", "laguna", "batangas", "rizal", "quezon", "lucena", "tagaytay", "calamba", "santa rosa", "biñan", "lipa", "antipolo", "dasmariñas", "bacoor", "imus"]
        ];
        
        // Count matches for each region
        $regionMatches = [
            "NCR" => 0,
            "CAR" => 0,
            "Region 1" => 0,
            "Region 2" => 0,
            "Region 3" => 0,
            "Region 4A" => 0
        ];
        
        // Look for keywords in all locations
        foreach ($locations as $location) {
            $locationLower = strtolower($location);
            
            foreach ($regionKeywords as $region => $keywords) {
                foreach ($keywords as $keyword) {
                    if (strpos($locationLower, $keyword) !== false) {
                        $regionMatches[$region]++;
                    }
                }
            }
        }
        
        // Find the region with the most matches
        $maxMatches = 0;
        $bestMatch = $defaultRegion;
        
        foreach ($regionMatches as $region => $matches) {
            if ($matches > $maxMatches) {
                $maxMatches = $matches;
                $bestMatch = $region;
            }
        }
        
        return $bestMatch;
    }

    private function calculateTotalCost($region, $days, $buses) {
        // Define base rates per region
        $regionalRates = [
            'NCR' => 19560, // Metro Manila
            'CAR' => 117539, // Cordillera Administrative Region
            'Region 1' => 117539, // Ilocos Region
            'Region 2' => 71040, // Cagayan Valley
            'Region 3' => 45020, // Central Luzon
            'Region 4A' => 20772, // Calabarzon
        ];
        
        // Get the rate for the region, or use default
        $rate = $regionalRates[$region] ?? $regionalRates['Region 4A'];
        
        // Calculate base cost (rate × days × buses)
        $totalCost = $rate * $days * $buses;
        
        return $totalCost;
    }

    public function createBooking() {
        header("Content-Type: application/json");
        
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Validate required fields
            $requiredFields = [
                'clientName', 'contactNumber', 'email', 'pickupPoint', 'destination', 
                'dateOfTour', 'pickupTime', 'numberOfDays', 'numberOfBuses', 'totalCost'
            ];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Required field missing: $field");
                }
            }
            
            // Process additional stops if any
            $stops = [];
            if (!empty($data['stops']) && is_array($data['stops'])) {
                $stops = array_filter($data['stops'], function($stop) {
                    return !empty($stop);
                });
            }
            
            // Prepare booking data
            $bookingData = [
                'client_name' => $data['clientName'],
                'contact_number' => $data['contactNumber'],
                'email' => $data['email'],
                'company_name' => $data['companyName'] ?? null,
                'pickup_point' => $data['pickupPoint'],
                'destination' => $data['destination'],
                'stops' => json_encode($stops),
                'date_of_tour' => $data['dateOfTour'],
                'pickup_time' => $data['pickupTime'],
                'number_of_days' => intval($data['numberOfDays']),
                'number_of_buses' => intval($data['numberOfBuses']),
                'total_cost' => floatval($data['totalCost']),
                'notes' => $data['notes'] ?? null,
                'status' => 'Confirmed', // Admin-created bookings are auto-confirmed
                'payment_status' => 'Unpaid', // Default payment status
                'created_by' => $_SESSION['role'] ?? 'admin_system', // Track which admin created it
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Save initial payment if provided
            $initialPayment = null;
            if (!empty($data['initialPayment']) && !empty($data['initialPayment']['amountPaid'])) {
                $initialPayment = [
                    'amount' => floatval($data['initialPayment']['amountPaid']),
                    'payment_method' => $data['initialPayment']['paymentMethod'] ?? 'Cash',
                    'reference_number' => $data['initialPayment']['paymentReference'] ?? null,
                    'payment_date' => date('Y-m-d H:i:s')
                ];
                
                // Update payment status based on amount
                if ($initialPayment['amount'] >= $bookingData['total_cost']) {
                    $bookingData['payment_status'] = 'Paid';
                } else if ($initialPayment['amount'] > 0) {
                    $bookingData['payment_status'] = 'Partially Paid';
                }
            }
            
            // Create the booking in the database
            $bookingId = $this->bookingModel->createBooking($bookingData, $initialPayment);
            
            if ($bookingId) {
                // Create notification for the booking
                $notificationData = [
                    'type' => 'booking_created',
                    'user_id' => null, // Will be sent to all admins
                    'booking_id' => $bookingId,
                    'message' => "Admin created a new booking for " . $bookingData['client_name'] . " (ID: $bookingId)",
                    'is_read' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $this->notificationModel->createNotification($notificationData);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Booking created successfully',
                    'booking_id' => $bookingId
                ]);
            } else {
                throw new Exception("Failed to create booking");
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
} 