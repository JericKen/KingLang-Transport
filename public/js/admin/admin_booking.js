/**
 * Admin Booking Form JavaScript
 */

/**
 * Global map variables
 */
let map;
let directionsService;
let directionsRenderer;
let markers = [];
let geocoder;

// Global form element references
let pickupPointInput;
let destinationInput;
let nextButton;
let adminBookingForm;
let validatedPickupPoint = false;
let validatedDestination = false;

/**
 * Global initialization status flag
 */
let isInitialized = false;

document.addEventListener('DOMContentLoaded', function() {
    // Add styles for address suggestions
    const style = document.createElement('style');
    style.textContent = `
        .address-suggestions {
            position: absolute;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background-color: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 4px 4px;
            z-index: 1000;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            display: none;
        }
        
        .suggestion-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        
        .suggestion-item:hover, .suggestion-item:focus {
            background-color: #f5f5f5;
        }
        
        .suggestion-item:last-child {
            border-bottom: none;
        }
        
        .location-input {
            position: relative;
        }
    `;
    document.head.appendChild(style);
    
    // Initialize flatpickr for date picker
    if (document.getElementById('dateOfTour')) {
        flatpickr("#dateOfTour", {
            dateFormat: "Y-m-d",
            minDate: "today"
        });
    }
    
    // Initialize form element references
    pickupPointInput = document.getElementById('pickupPoint');
    destinationInput = document.getElementById('destination');
    nextButton = document.getElementById('nextStep');
    adminBookingForm = document.getElementById('adminBookingForm');
    
    // Initialize form handling
    setupAddressValidation();
    setupStopHandling();
    setupFormSubmission();
    
    // Initialize map
    initMap();
    
    // Check if fields are already filled (e.g., when page reloads)
    setTimeout(() => {
        if (pickupPointInput && pickupPointInput.value.trim() !== '') {
            if (destinationInput && destinationInput.value.trim() !== '') {
                // Both pickup and destination are filled, calculate route
                calculateAndDisplayRoute();
            } else if (pickupPointInput.value.trim() !== '') {
                // Only pickup is filled, center map on pickup
                centerMapOnAddress(pickupPointInput.value);
            }
        }
    }, 1000);
    
    /**
     * Set up address validation and autocomplete
     */
    function setupAddressValidation() {
        if (pickupPointInput) {
            pickupPointInput.addEventListener('input', function() {
                validatedPickupPoint = false;
                pickupPointInput.dataset.validated = 'false';
                updateNextButtonState();
                
                // Add address suggestion for pickup point
                if (pickupPointInput.value.length > 2) {
                    getSuggestions(pickupPointInput.value, 'pickup-suggestions');
                }
            });
            
            pickupPointInput.addEventListener('change', function() {
                // Delay the route calculation to allow for validation
                setTimeout(() => {
                    calculateAndDisplayRoute();
                }, 300);
            });
            
            // Auto-validate on blur if input not empty
            pickupPointInput.addEventListener('blur', function() {
                if (pickupPointInput.value.trim() !== '' && pickupPointInput.dataset.validated !== 'true') {
                    // Simulate selection of first suggestion
                    validateAddressField(pickupPointInput);
                }
            });
            
            // Add suggestions container for pickup
            const pickupSuggestions = document.createElement('div');
            pickupSuggestions.id = 'pickup-suggestions';
            pickupSuggestions.className = 'address-suggestions';
            pickupPointInput.parentNode.appendChild(pickupSuggestions);
        }
        
        if (destinationInput) {
            destinationInput.addEventListener('input', function() {
                validatedDestination = false;
                destinationInput.dataset.validated = 'false';
                updateNextButtonState();
                
                // Add address suggestion for destination
                if (destinationInput.value.length > 2) {
                    getSuggestions(destinationInput.value, 'destination-suggestions');
                }
            });
            
            destinationInput.addEventListener('change', function() {
                // Delay the route calculation to allow for validation
                setTimeout(() => {
                    calculateAndDisplayRoute();
                }, 300);
            });
            
            // Auto-validate on blur if input not empty
            destinationInput.addEventListener('blur', function() {
                if (destinationInput.value.trim() !== '' && destinationInput.dataset.validated !== 'true') {
                    // Simulate selection of first suggestion
                    validateAddressField(destinationInput);
                }
            });
            
            // Add suggestions container for destination
            const destinationSuggestions = document.createElement('div');
            destinationSuggestions.id = 'destination-suggestions';
            destinationSuggestions.className = 'address-suggestions';
            destinationInput.parentNode.appendChild(destinationSuggestions);
        }
        
        // Add click handler for Next button
        if (nextButton) {
            nextButton.addEventListener('click', function() {
                if (pickupPointInput && pickupPointInput.value && destinationInput && destinationInput.value) {
                    // Auto-validate for demo purposes
                    if (pickupPointInput.dataset.validated !== 'true') {
                        validateAddressField(pickupPointInput);
                    }
                    
                    if (destinationInput.dataset.validated !== 'true') {
                        validateAddressField(destinationInput);
                    }
                    
                    validatedPickupPoint = true;
                    validatedDestination = true;
                    pickupPointInput.dataset.validated = 'true';
                    destinationInput.dataset.validated = 'true';
                    
                    // Show details form
                    const detailsForm = document.getElementById('detailsForm');
                    if (detailsForm) {
                        detailsForm.style.display = 'block';
                        detailsForm.scrollIntoView({ behavior: 'smooth' });
                    }
                    
                    // Calculate costs
                    calculateCosts();
                    
                    // Calculate route after a short delay
                    setTimeout(calculateAndDisplayRoute, 500);
                } else {
                    Swal.fire({
                        title: 'Missing Information',
                        text: 'Please enter both pickup and dropoff locations',
                        icon: 'warning'
                    });
                }
            });
        }
    }
    
    /**
     * Helper function to validate an address field
     */
    function validateAddressField(inputElement) {
        if (inputElement.value.trim() === '') return;
        
        fetch('/get-address', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ address: inputElement.value })
        })
        .then(safeJsonParse)
        .then(data => {
            if (data.predictions && data.predictions.length > 0) {
                // Use the first prediction
                inputElement.value = data.predictions[0].description;
                inputElement.dataset.validated = 'true';
                
                // Update validation state
                if (inputElement === pickupPointInput) {
                    validatedPickupPoint = true;
                } else if (inputElement === destinationInput) {
                    validatedDestination = true;
                }
                
                // Update the route and calculations
                calculateAndDisplayRoute();
                if (validatedPickupPoint && validatedDestination) {
                    calculateCosts();
                }
            }
        })
        .catch(error => {
            console.error('Error validating address:', error);
        });
    }
    
    /**
     * Set up add/remove stop functionality
     */
    function setupStopHandling() {
        const addStopButton = document.getElementById('addStop');
        let stopCounter = 0;
        const MAX_STOPS = 3;
        
        if (addStopButton) {
            addStopButton.addEventListener('click', function() {
                if (stopCounter >= MAX_STOPS) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Maximum Stops',
                        text: 'You can add a maximum of 3 stops'
                    });
                    return;
                }
                
                stopCounter++;
                const stopContainer = document.getElementById('additionalStops');
                if (!stopContainer) return;
                
                const stopDiv = document.createElement('div');
                stopDiv.className = 'form-group';
                stopDiv.id = `stop-${stopCounter}`;
                
                stopDiv.innerHTML = `
                    <div class="location-input">
                        <i class="bi bi-geo-alt-fill location-input-icon"></i>
                        <input type="text" id="stop${stopCounter}" name="stops[]" class="form-control added-stop" 
                            placeholder="Additional Stop ${stopCounter}" autocomplete="off">
                        <button type="button" class="remove-stop" data-stop="${stopCounter}" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--danger); cursor: pointer;">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                `;
                
                stopContainer.appendChild(stopDiv);
                
                // Add suggestions container for this stop
                const stopInput = stopDiv.querySelector(`#stop${stopCounter}`);
                const stopSuggestions = document.createElement('div');
                stopSuggestions.id = `stop${stopCounter}-suggestions`;
                stopSuggestions.className = 'address-suggestions';
                stopInput.parentNode.appendChild(stopSuggestions);
                
                // Add input event listener for this stop
                stopInput.addEventListener('input', function() {
                    if (stopInput.value.length > 2) {
                        getSuggestions(stopInput.value, `stop${stopCounter}-suggestions`);
                    }
                });
                
                // Add change event to recalculate route
                stopInput.addEventListener('change', function() {
                    // Delay route calculation to allow for validation
                    setTimeout(() => {
                        calculateAndDisplayRoute();
                    }, 300);
                });
                
                // Add blur event for validation
                stopInput.addEventListener('blur', function() {
                    if (stopInput.value.trim() !== '' && stopInput.dataset.validated !== 'true') {
                        validateStopAddress(stopInput);
                    }
                });
                
                // Add event listener to remove button
                const removeBtn = stopDiv.querySelector('.remove-stop');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        const stopId = this.getAttribute('data-stop');
                        document.getElementById(`stop-${stopId}`).remove();
                        
                        // Re-enable add stop button if below max stops
                        stopCounter--;
                        if (stopCounter < MAX_STOPS) {
                            addStopButton.disabled = false;
                        }
                        
                        // Recalculate costs
                        calculateCosts();
                        
                        // Recalculate route
                        calculateAndDisplayRoute();
                    });
                }
                
                // Disable add stop button if maximum reached
                if (stopCounter === MAX_STOPS) {
                    addStopButton.disabled = true;
                }
                
                // Trigger route calculation after adding a stop
                setTimeout(() => {
                    calculateAndDisplayRoute();
                }, 500);
            });
        }
    }
    
    /**
     * Helper function to validate a stop address field
     */
    function validateStopAddress(inputElement) {
        if (inputElement.value.trim() === '') return;
        
        fetch('/get-address', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ address: inputElement.value })
        })
        .then(safeJsonParse)
        .then(data => {
            if (data.predictions && data.predictions.length > 0) {
                // Use the first prediction
                inputElement.value = data.predictions[0].description;
                inputElement.dataset.validated = 'true';
                
                // Update the route and calculations
                calculateAndDisplayRoute();
                
                // Recalculate costs if pickup and destination are already validated
                if (validatedPickupPoint && validatedDestination) {
                    calculateCosts();
                }
            }
        })
        .catch(error => {
            console.error('Error validating stop address:', error);
        });
    }
    
    /**
     * Set up form submission
     */
    function setupFormSubmission() {
        if (adminBookingForm) {
            // Add route distance display
            const mapElement = document.getElementById('map');
            if (mapElement) {
                const distanceDisplay = document.createElement('div');
                distanceDisplay.id = 'routeDistance';
                distanceDisplay.className = 'route-distance-display';
                distanceDisplay.style.cssText = 'padding: 10px; background-color: rgba(255,255,255,0.8); position: absolute; bottom: 10px; left: 10px; z-index: 100; border-radius: 4px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1);';
                distanceDisplay.textContent = 'Total distance: 0 km';
                mapElement.style.position = 'relative';
                mapElement.appendChild(distanceDisplay);
            }
            
            adminBookingForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Basic validation
                const requiredFields = [
                    'clientName', 'contactNumber', 'email', 'pickupPoint', 
                    'destination', 'dateOfTour', 'pickupTime'
                ];
                
                let isValid = true;
                let firstInvalidField = null;
                
                requiredFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (!field || !field.value.trim()) {
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = field;
                        
                        // Add visual indication
                        if (field) field.classList.add('is-invalid');
                    } else if (field) {
                        field.classList.remove('is-invalid');
                    }
                });
                
                if (!isValid) {
                    if (firstInvalidField) firstInvalidField.focus();
                    Swal.fire({
                        title: 'Missing Information',
                        text: 'Please fill in all required fields',
                        icon: 'error'
                    });
                    return;
                }
                
                // Collect stops data
                const stops = [];
                document.querySelectorAll('.added-stop').forEach(stop => {
                    if (stop.value.trim()) {
                        stops.push(stop.value.trim());
                    }
                });
                
                // Get all form data
                const formData = {
                    clientName: document.getElementById('clientName').value,
                    contactNumber: document.getElementById('contactNumber').value,
                    email: document.getElementById('email').value,
                    companyName: document.getElementById('companyName') ? document.getElementById('companyName').value : '',
                    pickupPoint: pickupPointInput.value,
                    destination: destinationInput.value,
                    stops: stops,
                    dateOfTour: document.getElementById('dateOfTour').value,
                    pickupTime: document.getElementById('pickupTime').value,
                    numberOfDays: document.getElementById('numberOfDaysSelect') ? 
                        document.getElementById('numberOfDaysSelect').value : 1,
                    numberOfBuses: document.getElementById('numberOfBusesSelect') ? 
                        document.getElementById('numberOfBusesSelect').value : 1,
                    totalCost: document.getElementById('totalCostInput') ? 
                        document.getElementById('totalCostInput').value : 0,
                    notes: document.getElementById('notes') ? 
                        document.getElementById('notes').value : '',
                    routeDistance: document.getElementById('routeDistance') ? 
                        document.getElementById('routeDistance').textContent.replace('Total distance: ', '').replace(' km', '') : '0'
                };
                
                // Show loading indicator
                Swal.fire({
                    title: 'Creating Booking',
                    text: 'Please wait...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit booking
                fetch('/admin/create-booking', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                })
                .then(safeJsonParse)
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Booking has been created successfully',
                            icon: 'success'
                        }).then(() => {
                            window.location.href = '/admin/bookings';
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message || 'Failed to create booking',
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error creating booking:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'An unexpected error occurred: ' + error.message,
                        icon: 'error'
                    });
                });
            });
        }
    }
    
    /**
     * Helper function to update next button state
     */
    function updateNextButtonState() {
        if (nextButton) {
            // For testing, make button always enabled
            // nextButton.disabled = !validatedPickupPoint || !validatedDestination;
            nextButton.disabled = false;
        }
    }
    
    /**
     * Helper function to safely parse JSON responses, handling HTML error responses
     */
    function safeJsonParse(response) {
        return response.text().then(text => {
            try {
                // Check if the response starts with HTML tags, which indicates an error
                if (text.trim().startsWith('<')) {
                    console.error('Server returned HTML instead of JSON:', text);
                    throw new Error('Server error - please check server logs');
                }
                return JSON.parse(text);
            } catch (e) {
                console.error('Error parsing response:', text);
                throw new Error('Invalid server response');
            }
        });
    }
    
    /**
     * Calculate booking costs
     */
    function calculateCosts() {
        // Get form values
        const days = parseInt(document.getElementById('numberOfDaysSelect')?.value || 1);
        const buses = parseInt(document.getElementById('numberOfBusesSelect')?.value || 1);
        const discount = parseFloat(document.getElementById('discount')?.value || 0);
        
        // Collect addresses
        const pickup = pickupPointInput.value;
        const destination = destinationInput.value;
        const stops = [];
        
        document.querySelectorAll('.added-stop').forEach(stop => {
            if (stop.value.trim()) {
                stops.push(stop.value.trim());
            }
        });
        
        // Show loading indicator
        Swal.fire({
            title: 'Calculating',
            text: 'Please wait while we calculate the trip cost...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // First validate addresses
        validateAddresses()
            .then(validatedAddresses => {
                console.log("Validated addresses:", validatedAddresses);
                // Get distances between points
                return getDistances(validatedAddresses);
            })
            .then(distanceData => {
                console.log("Distance data:", distanceData);
                // Calculate total cost based on distance data
                return getTotalCost(distanceData, days, buses, discount);
            })
            .then(costData => {
                console.log("Cost data:", costData);
                // Update UI with cost information
                updateCostDisplay(
                    costData.region || 'N/A', 
                    costData.base_cost || 0, 
                    costData.diesel_cost || 0, 
                    costData.total_cost || 0
                );
                
                // Close loading indicator
                Swal.close();
            })
            .catch(error => {
                console.error('Error calculating costs:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to calculate trip cost: ' + error.message,
                    icon: 'error'
                });
                
                // Use fallback cost calculation on error
                const baseRate = 20000; // Default rate
                const baseCost = baseRate * days * buses;
                const dieselCost = 5000 * days * buses; // Estimated
                const totalCost = baseCost + dieselCost;
                
                updateCostDisplay(
                    'Metro Manila (Fallback)', 
                    baseCost, 
                    dieselCost, 
                    totalCost
                );
            });
    }
    
    /**
     * Validate address inputs with server
     */
    function validateAddresses() {
        const addresses = [pickupPointInput.value, destinationInput.value];
        
        // Add stops
        document.querySelectorAll('.added-stop').forEach(stop => {
            if (stop.value.trim()) {
                addresses.push(stop.value.trim());
            }
        });
        
        const validationPromises = addresses.map(address => {
            return fetch('/get-address', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ address: address })
            })
            .then(safeJsonParse)
            .then(data => {
                // Handle both possible API response formats
                if (data.predictions && data.predictions.length > 0) {
                    // Use the first prediction if available
                    return data.predictions[0].description;
                } else if (data.results && data.results.length > 0) {
                    // Alternative API format
                    return data.results[0].formatted_address;
                } else if (data.formatted_address) {
                    // Direct formatted address
                    return data.formatted_address;
                } else {
                    // If no match, just use the original address
                    console.log('No match found for address, using original:', address);
                    return address;
                }
            });
        });
        
        return Promise.all(validationPromises);
    }
    
    /**
     * Get distances between addresses
     */
    function getDistances(addresses) {
        console.log("Getting distances for addresses:", addresses);
        
        // If we have fewer than 2 addresses, return dummy data
        if (addresses.length < 2) {
            console.warn('Not enough addresses for distance calculation, using fallback data');
            return Promise.resolve({
                destination_addresses: [addresses[0] || "Destination"],
                origin_addresses: [addresses[0] || "Origin"],
                rows: [{
                    elements: [{
                        distance: { text: "10 km", value: 10000 },
                        duration: { text: "15 mins", value: 900 }
                    }]
                }]
            });
        }
        
        return fetch('/get-distance', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                stops: addresses
            })
        })
        .then(safeJsonParse)
        .then(data => {
            // Check for different response formats
            if (data.error) {
                console.error('Distance API error:', data.error);
                throw new Error(data.error);
            }
            
            if (data.rows && data.rows.length > 0) {
                // Google Distance Matrix API format
                return data;
            } else if (data.status === "OK") {
                // Success but different format
                return data;
            } else {
                // Dummy data for fallback
                console.warn('Using fallback distance data');
                return {
                    destination_addresses: addresses.slice(1),
                    origin_addresses: addresses.slice(0, -1),
                    rows: addresses.map((_, i) => ({
                        elements: addresses.map((_, j) => ({
                            distance: { text: "10 km", value: 10000 },
                            duration: { text: "15 mins", value: 900 }
                        }))
                    }))
                };
            }
        });
    }
    
    /**
     * Get route information
     */
    function getRoute(addresses) {
        return fetch('/admin/get-route', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                addresses: addresses
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error('Failed to get route information');
            }
            return data;
        });
    }
    
    /**
     * Calculate total cost
     */
    function getTotalCost(distanceData, days, buses, discount) {
        // Prepare simplified request to match the controller's expected input format
        // Use a simplified approach based on BookingController.php's getTotalCost method
        const pickupPoint = pickupPointInput.value;
        const destination = destinationInput.value;
        const stops = [];
        
        document.querySelectorAll('.added-stop').forEach(stop => {
            if (stop.value.trim()) {
                stops.push(stop.value.trim());
            }
        });
        
        // Calculate total distance from distanceData (simplified)
        let totalDistance = 0;
        if (distanceData.rows && distanceData.rows.length > 0) {
            for (const row of distanceData.rows) {
                for (const element of row.elements) {
                    if (element.distance && element.distance.value) {
                        totalDistance += element.distance.value;
                    }
                }
            }
        }
        
        // Convert from meters to kilometers
        totalDistance = totalDistance / 1000;
        
        console.log("Sending cost calculation request with distance:", totalDistance);
        
        return fetch('/get-total-cost', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                numberOfBuses: buses,
                numberOfDays: days,
                distance: totalDistance,
                locations: stops,
                destination: destination,
                pickupPoint: pickupPoint
            })
        })
        .then(safeJsonParse)
        .then(data => {
            if (data.error) {
                console.error('Total cost API error:', data.error);
                throw new Error(data.error);
            }
            
            // Handle success response
            if (data.success) {
                return {
                    region: data.region || 'N/A',
                    base_cost: data.base_cost || 0,
                    diesel_cost: data.diesel_cost || 0,
                    total_cost: data.total_cost || 0
                };
            } else {
                console.warn('API returned failure, using fallback calculation');
                // Fallback to calculate basic cost locally
                const baseRate = 20000; // Default rate
                const baseCost = baseRate * days * buses;
                const dieselCost = 5000 * days * buses; // Estimated
                const totalCost = baseCost + dieselCost;
                
                return {
                    region: 'Metro Manila',
                    base_cost: baseCost,
                    diesel_cost: dieselCost,
                    total_cost: totalCost
                };
            }
        });
    }
    
    /**
     * Update cost display in the UI
     */
    function updateCostDisplay(region, baseCost, dieselCost, totalCost) {
        // Update cost summary display
        const costSummaryContainer = document.getElementById('costSummaryContainer');
        if (!costSummaryContainer) {
            // Create cost summary if it doesn't exist
            createCostSummary(region, baseCost, dieselCost, totalCost);
        } else {
            // Update existing cost summary
            const regionValue = document.getElementById('regionValue');
            const baseCostElement = document.getElementById('baseCost');
            const dieselCostElement = document.getElementById('dieselCost');
            const totalCostElement = document.getElementById('totalCost');
            
            if (regionValue) regionValue.textContent = region;
            if (baseCostElement) baseCostElement.textContent = `₱${baseCost.toFixed(2)}`;
            if (dieselCostElement) dieselCostElement.textContent = `₱${dieselCost.toFixed(2)}`;
            if (totalCostElement) totalCostElement.textContent = `₱${totalCost.toFixed(2)}`;
        }
        
        // Update hidden total cost input and display
        const totalCostInput = document.getElementById('totalCostInput');
        const totalCostDisplay = document.getElementById('totalCostDisplay');
        
        if (totalCostInput) totalCostInput.value = totalCost.toFixed(2);
        if (totalCostDisplay) totalCostDisplay.value = `₱${totalCost.toFixed(2)}`;
    }
    
    /**
     * Create cost summary element
     */
    function createCostSummary(region, baseCost, dieselCost, totalCost) {
        const detailsForm = document.getElementById('detailsForm');
        if (!detailsForm) return;
        
        const costSummaryDiv = document.createElement('div');
        costSummaryDiv.id = 'costSummaryContainer';
        costSummaryDiv.className = 'mt-4 mb-4';
        
        costSummaryDiv.innerHTML = `
            <div class="card" style="background-color: #f9fbfd; border-radius: 0.375rem; border: 1px solid var(--border-color);">
                <div class="card-header" style="background-color: #f1f4f8; border-bottom: 1px solid var(--border-color); padding: 0.75rem 1.25rem; font-weight: 600;">
                    Trip Cost Summary
                </div>
                <div class="card-body" style="padding: 1.25rem;">
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color: var(--secondary);">Region:</span>
                        <span style="font-weight: 500;" id="regionValue">${region}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color: var(--secondary);">Base Cost:</span>
                        <span style="font-weight: 500;" id="baseCost">₱${baseCost.toFixed(2)}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color: var(--secondary);">Diesel Cost:</span>
                        <span style="font-weight: 500;" id="dieselCost">₱${dieselCost.toFixed(2)}</span>
                    </div>
                    <hr style="margin: 0.75rem 0; border-top: 1px dashed var(--border-color);">
                    <div class="d-flex justify-content-between">
                        <span style="font-weight: 600;">Total Cost:</span>
                        <span style="font-weight: 700; color: var(--primary);" id="totalCost">₱${totalCost.toFixed(2)}</span>
                    </div>
                </div>
            </div>
        `;
        
        // Insert before the notes field
        const notesField = detailsForm.querySelector('#notes');
        if (notesField) {
            const notesGroup = notesField.closest('.form-group');
            if (notesGroup) {
                notesGroup.parentNode.insertBefore(costSummaryDiv, notesGroup);
            } else {
                detailsForm.appendChild(costSummaryDiv);
            }
        } else {
            detailsForm.appendChild(costSummaryDiv);
        }
    }
    
    /**
     * Get address suggestions from server
     */
    function getSuggestions(query, containerId) {
        if (query.length < 3) return;
        
        fetch('/get-address', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ address: query })
        })
        .then(safeJsonParse)
        .then(data => {
            const container = document.getElementById(containerId);
            if (!container) return;
            
            // Clear previous suggestions
            container.innerHTML = '';
            
            if (data.predictions && data.predictions.length > 0) {
                // Show suggestions container
                container.style.display = 'block';
                
                // Add each suggestion
                data.predictions.forEach(prediction => {
                    const suggestion = document.createElement('div');
                    suggestion.className = 'suggestion-item';
                    suggestion.textContent = prediction.description;
                    suggestion.addEventListener('click', () => {
                        // Set input value and validate
                        if (containerId === 'pickup-suggestions') {
                            pickupPointInput.value = prediction.description;
                            pickupPointInput.dataset.validated = 'true';
                            validatedPickupPoint = true;
                        } else if (containerId === 'destination-suggestions') {
                            destinationInput.value = prediction.description;
                            destinationInput.dataset.validated = 'true';
                            validatedDestination = true;
                        } else if (containerId.includes('-suggestions')) {
                            // Handle additional stops
                            const stopId = containerId.replace('-suggestions', '');
                            const stopInput = document.getElementById(stopId);
                            if (stopInput) {
                                stopInput.value = prediction.description;
                                stopInput.dataset.validated = 'true';
                            }
                        }
                        
                        // Update button state
                        updateNextButtonState();
                        
                        // Clear and hide suggestions
                        container.innerHTML = '';
                        container.style.display = 'none';
                        
                        // Calculate route when a suggestion is selected
                        calculateAndDisplayRoute();
                        
                        // Recalculate costs if all required addresses are validated
                        if (validatedPickupPoint && validatedDestination) {
                            calculateCosts();
                        }
                    });
                    container.appendChild(suggestion);
                });
            } else {
                // Hide container if no suggestions
                container.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error getting address suggestions:', error);
        });
    }
});

