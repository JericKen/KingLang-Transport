let isRebooking = false;



const bookingId = sessionStorage.getItem("bookingId") || 0;

sessionStorage.removeItem("bookingId");



if (bookingId > 0) isRebooking = !isRebooking;



document.addEventListener("DOMContentLoaded", async function () {

    const picker = flatpickr("#date_of_tour", {

        dateFormat: "Y-m-d",

        altInput: true,

        altFormat: "D, M j", 

        minDate: new Date(Date.now() + 3 * 24 * 60 * 60 * 1000),

        maxDate: new Date(new Date().setMonth(new Date().getMonth() + 1)),

        onChange: function(selectedDates, dateStr) {

            // When user selects a date, check bus availability

            checkBusAvailabilityForDate(dateStr);

        }

    });



    // Add a check buses availability button - in a separate container

    const busAvailabilityButtonContainer = document.createElement('div');

    busAvailabilityButtonContainer.className = 'bus-availability-button-container mt-3 mb-3';

    

    const viewBusesBtn = document.createElement('button');

    viewBusesBtn.type = 'button';

    viewBusesBtn.className = 'btn btn-outline-success btn-sm w-100';

    viewBusesBtn.textContent = 'View Available Buses';

    viewBusesBtn.id = 'viewAvailableBuses';

    viewBusesBtn.style.position = 'relative';

    viewBusesBtn.style.zIndex = '1050';

    

    busAvailabilityButtonContainer.appendChild(viewBusesBtn);

    

    // Create a container for the bus availability calendar

    const availabilityContainer = document.createElement('div');

    availabilityContainer.id = 'busAvailabilityContainer';

    availabilityContainer.className = 'bus-availability-container mt-2 d-none';

    busAvailabilityButtonContainer.appendChild(availabilityContainer);

    

    // Insert the button after the time picker

    const timePickerContainer = document.getElementById('pickup_time').parentNode;

    timePickerContainer.parentNode.insertBefore(busAvailabilityButtonContainer, timePickerContainer.nextSibling);

    

    // Add event listener to the button

    viewBusesBtn.addEventListener('click', async function() {

        // Toggle the visibility of the availability calendar

        if (availabilityContainer.classList.contains('d-none')) {

            await loadBusAvailability();

            availabilityContainer.classList.remove('d-none');

            viewBusesBtn.textContent = 'Hide Available Buses';

            

            // Add click event listener to close calendar when clicking outside

            setTimeout(() => {

                document.addEventListener('click', closeCalendarOnClickOutside);

            }, 100);

        } else {

            availabilityContainer.classList.add('d-none');

            viewBusesBtn.textContent = 'View Available Buses';

            // Remove the click outside listener

            document.removeEventListener('click', closeCalendarOnClickOutside);

        }

    });

    

    // Function to close calendar when clicking outside

    function closeCalendarOnClickOutside(event) {

        if (!availabilityContainer.classList.contains('d-none') && 

            !availabilityContainer.contains(event.target) && 

            event.target !== viewBusesBtn) {

            availabilityContainer.classList.add('d-none');

            viewBusesBtn.textContent = 'View Available Buses';

            document.removeEventListener('click', closeCalendarOnClickOutside);

        }

    }

    

    async function loadBusAvailability() {

        try {

            // Show loading indicator

            availabilityContainer.innerHTML = '<div class="text-center"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div></div>';

            

            // Set date range (current month)

            const today = new Date();

            const startDate = new Date(today.getFullYear(), today.getMonth(), 1);

            const endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);

            

            // Format dates for API using our safe formatter

            const startFormatted = formatDateYYYYMMDD(startDate);

            const endFormatted = formatDateYYYYMMDD(endDate);

            

            // Check if we already have cached data

            const cacheKey = getBusAvailabilityCacheKey(startFormatted, endFormatted);

            const driverCacheKey = getDriverAvailabilityCacheKey(startFormatted, endFormatted);

            

            let busData = null;

            let driverData = null;

            

            // Check for cached bus data

            if (busAvailabilityCache.has(cacheKey)) {

                console.log("Using cached bus data for calendar view");

                busData = {

                    success: true,

                    availability: busAvailabilityCache.get(cacheKey)

                };

            }

            

            // Check for cached driver data

            if (driverAvailabilityCache.has(driverCacheKey)) {

                console.log("Using cached driver data for calendar view");

                driverData = {

                    success: true,

                    availability: driverAvailabilityCache.get(driverCacheKey)

                };

            }

            

            // If we have both cached data, render the calendar

            if (busData && driverData) {

                renderAvailabilityCalendar(busData.availability, driverData.availability, startDate);

                return;

            }

            

            // Otherwise fetch data from server

            const [busResponse, driverResponse] = await Promise.all([

                fetch('/get-bus-availability', {

                    method: 'POST',

                    headers: { 'Content-Type': 'application/json' },

                    body: JSON.stringify({ 

                        start_date: startFormatted,

                        end_date: endFormatted

                    })

                }),

                fetch('/get-driver-availability', {

                    method: 'POST',

                    headers: { 'Content-Type': 'application/json' },

                    body: JSON.stringify({ 

                        start_date: startFormatted,

                        end_date: endFormatted

                    })

                })

            ]);

            

            busData = await busResponse.json();

            driverData = await driverResponse.json();

            

            if (busData.success && driverData.success) {

                // Cache the data

                busAvailabilityCache.set(cacheKey, busData.availability);

                driverAvailabilityCache.set(driverCacheKey, driverData.availability);

                

                // Set cache expiration (10 minutes)

                setTimeout(() => {

                    busAvailabilityCache.delete(cacheKey);

                    driverAvailabilityCache.delete(driverCacheKey);

                }, 10 * 60 * 1000);

                

                renderAvailabilityCalendar(busData.availability, driverData.availability, startDate);

            } else {

                availabilityContainer.innerHTML = `<div class="alert alert-danger">Error: ${busData.message || driverData.message || 'Could not load availability'}</div>`;

            }

        } catch (error) {

            availabilityContainer.innerHTML = '<div class="alert alert-danger">Error: Could not load availability</div>';

            console.error('Error loading availability:', error);

        }

    }

    

    function renderAvailabilityCalendar(busAvailability, driverAvailability, startDate) {

        // Clear container

        availabilityContainer.innerHTML = '';

        

        // Create calendar container

        const calendarContainer = document.createElement('div');

        calendarContainer.className = 'bus-calendar';

        

        // Create header with month/year

        const calendarHeader = document.createElement('div');

        calendarHeader.className = 'calendar-header text-center mb-2';

        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        calendarHeader.textContent = `${monthNames[startDate.getMonth()]} ${startDate.getFullYear()}`;

        

        // Create days header (Sun-Sat)

        const daysHeader = document.createElement('div');

        daysHeader.className = 'calendar-days-header d-flex';

        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        

        dayNames.forEach(day => {

            const dayElement = document.createElement('div');

            dayElement.className = 'calendar-day-name';

            dayElement.textContent = day;

            daysHeader.appendChild(dayElement);

        });

        

        // Create dates grid

        const datesGrid = document.createElement('div');

        datesGrid.className = 'calendar-dates';

        

        // Get first day of month offset

        const firstDay = new Date(startDate.getFullYear(), startDate.getMonth(), 1).getDay();

        

        // Add empty cells for days before month start

        for (let i = 0; i < firstDay; i++) {

            const emptyCell = document.createElement('div');

            emptyCell.className = 'calendar-date empty';

            datesGrid.appendChild(emptyCell);

        }

        

        // Convert availability arrays to maps for easier lookup

        const busAvailabilityMap = {};

        busAvailability.forEach(item => {

            busAvailabilityMap[item.date] = item;

        });

        

        const driverAvailabilityMap = {};

        driverAvailability.forEach(item => {

            driverAvailabilityMap[item.date] = item;

        });

        

        // Add cells for each day of the month

        const lastDay = new Date(startDate.getFullYear(), startDate.getMonth() + 1, 0).getDate();

        

        // Get today's date for comparison
        const today = new Date();
        const todayStr = formatDateYYYYMMDD(today);

        for (let day = 1; day <= lastDay; day++) {

            const date = new Date(startDate.getFullYear(), startDate.getMonth(), day);

            // Fix for timezone issues - use YYYY-MM-DD format directly instead of ISO string

            const dateStr = formatDateYYYYMMDD(date);

            // Skip past dates - only show today and future dates
            if (dateStr < todayStr) {
                const pastDateCell = document.createElement('div');
                pastDateCell.className = 'calendar-date past-date';
                pastDateCell.style.opacity = '0.3';
                pastDateCell.style.cursor = 'not-allowed';
                
                const pastDateNumber = document.createElement('div');
                pastDateNumber.className = 'date-number';
                pastDateNumber.textContent = day;
                pastDateCell.appendChild(pastDateNumber);
                
                datesGrid.appendChild(pastDateCell);
                continue;
            }

            const dateCell = document.createElement('div');

            dateCell.className = 'calendar-date';

            

            // Add date number

            const dateNumber = document.createElement('div');

            dateNumber.className = 'date-number';

            dateNumber.textContent = day;

            dateCell.appendChild(dateNumber);

            

            // Add availability info if available

            if (busAvailabilityMap[dateStr] && driverAvailabilityMap[dateStr]) {

                const busAvailableCount = busAvailabilityMap[dateStr].available;

                const busTotalCount = busAvailabilityMap[dateStr].total;

                const driverAvailableCount = driverAvailabilityMap[dateStr].available;

                const driverTotalCount = driverAvailabilityMap[dateStr].total;

                

                // Add bus availability indicator

                const busIndicator = document.createElement('div');

                busIndicator.className = 'availability-indicator';

                

                // Set color based on availability percentage

                const busAvailabilityPercent = (busAvailableCount / busTotalCount) * 100;

                

                if (busAvailabilityPercent === 0) {

                    busIndicator.classList.add('no-availability');

                } else if (busAvailabilityPercent < 30) {

                    busIndicator.classList.add('low-availability');

                } else if (busAvailabilityPercent < 70) {

                    busIndicator.classList.add('medium-availability');

                } else {

                    busIndicator.classList.add('high-availability');

                }

                

                // Add bus availability text

                busIndicator.innerHTML = `<span title="Buses">🚌 ${busAvailableCount}/${busTotalCount}</span>`;

                dateCell.appendChild(busIndicator);

                

                // Add driver availability indicator

                const driverIndicator = document.createElement('div');

                driverIndicator.className = 'availability-indicator';

                

                // Set color based on availability percentage

                const driverAvailabilityPercent = (driverAvailableCount / driverTotalCount) * 100;

                

                if (driverAvailabilityPercent === 0) {

                    driverIndicator.classList.add('no-availability');

                } else if (driverAvailabilityPercent < 30) {

                    driverIndicator.classList.add('low-availability');

                } else if (driverAvailabilityPercent < 70) {

                    driverIndicator.classList.add('medium-availability');

                } else {

                    driverIndicator.classList.add('high-availability');

                }

                

                // Add driver availability text

                driverIndicator.innerHTML = `<span title="Drivers">👨‍✈️ ${driverAvailableCount}/${driverTotalCount}</span>`;

                dateCell.appendChild(driverIndicator);

                

                // Calculate the minimum available between buses and drivers

                const minAvailable = Math.min(busAvailableCount, driverAvailableCount);

                

                // Make the cell clickable to set the date_of_tour value if any are available

                if (minAvailable > 0) {

                    dateCell.style.cursor = 'pointer';

                    dateCell.addEventListener('click', function() {

                        // Set the date in the date picker

                        picker.setDate(dateStr);

                        

                        // Hide the calendar

                        availabilityContainer.classList.add('d-none');

                        viewBusesBtn.textContent = 'View Available Buses';

                    });

                }

            } else {

                // Date not in range or no data

                dateCell.classList.add('unavailable');

            }

            

            datesGrid.appendChild(dateCell);

        }

        

        // Add legend

        const legend = document.createElement('div');

        legend.className = 'availability-legend mt-2';

        legend.innerHTML = `

            <div class="legend-title">Availability Legend</div>

            <div class="d-flex justify-content-between">

                <div class="legend-item">

                    <span class="legend-color high-availability"></span>

                    <span>High</span>

                </div>

                <div class="legend-item">

                    <span class="legend-color medium-availability"></span>

                    <span>Medium</span>

                </div>

                <div class="legend-item">

                    <span class="legend-color low-availability"></span>

                    <span>Low</span>

                </div>

                <div class="legend-item">

                    <span class="legend-color no-availability"></span>

                    <span>None</span>

                </div>

            </div>

            <div class="mt-1 small text-muted">🚌 = Buses, 👨‍✈️ = Drivers</div>

        `;

        

        // Assemble calendar

        calendarContainer.appendChild(calendarHeader);

        calendarContainer.appendChild(daysHeader);

        calendarContainer.appendChild(datesGrid);

        availabilityContainer.appendChild(calendarContainer);

        availabilityContainer.appendChild(legend);

    }



    if (!isRebooking) return;



    const data = await getBooking(bookingId);



    const booking = data.booking;

    const stops = data.stops;

    const locations = data.distances;



    console.log("Booking detail: ", booking);

    console.log("Stops detail: ", data.distances);



    document.getElementById("bookingHeader").textContent = "Rebook a Trip";

    const buttonText = (booking.status === "Confirmed") ? "Request Rebooking" : "Rebook";

    document.getElementById("submitBooking").textContent = buttonText;



    if (stops.length > 0) {

        for (let i = 0; i < stops.length; i++) 

            document.getElementById("addStop").click();

    }



    const addressInputs = Array.from(document.querySelectorAll(".address"));



    locations.forEach((location, i) => {

        addressInputs[i].value = location.origin;

    });



    const date_of_tour = new Date(booking.date_of_tour);



    picker.setDate(date_of_tour);



    // Set the number of days and buses

    const daysElement = document.getElementById("number_of_days");

    const busesElement = document.getElementById("number_of_buses");

    

    daysElement.textContent = booking.number_of_days;

    busesElement.textContent = booking.number_of_buses;

    

    // Update localStorage with the booking values

    localStorage.setItem("buses", booking.number_of_buses);

    localStorage.setItem("days", booking.number_of_days);

    

    // Calculate route and total cost

    calculateRoute();

    if (allInputsFilled()) {

        renderTotalCost();

    }

});





