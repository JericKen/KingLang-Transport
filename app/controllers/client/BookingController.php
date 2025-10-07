<?php

require_once __DIR__ . '/../../models/client/BookingModel.php';

require_once __DIR__ . '/../../models/admin/NotificationModel.php';

require_once __DIR__ . "/../AuditTrailTrait.php";



class BookingController {

    use AuditTrailTrait;

    private $bookingModel;

    private $notificationModel;



    public function __construct() {

        $this->bookingModel = new Booking();

        $this->notificationModel = new NotificationModel();

    }



    public function bookingForm() {

        require_once __DIR__ . "/../../views/client/booking.php";

    }



    public function getAddress() {

        header("Access-Control-Allow-Origin: *");

        header("Content-Type: application/json");



        $apiKey = "AIzaSyABxRtbMl6Yo1T3na9GbH3bW6GobHmZ_1Q";



        $input = json_decode(file_get_contents("php://input"), true);



        if (empty($input["address"])) {

            echo json_encode(["error" => "Input is required"]);

            return;

        }



        $address = urlencode($input["address"]);

        $country = "PH"; // Philippines



        // Check if we have a cached result

        $cacheFile = __DIR__ . "/../../cache/address_" . md5($address) . ".json";

        $cacheExpiry = 60 * 60 * 24 * 7; // 7 days in seconds (extended for fallback purposes)

        

        // Modified URL to include more location types and session token for better results

        // Removed the types=address restriction to get all possible location types

        // Added sessiontoken parameter for better results

        $sessionToken = md5(uniqid(rand(), true));

        $url = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input=$address&key=$apiKey&components=country:$country&sessiontoken=$sessionToken";



        // Use cURL instead of file_get_contents for better performance

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second timeout

        $response = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $curlError = curl_errno($ch);

        curl_close($ch);

        

        // Check if API call failed or returned an error

        $apiCallFailed = $curlError || $httpCode >= 400 || empty($response);

        $responseData = json_decode($response, true);

        $apiReturnedError = !$apiCallFailed && isset($responseData['status']) && $responseData['status'] != 'OK';

        

        // If API call failed and we have a cached result, use that instead

        if (($apiCallFailed || $apiReturnedError) && file_exists($cacheFile)) {

            $cachedResponse = file_get_contents($cacheFile);

            echo $cachedResponse;

            return;

        }

        

        // If API call was successful, cache the result

        if (!$apiCallFailed && !$apiReturnedError) {

            if (!is_dir(__DIR__ . "/../../cache")) {

                mkdir(__DIR__ . "/../../cache", 0755, true);

            }

            file_put_contents($cacheFile, $response);

            echo $response;

        } else {

            // API failed and no cache available

            echo json_encode([

                "status" => "FALLBACK_FAILED", 

                "error_message" => "Unable to retrieve address data and no cached data available"

            ]);

        }

    }



    public function getDistance() {

        header("Access-Control-Allow-Origin: *");

        header("Content-Type: application/json");

    

        $apiKey = "AIzaSyABxRtbMl6Yo1T3na9GbH3bW6GobHmZ_1Q";

    

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

        $cacheExpiry = 60 * 60 * 24 * 7; // 7 days in seconds (extended for fallback purposes)

    

        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$originStr&destinations=$destinationStr&key=$apiKey";

    

        // Use cURL instead of file_get_contents for better performance

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second timeout

        $response = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $curlError = curl_errno($ch);

        curl_close($ch);

        

        // Check if API call failed or returned an error

        $apiCallFailed = $curlError || $httpCode >= 400 || empty($response);

        $responseData = json_decode($response, true);

        $apiReturnedError = !$apiCallFailed && isset($responseData['status']) && $responseData['status'] != 'OK';

        

        // If API call failed and we have a cached result, use that instead

        if (($apiCallFailed || $apiReturnedError) && file_exists($cacheFile)) {

            $cachedResponse = file_get_contents($cacheFile);

            echo $cachedResponse;

            return;

        }

        

        // If API call was successful, cache the result

        if (!$apiCallFailed && !$apiReturnedError) {

            if (!is_dir(__DIR__ . "/../../cache")) {

                mkdir(__DIR__ . "/../../cache", 0755, true);

            }

            file_put_contents($cacheFile, $response);

            echo $response;

        } else {

            // API failed and no cache available

            echo json_encode([

                "status" => "FALLBACK_FAILED", 

                "error_message" => "Unable to retrieve distance data and no cached data available"

            ]);

        }

    }



    public function getCoordinates($address, $apiKey) {

        // Check if we have a cached result

        $cacheKey = md5($address);

        $cacheFile = __DIR__ . "/../../cache/coordinates_" . $cacheKey . ".json";

        $cacheExpiry = 60 * 60 * 24 * 7; // 7 days in seconds (extended for fallback purposes)

        

        if (file_exists($cacheFile)) {

            // Return cached result if available (regardless of expiry when used as fallback)

            $cachedData = json_decode(file_get_contents($cacheFile), true);

            // If we're not checking API first, return cached data immediately

            if (time() - filemtime($cacheFile) < $cacheExpiry) {

                return $cachedData;

            }

        }

        

        $address = urlencode($address);

        $geoUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=$apiKey";



        // Use cURL instead of file_get_contents for better performance

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $geoUrl);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second timeout

        $geoResponse = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $curlError = curl_errno($ch);

        curl_close($ch);

        

        // Check if API call failed

        $apiCallFailed = $curlError || $httpCode >= 400 || empty($geoResponse);

        

        // If API call failed and we have cached data, use that instead

        if ($apiCallFailed && file_exists($cacheFile)) {

            return json_decode(file_get_contents($cacheFile), true);

        }

        

        $geoData = json_decode($geoResponse, true);



        // Check if geocoding was successful

        if (!$apiCallFailed && isset($geoData["status"]) && $geoData["status"] === "OK") {

            $latitude = $geoData["results"][0]["geometry"]["location"]["lat"];

            $longitude = $geoData["results"][0]["geometry"]["location"]["lng"];

            $result = ["lat" => $latitude, "lng" => $longitude];

            

            // Cache the result

            if (!is_dir(__DIR__ . "/../../cache")) {

                mkdir(__DIR__ . "/../../cache", 0755, true);

            }

            file_put_contents($cacheFile, json_encode($result));

            

            return $result;

        } else if (file_exists($cacheFile)) {

            // If API returned error but we have cached data, use that

            return json_decode(file_get_contents($cacheFile), true);

        }

        