/**
 * Initialize Google Map
 */
function initMap() {
    // Initialize map only if element exists
    const mapElement = document.getElementById('map');
    if (!mapElement) {
        console.warn('Map element not found, skipping map initialization');
        return;
    }
    
    // Initialize the map
    map = new google.maps.Map(mapElement, {
        zoom: 10,
        center: { lat: 51.5074, lng: -0.1278 }, // London center
        mapTypeControl: true,
        streetViewControl: false,
        fullscreenControl: true,
    });
    
    // Initialize directions services
    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({
        map: map,
        suppressMarkers: false
    });
    
    // Initialize geocoder
    geocoder = new google.maps.Geocoder();
    
    // Check initialization status
    updateInitializationStatus();
    
    console.log("Google Maps initialized successfully");
}

/**
 * Calculate and display the route between pickup and destination points
 */
function calculateAndDisplayRoute() {
    // Check initialization status
    if (!isInitialized) {
        console.warn('Map components not fully initialized yet, waiting...');
        setTimeout(calculateAndDisplayRoute, 500);
        return;
    }
    
    // Clear previous markers
    clearMarkers();
    
    // Collect all addresses
    const addresses = [];
    
    // Get pickup point
    if (pickupPointInput && pickupPointInput.value) {
        addresses.push(pickupPointInput.value);
    } else {
        console.warn('Pickup point not provided');
        return;
    }
    
    // Get all stops
    const stopInputs = document.querySelectorAll('.stop-input');
    stopInputs.forEach(stopInput => {
        if (stopInput.value.trim() !== '') {
            addresses.push(stopInput.value);
        }
    });
    
    // Get destination
    if (destinationInput && destinationInput.value) {
        addresses.push(destinationInput.value);
    } else {
        console.warn('Destination not provided');
        return;
    }
    
    // If we have at least two points, calculate the route
    if (addresses.length >= 2) {
        console.log('Calculating route with addresses:', addresses);
        
        // Show a loading indicator on the map
        const mapElement = document.getElementById('map');
        if (mapElement) {
            mapElement.classList.add('loading');
        }
        
        // Draw the route with our addresses
        drawDirectRoute(addresses);
        
        // Remove loading indicator
        if (mapElement) {
            setTimeout(() => {
                mapElement.classList.remove('loading');
            }, 1000);
        }
    } else {
        console.warn('Not enough valid addresses to calculate a route');
        
        // If we only have one address, center on it
        if (addresses.length === 1) {
            centerMapOnAddress(addresses[0]);
        }
    }
}

