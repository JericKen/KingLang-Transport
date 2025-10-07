/**

 * Bus Management JavaScript

 * Handles all client-side functionality for the bus management feature

 */



let busIncludeDeleted = false;



document.addEventListener('DOMContentLoaded', function() {

    // Initialize components

    loadBuses();

    loadBusStats();

    setupEventListeners();

    initDatePickers();

});



/**

 * Load all buses from the server

 */

function loadBuses() {

    const url = busIncludeDeleted ? '/admin/get-all-buses?includeDeleted=1' : '/admin/get-all-buses';

    fetch(url)

        .then(response => response.json())

        .then(data => {

            if (data.success) {

                // Sync toggle if API echoes includeDeleted

                if (typeof data.includeDeleted !== 'undefined') {

                    busIncludeDeleted = !!data.includeDeleted;

                    const t = document.getElementById('toggleBusTrash');

                    if (t) t.checked = busIncludeDeleted;

                }

                renderBusTable(data.buses);

            } else {

                showAlert('error', 'Error', 'Failed to load buses: ' + data.message);

            }

        })

        .catch(error => {

            console.error('Error loading buses:', error);

            showAlert('error', 'Error', 'Failed to load buses. Please try again.');

        });

}



/**

 * Load bus statistics

 */

function loadBusStats() {

    fetch('/admin/get-bus-stats')

        .then(response => response.json())

        .then(data => {

            if (data.success) {

                updateStatsDashboard(data.stats);

            } else {

                console.error('Failed to load bus stats:', data.message);

            }

        })

        .catch(error => {

            console.error('Error loading bus stats:', error);

        });

}



/**

 * Render the bus table with the provided data

 * @param {Array} buses Array of bus objects

 */

function renderBusTable(buses) {

    const tableBody = document.getElementById('busTableBody');

    if (!tableBody) return;

    

    tableBody.innerHTML = '';

    

    if (buses.length === 0) {

        tableBody.innerHTML = `

            <tr>

                <td colspan="9" class="text-center">No buses found</td>

            </tr>

        `;

        return;

    }

    

    buses.forEach(bus => {

        const statusClass = bus.status === 'Active' ? 'bg-success' : 'bg-warning';

        const formattedMaintenance = bus.last_maintenance ? formatDate(bus.last_maintenance) : '-';

        

        const row = document.createElement('tr');

        row.innerHTML = `

            <td>${bus.bus_id}</td>

            <td>${bus.name}</td>

            <td class="text-center">${bus.license_plate || '-'}</td>

            <td>${bus.model || '-'}</td>

            <td>${bus.year || '-'}</td>

            <td class="text-center">${bus.capacity}</td>

            <td>${formattedMaintenance}</td>

            <td><span class="badge ${statusClass}">${bus.status}</span></td>

            <td>

                <div class="actions-compact">

                    ${busIncludeDeleted ? `

                        <button class="btn btn-sm btn-outline-success restore-bus-btn" data-bus-id="${bus.bus_id}" data-bus-name="${bus.name}">

                            <i class="bi bi-arrow-counterclockwise"></i> Restore

                        </button>

                    ` : `

                        <button class="btn btn-sm btn-outline-primary view-schedule-btn" data-bus-id="${bus.bus_id}" data-bus-name="${bus.name}">

                            <i class="bi bi-calendar3"></i> Schedule

                        </button>

                        <button class="btn btn-sm btn-outline-success edit-bus-btn" 

                            data-bus-id="${bus.bus_id}" 

                            data-bus-name="${bus.name}" 

                            data-capacity="${bus.capacity}" 

                            data-status="${bus.status}"

                            data-license-plate="${bus.license_plate || ''}"

                            data-model="${bus.model || ''}"

                            data-year="${bus.year || ''}"

                            data-last-maintenance="${bus.last_maintenance || ''}">

                            <i class="bi bi-pencil"></i> Edit

                        </button>

                        <button class="btn btn-sm btn-outline-danger delete-bus-btn" data-bus-id="${bus.bus_id}" data-bus-name="${bus.name}">

                            <i class="bi bi-trash"></i> Delete

                        </button>

                    `}

                </div>

            </td>

        `;

        

        tableBody.appendChild(row);

    });

    

    // Add event listeners to the buttons

    addBusTableEventListeners();

}