async function getBooking(bookingId) {

    try {

        const response = await fetch("/get-booking", {

            method: "POST",

            headers: { "Content-Type": "application/json" },

            body: JSON.stringify({ bookingId })

        });



        const data = await response.json();



        if (data.success) {

            return data;

        } else {

            return [];

        }

    } catch (error) {

        console.error(error);

    }

}





























document.addEventListener("DOMContentLoaded", initMap);



// Initialize days and buses counters

document.addEventListener("DOMContentLoaded", function() {

    const daysElement = document.getElementById("number_of_days");

    const busesElement = document.getElementById("number_of_buses");

    

    if (!isRebooking) {

        // Always set to zero for new bookings

        daysElement.textContent = "1";

        busesElement.textContent = "1";

        

        // Clear any previous values in localStorage

        localStorage.removeItem("days");

        localStorage.removeItem("buses");

    }

    

    // Add event listeners for days and buses counter buttons

    document.getElementById("increaseDays").addEventListener("click", function() {

        const currentDays = parseInt(daysElement.textContent);

        const newDays = currentDays + 1;

        

        // Skip availability check if rebooking

        if (isRebooking) {

            daysElement.textContent = newDays;

            localStorage.setItem("days", newDays);

            if (allInputsFilled()) {

                renderTotalCost();

            }

            return;

        }

        

        // Check availability for the new duration

        const dateOfTour = document.getElementById("date_of_tour").value;

        const numberOfBuses = parseInt(document.getElementById("number_of_buses").textContent);

        

        if (dateOfTour && numberOfBuses > 0) {

            // Validate that buses are available for all days

            checkBusAvailabilityForDateRange(dateOfTour, newDays, numberOfBuses).then(available => {

                if (available) {

                    daysElement.textContent = newDays;

                    localStorage.setItem("days", newDays);

                    if (allInputsFilled()) {

                        renderTotalCost();

                    }

                } else {

                    // Show error that buses are not available for the extended duration

                    Swal.fire({

                        icon: 'error',

                        title: 'Bus Availability',

                        text: `Sorry, there are not enough buses available for a ${newDays}-day trip starting on the selected date.`,

                        timer: 3000,

                        timerProgressBar: true

                    });

                }

            });

        } else {

            daysElement.textContent = newDays;

            localStorage.setItem("days", newDays);

            if (allInputsFilled()) {

                renderTotalCost();

            }

        }

    });

    

    document.getElementById("decreaseDays").addEventListener("click", function() {

        const currentDays = parseInt(daysElement.textContent);

        if (currentDays > 1) {

            const newDays = currentDays - 1;

            daysElement.textContent = newDays;

            localStorage.setItem("days", newDays);

            if (allInputsFilled()) {

                renderTotalCost();

            }

        }

    });

    

    document.getElementById("increaseBuses").addEventListener("click", function() {

        const currentBuses = parseInt(busesElement.textContent);

        const newBuses = currentBuses + 1;

        

        // Skip availability check if rebooking

        if (isRebooking) {

            busesElement.textContent = newBuses;

            localStorage.setItem("buses", newBuses);

            if (allInputsFilled()) {

                renderTotalCost();

            }

            return;

        }

        

        // Check if new number of buses is available on the selected date

        const dateOfTour = document.getElementById("date_of_tour").value;

        const numberOfDays = parseInt(document.getElementById("number_of_days").textContent);

        

        if (dateOfTour && numberOfDays > 0) {

            checkBusAvailabilityForDateRange(dateOfTour, numberOfDays, newBuses).then(available => {

                if (available) {

                    busesElement.textContent = newBuses;

                    localStorage.setItem("buses", newBuses);

                    if (allInputsFilled()) {

                        renderTotalCost();

                    }

                } else {

                    // Show error that requested buses are not available

                    Swal.fire({

                        icon: 'error',

                        title: 'Bus Availability',

                        text: `Sorry, there are not enough buses available for a ${numberOfDays}-day trip starting on the selected date.`,

                        timer: 3000,

                        timerProgressBar: true

                    });

                }

            });

        } else {

            busesElement.textContent = newBuses;

            localStorage.setItem("buses", newBuses);

            if (allInputsFilled()) {

                renderTotalCost();

            }

        }

    });

    

    document.getElementById("decreaseBuses").addEventListener("click", function() {

        const currentBuses = parseInt(busesElement.textContent);

        if (currentBuses > 1) {

            const newBuses = currentBuses - 1;

            busesElement.textContent = newBuses;

            localStorage.setItem("buses", newBuses);

            if (allInputsFilled()) {

                renderTotalCost();

            }

        }

    });

});