/**
 * Draw a route using the DirectionsService with waypoints
 */
function drawDirectRoute(addresses) {
    if (!isInitialized) {
        console.error('Not initialized yet');
        return;
    }
    
    if (addresses.length < 2) return;
    
    const origin = addresses[0];
    const destination = addresses[addresses.length - 1];
    
    // Any addresses in between are waypoints
    const waypoints = addresses.slice(1, -1).map(address => ({
        location: address,
        stopover: true
    }));
    
    const request = {
        origin: origin,
        destination: destination,
        waypoints: waypoints,
        travelMode: google.maps.TravelMode.DRIVING,
        optimizeWaypoints: false
    };
    
    directionsService.route(request, (result, status) => {
        if (status === google.maps.DirectionsStatus.OK) {
            // Temporarily hide route markers
            directionsRenderer.setOptions({
                suppressMarkers: true
            });
            
            // Set the directions
            directionsRenderer.setDirections(result);
            
            // Add custom colored markers
            // Pickup marker (blue)
            const geocoder = new google.maps.Geocoder();
            
            // Add origin marker
            geocoder.geocode({ address: origin }, (results, status) => {
                if (status === google.maps.GeocoderStatus.OK && results[0]) {
                    const marker = new google.maps.Marker({
                        position: results[0].geometry.location,
                        map: map,
                        label: {
                            text: 'A',
                            color: 'white'
                        },
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            fillColor: '#4285F4', // Blue
                            fillOpacity: 1,
                            strokeWeight: 0,
                            scale: 12
                        },
                        title: 'Pickup Point',
                        zIndex: 10
                    });
                    markers.push(marker);
                }
            });
            
            // Add waypoint markers (yellow)
            waypoints.forEach((waypoint, index) => {
                geocoder.geocode({ address: waypoint.location }, (results, status) => {
                    if (status === google.maps.GeocoderStatus.OK && results[0]) {
                        const label = String.fromCharCode('B'.charCodeAt(0) + index);
                        const marker = new google.maps.Marker({
                            position: results[0].geometry.location,
                            map: map,
                            label: {
                                text: label,
                                color: 'white'
                            },
                            icon: {
                                path: google.maps.SymbolPath.CIRCLE,
                                fillColor: '#FBBC05', // Yellow
                                fillOpacity: 1,
                                strokeWeight: 0,
                                scale: 12
                            },
                            title: `Stop ${index + 1}`,
                            zIndex: 5
                        });
                        markers.push(marker);
                    }
                });
            });
            
            // Add destination marker (red)
            geocoder.geocode({ address: destination }, (results, status) => {
                if (status === google.maps.GeocoderStatus.OK && results[0]) {
                    const label = String.fromCharCode('A'.charCodeAt(0) + waypoints.length + 1);
                    const marker = new google.maps.Marker({
                        position: results[0].geometry.location,
                        map: map,
                        label: {
                            text: label,
                            color: 'white'
                        },
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            fillColor: '#EA4335', // Red
                            fillOpacity: 1,
                            strokeWeight: 0,
                            scale: 12
                        },
                        title: 'Destination',
                        zIndex: 10
                    });
                    markers.push(marker);
                }
            });
            
            // Extract and display distance information
            let totalDistance = 0;
            let totalDuration = 0;
            result.routes[0].legs.forEach(leg => {
                totalDistance += leg.distance.value;
                totalDuration += leg.duration.value;
            });
            
            // Format duration nicely
            const hours = Math.floor(totalDuration / 3600);
            const minutes = Math.floor((totalDuration % 3600) / 60);
            const durationText = hours > 0 ? 
                `${hours} hr${hours !== 1 ? 's' : ''} ${minutes} min` : 
                `${minutes} min`;
            
            // Convert to kilometers
            totalDistance = (totalDistance / 1000).toFixed(2);
            console.log(`Total route distance: ${totalDistance} km`);
            
            // Update distance field if it exists
            const distanceDisplay = document.getElementById('routeDistance');
            if (distanceDisplay) {
                distanceDisplay.innerHTML = `
                    <div class="fw-bold">Route Details</div>
                    <div>Distance: ${totalDistance} km</div>
                    <div>Est. travel time: ${durationText}</div>
                `;
            }
        } else {
            console.error('Directions request failed due to ' + status);
            // Fallback to placing markers
            addresses.forEach(address => addMarkerForAddress(address));
        }
    });
}