        return null;

    }



    public function processCoordinates() {

        header("Access-Control-Allow-Origin: *");

        header("Content-Type: application/json");



        $apiKey = "AIzaSyABxRtbMl6Yo1T3na9GbH3bW6GobHmZ_1Q";



        $input = json_decode(file_get_contents("php://input"), true);



        if (empty($input["pickupPoint"]) || empty($input["destination"])) {

            echo json_encode(["error" => "Both pickup and destination points are required"]);

            return;

        }



        // Check if we have a cached result for the entire route

        $cacheKey = md5($input["pickupPoint"] . $input["destination"] . json_encode($input["stops"] ?? []));

        $cacheFile = __DIR__ . "/../../cache/route_" . $cacheKey . ".json";

        $cacheExpiry = 60 * 60 * 24 * 7; // 7 days in seconds (extended for fallback purposes)

        

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



        $stops = [];

        $invalidStops = [];



        if (!empty($input["stops"])) {

            foreach ($input["stops"] as $index => $stop) {

                $coordinates = $this->getCoordinates($stop, $apiKey);

                if ($coordinates) {

                    $stops[] = $coordinates;

                } else {

                    $invalidStops[] = $stop;

                }

            }

        }



        // If there are any invalid stops, return an error

        if (!empty($invalidStops)) {

            // Check if we have a cached version we can use as fallback

            if (file_exists($cacheFile)) {

                echo file_get_contents($cacheFile);

                return;

            }

            

            echo json_encode([

                "error" => "Could not find coordinates for the following stops: " . implode(", ", $invalidStops),

                "status" => "FALLBACK_FAILED"

            ]);

            return;

        }



        if ($pickup_point && $destination) {

            $result = ["pickup_point" => $pickup_point, "destination" => $destination, "stops" => $stops];

            

            // Cache the result

            if (!is_dir(__DIR__ . "/../../cache")) {

                mkdir(__DIR__ . "/../../cache", 0755, true);

            }

            file_put_contents($cacheFile, json_encode($result));

            

            echo json_encode($result);

        } else {

            // Check if we have a cached version we can use as fallback

            if (file_exists($cacheFile)) {

                echo file_get_contents($cacheFile);

                return;

            }

            

            echo json_encode([

                "error" => "Unable to get coordinates for the specified locations",

                "status" => "FALLBACK_FAILED"

            ]);

        }

    }



    



    public function getDieselPrice() {

        require_once __DIR__ . "/../../models/admin/Settings.php";

        $settings = new Settings();

        return $settings->getSetting('diesel_price');

    }



    public function getTotalCost() {

        header("Content-Type: application/json");



        $input = json_decode(file_get_contents("php://input"), true);

        $number_of_buses = (int) $input["numberOfBuses"] ?? 0;

        $number_of_days = (int) $input["numberOfDays"] ?? 0;

        $distance = (float) number_format($input["distance"], 2) ?? 0;

        $diesel_price = (float) $this->getDieselPrice() ?? 0;

        $locations = $input["locations"] ?? [];

        $destination = $input["destination"] ?? "";

        $pickupPoint = $input["pickupPoint"] ?? "";



        if ($number_of_buses <= 0 || $number_of_days <= 0 || $distance <= 0 || $diesel_price <= 0) {

            echo json_encode(["success" => false, "message" => "Invalid input values."]);

            return;

        }



        // Define rates per region

        $regional_rates = [

            'NCR' => 19560, // Metro Manila

            'CAR' => 71040, // Cordillera Administrative Region

            'Region 1' => 117539, // Ilocos Region

            'Region 2' => 71040, // Cagayan Valley

            'Region 3' => 45020, // Central Luzon

            'Region 4A' => 20772, // Calabarzon

        ];



        // Get distances between locations

        $all_locations = [];

        if (!empty($pickupPoint)) $all_locations[] = $pickupPoint;

        if (!empty($locations)) $all_locations = array_merge($all_locations, $locations);

        

        // Determine region for each location

        $location_regions = [];

        $farthest_region = null;

        $farthest_distance = 0;

        $highest_rate = 0;

        

        // First, identify the region of each location

        foreach ($all_locations as $location) {

            $region = $this->determineRegionFromLocations([$location]);

            $location_regions[$location] = [

                'region' => $region,

                'rate' => $regional_rates[$region] ?? $regional_rates['Region 4A']

            ];

        }

        

        // Find the region with the highest rate

        foreach ($location_regions as $location => $info) {

            if ($info['rate'] > $highest_rate) {

                $highest_rate = $info['rate'];

                $farthest_region = $info['region'];

            }

        }

        

        if (!$farthest_region) {

            $farthest_region = 'Region 4A'; // Default to CALABARZON if no region found

        }



        $base_rate = $regional_rates[$farthest_region] ?? $regional_rates['Region 4A'];



        // Calculate base cost using regional rate

        $base_cost = round($base_rate * $number_of_buses * $number_of_days, 2);

        

        // Calculate fuel cost based on distance and diesel price

        $diesel_cost = round($distance * $diesel_price * $number_of_buses, 2);

        

        // Total cost is base cost plus fuel cost

        $total_cost = round($base_cost + $diesel_cost, 2);



        echo json_encode([

            "success" => true, 

            "total_cost" => $total_cost,

            "base_rate" => $base_rate,

            "base_cost" => $base_cost,

            "diesel_price" => $diesel_price,

            "diesel_cost" => $diesel_cost,

            "region" => $farthest_region,

            "location_regions" => $location_regions // Include this for debugging

        ]);

    }



    /**

     * Determine which region a set of locations belongs to

     * 

     * @param array $locations Array of location strings

     * @return string The determined region code

     */

    private function determineRegionFromLocations($locations) {

        if (empty($locations)) {

            return 'Region 4A'; // Default to CALABARZON if no locations

        }



        // Keywords for each region with more comprehensive listings

        $region_keywords = [

            'NCR' => [

                'metro manila', 'ncr', 'manila', 'quezon city', 'makati', 'pasig', 'taguig', 'mandaluyong',

                'pasay', 'parañaque', 'caloocan', 'marikina', 'muntinlupa', 'las piñas', 'malabon',

                'valenzuela', 'navotas', 'san juan', 'pateros', 'maynila', 'intramuros', 'malate', 'ermita',

                'binondo', 'quiapo', 'santa cruz', 'sampaloc', 'tondo', 'port area', 'paco', 'pandacan',

                'sta. mesa', 'sta. ana', 'san andres', 'san nicolas', 'commonwealth', 'fairview', 'novaliches',

                'cubao', 'ortigas', 'greenhills', 'eastwood', 'bgc', 'bonifacio global city', 'mckinley hill',

                'rockwell', 'poblacion', 'magallanes', 'moa', 'mall of asia', 'alabang'

            ],

            'CAR' => [

                'cordillera', 'car', 'baguio', 'benguet', 'mountain province', 'mt. province', 'ifugao', 'abra',

                'kalinga', 'apayao', 'banaue', 'sagada', 'la trinidad', 'itogon', 'kibungan', 'bakun', 'kabayan',

                'atok', 'tuba', 'tublay', 'bokod', 'buguias', 'mankayan', 'kapangan', 'sablan', 'bontoc',

                'barlig', 'bauko', 'besao', 'natonin', 'paracelis', 'sadanga', 'sagada', 'tadian', 'banaue',

                'aguinaldo', 'asipulo', 'hingyon', 'hungduan', 'kiangan', 'lagawe', 'lamut', 'mayoyao', 'tinoc'

            ],

            'Region 2' => [

                'cagayan valley', 'region 2', 'cagayan', 'isabela', 'nueva vizcaya', 'quirino', 'batanes',

                'tuguegarao', 'ilagan', 'cauayan', 'santiago', 'alaminos', 'alicia', 'angadanan', 'aurora',

                'bambang', 'bayombong', 'cabagan', 'cabarroguis', 'calayan', 'camalaniugan', 'cauayan', 'cordon',

                'diffun', 'dinapigue', 'divilacan', 'dumaran', 'echague', 'enrile', 'gamu', 'gattaran', 'ilagan',

                'jones', 'lal-lo', 'laoag', 'maconacon', 'maddela', 'mallig', 'nagtipunan', 'naguilian',

                'palanan', 'peñablanca', 'quezon', 'quirino', 'ramon', 'reina mercedes', 'roxas', 'san isidro',

                'santiago', 'santo tomas', 'solano', 'tuguegarao', 'tumauini', 'basco', 'ivana', 'mahatao', 'sabtang'

            ],

            'Region 3' => [

                'central luzon', 'region 3', 'bulacan', 'pampanga', 'tarlac', 'zambales', 'nueva ecija',

                'bataan', 'aurora', 'angeles', 'san fernando', 'malolos', 'cabanatuan', 'tarlac city', 'baler',

                'iba', 'balanga', 'olongapo', 'subic', 'clark', 'bacolor', 'guagua', 'lubao', 'san jose del monte',

                'meycauayan', 'bustos', 'baliwag', 'plaridel', 'pulilan', 'calumpit', 'hagonoy', 'obando',

                'san ildefonso', 'san miguel', 'san rafael', 'bocaue', 'marilao', 'sta. maria', 'guiguinto',

                'angat', 'norzagaray', 'dona remedios trinidad', 'candaba', 'arayat', 'mabalacat',

                'concepcion', 'gerona', 'paniqui', 'camiling', 'capas', 'bamban', 'cabanatuan', 'gapan',

                'palayan', 'san jose', 'munoz', 'talavera'

            ],

            'Region 4A' => [

                'calabarzon', 'region 4a', 'cavite', 'laguna', 'batangas', 'rizal', 'quezon', 'lucena',

                'tagaytay', 'calamba', 'santa rosa', 'sta. rosa', 'lipa', 'batangas city', 'antipolo', 'taytay', 'cainta',

                'biñan', 'san pedro', 'cabuyao', 'tanauan', 'bacoor', 'dasmariñas', 'imus', 'general trias',

                'trece martires', 'kawit', 'alfonso', 'amadeo', 'carmona', 'cavite city', 'general mariano alvarez',

                'indang', 'magallanes', 'maragondon', 'mendez', 'naic', 'noveleta', 'rosario', 'silang', 'tanza',

                'ternate', 'alaminos', 'bay', 'cabuyao', 'calauan', 'famy', 'kalayaan', 'liliw', 'los baños',

                'luisiana', 'lumban', 'mabitac', 'magdalena', 'majayjay', 'nagcarlan', 'paete', 'pagsanjan',

                'pakil', 'pangil', 'pila', 'rizal', 'san pablo', 'santa cruz', 'santa maria', 'siniloan',

                'victoria', 'agoncillo', 'alitagtag', 'balete', 'balayan', 'bauan', 'calaca', 'calatagan', 'cuenca',

                'ibaan', 'laurel', 'lemery', 'lian', 'lobo', 'mabini', 'malvar', 'mataas na kahoy', 'nasugbu',

                'padre garcia', 'rosario', 'san jose', 'san juan', 'san luis', 'san nicolas', 'san pascual',

                'santa teresita', 'santo tomas', 'taal', 'talisay', 'taysan', 'tingloy', 'angono', 'baras',

                'binangonan', 'cardona', 'jala-jala', 'morong', 'pililla', 'rodriguez', 'san mateo', 'tanay', 'teresa',

                'agdangan', 'alabat', 'atimonan', 'buenavista', 'burdeos', 'calauag', 'candelaria', 'catanauan',

                'dolores', 'general luna', 'general nakar', 'guinayangan', 'gumaca', 'infanta', 'jomalig', 'lopez',

                'lucban', 'macalelon', 'mauban', 'mulanay', 'padre burgos', 'pagbilao', 'panukulan', 'patnanungan',

                'perez', 'pitogo', 'plaridel', 'polillo', 'quezon', 'real', 'sampaloc', 'san andres', 'san antonio',

                'san francisco', 'san narciso', 'sariaya', 'tagkawayan', 'tiaong', 'unisan', 'enchanted kingdom'

            ],

            'Region 1' => [

                'ilocos region', 'region 1', 'ilocos norte', 'ilocos sur', 'la union', 'pangasinan',

                'laoag', 'vigan', 'san fernando', 'dagupan', 'batac', 'candon', 'alaminos', 'urdaneta',

                'san carlos', 'pagudpud', 'bangui', 'burgos', 'pasuquin', 'bacarra', 'vintar', 'paoay',

                'currimao', 'badoc', 'pinili', 'marcos', 'nueva era', 'sarrat', 'piddig', 'carasi', 'solsona',

                'dingras', 'san nicolas', 'cabugao', 'sinait', 'santa catalina', 'santa lucia', 'santa cruz',

                'san vicente', 'santa', 'narvacan', 'santa maria', 'san esteban', 'santiago', 'bantay',

                'caoayan', 'magsingal', 'santo domingo', 'san ildefonso', 'san juan', 'san vicente', 'aringay',

                'agoo', 'bauang', 'caba', 'santo tomas', 'rosario', 'pugo', 'tubao', 'naguilian', 'bagulin',

                'burgos', 'san gabriel', 'santol', 'sudipen', 'luna', 'bangar', 'balaoan', 'bacnotan',

                'lingayen', 'bolinao', 'san fabian', 'manaoag', 'binmaley', 'calasiao', 'santa barbara',

                'malasiqui', 'bayambang', 'basista', 'bautista', 'alcala', 'santo tomas', 'mangaldan',

                'mangatarem', 'aguilar', 'bugallon', 'labrador', 'infanta', 'mabini', 'burgos', 'dasol',

                'agno', 'bani', 'alaminos', 'sual', 'san manuel', 'binalonan', 'laoac', 'pozorrubio',

                'san jacinto', 'san nicolas', 'tayug', 'natividad', 'san quintin', 'umingan', 'balungao',

                'rosales', 'asingan', 'santa maria', 'villasis', 'anda', 'sison', 'san carlos'

            ]

        ];



        // Count matches for each region

        $region_matches = [

            'NCR' => 0,

            'CAR' => 0,

            'Region 1' => 0,

            'Region 2' => 0,

            'Region 3' => 0,

            'Region 4A' => 0

        ];



        // Look for region keywords in each location

        foreach ($locations as $location) {

            $location = strtolower($location);

            

            // First check for exact region matches

            foreach ($region_keywords as $region => $keywords) {

                if (strpos($location, strtolower($region)) !== false) {

                    $region_matches[$region] += 2; // Give higher weight to exact region matches

                    continue;

                }

                

                foreach ($keywords as $keyword) {

                    // To avoid partial word matches, check if the keyword is a whole word or part of a compound word

                    // Using word boundary check for common keywords that might cause false matches

                    if ($keyword === 'santa' || $keyword === 'san') {

                        // For 'santa' and 'san', make sure they're followed by another word

                        if (preg_match('/\b' . preg_quote($keyword, '/') . '\s+[a-z]+\b/i', $location)) {

                            $region_matches[$region]++;

                        }

                    }

                    else if (strpos($location, $keyword) !== false) {

                        $region_matches[$region]++;

                        // Don't break here - allow multiple matches per region for better accuracy

                    }

                }

            }

            

            // Give higher weight to more specific location matches

            if (strpos($location, 'santa rosa') !== false && strpos($location, 'laguna') !== false) {

                $region_matches['Region 4A'] += 3; // Strong indicator for Region 4A

            }

            

            if (strpos($location, 'enchanted kingdom') !== false) {

                $region_matches['Region 4A'] += 2; // Enchanted Kingdom is definitely in Region 4A

            }

        }



        // Find the region with the most matches

        $max_matches = 0;

        $matched_region = 'Region 4A'; // Default

        

        foreach ($region_matches as $region => $matches) {

            if ($matches > $max_matches) {

                $max_matches = $matches;

                $matched_region = $region;

            }

        }



        // If no matches were found, try to determine region using more advanced geolocation methods

        if ($max_matches === 0 && !empty($locations[0])) {

            // Log the unmatched location for future improvements

            error_log("Could not determine region for location: " . $locations[0]);

        }



        return $matched_region;

    }



    public function requestBooking() {

        header("Content-Type: application/json");



        if (!isset($_SESSION["user_id"])) {

            echo json_encode(["success" => false, "message" => "You are not logged in."]);

            return;

        }



        $input = json_decode(file_get_contents("php://input"), true);



        // Validate required data

        if (empty($input["pickupPoint"]) || empty($input["destination"]) || empty($input["dateOfTour"]) || empty($input["numberOfDays"]) || empty($input["numberOfBuses"])) {

            echo json_encode(["success" => false, "message" => "Missing required data."]);

            return;

        }



        // Validate terms agreement

        if (!isset($input["agreeTerms"]) || $input["agreeTerms"] !== true) {

            echo json_encode(["success" => false, "message" => "You must agree to the terms and conditions."]);

            return;

        }



        // Get the user's IP address

        $user_ip = $this->getUserIP();



        $pickup_point = $input["pickupPoint"];

        $destination = $input["destination"];

        $date_of_tour = $input["dateOfTour"];

        $pickup_time = $input["pickupTime"] ?? null;

        $number_of_days = $input["numberOfDays"];

        $number_of_buses = $input["numberOfBuses"];

        $stops = $input["stops"] ?? [];

        $total_cost = $input["totalCost"] ?? 0;

        $balance = $input["balance"] ?? $total_cost;

        $trip_distances = $input["tripDistances"] ?? [];

        $addresses = $input["addresses"] ?? [];

        $is_rebooking = isset($input["isRebooking"]) && $input["isRebooking"] === true;

        $rebooking_id = $input["rebookingId"] ?? null;

        $base_cost = $input["baseCost"] ?? null;

        $diesel_cost = $input["dieselCost"] ?? null;

        $base_rate = $input["baseRate"] ?? null;

        $diesel_price = $input["dieselPrice"] ?? null;

        $total_distance = $input["totalDistance"] ?? null;



        // Check if the user is rebooking

        if ($is_rebooking) {

            $result = $this->requestRebooking(

                $rebooking_id, $date_of_tour, $destination, $pickup_point, $number_of_days, $number_of_buses, $_SESSION["user_id"], $stops, $total_cost, 

                $balance, $trip_distances, $addresses, $base_cost, $diesel_cost, $base_rate, $diesel_price, $total_distance, $pickup_time

            );

            echo json_encode(["success" => true, "message" => $result["message"]]);

            return;

        }

        

        $booking_result = $this->bookingModel->requestBooking(

            $date_of_tour, $destination, $pickup_point, $number_of_days, $number_of_buses, $_SESSION["user_id"], $stops, $total_cost,

            $balance, $trip_distances, $addresses, $base_cost, $diesel_cost, $base_rate, $diesel_price, $total_distance, $pickup_time

        );



        if ($booking_result["success"]) {

            // Get the booking ID 

            $booking_id = $booking_result["booking_id"];

            

            // Save the terms agreement

            if ($booking_id) {

                $this->bookingModel->saveTermsAgreement(

                    $booking_id,

                    $_SESSION["user_id"],

                    true, // $input["agreeTerms"] is already validated to be true

                    $user_ip

                );

            }



            // Create notification for admin

            $user = $this->getUserInfo($_SESSION["user_id"]);

            $user_name = $user["first_name"] . " " . $user["last_name"];

            $notification_message = "New booking request from " . $user_name;

            $this->notificationModel->addNotification("booking_request", $notification_message, $booking_id);

        }



        echo json_encode($booking_result);

    }



    public function requestRebooking(

        $bookingId, $date_of_tour, $destination, $pickup_point, $number_of_days, $number_of_buses, $user_id, $stops, $total_cost,

        $balance, $trip_distances, $addresses, $base_cost, $diesel_cost, $base_rate, $diesel_price, $total_distance, $pickup_time

    ) {

        $oldBookingData = $this->getEntityBeforeUpdate('bookings', 'booking_id', $bookingId);

        $oldBookingData["trip_distances"] = $this->getEntityBeforeUpdate('trip_distances', 'booking_id', $bookingId);

        $oldBookingData["booking_costs"] = $this->getEntityBeforeUpdate('booking_costs', 'booking_id', $bookingId);

        $oldBookingData["stops"] = $this->getEntityBeforeUpdate('booking_stops', 'booking_id', $bookingId) ?? [];



        $new_stops = [];

        if (!empty($stops)) {

            foreach ($stops as $index => $stop) {

                $stops = [

                    "booking_stops_id" => $oldBookingData["stops"][$index]["booking_stops_id"] ?? null,

                    "stop_order" => $index + 1,

                    "location" => $stop,

                    "boking_id" => $bookingId

                ];

                $new_stops[] = $stops;

            }

        }

        $new_stops = (empty($new_stops)) ? false : $new_stops;



        $new_trip_distances = [];

        foreach ($trip_distances["rows"] as $i => $row) {

            $distance_value = number_format($row["elements"][$i]["distance"]["value"] ?? 0, 2, ".", ""); // in km

            $origin = $addresses[$i];

            $destinations = $addresses[$i + 1] ?? $addresses[0]; // round trip fallback



            $trip_distance = [

                "id" => $oldBookingData["trip_distances"][$i]["id"] ?? null,

                "origin" => $origin,

                "destination" => $destinations,

                "distance" =>  $distance_value,

                "booking_id" => (int)$bookingId

            ];



            $new_trip_distances[] = $trip_distance;

        }



        $newBookingData = [ 

            "date_of_tour" => $date_of_tour,

            "pickup_point" => $pickup_point,

            "destination" => $destination,

            "number_of_buses" => $number_of_buses,

            "number_of_days" => $number_of_days,

            "user_id" => $user_id,

            "stops" => $new_stops,

            "booking_costs" => [

                "id" => $oldBookingData["booking_costs"]["id"] ?? null,

                "total_cost" => round($total_cost, 2),

                "base_rate" => round($base_rate, 2),

                "total_distance" => round($total_distance, 2),

                "booking_id" => $bookingId,

                "diesel_price" => round($diesel_price, 2),

                "diesel_cost" => round($diesel_cost, 2),

                "base_cost" => round($base_cost, 2),

                "discount" => $oldBookingData["booking_costs"]["discount"] ?? null,

                "discount_type" => $oldBookingData["booking_costs"]["discount_type"] ?? null,

                "discount_amount" => $oldBookingData["booking_costs"]["discount_amount"] ?? null,

                "gross_price" => $oldBookingData["booking_costs"]["gross_price"] ?? null

            ],

            "balance" => $balance,

            "trip_distances" => $trip_distances,

            "pickup_time" => $pickup_time,

            "addresses" => $addresses

        ];



        // Check if the booking is pending, then update it directly without rebooking

        if ($oldBookingData["status"] === "Pending") {

            $this->bookingModel->updateBooking(

                $bookingId, $date_of_tour, $destination, $pickup_point, $number_of_days, $number_of_buses, $user_id, 

                $new_stops, $total_cost, $balance, $trip_distances, $addresses, $base_cost, $diesel_cost, 

                $base_rate, $diesel_price, $total_distance, $pickup_time

            );



            $this->logAudit('update', 'bookings', $bookingId, $oldBookingData, $newBookingData, $_SESSION["user_id"]);

            return ["success" => true, "message" => "Booking updated successfully."];

        }



        $result = $this->bookingModel->requestRebooking($bookingId, $_SESSION["user_id"]);

        $this->logAudit('update', 'bookings', $bookingId, $oldBookingData, $newBookingData, $_SESSION["user_id"]);



        if ($result["success"]) {

            // Create notification for admin about rebooking

            $user = $this->getUserInfo($_SESSION["user_id"]);

            $user_name = $user["first_name"] . " " . $user["last_name"];

            $notification_message = "Rebooking request from " . $user_name . " for booking #" . $bookingId;

            $this->notificationModel->addNotification("rebooking_request", $notification_message, $bookingId);

        }



        return [

            "success" => $result["success"],

            "message" => $result["success"] 

                ? $result["message"]

                : "Failed to update booking: " . $result["message"]

        ];

    }



    /**

     * Get the current user's IP address

     * @return string

     */

    private function getUserIP() {

        $ip = '';

        

        // Check for proxy forwarded IP

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

        } 

        // Check for shared IP

        elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {

            $ip = $_SERVER['HTTP_CLIENT_IP'];

        } 

        // Get the remote address

        elseif (!empty($_SERVER['REMOTE_ADDR'])) {

            $ip = $_SERVER['REMOTE_ADDR'];

        }

        

        // Sanitize the IP to prevent SQL injection

        return filter_var($ip, FILTER_VALIDATE_IP) ?: '0.0.0.0';

    }



    public function getAllBookings() {

        $data = json_decode(file_get_contents("php://input"), true);

        $status = $data["status"];

        $column = $data["column"];

        $order = $data["order"];

        $page = isset($data["page"]) ? (int)$data["page"] : 1;

        $limit = isset($data["limit"]) ? (int)$data["limit"] : 10;

        $search = isset($data["search"]) ? $data["search"] : "";

        $date_filter = isset($data["date_filter"]) ? $data["date_filter"] : null;

        $balance_filter = isset($data["balance_filter"]) ? $data["balance_filter"] : null;



        $user_id = $_SESSION["user_id"];

        $result = $this->bookingModel->getAllBookings($user_id, $status, $column, $order, $page, $limit, $search, $date_filter, $balance_filter);



        header("Content-Type: application/json");



        if (is_array($result)) {

            echo json_encode([

                "success" => true, 

                "bookings" => $result["bookings"],

                "pagination" => [

                    "total_records" => $result["total_records"],

                    "total_pages" => $result["total_pages"],

                    "current_page" => $result["current_page"]

                ]

            ]);

        } else {

            echo json_encode(["success" => false, "message" => $result]);

        }

    }



    public function getBooking() {

        header("Content-Type: application/json");



        $data = json_decode(file_get_contents("php://input"), true);



        $booking_id = $data["bookingId"];

        $user_id = $_SESSION["user_id"];



        $booking = $this->bookingModel->getBooking($booking_id, $user_id);

        $stops = $this->bookingModel->getBookingStops($booking_id);

        $distances = $this->bookingModel->getTripDistances($booking_id);



        if ($booking) {

            echo json_encode(["success" => true, "booking" => $booking, "stops" => $stops, "distances" =>  $distances]);

        } else {

            echo json_encode(["success" => false, "message" => $booking]);

        }

    }



    public function showBookingRequestTable() {

        require_once __DIR__ . "/../../views/client/booking_requests.php";

    }

    

    public function showBookingDetail() {

        require_once __DIR__ . "/../../views/client/booking_request.php";

    }



    public function cancelBooking() {

        header("Content-Type: application/json");



        $data = json_decode(file_get_contents("php://input"), true);



        $booking_id = $data["bookingId"];

        $user_id = $_SESSION["user_id"];

        $reason = $data["reason"];

        $reason_category = isset($data["reasonCategory"]) ? $data["reasonCategory"] : null;

        $amount_paid = 0;



        if ($this->bookingModel->isClientPaid($booking_id)) {

            $amount_paid = $this->bookingModel->getAmountPaid($booking_id, $user_id);

            $this->bookingModel->cancelPayment($booking_id, $user_id);

        }



        $amount_refunded = $amount_paid * 0.80;



        // Get booking details before cancelling to include in notification

        $booking = $this->bookingModel->getBooking($booking_id, $user_id);

        

        $result = $this->bookingModel->cancelBooking($reason, $booking_id, $user_id, $amount_refunded, $reason_category);



        if ($result["success"] && $booking) {

            // Create notification for admin about booking cancellation

            $clientName = $_SESSION["client_name"];

            $destination = $booking["destination"] ?? "Unknown destination";

            $message = "Booking #{$booking_id} to {$destination} cancelled by {$clientName}. Reason: {$reason}";

            $this->notificationModel->addNotification("booking_cancelled_by_client", $message, $booking_id);

        }



        echo json_encode([

            "success" => $result["success"], 

            "message" => $result["success"] 

                ? "Booking Canceled Successfully." 

                : $result["message"]

        ]);

    }



    public function updatePastBookings() {

        return $this->bookingModel->updatePastBookings();

    }



    public function addPayment() {

        header("Content-Type: application/json");

        

        // Check if it's a regular form submission or JSON

        if (!empty($_POST)) {

            $booking_id = $_POST["booking_id"];

            $client_id = $_POST["user_id"];

            $amount = $_POST["amount"];

            $payment_method = $_POST["payment_method"];

        } else {

            // Fallback to JSON input if no POST data

            $data = json_decode(file_get_contents("php://input"), true);

            $booking_id = $data["bookingId"] ?? null;

            $client_id = $data["userId"] ?? null;

            $amount = $data["amount"] ?? null;

            $payment_method = $data["paymentMethod"] ?? null;

        }

        

        // Validate required data

        if (!$booking_id || !$client_id || !$amount || !$payment_method) {

            echo json_encode([

                "success" => false,

                "message" => "Missing required payment information"

            ]);

            return;

        }

        

        // Handle PayMongo payments differently

        if ($payment_method === "PayMongo" || $payment_method === "GCash") {

            $this->handlePayMongoPayment($booking_id, $client_id, $amount, $payment_method);

            return;

        }

        

        // Validate proof of payment for payment methods that require it

        $requiresProof = in_array($payment_method, ["Bank Transfer", "Online Payment", "Maya"]);

        $hasProofFile = isset($_FILES["proof_of_payment"]) && $_FILES["proof_of_payment"]["error"] === UPLOAD_ERR_OK;

        

        if ($requiresProof && !$hasProofFile) {

            echo json_encode([

                "success" => false,

                "message" => "Proof of payment is required for {$payment_method}."

            ]);

            return;

        }

        

        // Handle proof of payment upload

        $proof_of_payment = null;

        if (isset($_FILES["proof_of_payment"]) && $_FILES["proof_of_payment"]["error"] === UPLOAD_ERR_OK) {

            $upload_dir = __DIR__ . "/../../uploads/payments/";

            

            // Create directory if it doesn't exist

            if (!file_exists($upload_dir)) {

                mkdir($upload_dir, 0777, true);

            }

            

            $file_extension = pathinfo($_FILES["proof_of_payment"]["name"], PATHINFO_EXTENSION);

            $file_name = "payment_" . $booking_id . "_" . time() . "." . $file_extension;

            $target_file = $upload_dir . $file_name;

            

            // Check file type

            $allowed_types = ["jpg", "jpeg", "png", "pdf"];

            if (!in_array(strtolower($file_extension), $allowed_types)) {

                echo json_encode([

                    "success" => false,

                    "message" => "Only JPG, PNG, and PDF files are allowed."

                ]);

                return;

            }

            

            // Move uploaded file

            if (move_uploaded_file($_FILES["proof_of_payment"]["tmp_name"], $target_file)) {

                $proof_of_payment = $file_name;

            } else {

                echo json_encode([

                    "success" => false,

                    "message" => "Failed to upload payment proof."

                ]);

                return;

            }

        }

    

        $result = $this->bookingModel->addPayment($booking_id, $client_id, $amount, $payment_method, $proof_of_payment);

    

        if ($result["success"]) {

            // Create notification for admin about new payment

            // Get booking details to include in notification

            $booking = $this->bookingModel->getBooking($booking_id, $client_id);

            if ($booking) {

                $clientName = $_SESSION["client_name"];

                $formattedAmount = number_format($amount, 2);

                $message = "New payment of PHP {$formattedAmount} submitted by {$clientName} for booking #{$booking_id}";

                $this->notificationModel->addNotification("payment_submitted", $message, $booking_id);

            }



            error_log("Payment submitted successfully fsor booking ID: {$booking_id} by user ID: {$client_id}");

            

            echo json_encode([

                "success" => true,

                "message" => "Payment submitted successfully!"

            ]);

        } else {

            echo json_encode([

                "success" => false,

                "message" => is_string($result) ? $result : "Payment submission failed. Please try again."

            ]);

        }

    }

    

    /**

     * Get payment settings from the database for display in payment modal

     */

    public function getPaymentSettings() {

        header("Content-Type: application/json");

        

        // Include the Settings model

        require_once __DIR__ . "/../../models/admin/Settings.php";

        $settingsModel = new Settings();

        

        // Get payment-related settings

        $paymentSettings = $settingsModel->getSettingsByGroup('payment');

        

        // Format settings into associative array for easier access

        $formattedSettings = [];

        foreach ($paymentSettings as $setting) {

            $formattedSettings[$setting['setting_key']] = $setting['setting_value'];

        }

        

        echo json_encode([

            "success" => true,

            "settings" => $formattedSettings

        ]);

    }

        

    public function getBookingStatistics() {

        header("Content-Type: application/json");

        

        $user_id = $_SESSION["user_id"];

        

        // Get total bookings count

        $total = $this->bookingModel->getBookingsCount($user_id, "all");

        

        // Get confirmed bookings count

        $confirmed = $this->bookingModel->getBookingsCount($user_id, "confirmed");

        

        // Get pending bookings count

        $pending = $this->bookingModel->getBookingsCount($user_id, "pending");

        

        // Get upcoming tours count (confirmed bookings with future dates)

        $upcoming = $this->bookingModel->getUpcomingToursCount($user_id);

        

        echo json_encode([

            "success" => true,

            "statistics" => [

                "total" => $total,

                "confirmed" => $confirmed,

                "pending" => $pending,

                "upcoming" => $upcoming

            ]

        ]);

    }

    

    public function getCalendarEvents() {

        header("Content-Type: application/json");

        

        $data = json_decode(file_get_contents("php://input"), true);

        $start = isset($data["start"]) ? $data["start"] : null;

        $end = isset($data["end"]) ? $data["end"] : null;

        

        if (!$start || !$end) {

            echo json_encode([

                "success" => false,

                "message" => "Start and end dates are required"

            ]);

            return;

        }

        

        $user_id = $_SESSION["user_id"];

        $events = $this->bookingModel->getBookingsForCalendar($user_id, $start, $end);

        

        echo json_encode([

            "success" => true,

            "events" => $events

        ]);

    }

    

    public function getAvailableBuses() {

        header("Content-Type: application/json");

        

        $data = json_decode(file_get_contents("php://input"), true);

        $start_date = isset($data["start_date"]) ? $data["start_date"] : null;

        $end_date = isset($data["end_date"]) ? $data["end_date"] : null;

        

        if (!$start_date || !$end_date) {

            echo json_encode([

                "success" => false,

                "message" => "Start and end dates are required"

            ]);

            return;

        }

        

        // Validate date formats

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {

            echo json_encode([

                "success" => false,

                "message" => "Invalid date format. Use YYYY-MM-DD."

            ]);

            return;

        }

        

        try {

            // Get total number of active buses

            $stmt = $this->bookingModel->conn->prepare("SELECT COUNT(*) FROM buses WHERE status = 'active'");

            $stmt->execute();

            $total_buses = (int) $stmt->fetchColumn();

            

            if ($total_buses <= 0) {

                echo json_encode([

                    "success" => false,

                    "message" => "No active buses found in the system."

                ]);

                return;

            }

            

            // Create an array for each date in the range

            $start = new DateTime($start_date);

            $end = new DateTime($end_date);

            

            // Validate that end date is not before start date

            if ($end < $start) {

                echo json_encode([

                    "success" => false,

                    "message" => "End date cannot be before start date."

                ]);

                return;

            }

            

            $interval = DateInterval::createFromDateString('1 day');

            $period = new DatePeriod($start, $interval, $end->modify('+1 day'));

            

            $availability = [];

            

            foreach ($period as $dt) {

                $current_date = $dt->format("Y-m-d");

                

                // For each date, find how many buses are already booked

                $stmt = $this->bookingModel->conn->prepare("

                    SELECT COUNT(DISTINCT bb.bus_id) 

                    FROM booking_buses bb

                    JOIN bookings bo ON bb.booking_id = bo.booking_id

                    WHERE 

                        -- Only consider bookings with active statuses

                        (bo.status = 'Confirmed' OR bo.status = 'Processing')

                        AND (bo.is_rebooked = 0)

                        -- Date range check

                        AND (bo.date_of_tour <= :current_date AND bo.end_of_tour >= :current_date)

                ");

                $stmt->bindParam(":current_date", $current_date);

                $stmt->execute();

                $booked_buses = (int) $stmt->fetchColumn();

                

                // Calculate available buses

                $available_buses = $total_buses - $booked_buses;

                if ($available_buses < 0) $available_buses = 0;

                

                $availability[] = [

                    "date" => $current_date,

                    "available" => $available_buses,

                    "total" => $total_buses,

                    "booked" => $booked_buses

                ];

            }

            

            // Add debugging information in development environment

            $debug_info = [];

            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {

                $debug_info = [

                    'query_params' => [

                        'start_date' => $start_date,

                        'end_date' => $end_date

                    ],

                    'total_buses' => $total_buses,

                    'date_count' => count($availability)

                ];

            }

            

            echo json_encode([

                "success" => true,

                "availability" => $availability,

                "debug" => $debug_info

            ]);

            

        } catch (Exception $e) {

            error_log("Error in getAvailableBuses: " . $e->getMessage());

            echo json_encode([

                "success" => false,

                "message" => "Error retrieving bus availability: " . $e->getMessage()

            ]);

        }

    }

    

    /**

     * Get driver availability for a date range

     */

    public function getDriverAvailability() {

        header("Content-Type: application/json");

        

        $data = json_decode(file_get_contents("php://input"), true);

        $start_date = isset($data["start_date"]) ? $data["start_date"] : null;

        $end_date = isset($data["end_date"]) ? $data["end_date"] : null;

        

        if (!$start_date || !$end_date) {

            echo json_encode([

                "success" => false,

                "message" => "Start and end dates are required"

            ]);

            return;

        }

        

        // Validate date formats

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {

            echo json_encode([

                "success" => false,

                "message" => "Invalid date format. Use YYYY-MM-DD."

            ]);

            return;

        }

        

        try {

            // Get total number of active drivers

            $stmt = $this->bookingModel->conn->prepare("SELECT COUNT(*) FROM drivers WHERE status = 'Active' AND availability = 'Available'");

            $stmt->execute();

            $total_drivers = (int) $stmt->fetchColumn();

            

            if ($total_drivers <= 0) {

                echo json_encode([

                    "success" => false,

                    "message" => "No active drivers found in the system."

                ]);

                return;

            }

            

            // Create an array for each date in the range

            $start = new DateTime($start_date);

            $end = new DateTime($end_date);

            

            // Validate that end date is not before start date

            if ($end < $start) {

                echo json_encode([

                    "success" => false,

                    "message" => "End date cannot be before start date."

                ]);

                return;

            }

            

            $interval = DateInterval::createFromDateString('1 day');

            $period = new DatePeriod($start, $interval, $end->modify('+1 day'));

            

            $availability = [];

            

            foreach ($period as $dt) {

                $current_date = $dt->format("Y-m-d");

                

                // For each date, find how many drivers are already assigned

                $stmt = $this->bookingModel->conn->prepare("

                    SELECT COUNT(DISTINCT bd.driver_id) 

                    FROM booking_driver bd

                    JOIN bookings bo ON bd.booking_id = bo.booking_id

                    WHERE 

                        -- Only consider bookings with active statuses

                        (bo.status = 'Confirmed' OR bo.status = 'Processing')

                        AND (bo.is_rebooked = 0)

                        -- Date range check

                        AND (bo.date_of_tour <= :current_date AND bo.end_of_tour >= :current_date)

                ");

                $stmt->bindParam(":current_date", $current_date);

                $stmt->execute();

                $assigned_drivers = (int) $stmt->fetchColumn();

                

                // Calculate available drivers

                $available_drivers = $total_drivers - $assigned_drivers;

                if ($available_drivers < 0) $available_drivers = 0;

                

                $availability[] = [

                    "date" => $current_date,

                    "available" => $available_drivers,

                    "total" => $total_drivers,

                    "assigned" => $assigned_drivers

                ];

            }

            

            echo json_encode([

                "success" => true,

                "availability" => $availability

            ]);

            

        } catch (Exception $e) {

            error_log("Error in getDriverAvailability: " . $e->getMessage());

            echo json_encode([

                "success" => false,

                "message" => "Error retrieving driver availability: " . $e->getMessage()

            ]);

        }

    }

    

    public function getBookingDetails() {

        header("Content-Type: application/json");

        

        $data = json_decode(file_get_contents("php://input"), true);

        $booking_id = isset($data["bookingId"]) ? $data["bookingId"] : null;

        

        if (!$booking_id) {

            echo json_encode([

                "success" => false,

                "message" => "Booking ID is required"

            ]);

            return;

        }



        $user_id = $_SESSION["user_id"];    

        $payments = $this->bookingModel->getPaymentHistory($booking_id);

        $booking = $this->bookingModel->getBooking($booking_id, $user_id);

        

        if ($booking) {

            $requestedChanges = null;

            // If this booking is currently in Rebooking status, include the requested changes (latest audit new_values)
            if (isset($booking['status']) && strtolower($booking['status']) === 'rebooking') {
                if (!class_exists('AuditTrailModel')) {
                    require_once __DIR__ . '/../../models/admin/AuditTrailModel.php';
                }
                try {
                    $auditModel = new AuditTrailModel();
                    $history = $auditModel->getEntityHistory('bookings', (int)$booking_id);
                    if (is_array($history) && count($history) > 0) {
                        $latest = $history[0];
                        if (!empty($latest['new_values'])) {
                            $decoded = json_decode($latest['new_values'], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $requestedChanges = $decoded;
                            }
                        }
                    }
                } catch (Exception $e) {
                    // ignore
                }
            }

            echo json_encode([

                "success" => true,

                "booking" => $booking,

                "payments" => $payments,

                "requested_changes" => $requestedChanges

            ]);

        } else {

            echo json_encode([

                "success" => false,

                "message" => "Booking not found or access denied"

            ]);

        }

    }

    

    public function exportBookings() {

        header("Content-Type: application/json");

        

        $data = json_decode(file_get_contents("php://input"), true);

        $format = isset($data["format"]) ? $data["format"] : "pdf";

        $status = isset($data["status"]) ? $data["status"] : "all";

        $search = isset($data["search"]) ? $data["search"] : "";

        $date_filter = isset($data["date_filter"]) ? $data["date_filter"] : null;

        $balance_filter = isset($data["balance_filter"]) ? $data["balance_filter"] : null;

        

        $user_id = $_SESSION["user_id"];

        

        // Get bookings data

        $result = $this->bookingModel->getAllBookings($user_id, $status, "date_of_tour", "asc", 1, 1000, $search, $date_filter, $balance_filter);

        

        if (!is_array($result) || empty($result["bookings"])) {

            echo json_encode([

                "success" => false,

                "message" => "No bookings found to export"

            ]);

            return;

        }

        

        $bookings = $result["bookings"];

        

        // Generate export file based on format

        if ($format === "pdf") {

            // Generate PDF file

            // Implementation will depend on PDF library used in the project

            echo json_encode([

                "success" => true,

                "url" => "/exports/bookings_" . $user_id . "_" . time() . ".pdf"

            ]);

        } else if ($format === "csv") {

            // Generate CSV file

            $filename = "bookings_" . $user_id . "_" . time() . ".csv";

            $filepath = __DIR__ . "/../../exports/" . $filename;

            

            // Create exports directory if it doesn't exist

            if (!file_exists(__DIR__ . "/../../exports/")) {

                mkdir(__DIR__ . "/../../exports/", 0777, true);

            }

            

            // Create CSV file

            $csv = fopen($filepath, "w");

            

            // Add headers

            fputcsv($csv, ["Booking ID", "Destination", "Date of Tour", "End of Tour", "Days", "Buses", "Total Cost", "Balance", "Status"]);

            

            // Add data

            foreach ($bookings as $booking) {

                fputcsv($csv, [

                    $booking["booking_id"],

                    $booking["destination"],

                    $booking["date_of_tour"],

                    $booking["end_of_tour"],

                    $booking["number_of_days"],

                    $booking["number_of_buses"],

                    $booking["total_cost"],

                    $booking["balance"],

                    $booking["status"]

                ]);

            }

            

            fclose($csv);

            

            echo json_encode([

                "success" => true,

                "url" => "/exports/" . $filename

            ]);

        } else {

            echo json_encode([

                "success" => false,

                "message" => "Invalid export format"

            ]);

        }

    }



    public function printInvoice($booking_id = null) {

        if (!$booking_id) {

            // Redirect to bookings page if no ID provided

            header("Location: /home/booking-requests");

            exit();

        }

        

        $user_id = $_SESSION["user_id"];

        

        // Get booking details

        $booking = $this->bookingModel->getBooking($booking_id, $user_id);

        

        if (!$booking) {

            // Booking not found or doesn't belong to this user

            header("Location: /home/booking-requests");

            exit();

        }

        

        // Get booking stops

        $stops = $this->bookingModel->getBookingStops($booking_id);

        

        // Get payment history

        $payments = $this->bookingModel->getPaymentHistory($booking_id);

        

        // Load the invoice template view

        require_once __DIR__ . "/../../views/client/invoice.php";

    }

    

    public function printContract($booking_id = null) {

        if (!$booking_id) {

            // Redirect to bookings page if no ID provided

            header("Location: /home/booking-requests");

            exit();

        }

        

        $user_id = $_SESSION["user_id"];

        

        // Get booking details

        $booking = $this->bookingModel->getBooking($booking_id, $user_id);

        

        if (!$booking) {

            // Booking not found or doesn't belong to this user

            header("Location: /home/booking-requests");

            exit();

        }

        

        // Get booking stops

        $stops = $this->bookingModel->getBookingStops($booking_id);

        

        // Get assigned drivers and buses

        $drivers = $this->bookingModel->getAssignedDrivers($booking_id);

        $buses = $this->bookingModel->getAssignedBuses($booking_id);

        

        // Load the contract template view

        require_once __DIR__ . "/../../views/client/contract.php";

    }



    /**

     * Handle PayMongo payment processing

     * 

     * @param int $booking_id

     * @param int $client_id

     * @param float $amount

     * @param string $payment_method

     */

    private function handlePayMongoPayment($booking_id, $client_id, $amount, $payment_method) {

        try {

            // Include PayMongo service

            require_once __DIR__ . "/../../services/PayMongoService.php";

            $payMongoService = new PayMongoService();

            

            // Validate amount

            if (!PayMongoService::validateAmount($amount)) {

                echo json_encode([

                    "success" => false,

                    "message" => "Invalid payment amount"

                ]);

                return;

            }

            

            // Get booking details for description

            $booking = $this->bookingModel->getBooking($booking_id, $client_id);

            if (!$booking) {

                echo json_encode([

                    "success" => false,

                    "message" => "Booking not found"

                ]);

                return;

            }

            

            // Create checkout session

            $result = $payMongoService->createCheckoutSession(

                $booking_id, 

                $client_id, 

                $amount,

                "Payment for booking to " . $booking['destination'] . " (Booking #" . $booking_id . ")"

            );

            

            if ($result['success']) {

                // Create notification for admin about new PayMongo payment

                $clientName = $_SESSION["client_name"] ?? "Client";

                $formattedAmount = PayMongoService::formatAmount($amount);

                $message = "New PayMongo payment of {$formattedAmount} initiated by {$clientName} for booking #{$booking_id}";

                $this->notificationModel->addNotification("payment_submitted", $message, $booking_id);

                

                echo json_encode([

                    "success" => true,

                    "payment_type" => "paymongo",

                    "checkout_url" => $result['checkout_url'],

                    "checkout_session_id" => $result['checkout_session_id'],

                    "amount" => $result['amount'],

                    "message" => "Redirecting to PayMongo payment gateway..."

                ]);

            } else {

                echo json_encode([

                    "success" => false,

                    "message" => $result['message']

                ]);

            }

            

        } catch (Exception $e) {

            error_log("PayMongo payment handler error: " . $e->getMessage());

            echo json_encode([

                "success" => false,

                "message" => "Payment processing failed. Please try again."

            ]);

        }

    }

    

    /**

     * Get user information by user ID

     * @param int $user_id

     * @return array

     */

    private function getUserInfo($user_id) {

        try {

            $stmt = $this->bookingModel->conn->prepare("SELECT * FROM users WHERE user_id = :user_id");

            $stmt->execute([":user_id" => $user_id]);

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error getting user info: " . $e->getMessage());

            return [];

        }

    }

}

?>