document.getElementById("nextButton").addEventListener("click", function () {

    // Get all address inputs

    const addressInputs = document.querySelectorAll(".address");

    const allAddressesFilled = Array.from(addressInputs).every(input => input.value.trim() !== "");

    

    if (!allAddressesFilled) {

        // Show error message

        Swal.fire({

            icon: 'error',

            title: 'Validation Error',

            text: 'Please fill in all location fields before proceeding.',

            timer: 2000,

            timerProgressBar: true

        });

        return;

    }



    if (allInputsFilled()) {

        renderTotalCost();

    }



    // If all addresses are filled, proceed to next step

    document.getElementById("firstInfo").classList.add("d-none");

    document.getElementById("nextInfo").classList.remove("d-none");

});



document.getElementById("back").addEventListener("click", function () {

    document.getElementById("firstInfo").classList.remove("d-none");

    document.getElementById("nextInfo").classList.add("d-none");

});



// submit booking 



document.addEventListener("DOMContentLoaded", function() {

    // Pre-validate bus and driver availability when all necessary values are set

    function checkAndPrevalidateAvailability() {

        const dateOfTour = document.getElementById("date_of_tour").value;

        const numberOfDays = parseInt(document.getElementById("number_of_days").textContent);

        const numberOfBuses = parseInt(document.getElementById("number_of_buses").textContent);

        

        if (dateOfTour && numberOfDays > 0 && numberOfBuses > 0) {

            console.log("Pre-validating availability for faster form submission");

            

            // Don't show loading indicator for pre-validation

            Promise.all([

                checkBusAvailabilityForDateRange(dateOfTour, numberOfDays, numberOfBuses),

                checkDriverAvailabilityForDateRange(dateOfTour, numberOfDays, numberOfBuses)

            ])

            .then(([busesAvailable, driversAvailable]) => {

                // Store results in data attributes on the form for quick access during submission

                document.getElementById("bookingForm").dataset.busesPrevalidated = busesAvailable ? "true" : "false";

                document.getElementById("bookingForm").dataset.driversPrevalidated = driversAvailable ? "true" : "false";

                console.log("Pre-validation complete - Buses available:", busesAvailable, "Drivers available:", driversAvailable);

                

                // Show warning if not available

                if (!busesAvailable) {

                    Swal.fire({

                        icon: 'warning',

                        title: 'Bus Availability Issue',

                        html: `There are not enough buses available for a ${numberOfDays}-day trip starting on the selected date.<br><br>Please adjust your selection before submitting the form.`,

                        confirmButtonText: 'OK'

                    });

                } else if (!driversAvailable) {

                    Swal.fire({

                        icon: 'warning',

                        title: 'Driver Availability Issue',

                        html: `There are not enough drivers available for a ${numberOfDays}-day trip starting on the selected date.<br><br>Please adjust your selection before submitting the form.`,

                        confirmButtonText: 'OK'

                    });

                }

            })

            .catch(error => {

                console.error("Error in pre-validation:", error);

            });

        }

    }

    

    // Add listeners to trigger pre-validation

    document.getElementById("date_of_tour").addEventListener("change", function() {

        // Clear previous pre-validation

        document.getElementById("bookingForm").dataset.busesPrevalidated = "";

        // Pre-validate after a short delay

        setTimeout(checkAndPrevalidateAvailability, 500);

    });

    

    document.getElementById("increaseDays").addEventListener("click", function() {

        // Previous increaseDays code...

        // After the original handler completes, trigger pre-validation

        setTimeout(checkAndPrevalidateAvailability, 500);

    });

    

    document.getElementById("increaseBuses").addEventListener("click", function() {

        // Previous increaseBuses code...

        // After the original handler completes, trigger pre-validation

        setTimeout(checkAndPrevalidateAvailability, 500);

    });

    

    document.getElementById("decreaseDays").addEventListener("click", function() {

        // Previous decreaseDays code...

        // After the original handler completes, trigger pre-validation

        setTimeout(checkAndPrevalidateAvailability, 500);

    });

    

    document.getElementById("decreaseBuses").addEventListener("click", function() {

        // Previous decreaseBuses code...

        // After the original handler completes, trigger pre-validation

        setTimeout(checkAndPrevalidateAvailability, 500);

    });

});