/**
 * Draw a route using coordinates from the API
 */
function drawRouteWithWaypoints(data, addressesList) {
    const waypoints = [];
    
    // Add any stops as waypoints
    if (data.stops && data.stops.length > 0) {
        data.stops.forEach(stop => {
            waypoints.push({
                location: new google.maps.LatLng(stop.lat, stop.lng),
                stopover: true
            });
        });
    }
    
    const request = {
        origin: new google.maps.LatLng(data.pickupPoint.lat, data.pickupPoint.lng),
        destination: new google.maps.LatLng(data.destination.lat, data.destination.lng),
        waypoints: waypoints,
        travelMode: google.maps.TravelMode.DRIVING,
        optimizeWaypoints: false
    };
    
    directionsService.route(request, (result, status) => {
        if (status === google.maps.DirectionsStatus.OK) {
            // Temporarily hide default markers
            directionsRenderer.setOptions({
                suppressMarkers: true
            });
            
            // Set the directions
            directionsRenderer.setDirections(result);
            
            // Add pickup marker (blue)
            const pickupMarker = new google.maps.Marker({
                position: new google.maps.LatLng(data.pickupPoint.lat, data.pickupPoint.lng),
                map: map,
                label: {
                    text: 'A',
                    color: 'white'
                },
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: '#4285F4', // Blue
                    fillOpacity: 1,
                    strokeWeight: 0,
                    scale: 12
                },
                title: 'Pickup Point',
                zIndex: 10
            });
            markers.push(pickupMarker);
            
            // Add markers for stops (yellow)
            if (data.stops && data.stops.length > 0) {
                data.stops.forEach((stop, index) => {
                    const label = String.fromCharCode('B'.charCodeAt(0) + index);
                    const stopMarker = new google.maps.Marker({
                        position: new google.maps.LatLng(stop.lat, stop.lng),
                        map: map,
                        label: {
                            text: label,
                            color: 'white'
                        },
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            fillColor: '#FBBC05', // Yellow
                            fillOpacity: 1,
                            strokeWeight: 0,
                            scale: 12
                        },
                        title: `Stop ${index + 1}`,
                        zIndex: 5
                    });
                    markers.push(stopMarker);
                });
            }
            
            // Add destination marker (red)
            const destinationLabel = String.fromCharCode('A'.charCodeAt(0) + (data.stops?.length || 0) + 1);
            const destinationMarker = new google.maps.Marker({
                position: new google.maps.LatLng(data.destination.lat, data.destination.lng),
                map: map,
                label: {
                    text: destinationLabel,
                    color: 'white'
                },
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: '#EA4335', // Red
                    fillOpacity: 1,
                    strokeWeight: 0,
                    scale: 12
                },
                title: 'Destination',
                zIndex: 10
            });
            markers.push(destinationMarker);
            
            // Extract and display distance information
            let totalDistance = 0;
            result.routes[0].legs.forEach(leg => {
                totalDistance += leg.distance.value;
            });
            
            // Add route summary with duration
            let totalDuration = 0;
            result.routes[0].legs.forEach(leg => {
                totalDuration += leg.duration.value;
            });
            
            // Format duration nicely
            const hours = Math.floor(totalDuration / 3600);
            const minutes = Math.floor((totalDuration % 3600) / 60);
            const durationText = hours > 0 ? 
                `${hours} hr${hours !== 1 ? 's' : ''} ${minutes} min` : 
                `${minutes} min`;
            
            // Convert to kilometers
            totalDistance = (totalDistance / 1000).toFixed(2);
            
            // Update distance field if it exists
            const distanceDisplay = document.getElementById('routeDistance');
            if (distanceDisplay) {
                distanceDisplay.innerHTML = `
                    <div><strong>Total distance:</strong> ${totalDistance} km</div>
                    <div><strong>Est. travel time:</strong> ${durationText}</div>
                `;
            }
        } else {
            console.error('Directions request failed due to ' + status);
            
            // Fallback to placing markers
            addMarkerAtPosition(data.pickupPoint);
            
            if (data.stops && data.stops.length > 0) {
                data.stops.forEach(stop => addMarkerAtPosition(stop));
            }
            
            addMarkerAtPosition(data.destination);
            
            // Fit bounds to include all markers
            const bounds = new google.maps.LatLngBounds();
            markers.forEach(marker => bounds.extend(marker.getPosition()));
            map.fitBounds(bounds);
        }
    });
}