/**

 * Add event listeners to the bus table buttons

 */

function addBusTableEventListeners() {

    if (!busIncludeDeleted) {

        // View schedule buttons

        document.querySelectorAll('.view-schedule-btn').forEach(button => {

            button.addEventListener('click', function() {

                const busId = this.getAttribute('data-bus-id');

                const busName = this.getAttribute('data-bus-name');

                openScheduleModal(busId, busName);

            });

        });

        

        // Edit bus buttons

        document.querySelectorAll('.edit-bus-btn').forEach(button => {

            button.addEventListener('click', function() {

                const busId = this.getAttribute('data-bus-id');

                const busName = this.getAttribute('data-bus-name');

                const capacity = this.getAttribute('data-capacity');

                const status = this.getAttribute('data-status');

                const licensePlate = this.getAttribute('data-license-plate');

                const model = this.getAttribute('data-model');

                const year = this.getAttribute('data-year');

                const lastMaintenance = this.getAttribute('data-last-maintenance');

                

                openEditBusModal(busId, busName, capacity, status, licensePlate, model, year, lastMaintenance);

            });

        });

        

        // Delete bus buttons

        document.querySelectorAll('.delete-bus-btn').forEach(button => {

            button.addEventListener('click', function() {

                const busId = this.getAttribute('data-bus-id');

                const busName = this.getAttribute('data-bus-name');

                

                confirmDeleteBus(busId, busName);

            });

        });

    } else {

        document.querySelectorAll('.restore-bus-btn').forEach(button => {

            button.addEventListener('click', function() {

                const busId = this.getAttribute('data-bus-id');

                const busName = this.getAttribute('data-bus-name');

                

                confirmRestoreBus(busId, busName);

            });

        });

    }

}



/**

 * Update the stats dashboard with the provided data

 * @param {Object} stats Bus statistics

 */

function updateStatsDashboard(stats) {

    // Update counts

    if (stats.counts) {

        document.getElementById('totalBusesCount').textContent = stats.counts.total || 0;

        document.getElementById('activeBusesCount').textContent = stats.counts.active || 0;

        document.getElementById('maintenanceBusesCount').textContent = stats.counts.maintenance || 0;

    }

    

    // Update most used buses

    if (stats.most_used && stats.most_used.length > 0) {

        const mostUsedList = document.getElementById('mostUsedBusesList');

        if (mostUsedList) {

            mostUsedList.innerHTML = '';

            

            stats.most_used.forEach(bus => {

                const listItem = document.createElement('li');

                listItem.className = 'list-group-item d-flex justify-content-between align-items-center';

                listItem.innerHTML = `

                    ${bus.name}

                    <span class="badge bg-primary rounded-pill">${bus.booking_count} bookings</span>

                `;

                mostUsedList.appendChild(listItem);

            });

        }

    }

    

    // Update current month usage

    if (stats.current_month && stats.current_month.length > 0) {

        const currentMonthList = document.getElementById('currentMonthBusesList');

        if (currentMonthList) {

            currentMonthList.innerHTML = '';

            

            stats.current_month.forEach(bus => {

                const listItem = document.createElement('li');

                listItem.className = 'list-group-item d-flex justify-content-between align-items-center';

                listItem.innerHTML = `

                    ${bus.name}

                    <span class="badge bg-success rounded-pill">${bus.booking_count} bookings</span>

                `;

                currentMonthList.appendChild(listItem);

            });

        }

    }

}



/**

 * Set up event listeners for the page

 */