document.getElementById("bookingForm").addEventListener("submit", async function (e) {

    e.preventDefault(); 

    

    console.log("Form submission started");

    

    // Check if terms are agreed to

    const agreeTerms = document.getElementById("agreeTerms").checked;

    if (!agreeTerms) {

        Swal.fire({

            icon: 'error',

            title: 'Terms Required',

            text: 'You must agree to the terms and conditions to proceed.',

            timer: 2000,

            timerProgressBar: true

        });

        return;

    }

    

    // Validate all required fields

    const dateOfTour = document.getElementById("date_of_tour").value;

    const numberOfDays = parseInt(document.getElementById("number_of_days").textContent);

    const numberOfBuses = parseInt(document.getElementById("number_of_buses").textContent);

    

    if (!dateOfTour) {

        Swal.fire({

            icon: 'error',

            title: 'Date Required',

            text: 'Please select a date for your tour.',

            timer: 2000,

            timerProgressBar: true

        });

        return;

    }

    

    if (numberOfDays <= 0) {

        Swal.fire({

            icon: 'error',

            title: 'Days Required',

            text: 'Please select at least 1 day for your trip.',

            timer: 2000,

            timerProgressBar: true

        });

        return;

    }

    

    if (numberOfBuses <= 0) {

        Swal.fire({

            icon: 'error',

            title: 'Buses Required',

            text: 'Please select at least 1 bus for your trip.',

            timer: 2000,

            timerProgressBar: true

        });

        return;

    }

    

    // Skip availability check if this is a rebooking

    let busesAvailable = false;

    let driversAvailable = false;

    

    if (isRebooking) {

        // For rebooking, we'll let the server handle availability check

        console.log("Rebooking: Skipping frontend availability check");

        busesAvailable = true;

        driversAvailable = true;

    } else {

        // Check if we have pre-validated results

        const busesPrevalidated = this.dataset.busesPrevalidated;

        const driversPrevalidated = this.dataset.driversPrevalidated;

        

        // Check bus availability first

        if (busesPrevalidated === "false") {

            console.log("Using pre-validated result: buses not available");

            Swal.fire({

                icon: 'error',

                title: 'No Buses Available',

                html: `There are not enough buses available for a ${numberOfDays}-day trip starting on the selected date.<br><br>Please choose a different date, reduce the number of days, or reduce the number of buses.`,

                confirmButtonText: 'OK'

            });

            return;

        }

        

        // Then check driver availability

        if (driversPrevalidated === "false") {

            console.log("Using pre-validated result: drivers not available");

            Swal.fire({

                icon: 'error',

                title: 'No Drivers Available',

                html: `There are not enough drivers available for a ${numberOfDays}-day trip starting on the selected date.<br><br>Please choose a different date, reduce the number of days, or reduce the number of buses.`,

                confirmButtonText: 'OK'

            });

            return;

        }

        

        // Only check availability if not already pre-validated and not rebooking

        if (busesPrevalidated !== "true" || driversPrevalidated !== "true") {

            try {

                console.log("No pre-validation result, checking availability...");

                

                // Show loading indicator

                await Swal.fire({

                    title: 'Checking availability...',

                    text: 'Please wait while we verify bus and driver availability.',

                    allowOutsideClick: false,

                    allowEscapeKey: false,

                    showConfirmButton: false,

                    willOpen: () => {

                        Swal.showLoading();

                    }

                });

                

                // Check availability for the entire date range

                console.log(`Checking availability for ${numberOfBuses} buses/drivers for ${numberOfDays} days starting ${dateOfTour}`);

                

                // Check both bus and driver availability in parallel

                [busesAvailable, driversAvailable] = await Promise.all([

                    checkBusAvailabilityForDateRange(dateOfTour, numberOfDays, numberOfBuses),

                    checkDriverAvailabilityForDateRange(dateOfTour, numberOfDays, numberOfBuses)

                ]);

                

                console.log("Buses available:", busesAvailable, "Drivers available:", driversAvailable);

                

                // Close the loading indicator

                Swal.close();

                

                if (!busesAvailable) {

                    console.log("No buses available, showing error");

                    Swal.fire({

                        icon: 'error',

                        title: 'No Buses Available',

                        html: `Sorry, there are not enough buses available for a ${numberOfDays}-day trip starting on the selected date.<br><br>Please choose a different date, reduce the number of days, or reduce the number of buses.`,

                        confirmButtonText: 'OK'

                    });

                    return; // Prevent form submission

                }

                

                if (!driversAvailable) {

                    console.log("No drivers available, showing error");

                    Swal.fire({

                        icon: 'error',

                        title: 'No Drivers Available',

                        html: `Sorry, there are not enough drivers available for a ${numberOfDays}-day trip starting on the selected date.<br><br>Please choose a different date, reduce the number of days, or reduce the number of buses.`,

                        confirmButtonText: 'OK'

                    });

                    return; // Prevent form submission

                }

            } catch (error) {

                console.error("Error checking availability:", error);

                Swal.fire({

                    icon: 'error',

                    title: 'Verification Error',

                    text: 'Unable to verify bus and driver availability. Please try again later.',

                    timer: 3000,

                    timerProgressBar: true

                });

                return; // Prevent form submission on error

            }

        } else {

            busesAvailable = true;

            driversAvailable = true;

        }

    }

    

    // Continue with form submission only if buses are available or we're rebooking

    console.log("Proceeding with form submission");

    

    const stops = Array.from(document.querySelectorAll(".added-stop")).map((stop, i) => stop.value).filter(stop => stop.trim() !== "");

    const destination = stops[stops.length - 1];

    stops.pop();



    const tripDistances = await getTripDistances();

    console.log("Trip Distances: ", tripDistances);



    const addressInputs = document.querySelectorAll(".address");

    const addresses = Array.from(addressInputs).map(input => input.value.trim()).filter(Boolean);



    const totalCost = await getTotalCost();

    console.log("Total Cost: ", totalCost);

    if (!totalCost || totalCost === 0) {

        Swal.fire({

            icon: 'error',

            title: 'Cost Calculation Error',

            text: 'Unable to calculate the total cost. Please try again.',

            timer: 3000,

            timerProgressBar: true

        });

        return;

    }

    

    // Get the cost breakdown (if available)

    const costBreakdown = window.costBreakdown || {};

    

    const formData = {

        dateOfTour: dateOfTour,

        destination: destination,

        pickupPoint: document.getElementById("pickup_point").value,

        pickupTime: document.getElementById("pickup_time").value,

        stops: stops,

        numberOfBuses: numberOfBuses,

        numberOfDays: numberOfDays,

        totalCost: totalCost,

        balance: totalCost,

        tripDistances: tripDistances,

        addresses: addresses,

        isRebooking: isRebooking,

        rebookingId: bookingId,

        agreeTerms: agreeTerms,

        busesVerified: true, // Add flag to indicate buses were verified



        baseCost: costBreakdown.baseCost || null,

        dieselCost: costBreakdown.dieselCost || null,

        baseRate: costBreakdown.baseRate || null,

        dieselPrice: costBreakdown.dieselPrice || null,

        totalDistance: costBreakdown.totalDistance || null

    };



    console.log("Submitting booking request with data:", formData);



    try {

        const response = await fetch("/request-booking", {

            method: "POST",

            headers: { "Content-Type": "application/json" },

            body: JSON.stringify(formData)

        });



        const data = await response.json();



        if (data.success) {

            // Always clear localStorage values regardless of booking success

            localStorage.removeItem("days");

            localStorage.removeItem("buses");

            

            Swal.fire({

                icon: 'success',

                title: 'Success!',

                text: data.message,

                timer: 2000,

                timerProgressBar: true

            });

            

            // Clear form data

            this.reset(); 

            document.getElementById("totalCost").textContent = "";

            document.getElementById("number_of_days").textContent = "1";

            document.getElementById("number_of_buses").textContent = "1";

            

            // Redirect to My Bookings page after a short delay

            setTimeout(() => {

                window.location.href = "/home/booking-requests";

            }, 2000); // 2 second delay to allow the user to see the success message

        } else {

            Swal.fire({

                icon: 'error',

                title: 'Error',

                text: data.message,

                timer: 2000,

                timerProgressBar: true

            });

        }

    } catch (error) {

        console.error("Error submitting booking:", error.message);

        Swal.fire({

            icon: 'error',

            title: 'Error',

            text: 'An error occurred while processing your request. Please try again.',

            timer: 2000,

            timerProgressBar: true

        });

    }



    initMap(); 

});