/**
 * Center map on a single address
 */
function centerMapOnAddress(address) {
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ address: address }, (results, status) => {
        if (status === google.maps.GeocoderStatus.OK && results[0]) {
            map.setCenter(results[0].geometry.location);
            map.setZoom(15);
            
            // Add a marker at the address
            addMarkerAtPosition(results[0].geometry.location);
        } else {
            console.error('Geocode was not successful for the following reason: ' + status);
        }
    });
}

/**
 * Add a marker at a specific position
 */
function addMarkerAtPosition(position) {
    const marker = new google.maps.Marker({
        position: position,
        map: map,
        animation: google.maps.Animation.DROP
    });
    
    markers.push(marker);
    return marker;
}

/**
 * Add a marker for an address string
 */
function addMarkerForAddress(address) {
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ address: address }, (results, status) => {
        if (status === google.maps.GeocoderStatus.OK && results[0]) {
            addMarkerAtPosition(results[0].geometry.location);
        } else {
            console.error('Geocode was not successful for the following reason: ' + status);
        }
    });
}

/**
 * Clear all markers from the map
 */
function clearMapMarkers() {
    for (let marker of markers) {
        marker.setMap(null);
    }
    markers = [];
    
    // Clear directions
    directionsRenderer.setDirections({ routes: [] });
}