function setupEventListeners() {

    // Add bus button

    const addBusBtn = document.getElementById('addBusBtn');

    if (addBusBtn) {

        addBusBtn.addEventListener('click', function() {

            openAddBusModal();

        });

    }

    

    // Check availability button

    const checkAvailabilityBtn = document.getElementById('checkAvailabilityBtn');

    if (checkAvailabilityBtn) {

        checkAvailabilityBtn.addEventListener('click', function() {

            checkBusAvailability();

        });

    }

    

    // Add bus form submission

    const addBusForm = document.getElementById('addBusForm');

    if (addBusForm) {

        addBusForm.addEventListener('submit', function(e) {

            e.preventDefault();

            submitAddBusForm();

        });

    }

    

    // Edit bus form submission

    const editBusForm = document.getElementById('editBusForm');

    if (editBusForm) {

        editBusForm.addEventListener('submit', function(e) {

            e.preventDefault();

            submitEditBusForm();

        });

    }

    

    // Refresh button

    const refreshBtn = document.getElementById('refreshBusesBtn');

    if (refreshBtn) {

        refreshBtn.addEventListener('click', function() {

            loadBuses();

            loadBusStats();

        });

    }

    const trashToggle = document.getElementById('toggleBusTrash');

    if (trashToggle) {

        trashToggle.addEventListener('change', function() {

            busIncludeDeleted = this.checked;

            loadBuses();

        });

    }

}



/**

 * Initialize date pickers

 */

function initDatePickers() {

    // Availability date pickers

    const startDateInput = document.getElementById('availabilityStartDate');

    const endDateInput = document.getElementById('availabilityEndDate');

    

    if (startDateInput && endDateInput) {

        // Set default dates (today and 7 days from now)

        const today = new Date();

        const nextWeek = new Date();

        nextWeek.setDate(today.getDate() + 7);

        

        startDateInput.valueAsDate = today;

        endDateInput.valueAsDate = nextWeek;

    }

    

    // Schedule date pickers

    const scheduleStartDateInput = document.getElementById('scheduleStartDate');

    const scheduleEndDateInput = document.getElementById('scheduleEndDate');

    

    if (scheduleStartDateInput && scheduleEndDateInput) {

        // Set default dates (today and 30 days from now)

        const today = new Date();

        const nextMonth = new Date();

        nextMonth.setDate(today.getDate() + 30);

        

        scheduleStartDateInput.valueAsDate = today;

        scheduleEndDateInput.valueAsDate = nextMonth;

    }

}



/**

 * Open the add bus modal

 */

function openAddBusModal() {

    const modal = new bootstrap.Modal(document.getElementById('addBusModal'));

    modal.show();

    document.getElementById('addBusForm').reset();

}



/**

 * Open the edit bus modal

 * @param {string} busId Bus ID

 * @param {string} busName Bus name

 * @param {string} capacity Bus capacity

 * @param {string} status Bus status

 * @param {string} licensePlate Bus license plate

 * @param {string} model Bus model

 * @param {string} year Bus year

 * @param {string} lastMaintenance Last maintenance date

 */

function openEditBusModal(busId, busName, capacity, status, licensePlate = '', model = '', year = '', lastMaintenance = '') {

    // Set form values

    document.getElementById('editBusId').value = busId;

    document.getElementById('editBusName').value = busName;

    document.getElementById('editBusCapacity').value = capacity;

    document.getElementById('editBusStatus').value = status;

    document.getElementById('editBusLicensePlate').value = licensePlate;

    document.getElementById('editBusModel').value = model;

    document.getElementById('editBusYear').value = year;

    document.getElementById('editBusLastMaintenance').value = lastMaintenance;

    

    // Open modal

    const modal = new bootstrap.Modal(document.getElementById('editBusModal'));

    modal.show();

}



/**

 * Open the bus schedule modal

 * @param {number} busId Bus ID

 * @param {string} busName Bus name

 */