// Array.from(document.getElementsByTagName("input")).forEach(input => {

//     input.addEventListener("change", renderTotalCost);

// });



// Handle terms and conditions modal accept button

document.addEventListener("DOMContentLoaded", function() {

    const acceptTermsBtn = document.getElementById("acceptTerms");

    if (acceptTermsBtn) {

        acceptTermsBtn.addEventListener("click", function() {

            document.getElementById("agreeTerms").checked = true;

        });

    }

});



async function getTripDistances() {

    const addressInputs = document.querySelectorAll(".address");

    const stops = Array.from(addressInputs).map(input => input.value.trim()).filter(Boolean);



    try {

        const response = await fetch("/get-distance", {

            method: "POST",

            headers: { "Content-Type": "application/json" },

            body: JSON.stringify({ stops })

        });



        const data = await response.json();



        if (data.status === "OK") {

            return data;

        }

    } catch (error) {

        console.error(error);

    }

}



async function renderTotalCost() {

    if (!allInputsFilled()) return;

    

    const costElement = document.getElementById("totalCost");

    costElement.textContent = "Calculating...";



    const totalCost = await getTotalCost();

    if (!totalCost) {

        costElement.textContent = "Unable to get total cost.";

        return;

    };



    // Get the cost breakdown details

    const costBreakdown = window.costBreakdown || {};

    console.log("Cost breakdown: ", costBreakdown);

    

    // Format the total cost

    const formattedTotal = totalCost.toLocaleString("en-US", { style: "currency", currency: "PHP" });

    

    // Create breakdown HTML

    let costHTML = `<div>Estimated total cost: <strong>${formattedTotal}</strong></div>`;

    

    // Add region info if available

    if (costBreakdown.region) {

        costHTML += `<div class="small text-muted mt-2">

            <div>Rate region: ${costBreakdown.region}</div>

            <div>Base rate: ${costBreakdown.baseRate?.toLocaleString("en-US", { style: "currency", currency: "PHP" })} per bus</div>

            <div>Base cost: ${costBreakdown.baseCost?.toLocaleString("en-US", { style: "currency", currency: "PHP" })}</div>

            <div>Diesel cost: ${costBreakdown.dieselCost?.toLocaleString("en-US", { style: "currency", currency: "PHP" })}</div>

        </div>`;

    }

    

    costElement.innerHTML = costHTML;

}







function debounce(func, delay) {

    let timeout;

    return function (...args) {

        clearTimeout(timeout);

        timeout = setTimeout(() => func.apply(this, args), delay);

    };

}



// Increase debounce delay for better performance

const debouncedGetAddress = debounce(getAddress, 800);



// Create a cache for address suggestions

const addressCache = new Map();



document.querySelectorAll(".address").forEach(input => {

    input.addEventListener("input", function (e) {

        const suggestionList = e.target.nextElementSibling;

        const input = this.value;

        const inputElement = this;

    

        // Only search if input is at least 3 characters

        if (input.length >= 3) {

            debouncedGetAddress(input, suggestionList, inputElement);

        } else {

            suggestionList.innerHTML = "";

            suggestionList.style.border = "none";

        }

    });     

});



function allInputsFilled() {

    const pickupPoint = document.getElementById("pickup_point").value.trim();

    const destinationInputs = document.querySelectorAll(".address");

    const destination = destinationInputs[destinationInputs.length - 1].value.trim();

    const numberOfDays = document.getElementById("number_of_days").textContent;

    const numberOfBuses = document.getElementById("number_of_buses").textContent;

    

    // Check if all required fields are filled

    return pickupPoint !== "" && 

           destination !== "" && 

           parseInt(numberOfDays) > 0 && 

           parseInt(numberOfBuses) > 0;

}



/**

 * Check bus availability for a date range

 * @param {string} startDate - Start date in YYYY-MM-DD format

 * @param {number} numberOfDays - The number of days for the booking

 * @param {number} requestedBuses - The number of buses requested

 * @returns {Promise<boolean>} - Whether the requested buses are available for the entire period

 */