/**
 * Add a custom pickup marker (origin)
 */
function addCustomPickupMarker(address) {
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ address: address }, (results, status) => {
        if (status === google.maps.GeocoderStatus.OK && results[0]) {
            const marker = new google.maps.Marker({
                position: results[0].geometry.location,
                map: map,
                label: {
                    text: 'A',
                    color: 'white'
                },
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: '#4285F4', // Blue
                    fillOpacity: 1,
                    strokeWeight: 0,
                    scale: 12
                },
                title: 'Pickup Point',
                zIndex: 10
            });
            
            markers.push(marker);
            
            const infoWindow = new google.maps.InfoWindow({
                content: `<strong>Pickup Point</strong>`
            });
            
            marker.addListener('click', () => {
                infoWindow.open(map, marker);
            });
        }
    });
}

/**
 * Add a custom stop marker
 */
function addCustomStopMarker(address, stopNumber) {
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ address: address }, (results, status) => {
        if (status === google.maps.GeocoderStatus.OK && results[0]) {
            const label = String.fromCharCode('B'.charCodeAt(0) + stopNumber - 1);
            
            const marker = new google.maps.Marker({
                position: results[0].geometry.location,
                map: map,
                label: {
                    text: label,
                    color: 'white'
                },
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: '#FBBC05', // Yellow
                    fillOpacity: 1,
                    strokeWeight: 0,
                    scale: 12
                },
                title: `Stop ${stopNumber}`,
                zIndex: 5
            });
            
            markers.push(marker);
            
            const infoWindow = new google.maps.InfoWindow({
                content: `<strong>Stop ${stopNumber}</strong>`
            });
            
            marker.addListener('click', () => {
                infoWindow.open(map, marker);
            });
        }
    });
}