function openScheduleModal(busId, busName) {

    const modal = document.getElementById('busScheduleModal');

    

    // Set bus name in modal title

    modal.querySelector('.modal-title').textContent = `Schedule for ${busName}`;

    

    // Set bus ID in hidden input

    modal.querySelector('#scheduleBusId').value = busId;

    

    // Show modal

    const bsModal = new bootstrap.Modal(modal);

    bsModal.show();

    

    // Load schedule

    loadBusSchedule(busId);

}



/**

 * Load the schedule for a specific bus

 * @param {number} busId Bus ID

 */

function loadBusSchedule(busId) {

    const startDate = document.getElementById('scheduleStartDate').value;

    const endDate = document.getElementById('scheduleEndDate').value;

    

    if (!startDate || !endDate) {

        showAlert('error', 'Error', 'Please select start and end dates');

        return;

    }

    

    const scheduleContainer = document.getElementById('busScheduleContainer');

    scheduleContainer.innerHTML = '<div class="text-center my-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading schedule...</p></div>';

    

    fetch('/admin/get-bus-schedule', {

        method: 'POST',

        headers: {

            'Content-Type': 'application/json'

        },

        body: JSON.stringify({

            bus_id: busId,

            start_date: startDate,

            end_date: endDate

        })

    })

    .then(response => response.json())

    .then(data => {

        if (data.success) {

            renderBusSchedule(data.schedule);

        } else {

            scheduleContainer.innerHTML = `<div class="alert alert-danger">${data.message || 'Failed to load schedule'}</div>`;

        }

    })

    .catch(error => {

        console.error('Error loading bus schedule:', error);

        scheduleContainer.innerHTML = '<div class="alert alert-danger">Failed to load schedule. Please try again.</div>';

    });

}



/**

 * Render the bus schedule

 * @param {Array} schedule Array of schedule objects

 */

function renderBusSchedule(schedule) {

    const scheduleContainer = document.getElementById('busScheduleContainer');

    

    if (!schedule || schedule.length === 0) {

        scheduleContainer.innerHTML = '<div class="alert alert-info">No bookings found for this bus in the selected date range.</div>';

        return;

    }

    

    let html = `

        <div class="table-responsive">

            <table class="table table-striped table-hover">

                <thead>

                    <tr>

                        <th>Booking ID</th>

                        <th>Destination</th>

                        <th>Pickup Point</th>

                        <th>Start Date</th>

                        <th>End Date</th>

                        <th class="d-none">Status</th>

                        <th>Client</th>

                    </tr>

                </thead>

                <tbody>

    `;

    

    schedule.forEach(booking => {

        const statusClass = getStatusClass(booking.status);

        

        html += `

            <tr>

                <td>${booking.booking_id}</td>

                <td>${booking.destination}</td>

                <td>${booking.pickup_point}</td>

                <td>${formatDate(booking.date_of_tour)}</td>

                <td>${formatDate(booking.end_of_tour)}</td>

                <td class="d-none"><span class="badge ${statusClass}">${booking.status}</span></td>

                <td>${booking.first_name} ${booking.last_name}</td>

            </tr>

        `;

    });

    

    html += `

                </tbody>

            </table>

        </div>

    `;

    

    scheduleContainer.innerHTML = html;

}



/**

 * Check bus availability for a date range

 */