async function checkBusAvailabilityForDateRange(startDate, numberOfDays, requestedBuses) {

    if (!startDate || numberOfDays <= 0 || requestedBuses <= 0) {

        console.error("Invalid parameters for bus availability check:", { startDate, numberOfDays, requestedBuses });

        return false;

    }

    

    try {

        console.log(`Checking availability for ${requestedBuses} buses for ${numberOfDays} days starting ${startDate}`);

        

        // Calculate end date based on start date and number of days

        const start = new Date(startDate);

        const end = new Date(startDate);

        end.setDate(end.getDate() + (numberOfDays - 1)); // -1 because the start day counts as day 1

        

        // Use our safe formatter to get the end date string

        const endDateStr = formatDateYYYYMMDD(end);

        console.log(`Date range: ${startDate} to ${endDateStr}`);

        

        // Check if we have cached data for this date range

        const cacheKey = getBusAvailabilityCacheKey(startDate, endDateStr);

        if (busAvailabilityCache.has(cacheKey)) {

            console.log("Using cached availability data");

            const cachedData = busAvailabilityCache.get(cacheKey);

            

            // Check if all days have enough buses available

            const hasEnoughBuses = cachedData.every(day => day.available >= requestedBuses);

            console.log("Buses available from cache:", hasEnoughBuses);

            return hasEnoughBuses;

        }

        

        // No cached data, send request to server

        console.log("Fetching bus availability data...");

        const response = await fetch('/get-bus-availability', {

            method: 'POST',

            headers: { 'Content-Type': 'application/json' },

            body: JSON.stringify({ 

                start_date: startDate,

                end_date: endDateStr

            })

        });

        

        const data = await response.json();

        console.log("Received availability data:", data);

        

        if (!data.success) {

            console.error("API returned error:", data.message);

            return false;

        }

        

        if (!data.availability || data.availability.length === 0) {

            console.error("No availability data returned");

            return false;

        }

        

        // Cache the availability data

        busAvailabilityCache.set(cacheKey, data.availability);

        

        // Set cache expiration (10 minutes)

        setTimeout(() => {

            busAvailabilityCache.delete(cacheKey);

        }, 10 * 60 * 1000);

        

        // Log the availability for each day

        data.availability.forEach(day => {

            console.log(`Date: ${day.date}, Available: ${day.available}/${day.total}, Requested: ${requestedBuses}`);

        });

        

        // Check if there are any days with insufficient buses

        const insufficientDays = data.availability.filter(day => day.available < requestedBuses);

        if (insufficientDays.length > 0) {

            console.warn("Insufficient buses available for some days:", insufficientDays);

            return false;

        }

        

        // Make sure we got data for all days in the range

        const expectedDays = daysBetween(new Date(startDate), new Date(endDateStr)) + 1; // +1 to include end date

        if (data.availability.length < expectedDays) {

            console.error(`Expected data for ${expectedDays} days, but got ${data.availability.length} days`);

            return false;

        }

        

        // If we reach here, buses are available for all days

        console.log("Buses are available for all days in the requested period");

        return true;

    } catch (error) {

        console.error("Error checking bus availability for date range:", error);

        return false;

    }

}



/**

 * Calculate the number of days between two dates

 * @param {Date} start - Start date

 * @param {Date} end - End date

 * @returns {number} - Number of days between the two dates (inclusive)

 */

function daysBetween(start, end) {

    // Use our own date formatter to get consistent date strings without timezone issues

    const startStr = formatDateYYYYMMDD(start);

    const endStr = formatDateYYYYMMDD(end);

    

    // Create new Date objects using the formatted strings to avoid timezone issues

    const startDate = new Date(startStr + "T00:00:00");

    const endDate = new Date(endStr + "T00:00:00");

    

    const oneDay = 24 * 60 * 60 * 1000; // hours*minutes*seconds*milliseconds

    return Math.round(Math.abs((startDate - endDate) / oneDay));

}



/**

 * Check bus availability for a specific date and update UI accordingly

 * @param {string} date - The date to check in YYYY-MM-DD format

 */

async function checkBusAvailabilityForDate(date) {

    if (!date) return;

    

    // Skip availability check if rebooking

    if (isRebooking) {

        console.log("Rebooking: Skipping availability check for date change");

        return;

    }

    

    try {

        // Get current requested buses and days

        const requestedBuses = parseInt(document.getElementById("number_of_buses").textContent);

        const numberOfDays = parseInt(document.getElementById("number_of_days").textContent);

        

        if (requestedBuses <= 0 || numberOfDays <= 0) return; // No need to check if no buses requested

        

        // Check availability for the entire date range

        const available = await checkBusAvailabilityForDateRange(date, numberOfDays, requestedBuses);

        

        // If not available, show warning

        if (!available) {

            Swal.fire({

                icon: 'warning',

                title: 'Limited Availability',

                text: `There are not enough buses available for a ${numberOfDays}-day trip starting on the selected date. Please choose another date or adjust your requirements.`,

                timer: 4000,

                timerProgressBar: true

            });

        }

    } catch (error) {

        console.error("Error checking bus availability:", error);

    }

}



/**

 * Format a date as YYYY-MM-DD without timezone issues

 * @param {Date} date - The date to format

 * @returns {string} - Date formatted as YYYY-MM-DD

 */

function formatDateYYYYMMDD(date) {

    const year = date.getFullYear();

    // Add 1 to month because getMonth() is zero-indexed

    const month = String(date.getMonth() + 1).padStart(2, '0');

    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;

}



async function getAddress(input, suggestionList, inputElement) {

    // Check if we have cached results for this input

    if (addressCache.has(input)) {

        displaySuggestions(addressCache.get(input), suggestionList, inputElement);

        return;

    }

    

    try {

        const response = await fetch("/get-address", {

            method: "POST",

            headers: { "Content-Type": "application/json" },

            body: JSON.stringify({ address: input })

        });



        const data = await response.json();

        

        // Cache the results

        addressCache.set(input, data);

        

        displaySuggestions(data, suggestionList, inputElement);

    } catch (error) {

        console.error(error);

    }

}



function displaySuggestions(data, suggestionList, inputElement) {

    suggestionList.innerHTML = "";

    suggestionList.style.border = "1px solid #ccc"; 

    

    if (data.status !== "OK") {

        const list = document.createElement("li");

        list.textContent = "No places found.";

        suggestionList.appendChild(list);

        return;

    }



    // Limit to top 5 results for better performance

    const topResults = data.predictions.slice(0, 5);

    

    topResults.forEach(place => {

        const list = document.createElement("li");

        

        // Create a container for the suggestion

        const suggestionContainer = document.createElement("div");

        suggestionContainer.className = "suggestion-item";

        

        // Add an icon based on the place type

        const icon = document.createElement("i");

        icon.className = getPlaceTypeIcon(place.types);

        suggestionContainer.appendChild(icon);

        

        // Add the main text

        const mainText = document.createElement("span");

        mainText.className = "main-text";

        mainText.textContent = place.structured_formatting?.main_text || place.description.split(',')[0];

        suggestionContainer.appendChild(mainText);

        

        // Add the secondary text if available

        if (place.structured_formatting?.secondary_text) {

            const secondaryText = document.createElement("span");

            secondaryText.className = "secondary-text";

            secondaryText.textContent = place.structured_formatting.secondary_text;

            suggestionContainer.appendChild(secondaryText);

        } else {

            // If no structured formatting, use the rest of the description

            const parts = place.description.split(',');

            if (parts.length > 1) {

                const secondaryText = document.createElement("span");

                secondaryText.className = "secondary-text";

                secondaryText.textContent = parts.slice(1).join(',').trim();

                suggestionContainer.appendChild(secondaryText);

            }

        }

        

        list.appendChild(suggestionContainer);



        list.addEventListener("click", function () {

            inputElement.value = place.description; 

            calculateRoute();

            suggestionList.innerHTML = "";

            suggestionList.style.border = "none";

        });



        document.addEventListener("click", function (e) {

            if (e.target !== list && !list.contains(e.target)) {

                suggestionList.innerHTML = "";

                suggestionList.style.border = "none";

            }

        });



        suggestionList.appendChild(list);

    });

}