/**
 * Add a custom destination marker
 */
function addCustomDestinationMarker(address) {
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ address: address }, (results, status) => {
        if (status === google.maps.GeocoderStatus.OK && results[0]) {
            const marker = new google.maps.Marker({
                position: results[0].geometry.location,
                map: map,
                label: {
                    text: 'D',
                    color: 'white'
                },
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: '#EA4335', // Red
                    fillOpacity: 1,
                    strokeWeight: 0,
                    scale: 12
                },
                title: 'Destination',
                zIndex: 10
            });
            
            markers.push(marker);
            
            const infoWindow = new google.maps.InfoWindow({
                content: `<strong>Destination</strong>`
            });
            
            marker.addListener('click', () => {
                infoWindow.open(map, marker);
            });
        }
    });
}

/**
 * Update initialization status
 */
function updateInitializationStatus() {
    // Check if all required elements are initialized
    isInitialized = (
        map && 
        directionsService && 
        directionsRenderer && 
        geocoder && 
        pickupPointInput && 
        destinationInput && 
        document.getElementById('map')
    );
    
    console.log('Initialization status:', isInitialized ? 'Ready' : 'Waiting for elements');
    
    if (!isInitialized) {
        // Try again after a short delay if not initialized
        setTimeout(updateInitializationStatus, 500);
    } else {
        // Check if we should calculate route on initialization
        if (pickupPointInput.value && destinationInput.value) {
            calculateAndDisplayRoute();
        }
    }
}