function checkBusAvailability() {

    const startDate = document.getElementById('availabilityStartDate').value;

    const endDate = document.getElementById('availabilityEndDate').value;

    

    if (!startDate || !endDate) {

        showAlert('error', 'Error', 'Please select start and end dates');

        return;

    }

    

    const availabilityContainer = document.getElementById('busAvailabilityContainer');

    availabilityContainer.innerHTML = '<div class="text-center my-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Checking availability...</p></div>';

    

    fetch('/admin/get-bus-availability', {

        method: 'POST',

        headers: {

            'Content-Type': 'application/json'

        },

        body: JSON.stringify({

            start_date: startDate,

            end_date: endDate

        })

    })

    .then(response => response.json())

    .then(data => {

        if (data.success) {

            renderBusAvailability(data.availability);

        } else {

            availabilityContainer.innerHTML = `<div class="alert alert-danger">${data.message || 'Failed to check availability'}</div>`;

        }

    })

    .catch(error => {

        console.error('Error checking bus availability:', error);

        availabilityContainer.innerHTML = '<div class="alert alert-danger">Failed to check availability. Please try again.</div>';

    });

}



/**

 * Render bus availability

 * @param {Array} availability Array of availability objects

 */

function renderBusAvailability(availability) {

    const availabilityContainer = document.getElementById('busAvailabilityContainer');

    

    if (!availability || availability.length === 0) {

        availabilityContainer.innerHTML = '<div class="alert alert-info">No availability data found for the selected date range.</div>';

        return;

    }

    

    let html = `

        <div class="table-responsive">

            <table class="table table-striped table-hover">

                <thead>

                    <tr>

                        <th>Date</th>

                        <th>Available Buses</th>

                        <th>Booked Buses</th>

                        <th>Total Buses</th>

                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

    `;

    

    availability.forEach(day => {

        const availabilityPercentage = (day.available / day.total) * 100;

        let statusClass, statusText;

        

        if (availabilityPercentage >= 70) {

            statusClass = 'bg-success';

            statusText = 'Good Availability';

        } else if (availabilityPercentage >= 30) {

            statusClass = 'bg-warning';

            statusText = 'Limited Availability';

        } else {

            statusClass = 'bg-danger';

            statusText = 'Low Availability';

        }

        

        html += `

            <tr>

                <td>${formatDate(day.date)}</td>

                <td>${day.available}</td>

                <td>${day.booked}</td>

                <td>${day.total}</td>

                <td><span class="badge ${statusClass}">${statusText}</span></td>

            </tr>

        `;

    });

    

    html += `

                </tbody>

            </table>

        </div>

    `;

    

    availabilityContainer.innerHTML = html;

}



/**

 * Submit the add bus form

 */

function submitAddBusForm() {

    const formData = new FormData(document.getElementById('addBusForm'));

    

    fetch('/admin/add-bus', {

        method: 'POST',

        body: formData

    })

    .then(response => response.json())

    .then(data => {

        if (data.success) {

            // Close modal and show success message

            const modal = bootstrap.Modal.getInstance(document.getElementById('addBusModal'));

            modal.hide();

            

            showAlert('success', 'Success', 'Bus added successfully');

            

            // Refresh the bus list

            loadBuses();

            loadBusStats();

        } else {

            showAlert('error', 'Error', 'Failed to add bus: ' + data.message);

        }

    })

    .catch(error => {

        console.error('Error adding bus:', error);

        showAlert('error', 'Error', 'Failed to add bus. Please try again.');

    });

}



/**

 * Submit the edit bus form

 */

function submitEditBusForm() {

    const formData = new FormData(document.getElementById('editBusForm'));

    

     // Proceed with update

    fetch('/admin/update-bus', {

        method: 'POST',

        body: formData

    })

    .then(response => response.json())

    .then(data => {

        if (data.success) {

            // Close modal and show success message

            const modal = bootstrap.Modal.getInstance(document.getElementById('editBusModal'));

            modal.hide();

            

            showAlert('success', 'Success', 'Bus updated successfully');

            

            // Refresh the bus list

            loadBuses();

            loadBusStats();

        } else {

            showAlert('error', 'Error', 'Failed to update bus: ' + data.message);

        }

    })

    .catch(error => {

        console.error('Error updating bus:', error);

        showAlert('error', 'Error', 'Failed to update bus. Please try again.');

    });

}



/**

 * Confirm deletion of a bus

 * @param {number} busId The ID of the bus to delete

 * @param {string} busName The name of the bus to delete

 */