// Helper function to determine the appropriate icon based on place type

function getPlaceTypeIcon(types) {

    if (!types || types.length === 0) return "bi bi-geo-alt";

    

    // Check for specific place types and return appropriate icon

    if (types.includes("establishment") || types.includes("point_of_interest")) {

        return "bi bi-building";

    } else if (types.includes("route") || types.includes("street_address")) {

        return "bi bi-signpost-split";

    } else if (types.includes("locality") || types.includes("sublocality")) {

        return "bi bi-geo";

    } else if (types.includes("park") || types.includes("natural_feature")) {

        return "bi bi-tree";

    } else if (types.includes("transit_station") || types.includes("bus_station")) {

        return "bi bi-bus-front";

    } else if (types.includes("restaurant") || types.includes("food")) {

        return "bi bi-cup-hot";

    } else if (types.includes("lodging") || types.includes("hotel")) {

        return "bi bi-house-door";

    } else {

        return "bi bi-geo-alt";

    }

}



async function getDistanceMatrix(stops) {

    try {

        const response = await fetch("/get-distance", {

            method: "POST",

            headers: { "Content-Type": "application/json" },

            body: JSON.stringify({ stops })

        });



        const data = await response.json();



        if (data.status === "OK") {

            let total = 0;

            for (let i = 0; i < data.rows.length; i++) {

                const element = data.rows[i].elements[i]; // diagonal contains the desired distances

                if (element.status === "OK") {

                    total += element.distance.value;

                }   

            }

            return total; // in meters

        } else {

            console.error("Distance API error:", data.status);

        }

    } catch (error) {

        console.error("Fetch error:", error);

    }

    return 0;

}



async function getTotalCost() {

    const addressInputs = document.querySelectorAll(".address");

    const stops = Array.from(addressInputs).map(input => input.value.trim()).filter(Boolean);



    if (stops.length < 2) return;



    // The first address is the pickup point, the last is the destination

    const pickupPoint = document.getElementById("pickup_point").value;

    const destination = stops[stops.length - 1];



    const totalDistanceInMeters = await getDistanceMatrix(stops);

    const distanceInKm = totalDistanceInMeters / 1000;



    const numberOfDays = document.getElementById("number_of_days").textContent;

    const numberOfBuses = document.getElementById("number_of_buses").textContent;



    if (!distanceInKm || !numberOfDays || !numberOfBuses) return;



    try {

        const response = await fetch("/get-total-cost", {

            method: "POST",

            headers: { "Content-Type": "application/json" },

            body: JSON.stringify({ 

                distance: distanceInKm, 

                numberOfBuses, 

                numberOfDays,

                locations: stops,

                destination: destination,

                pickupPoint: pickupPoint

            })

        });



        const data = await response.json();

        

        if (data.success) {

            // Store cost breakdown for display

            window.costBreakdown = {

                region: data.region,

                baseRate: data.base_rate,

                baseCost: data.base_cost,

                dieselPrice: data.diesel_price,

                dieselCost: data.diesel_cost,

                locationRegions: data.location_regions,

                totalDistance: distanceInKm

            };

            

            // Log additional details for debugging

            // console.log("Cost breakdown:", window.costBreakdown);

            

            return data.total_cost;

        } else {

            console.error(data.message);

        }

    } catch (error) {

        console.error(error);

    }

}



function initMap() {

    let map;

    const mapOptions = {

        center: { lat: 14.5995, lng: 120.9842 }, // Default center (e.g., Manila)

        zoom: 10,

        disableDefaultUI: true, // disable all controls

        zoomControl: true,

        fullscreenControl: false,

        streetViewControl: false,

        mapTypeControl: false,

        rotateControl: false

      };



    map = new google.maps.Map(document.getElementById("map"), mapOptions);

    

    directionsService = new google.maps.DirectionsService();

    directionsRenderer = new google.maps.DirectionsRenderer({ map: map});

}





let directionsService, directionsRenderer;



async function calculateRoute() {

    const pickupPoint = document.getElementById("pickup_point").value;

    const destinationInputs = document.querySelectorAll(".address");

    const destination = destinationInputs[destinationInputs.length - 1].value;

    const stops = Array.from(document.querySelectorAll(".added-stop")).map((stop, i) => stop.value ).filter(stop => stop.trim() !== "");

    stops.pop();



    // Check if pickup and destination are filled

    if (!pickupPoint || !destination) {

        // Show a notification if either pickup or destination is missing

        return;

    }



    // Show loading indicator

    const mapElement = document.getElementById("map");

    mapElement.innerHTML = '<div class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    

    try {

        const response = await fetch("/get-route", {

            method: "POST",

            headers: { "Content-Type": "application/json" },

            body: JSON.stringify({ pickupPoint, destination, stops })

        });



        const data = await response.json();

        

        // Reinitialize the map

        initMap();

        

        if (data.error) {

            console.error(data.error);

            // Show error notification

            // Swal.fire({

            //     icon: 'error',

            //     title: 'Route Calculation Error',

            //     text: 'Unable to calculate the route. Please check your locations and try again.',

            //     timer: 2000,

            //     timerProgressBar: true

            // });

        }



        const waypoints = data.stops.map(stop => ({ location: stop, stopover: true }));

        const request = {

            origin: pickupPoint,

            destination: destination,

            waypoints: waypoints,

            travelMode: google.maps.TravelMode.DRIVING,

        };

    

        directionsService.route(request, (result, status) => {

            if (status === google.maps.DirectionsStatus.OK) {

                directionsRenderer.setDirections(result);

                

                // Show success notification with route details

                const route = result.routes[0];

                const distance = route.legs.reduce((total, leg) => total + leg.distance.value, 0) / 1000; // in km

                const duration = route.legs.reduce((total, leg) => total + leg.duration.value, 0) / 60; // in minutes

                

                // Only show notification if the route is significantly different from previous calculations

                if (window.lastCalculatedDistance && Math.abs(window.lastCalculatedDistance - distance) < 1) {

                    return; // Skip notification if distance is similar to previous calculation

                }

                

                window.lastCalculatedDistance = distance;

            } else {

                console.error("Directions request failed due to " + status);

                

                // Show specific error message based on status

                let errorMessage = "Unable to calculate the route. ";

                

                switch (status) {

                    case google.maps.DirectionsStatus.NOT_FOUND:

                        errorMessage += "One or more locations could not be found.";

                        break;

                    case google.maps.DirectionsStatus.ZERO_RESULTS:

                        errorMessage += "No route could be found between the specified locations.";

                        break;

                    case google.maps.DirectionsStatus.MAX_WAYPOINTS_EXCEEDED:

                        errorMessage += "Too many waypoints. Please reduce the number of stops.";

                        break;

                    default:

                        errorMessage += "Please check your locations and try again.";

                }

                

                // Swal.fire({

                //     icon: 'error',

                //     title: 'Route Calculation Failed',

                //     text: errorMessage,

                //     timer: 2000,

                //     timerProgressBar: true

                // });

            }

        });

    } catch (error) {

        console.error("Error fetching route: ", error.message);

        

        // Reinitialize the map

        initMap();

        

        // Show error notification

        Swal.fire({

            icon: 'error',

            title: 'Connection Error',

            text: 'Unable to connect to the route service. Please check your internet connection and try again.',

            timer: 2000,

            timerProgressBar: true

        });

        return;

    }   

}









