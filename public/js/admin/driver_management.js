document.addEventListener('DOMContentLoaded', function() {

    // Initialize modals

    const driverModal = new bootstrap.Modal(document.getElementById('driverModal'));

    const scheduleModal = new bootstrap.Modal(document.getElementById('driverScheduleModal'));

    

    // Make functions globally accessible

    window.driverModal = driverModal;

    window.scheduleModal = scheduleModal;

    window.editDriver = editDriver;

    window.confirmDelete = confirmDelete;

    window.viewSchedule = viewSchedule;

    

    // Load initial data

    let driverIncludeDeleted = false;

    loadDrivers();

    loadDriverStatistics();

    loadMostActiveDrivers();

    loadExpiringLicenses();

    

    // Event listeners

    document.getElementById('refreshDriversBtn').addEventListener('click', loadDrivers);

    document.getElementById('addDriverBtn').addEventListener('click', showAddDriverModal);

    document.getElementById('saveDriverBtn').addEventListener('click', saveDriver);

    

    // Date range picker for schedule modal

    if (document.getElementById('scheduleStartDate') && document.getElementById('scheduleEndDate')) {

        document.getElementById('scheduleStartDate').valueAsDate = new Date();

        

        const endDate = new Date();

        endDate.setDate(endDate.getDate() + 30);

        document.getElementById('scheduleEndDate').valueAsDate = endDate;

        

        document.getElementById('filterScheduleBtn').addEventListener('click', function() {

            const driverId = document.getElementById('schedule_driver_id').value;

            const startDate = document.getElementById('scheduleStartDate').value;

            const endDate = document.getElementById('scheduleEndDate').value;

            loadDriverSchedule(driverId, startDate, endDate);

        });

    }

    

    // Photo preview

    document.getElementById('profile_photo').addEventListener('change', function() {

        const file = this.files[0];

        if (file) {

            const reader = new FileReader();

            reader.onload = function(e) {

                document.getElementById('photoPreview').src = e.target.result;

            }

            reader.readAsDataURL(file);

        }

    });

    

    // Add click handler for the camera icon button

    document.querySelector('label[for="profile_photo"]').addEventListener('click', function(e) {

        e.preventDefault();

        document.getElementById('profile_photo').click();

    });

    

    /**

     * Load all drivers

     */

    function loadDrivers() {

        const url = driverIncludeDeleted ? '/admin/api/drivers/all?includeDeleted=1' : '/admin/api/drivers/all';

        fetch(url)

            .then(response => {

                if (!response.ok) {

                    throw new Error(`HTTP error! Status: ${response.status}`);

                }

                return response.json();

            })

            .then(response => {

                if (response.success) {

                    if (typeof response.includeDeleted !== 'undefined') {

                        driverIncludeDeleted = !!response.includeDeleted;

                        const t = document.getElementById('toggleDriverTrash');

                        if (t) t.checked = driverIncludeDeleted;

                    }

                    renderDriversTable(response.data);

                } else {

                    showAlert('error', 'Error', response.message || 'Failed to load drivers');

                }

            })

            .catch((error) => {

                console.error('Error loading drivers:', error);

                showAlert('error', 'Error', 'Failed to connect to the server');

            });

    }

    

    /**

     * Load driver statistics

     */

    function loadDriverStatistics() {

        fetch('/admin/api/drivers/statistics')

            .then(response => {

                if (!response.ok) {

                    throw new Error(`HTTP error! Status: ${response.status}`);

                }

                return response.json();

            })

            .then(response => {

                if (response.success) {

                    updateStatistics(response.data);

                }

            })

            .catch(error => console.error('Error loading statistics:', error));

    }

    

    /**

     * Load most active drivers

     */

    function loadMostActiveDrivers() {

        fetch('/admin/api/drivers/most-active')

            .then(response => {

                if (!response.ok) {

                    throw new Error(`HTTP error! Status: ${response.status}`);

                }

                return response.json();

            })

            .then(response => {

                if (response.success) {

                    renderMostActiveDrivers(response.data);

                } else {

                    document.getElementById('mostActiveDriversList').innerHTML = '<li class="list-group-item text-center">No data available</li>';

                }

            })

            .catch((error) => {

                console.error('Error loading most active drivers:', error);

                document.getElementById('mostActiveDriversList').innerHTML = '<li class="list-group-item text-center">Failed to load data</li>';

            });

    }

    

    /**

     * Load drivers with expiring licenses

     */

    function loadExpiringLicenses() {

        fetch('/admin/api/drivers/expiring-licenses')

            .then(response => {

                if (!response.ok) {

                    throw new Error(`HTTP error! Status: ${response.status}`);

                }

                return response.json();

            })

            .then(response => {

                if (response.success) {

                    renderExpiringLicenses(response.data);

                } else {

                    document.getElementById('expiringLicensesList').innerHTML = '<li class="list-group-item text-center">No expiring licenses</li>';

                }

            })

            .catch((error) => {

                console.error('Error loading expiring licenses:', error);

                document.getElementById('expiringLicensesList').innerHTML = '<li class="list-group-item text-center">Failed to load data</li>';

            });

    }

    

    /**

     * Render drivers table

     */

    function renderDriversTable(drivers) {

        const tableBody = document.getElementById('driverTableBody');

        

        if (!drivers || drivers.length === 0) {

            tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-3">No drivers found</td></tr>';

            return;

        }

        

        let html = '';

        drivers.forEach(driver => {

            // Format license expiry date

            let expiryDate = driver.license_expiry ? new Date(driver.license_expiry) : null;

            let expiryFormatted = expiryDate ? expiryDate.toLocaleDateString() : 'N/A';

            

            // Check if license is expiring soon (within 30 days)

            let isExpiringSoon = false;

            if (expiryDate) {

                const today = new Date();

                const daysUntilExpiry = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));

                isExpiringSoon = daysUntilExpiry <= 30 && daysUntilExpiry >= 0;

            }

            

            // Status badge class

            let statusClass = '';

            switch (driver.status) {

                case 'Active':

                    statusClass = 'text-bg-success';

                    break;

                case 'Inactive':

                    statusClass = 'text-bg-secondary';

                    break;

                case 'On Leave':

                    statusClass = 'text-bg-warning';

                    break;

            }

            

            // Availability badge class

            let availabilityClass = '';

            switch (driver.availability) {

                case 'Available':

                    availabilityClass = 'text-bg-primary';

                    break;

                case 'Assigned':

                    availabilityClass = 'text-bg-success';

                    break;

            }

            

            html += `

                <tr>

                    <td>

                        ${driver.profile_photo 

                            ? `<img src="${driver.profile_photo}" alt="${driver.full_name}" class="driver-avatar">` 

                            : `<div class="driver-avatar-placeholder"><i class="bi bi-person"></i></div>`

                        }

                    </td>

                    <td>${driver.full_name}</td>

                    <td>${driver.license_number}</td>

                    <td>${driver.contact_number || 'N/A'}</td>

                    <td class="${isExpiringSoon ? 'license-expiring' : ''}">${expiryFormatted}</td>

                    <td><span class="status-badge badge ${statusClass}">${driver.status}</span></td>

                    <td><span class="availability-badge badge ${availabilityClass}">${driver.availability}</span></td>

                    <td>

                        <div class="actions-compact">

                            ${driverIncludeDeleted ? `

                                <button type="button" class="btn btn-sm btn-outline-success" onclick="restoreDriver(${driver.driver_id})">

                                    <i class="bi bi-arrow-counterclockwise"></i> Restore

                                </button>

                            ` : `

                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewSchedule(${driver.driver_id}, '${driver.full_name.replace(/'/g, "\'")}')">

                                    <i class="bi bi-calendar-week"></i> Schedule

                                </button>

                                <button type="button" class="btn btn-sm btn-outline-success" onclick="editDriver(${driver.driver_id})">

                                    <i class="bi bi-pencil"></i> Edit

                                </button>

                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(${driver.driver_id})">

                                    <i class="bi bi-trash"></i> Delete

                                </button>

                            `}

                        </div>

                    </td>

                </tr>

            `;

        });

        

        tableBody.innerHTML = html;

    }

    

    /**

     * Update statistics

     */

    function updateStatistics(stats) {

        document.getElementById('totalDriversCount').textContent = stats.total || 0;

        document.getElementById('activeDriversCount').textContent = stats.active || 0;

        document.getElementById('inactiveDriversCount').textContent = stats.inactive || 0;

        document.getElementById('onLeaveDriversCount').textContent = stats.on_leave || 0;

        document.getElementById('availableDriversCount').textContent = stats.available || 0;

        document.getElementById('assignedDriversCount').textContent = stats.assigned || 0;

    }

    

    /**

     * Render most active drivers

     */

    function renderMostActiveDrivers(drivers) {

        const listElement = document.getElementById('mostActiveDriversList');

        

        if (!drivers || drivers.length === 0) {

            listElement.innerHTML = '<li class="list-group-item text-center">No data available</li>';

            return;

        }

        

        let html = '';

        drivers.forEach(driver => {

            html += `

                <li class="list-group-item d-flex justify-content-between align-items-center">

                    <span>${driver.full_name}</span>

                    <span class="badge bg-success rounded-pill">${driver.trip_count} trips</span>

                </li>

            `;

        });

        

        listElement.innerHTML = html;

    }

    

    /**

     * Render expiring licenses

     */

    function renderExpiringLicenses(drivers) {

        const listElement = document.getElementById('expiringLicensesList');

        

        if (!drivers || drivers.length === 0) {

            listElement.innerHTML = '<li class="list-group-item text-center">No expiring licenses</li>';

            return;

        }

        

        let html = '';

        drivers.forEach(driver => {

            const expiryDate = new Date(driver.license_expiry);

            const today = new Date();

            const daysUntilExpiry = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));

            

            html += `

                <li class="list-group-item">

                    <div class="d-flex justify-content-between">

                        <span>${driver.full_name}</span>

                        <span class="license-expiring">${daysUntilExpiry} days</span>

                    </div>

                    <small class="text-muted">Expires: ${expiryDate.toLocaleDateString()}</small>

                </li>

            `;

        });

        

        listElement.innerHTML = html;

    }

    

    /**

     * Show add driver modal

     */

    function showAddDriverModal() {

        // Reset form

        document.getElementById('driverForm').reset();

        document.getElementById('driver_id').value = '';

        document.getElementById('photoPreview').src = '/public/images/icons/user-placeholder.png';

        document.getElementById('driverModalLabel').textContent = 'Add New Driver';

        

        // Set default values

        document.getElementById('status').value = 'Active';

        document.getElementById('availability').value = 'Available';

        document.getElementById('date_hired').value = new Date().toISOString().split('T')[0];

        

        driverModal.show();

    }

    

    /**

     * View driver schedule

     */

    function viewSchedule(driverId, driverName) {

        document.getElementById('schedule_driver_id').value = driverId;

        document.getElementById('scheduleModalLabel').textContent = `Schedule for ${driverName}`;

        

        // Set default date range (current month)

        const startDate = document.getElementById('scheduleStartDate').value;

        const endDate = document.getElementById('scheduleEndDate').value;

        

        // Load schedule data

        loadDriverSchedule(driverId, startDate, endDate);

        

        scheduleModal.show();

    }

    

    /**

     * Load driver schedule

     */

    function loadDriverSchedule(driverId, startDate, endDate) {

        const tableBody = document.getElementById('scheduleTableBody');

        tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-3">Loading schedule...</td></tr>';

        

        fetch(`/admin/api/drivers/schedule?id=${driverId}&start_date=${startDate}&end_date=${endDate}`)

            .then(response => {

                if (!response.ok) {

                    throw new Error(`HTTP error! Status: ${response.status}`);

                }

                return response.json();

            })

            .then(response => {

                if (response.success) {

                    renderScheduleTable(response.data);

                } else {

                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-3">Failed to load schedule</td></tr>';

                    showAlert('error', 'Error', response.message || 'Failed to load driver schedule');

                }

            })

            .catch((error) => {

                console.error('Error loading driver schedule:', error);

                tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-3">Failed to load schedule</td></tr>';

                showAlert('error', 'Error', 'Failed to connect to the server');

            });

    }

    

    /**

     * Render schedule table

     */

    function renderScheduleTable(schedules) {

        const tableBody = document.getElementById('scheduleTableBody');

        

        if (!schedules || schedules.length === 0) {

            tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-3">No scheduled trips found</td></tr>';

            return;

        }

        

        let html = '';

        schedules.forEach(schedule => {

            // Format dates

            const startDate = new Date(schedule.date_of_tour).toLocaleDateString();

            const endDate = new Date(schedule.end_of_tour).toLocaleDateString();

            

            // Format pickup time

            let pickupTime = 'N/A';

            if (schedule.pickup_time) {

                const time = new Date(`1970-01-01T${schedule.pickup_time}`);

                pickupTime = time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            }

            

            // Status badge class

            let statusClass = '';

            switch (schedule.status) {

                case 'Confirmed':

                    statusClass = 'bg-success';

                    break;

                case 'Processing':

                    statusClass = 'bg-warning';

                    break;

            }

            

            html += `

                <tr>

                    <td>${schedule.booking_id}</td>

                    <td>${schedule.company_name || `${schedule.first_name} ${schedule.last_name}`}</td>

                    <td>${schedule.destination}</td>

                    <td>${startDate} - ${endDate}</td>

                    <td>${pickupTime}</td>

                    <td class="d-none"><span class="badge ${statusClass}">${schedule.status}</span></td>

                </tr>

            `;

        });

        

        tableBody.innerHTML = html;

    }

    

    /**

     * Edit driver

     */

    function editDriver(driverId) {

        console.log('Editing driver with ID:', driverId);

        

        // Show loading state

        document.getElementById('driverModalLabel').textContent = 'Loading driver data...';

        driverModal.show();

        

        fetch(`/admin/api/drivers/get?id=${driverId}`)

            .then(response => {

                if (!response.ok) {

                    throw new Error(`HTTP error! Status: ${response.status}`);

                }

                return response.json();

            })

            .then(response => {

                if (response.success) {

                    const driver = response.data;

                    console.log('Driver data:', driver);

                    

                    // Populate form

                    document.getElementById('driver_id').value = driver.driver_id;

                    document.getElementById('full_name').value = driver.full_name;

                    document.getElementById('license_number').value = driver.license_number;

                    document.getElementById('contact_number').value = driver.contact_number || '';

                    document.getElementById('address').value = driver.address || '';

                    document.getElementById('status').value = driver.status || 'Active';

                    document.getElementById('availability').value = driver.availability || 'Available';

                    

                    // Handle dates - ensure they're in the correct format

                    if (driver.date_hired) {

                        document.getElementById('date_hired').value = formatDateForInput(driver.date_hired);

                    }

                    

                    if (driver.license_expiry) {

                        document.getElementById('license_expiry').value = formatDateForInput(driver.license_expiry);

                    }

                    

                    document.getElementById('notes').value = driver.notes || '';

                    

                    // Set photo preview

                    if (driver.profile_photo) {

                        document.getElementById('photoPreview').src = driver.profile_photo;

                    } else {

                        document.getElementById('photoPreview').src = '/public/images/icons/user-placeholder.png';

                    }

                    

                    document.getElementById('driverModalLabel').textContent = 'Edit Driver';

                } else {

                    driverModal.hide();

                    showAlert('error', 'Error', response.message || 'Failed to load driver details');

                }

            })

            .catch((error) => {

                console.error('Error fetching driver:', error);

                driverModal.hide();

                showAlert('error', 'Error', 'Failed to connect to the server. Please check the console for details.');

            });

    }

    

    /**

     * Format date for input field (YYYY-MM-DD)

     */

    function formatDateForInput(dateString) {

        const date = new Date(dateString);

        if (isNaN(date.getTime())) {

            return '';

        }

        return date.toISOString().split('T')[0];

    }

    

    /**

     * Save driver (add or update)

     */

    function saveDriver() {

        // Validate form

        if (!validateDriverForm()) {

            return;

        }

        

        const driverId = document.getElementById('driver_id').value;

        const isNewDriver = !driverId;

        const formData = new FormData(document.getElementById('driverForm'));

        



        saveDriverData(formData, isNewDriver);

    }

    

    /**

     * Save driver data to server

     */

    function saveDriverData(formData, isNewDriver) {

        fetch(isNewDriver ? '/admin/api/drivers/add' : '/admin/api/drivers/update', {

            method: 'POST',

            body: formData

        })

            .then(response => {

                if (!response.ok) {

                    throw new Error(`HTTP error! Status: ${response.status}`);

                }

                return response.json();

            })

            .then(response => {

                if (response.success) {

                    driverModal.hide();

                    showAlert('success', 'Success', isNewDriver ? 'Driver added successfully' : 'Driver updated successfully');

                    loadDrivers();

                    loadDriverStatistics();

                    loadMostActiveDrivers();

                    loadExpiringLicenses();

                } else {

                    showAlert('error', 'Error', response.message || 'Failed to save driver');

                }

            })

            .catch((error) => {

                console.error('Error saving driver:', error);

                showAlert('error', 'Error', 'Failed to connect to the server');

            });

    }

    

    /**

     * Confirm delete driver

     */

    function confirmDelete(driverId) {

        Swal.fire({

            title: 'Delete Driver?',

            text: 'Are you sure you want to delete this driver?',

            icon: 'warning',

            showCancelButton: true,

            confirmButtonText: 'Yes, delete it!',

            cancelButtonText: 'Cancel'

        }).then((result) => {

            if (result.isConfirmed) {

                deleteDriver(driverId);

            }

        });

    }

    

    /**

     * Delete driver

     */

    function deleteDriver(driverId) {

        const formData = new FormData();

        formData.append('driver_id', driverId);

        

        fetch('/admin/api/drivers/delete', {

            method: 'POST',

            body: formData

        })

            .then(response => {

                if (!response.ok) {

                    throw new Error(`HTTP error! Status: ${response.status}`);

                }

                return response.json();

            })

            .then(response => {

                if (response.success) {

                    showAlert('success', 'Success', 'Driver deleted successfully');

                    loadDrivers();

                    loadDriverStatistics();

                    loadMostActiveDrivers();

                    loadExpiringLicenses();

                } else {

                    showAlert('error', 'Error', response.message || 'Failed to delete driver');

                }

            })

            .catch((error) => {

                console.error('Error deleting driver:', error);

                showAlert('error', 'Error', 'Failed to connect to the server');

            });

    }

    

    /**

     * Validate driver form

     */

    function validateDriverForm() {

        const fullName = document.getElementById('full_name').value.trim();

        const licenseNumber = document.getElementById('license_number').value.trim();

        const contactNumber = document.getElementById('contact_number').value.trim();

        

        if (!fullName) {

            showAlert('error', 'Validation Error', 'Full name is required');

            return false;

        }

        

        if (!licenseNumber) {

            showAlert('error', 'Validation Error', 'License number is required');

            return false;

        }

        

        if (!contactNumber) {

            showAlert('error', 'Validation Error', 'Contact number is required');

            return false;

        }

        

        return true;

    }

    

    /**

     * Show alert

     */

    function showAlert(icon, title, text) {

        Swal.fire({

            icon: icon,

            title: title,

            text: text,

            timer: 3000,

            timerProgressBar: true

        });

    }

    // Hook trash toggle
    const driverTrashToggle = document.getElementById('toggleDriverTrash');
    if (driverTrashToggle) {
        driverTrashToggle.addEventListener('change', function() {
            driverIncludeDeleted = this.checked;
            loadDrivers();
        });
    }

    // Expose restore function globally
    window.restoreDriver = function(driverId) {
        fetch('/admin/api/drivers/restore', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ driver_id: driverId })
        })
        .then(r => r.json())
        .then(resp => {
            if (resp.success) {
                showAlert('success', 'Restored', 'Driver restored successfully');
                loadDrivers();
                loadDriverStatistics();
                loadMostActiveDrivers();
                loadExpiringLicenses();
            } else {
                showAlert('error', 'Error', resp.message || 'Failed to restore driver');
            }
        })
        .catch(err => {
            console.error('Error restoring driver:', err);
            showAlert('error', 'Error', 'Failed to connect to the server');
        });
    }

});