function confirmDeleteBus(busId, busName) {

    Swal.fire({

        title: 'Delete Bus?',

        text: `Are you sure you want to delete "${busName}"?`,

        icon: 'warning',

        showCancelButton: true,

        confirmButtonText: 'Yes, delete it!',

        cancelButtonText: 'Cancel',

        confirmButtonColor: '#dc3545'

    }).then((result) => {

        if (result.isConfirmed) {

            deleteBus(busId);

        }

    });

}



/**

 * Delete a bus

 * @param {number} busId The ID of the bus to delete

 */

function deleteBus(busId) {

    fetch('/admin/delete-bus', {

        method: 'POST',

        headers: {

            'Content-Type': 'application/json'

        },

        body: JSON.stringify({ bus_id: busId })

    })

    .then(response => response.json())

    .then(data => {

        if (data.success) {

            showAlert('success', 'Success', 'Bus deleted successfully');

            

            // Refresh the bus list

            loadBuses();

            loadBusStats();

        } else {

            showAlert('error', 'Error', 'Failed to delete bus: ' + data.message);

        }

    })

    .catch(error => {

        console.error('Error deleting bus:', error);

        showAlert('error', 'Error', 'Failed to delete bus. Please try again.');

    });

}



/**

 * Confirm restoration of a bus

 * @param {number} busId The ID of the bus to restore

 * @param {string} busName The name of the bus to restore

 */

function confirmRestoreBus(busId, busName) {

    Swal.fire({

        title: 'Restore Bus?',

        text: `Do you want to restore "${busName}"?`,

        icon: 'question',

        showCancelButton: true,

        confirmButtonText: 'Restore',

        cancelButtonText: 'Cancel',

        confirmButtonColor: '#198754'

    }).then((result) => {

        if (result.isConfirmed) {

            restoreBus(busId);

        }

    });

}



/**

 * Restore a bus

 * @param {number} busId The ID of the bus to restore

 */

function restoreBus(busId) {

    fetch('/admin/restore-bus', {

        method: 'POST',

        headers: { 'Content-Type': 'application/json' },

        body: JSON.stringify({ bus_id: busId })

    })

    .then(response => response.json())

    .then(data => {

        if (data.success) {

            showAlert('success', 'Restored', 'Bus restored successfully');

            loadBuses();

            loadBusStats();

        } else {

            showAlert('error', 'Error', 'Failed to restore bus: ' + data.message);

        }

    })

    .catch(error => {

        console.error('Error restoring bus:', error);

        showAlert('error', 'Error', 'Failed to restore bus. Please try again.');

    });

}



/**

 * Show an alert using SweetAlert2

 * @param {string} type Alert type (success, error, warning, info)

 * @param {string} title Alert title

 * @param {string} message Alert message

 */

function showAlert(type, title, message) {

    Swal.fire({

        icon: type,

        title: title,

        text: message,

        timer: 3000,

        timerProgressBar: true

    });

}



/**

 * Format a date string to a more readable format

 * @param {string} dateString Date string in YYYY-MM-DD format

 * @return {string} Formatted date string

 */

function formatDate(dateString) {

    const date = new Date(dateString);

    return date.toLocaleDateString('en-US', {

        year: 'numeric',

        month: 'short',

        day: 'numeric'

    });

}



/**

 * Get the appropriate CSS class for a booking status

 * @param {string} status Booking status

 * @return {string} CSS class

 */

function getStatusClass(status) {

    switch (status) {

        case 'Confirmed':

            return 'bg-success';

        case 'Processing':

            return 'bg-primary';

        case 'Pending':

            return 'bg-warning';

        case 'Completed':

            return 'bg-info';

        case 'Canceled':

            return 'bg-danger';

        case 'Rejected':

            return 'bg-secondary';

        default:

            return 'bg-secondary';

    }

}