// add stop

let position = 3, count = 0;

document.getElementById("addStop").addEventListener("click", () => {

    count++;

    document.getElementById("destination").placeholder = "Add a stop";



    const form = document.getElementById("firstInfo");

    const div = document.createElement("div");

    const input = document.createElement("input");

    const ul = document.createElement("ul");



    div.classList.add("mb-3", "position-relative");

    input.id = "destination";

    input.placeholder = "Add a stop";

    input.autocomplete = "off";

    input.classList.add("form-control", "address", "added-stop", "position-relative", "px-4", "py-2", "destination");

    ul.classList.add("suggestions");



    input.addEventListener("input", function (e) {

        const suggestionList = e.target.nextElementSibling;

        const input = this.value;

        const inputElement = this;

    

        debouncedGetAddress(input, suggestionList, inputElement);    

    });



    input.addEventListener("change", async function () {

        if (!allInputsFilled()) return;

        

        const debouncedRenderTotalCost = debounce(renderTotalCost, 500);

        debouncedRenderTotalCost();

    });



    const locationIcon = document.createElement("i");

    locationIcon.classList.add("bi", "bi-geo-alt-fill", "location-icon")



    const removeButton = document.createElement("i");

    removeButton.classList.add("bi", "bi-x-circle-fill", "remove-icon");

    removeButton.title = "Remove stop";



    removeButton.addEventListener("click", function () {

        div.remove();

        if (input.value.length > 5) {

            calculateRoute();

        }

        position--;

        count--;

        if (count === 0) document.getElementById("destination").placeholder = "Dropoff Location";

    });

    

    div.append(locationIcon, removeButton);



    const referenceElement = form.children[position];

    position++;



    div.append(input, ul);

    form.insertBefore(div, referenceElement);

});



// Add event listeners to all address inputs to check for total cost calculation

document.querySelectorAll(".address").forEach(input => {

    input.addEventListener("change", function() {

        if (allInputsFilled()) {

            renderTotalCost();

        }

    });

});



// Add a global cache for bus availability results

const busAvailabilityCache = new Map();

const driverAvailabilityCache = new Map();



/**

 * Cache key generator for bus availability

 * @param {string} startDate - Start date in YYYY-MM-DD format

 * @param {string} endDate - End date in YYYY-MM-DD format

 * @returns {string} - Cache key

 */

function getBusAvailabilityCacheKey(startDate, endDate) {

    return `${startDate}_to_${endDate}`;

}



/**

 * Cache key generator for driver availability

 * @param {string} startDate - Start date in YYYY-MM-DD format

 * @param {string} endDate - End date in YYYY-MM-DD format

 * @returns {string} - Cache key

 */

function getDriverAvailabilityCacheKey(startDate, endDate) {

    return `driver_${startDate}_to_${endDate}`;

}



/**

 * Check driver availability for a date range

 * @param {string} startDate - Start date in YYYY-MM-DD format

 * @param {number} numberOfDays - The number of days for the booking

 * @param {number} requestedDrivers - The number of drivers requested (typically same as buses)

 * @returns {Promise<boolean>} - Whether the requested drivers are available for the entire period

 */

async function checkDriverAvailabilityForDateRange(startDate, numberOfDays, requestedDrivers) {

    if (!startDate || numberOfDays <= 0 || requestedDrivers <= 0) {

        console.error("Invalid parameters for driver availability check:", { startDate, numberOfDays, requestedDrivers });

        return false;

    }

    

    try {

        console.log(`Checking availability for ${requestedDrivers} drivers for ${numberOfDays} days starting ${startDate}`);

        

        // Calculate end date based on start date and number of days

        const start = new Date(startDate);

        const end = new Date(startDate);

        end.setDate(end.getDate() + (numberOfDays - 1)); // -1 because the start day counts as day 1

        

        // Use our safe formatter to get the end date string

        const endDateStr = formatDateYYYYMMDD(end);

        console.log(`Date range for drivers: ${startDate} to ${endDateStr}`);

        

        // Check if we have cached data for this date range

        const cacheKey = getDriverAvailabilityCacheKey(startDate, endDateStr);

        if (driverAvailabilityCache.has(cacheKey)) {

            console.log("Using cached driver availability data");

            const cachedData = driverAvailabilityCache.get(cacheKey);

            

            // Check if all days have enough drivers available

            const hasEnoughDrivers = cachedData.every(day => day.available >= requestedDrivers);

            console.log("Drivers available from cache:", hasEnoughDrivers);

            return hasEnoughDrivers;

        }

        

        // No cached data, send request to server

        console.log("Fetching driver availability data...");

        const response = await fetch('/get-driver-availability', {

            method: 'POST',

            headers: { 'Content-Type': 'application/json' },

            body: JSON.stringify({ 

                start_date: startDate,

                end_date: endDateStr

            })

        });

        

        const data = await response.json();

        console.log("Received driver availability data:", data);

        

        if (!data.success) {

            console.error("API returned error:", data.message);

            return false;

        }

        

        if (!data.availability || data.availability.length === 0) {

            console.error("No driver availability data returned");

            return false;

        }

        

        // Cache the availability data

        driverAvailabilityCache.set(cacheKey, data.availability);

        

        // Set cache expiration (10 minutes)

        setTimeout(() => {

            driverAvailabilityCache.delete(cacheKey);

        }, 10 * 60 * 1000);

        

        // Log the availability for each day

        data.availability.forEach(day => {

            console.log(`Date: ${day.date}, Available Drivers: ${day.available}/${day.total}, Requested: ${requestedDrivers}`);

        });

        

        // Check if there are any days with insufficient drivers

        const insufficientDays = data.availability.filter(day => day.available < requestedDrivers);

        if (insufficientDays.length > 0) {

            console.warn("Insufficient drivers available for some days:", insufficientDays);

            return false;

        }

        

        // Make sure we got data for all days in the range

        const expectedDays = daysBetween(new Date(startDate), new Date(endDateStr)) + 1; // +1 to include end date

        if (data.availability.length < expectedDays) {

            console.error(`Expected data for ${expectedDays} days, but got ${data.availability.length} days`);

            return false;

        }

        

        // If we reach here, drivers are available for all days

        console.log("Drivers are available for all days in the requested period");

        return true;

    } catch (error) {

        console.error("Error checking driver availability for date range:", error);

        return false;

    }

}





