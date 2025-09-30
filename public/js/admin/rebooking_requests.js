// const confirmBookingModal = new bootstrap.Modal(document.getElementById("confirmRebookingModal"));

// const rejectBookingModal = new bootstrap.Modal(document.getElementById("rejectRebookingModal"));

// const messageModal = new bootstrap.Modal(document.getElementById("messageModal"));



// const messageTitle = document.getElementById("messageTitle");

// const messageBody = document.getElementById("messageBody");





document.addEventListener('DOMContentLoaded', async function () {

    // Initial data load - get all requests first to check counts

    const initialAll = await getRebookingRequestsPaginated('All', 'asc', 'booking_id', 1, getSelectedLimit());

    // Update stats counters (based on current page items only initially)
    updateStatsCounters(initialAll.items || []);

    

    // Check if there are any pending requests

    const pendingRequests = (initialAll.items || []).filter(r => r.status === 'Pending');

    

    // If there are pending requests, show them by default, otherwise show all

    const defaultStatus = pendingRequests.length > 0 ? 'Pending' : 'All';

    

    // Set the select value

    document.getElementById('statusSelect').value = defaultStatus;

    

    // Update active filter button

    document.querySelectorAll('.quick-filter').forEach(btn => {

        btn.classList.remove('active');

        if (btn.getAttribute('data-status') === defaultStatus) {

            btn.classList.add('active');

        }

    });

    

    // Render the appropriate requests

    const initialStatusData = await getRebookingRequestsPaginated(defaultStatus, 'asc', 'booking_id', 1, getSelectedLimit());
    renderRebookingRequests(initialStatusData.items || []);
    renderPaginationControls(initialStatusData.pagination);
    updateRecordInfo(initialStatusData.pagination);

    

    // Set up event listeners for search and filters

    setupEventListeners();

}); 



function setupEventListeners() {
    // Rows per page selector
    const rowsSelect = document.getElementById('rowsPerPage');
    if (rowsSelect) {
        rowsSelect.addEventListener('change', async function () {
            const status = document.getElementById('statusSelect').value;
            const limit = getSelectedLimit();
            const data = await getRebookingRequestsPaginated(status, 'asc', 'booking_id', 1, limit);
            renderRebookingRequests(data.items || []);
            renderPaginationControls(data.pagination);
            updateRecordInfo(data.pagination);
        });
    }

    // Status select filter

    document.getElementById('statusSelect').addEventListener('change', async function () {

        const status = this.value;

        console.log(status);

        const data = await getRebookingRequestsPaginated(status, 'asc', 'client_name', 1, getSelectedLimit());
        renderRebookingRequests(data.items || []);
        renderPaginationControls(data.pagination);
        updateRecordInfo(data.pagination);

        

        // Update active filter button

        document.querySelectorAll('.quick-filter').forEach(btn => {

            btn.classList.remove('active');

            if (btn.getAttribute('data-status') === status) {

                btn.classList.add('active');

            }

        });

        

        // Show/hide no results message

        document.getElementById('noResultsFound').style.display = 
            (!data.items || data.items.length === 0) ? 'block' : 'none';

    });



    // Sort functionality

    document.querySelectorAll('.sort').forEach(button => {

        button.style.cursor = 'pointer';

        

        button.addEventListener('click', async function () {

            // Clear active class from all headers

            document.querySelectorAll('.sort').forEach(header => {

                // Reset sort icons

                const icon = header.querySelector('.sort-icon');

                if (icon) {

                    icon.textContent = '↑';

                }

            });

            

            const status = document.getElementById('statusSelect').value;

            const column = this.getAttribute('data-column');

            const order = this.getAttribute('data-order');



            // Update sort icon

            const sortIcon = this.querySelector('.sort-icon');

            if (sortIcon) {

                sortIcon.textContent = order === 'asc' ? '↑' : '↓';

            }



            const data = await getRebookingRequestsPaginated(status, order, column, 1, getSelectedLimit());
            renderRebookingRequests(data.items || []);
            renderPaginationControls(data.pagination);
            updateRecordInfo(data.pagination);



            // Toggle sort order for next click

            this.setAttribute('data-order', order === 'asc' ? 'desc' : 'asc');

        });

    });

    

    // Quick filter buttons

    document.querySelectorAll('.quick-filter').forEach(button => {

        button.addEventListener('click', async function() {

            // Remove active class from all buttons

            document.querySelectorAll('.quick-filter').forEach(btn => {

                btn.classList.remove('active');

            });

            

            // Add active class to clicked button

            this.classList.add('active');

            

            const status = this.getAttribute('data-status');

            document.getElementById('statusSelect').value = status;

            

            const data = await getRebookingRequestsPaginated(status, 'asc', 'client_name', 1, getSelectedLimit());
            renderRebookingRequests(data.items || []);
            renderPaginationControls(data.pagination);
            updateRecordInfo(data.pagination);

            

            // Show/hide no results message

            document.getElementById('noResultsFound').style.display = 
                (!data.items || data.items.length === 0) ? 'block' : 'none';

        });

    });

    

    // Search functionality

    const searchInput = document.getElementById('searchRequests');
    

    if (searchInput) {
        const debounce = (fn, delay = 300) => {
            let t;
            return (...args) => {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), delay);
            };
        };
        const onSearch = debounce(async () => {
            await performSearch();
        }, 300);
        searchInput.addEventListener('input', onSearch);
        searchInput.addEventListener('keyup', async function(event) {
            if (event.key === 'Enter') {
                await performSearch();
            }
        });
    }

    

    // Reset filters button

    const resetFiltersBtn = document.getElementById('resetFilters');

    if (resetFiltersBtn) {

        resetFiltersBtn.addEventListener('click', async function() {

            // Reset search input

            if (searchInput) searchInput.value = '';

            

            // Reset status filter

            document.getElementById('statusSelect').value = 'All';

            

            // Reset active filter button

            document.querySelectorAll('.quick-filter').forEach(btn => {

                btn.classList.remove('active');

                if (btn.getAttribute('data-status') === 'All') {

                    btn.classList.add('active');

                }

            });

            

            // Load all requests

            const data = await getRebookingRequestsPaginated('All', 'asc', 'booking_id', 1, getSelectedLimit());
            renderRebookingRequests(data.items || []);
            renderPaginationControls(data.pagination);
            updateRecordInfo(data.pagination);
            updateStatsCounters(data.items || []);

            

            // Hide no results message

            document.getElementById('noResultsFound').style.display = 'none';

        });

    }

    

    // Refresh button

    const refreshBtn = document.getElementById('refreshRequests');

    if (refreshBtn) {

        refreshBtn.addEventListener('click', async function() {

            const status = document.getElementById('statusSelect').value;

            const data = await getRebookingRequestsPaginated(status, 'asc', 'booking_id', 1, getSelectedLimit());
            renderRebookingRequests(data.items || []);
            renderPaginationControls(data.pagination);
            updateRecordInfo(data.pagination);
            updateStatsCounters(data.items || []);

            

            // Show success toast

            Swal.fire({

                toast: true,

                position: 'top-end',

                icon: 'success',

                title: 'Data refreshed',

                showConfirmButton: false,

                timer: 1500

            });

        });

    }

    

    // Export buttons

    const exportPDFBtn = document.getElementById('exportPDF');

    const exportCSVBtn = document.getElementById('exportCSV');

    

    if (exportPDFBtn) {

        exportPDFBtn.addEventListener('click', function() {

            // PDF export functionality would be implemented here

            Swal.fire({

                icon: 'info',

                title: 'Export to PDF',

                text: 'This feature will be implemented soon!',

                confirmButtonColor: '#198754'

            });

        });

    }

    

    if (exportCSVBtn) {

        exportCSVBtn.addEventListener('click', function() {

            // CSV export functionality would be implemented here

            Swal.fire({

                icon: 'info',

                title: 'Export to CSV',

                text: 'This feature will be implemented soon!',

                confirmButtonColor: '#198754'

            });

        });

    }
}



// Function to perform search

async function performSearch() {

    const searchTerm = document.getElementById('searchRequests').value.trim();

    const status = document.getElementById('statusSelect').value;

    

    if (!searchTerm) {

        // If search term is empty, just load based on status

        const data = await getRebookingRequestsPaginated(status, 'asc', 'client_name', 1, getSelectedLimit());
        renderRebookingRequests(data.items || []);
        renderPaginationControls(data.pagination);
        updateRecordInfo(data.pagination);

        return;

    }

    

    try {

        // Get all requests for the current status

        const data = await getRebookingRequestsPaginated(status, 'asc', 'client_name', 1, getSelectedLimit());

        

        // Filter requests based on search term (client name, email, or destination)

        const filteredRequests = (data.items || []).filter(request => {

            const searchFields = [

                request.client_name,

                request.email,

                request.destination,

                request.contact_number

            ].filter(Boolean).map(field => field.toLowerCase());

            

            return searchFields.some(field => field.includes(searchTerm.toLowerCase()));

        });

        

        renderRebookingRequests(filteredRequests);

        

        // Show/hide no results message

        document.getElementById('noResultsFound').style.display = 

            (!filteredRequests || filteredRequests.length === 0) ? 'block' : 'none';

    } catch (error) {

        console.error('Search error:', error);

    }

}



// Update stats counters based on rebooking requests data

function updateStatsCounters(requests) {

    // Calculate counts

    const totalCount = requests.length;

    const pendingCount = requests.filter(r => r.status === 'Pending').length;

    const confirmedCount = requests.filter(r => r.status === 'Confirmed').length;

    const rejectedCount = requests.filter(r => r.status === 'Rejected').length;

    

    // Update the UI

    document.getElementById('totalRequestsCount').textContent = totalCount;

    document.getElementById('pendingRequestsCount').textContent = pendingCount;

    document.getElementById('confirmedRequestsCount').textContent = confirmedCount;

    document.getElementById('rejectedRequestsCount').textContent = rejectedCount;

}



function formatDate(date) {

    return new Date(date).toLocaleDateString("en-US", {

        year: 'numeric',

        month: 'short',

        day: 'numeric'

    });

}



async function getRebookingRequestsPaginated(status, order, column, page = 1, limit = 10) {

    try {

        const response = await fetch("/admin/get-rebooking-requests", {

            method: 'POST', 

            headers: { 'Content-Type': 'application/json' },

            body: JSON.stringify({ status, order, column, page, limit })

        });



        const data = await response.json();

        console.log('Rebooking requests data:', data);

        if (data.success) {

            return {
                items: data.requests,
                pagination: data.pagination || { total: (data.requests || []).length, totalPages: 1, currentPage: page, limit }
            };

        }

    } catch (error) {

        console.error('Fetch error:', error);

        return { items: [], pagination: { total: 0, totalPages: 1, currentPage: page, limit } };

    }

}



async function renderRebookingRequests(requests) {

    console.log(requests);

    const tbody = document.getElementById('tableBody');

    tbody.innerHTML = '';

    

    // Show/hide no results message

    document.getElementById('noResultsFound').style.display = 

        (!requests || requests.length === 0) ? 'block' : 'none';

    

    if (!requests || requests.length === 0) {

        return;

    }



    requests.forEach(request => {

        const row = document.createElement('tr');

        

        const  bookingIdCell = document.createElement('td');

        const clientNameCell = document.createElement('td');

        const clientContactCell = document.createElement('td');

        const clientEmailCell = document.createElement("td");

        const dateOfTourCell = document.createElement('td');

        const statusCell = document.createElement('td');



        bookingIdCell.textContent = request.request_id;

        clientNameCell.textContent = request.client_name;

        clientNameCell.style.maxWidth = "120px";

        clientNameCell.style.overflow = "hidden";

        clientNameCell.style.textOverflow = "ellipsis";

        clientNameCell.style.whiteSpace = "nowrap";

        clientNameCell.title = request.client_name;

        

        clientContactCell.textContent = request.contact_number;

        clientContactCell.style.maxWidth = "120px";

        clientContactCell.style.overflow = "hidden";

        clientContactCell.style.textOverflow = "ellipsis";

        clientContactCell.style.whiteSpace = "nowrap";

        

        clientEmailCell.textContent = request.email;

        clientEmailCell.style.maxWidth = "150px";

        clientEmailCell.style.overflow = "hidden";

        clientEmailCell.style.textOverflow = "ellipsis";

        clientEmailCell.style.whiteSpace = "nowrap";

        clientEmailCell.title = request.email;

        

        dateOfTourCell.textContent = formatDate(request.date_of_tour);

        

        // Style status cell based on status value

        statusCell.textContent = request.status;

        const statusClasses = {

            'Pending': 'text-warning fw-bold',

            'Confirmed': 'text-success fw-bold',

            'Rejected': 'text-danger fw-bold'

        };

        statusCell.className = statusClasses[request.status] || '';



        row.append(bookingIdCell, clientNameCell, clientContactCell, clientEmailCell, dateOfTourCell, statusCell, createActionButtons(request));

        tbody.appendChild(row);

    });

}



function createActionButtons(request) {

    const actionCell = document.createElement('td');

    const buttonGroup = document.createElement('div');

    buttonGroup.classList.add('d-flex', 'gap-2', 'justify-content-start');   



    // View details button (always present)

    const viewButton = createButton('btn-outline-primary', 'bi-info-circle', 'Details', function() {

        showBookingDetails(request.booking_id, request.request_id);

    });

    

    buttonGroup.appendChild(viewButton);



    // Add conditional buttons based on request status

    if (request.status === 'Pending') {

        // Confirm button

        const confirmButton = createButton('btn-outline-success', 'bi-check-circle', 'Confirm', function() {

            const bookingId = request.booking_id;

            

            Swal.fire({

                title: 'Apply Discount?',

                icon: 'question',

                html: `

                    <p>Would you like to apply a discount to this booking?</p>

                    <div class="form-check mb-3">

                        <input type="radio" class="form-check-input" id="noDiscount" name="discountOption" value="none" checked>

                        <label class="form-check-label" for="noDiscount">No discount</label>

                    </div>

                    <div class="form-check mb-3">

                        <input type="radio" class="form-check-input" id="percentageDiscount" name="discountOption" value="percentage">

                        <label class="form-check-label" for="percentageDiscount">Percentage discount</label>

                    </div>

                    <div class="form-check mb-3">

                        <input type="radio" class="form-check-input" id="flatDiscount" name="discountOption" value="flat">

                        <label class="form-check-label" for="flatDiscount">Flat amount discount</label>

                    </div>

                    <div id="discountInputContainer" style="display: none; margin-top: 15px;">

                        <div id="percentageInput" style="display: none;">

                            <label for="percentageValue">Discount percentage (0-100)</label>

                            <div class="input-group">

                                <input type="number" id="percentageValue" class="form-control" min="0" max="100" step="0.01" value="0">

                                <span class="input-group-text">%</span>

                            </div>

                        </div>

                        <div id="flatInput" style="display: none;">

                            <label for="flatValue">Discount amount (PHP)</label>

                            <div class="input-group">

                                <span class="input-group-text">₱</span>

                                <input type="number" id="flatValue" class="form-control" min="0" step="0.01" value="0">

                            </div>

                        </div>

                    </div>

                `,

                showCancelButton: true,

                confirmButtonText: 'Confirm Booking',

                cancelButtonText: 'Cancel',

                confirmButtonColor: '#28a745',

                cancelButtonColor: '#6c757d',

                didOpen: () => {

                    // Show/hide discount inputs based on selection

                    const discountOptions = document.getElementsByName('discountOption');

                    const discountInputContainer = document.getElementById('discountInputContainer');

                    const percentageInput = document.getElementById('percentageInput');

                    const flatInput = document.getElementById('flatInput');

                    

                    discountOptions.forEach(option => {

                        option.addEventListener('change', function() {

                            if (this.value === 'none') {

                                discountInputContainer.style.display = 'none';

                            } else {

                                discountInputContainer.style.display = 'block';

                                if (this.value === 'percentage') {

                                    percentageInput.style.display = 'block';

                                    flatInput.style.display = 'none';

                                } else if (this.value === 'flat') {

                                    percentageInput.style.display = 'none';

                                    flatInput.style.display = 'block';

                                }

                            }

                        });

                    });

                },

                preConfirm: () => {

                    const selectedOption = document.querySelector('input[name="discountOption"]:checked').value;

                    

                    if (selectedOption === 'none') {

                        return { discountValue: null, discountType: null };

                    } else if (selectedOption === 'percentage') {

                        const percentageValue = document.getElementById('percentageValue').value;

                        const numValue = parseFloat(percentageValue);

                        

                        if (isNaN(numValue) || numValue < 0 || numValue > 100) {

                            Swal.showValidationMessage('Percentage must be between 0 and 100');

                            return false;

                        }

                        

                        return { discountValue: numValue, discountType: 'percentage' };

                    } else if (selectedOption === 'flat') {

                        const flatValue = document.getElementById('flatValue').value;

                        const numValue = parseFloat(flatValue);

                        

                        if (isNaN(numValue) || numValue < 0) {

                            Swal.showValidationMessage('Flat amount must be greater than or equal to 0');

                            return false;

                        }

                        

                        return { discountValue: numValue, discountType: 'flat' };

                    }

                }

            }).then((result) => {

                if (result.isConfirmed) {

                    confirmBookingRequest(

                        bookingId, 

                        result.value.discountValue, 

                        result.value.discountType

                    );

                }

            });

        });

        

        // Reject button

        const rejectButton = createButton('btn-outline-danger', 'bi-x-circle', 'Reject', function() {

            const bookingId = request.booking_id;

            const userId = request.user_id;

            

            Swal.fire({

                title: 'Reject Booking?',

                text: 'Are you sure you want to reject this booking request?',

                input: 'textarea',

                inputLabel: 'Reason',

                inputPlaceholder: 'Kindly provide the reason here.',

                icon: 'warning',

                showCancelButton: true,

                confirmButtonColor: '#dc3545',

                cancelButtonColor: '#6c757d',

                confirmButtonText: 'Reject',

                cancelButtonText: 'Cancel',

                inputValidator: (value) => {

                    if (!value) {

                        return 'Please provide a reason for rejection!';

                    }

                }

            }).then((result) => {

                if (result.isConfirmed) {

                    rejectBookingRequest(bookingId, result.value, userId);

                }

            });

        });

        

        buttonGroup.appendChild(confirmButton);

        buttonGroup.appendChild(rejectButton);

    }



    actionCell.appendChild(buttonGroup);

    return actionCell;

}



// Helper function to create buttons with consistent styling

function createButton(btnClass, iconClass, text, clickHandler) {

    const button = document.createElement('button');

    button.className = `btn ${btnClass} btn-sm d-flex align-items-center gap-1`;

    

    const icon = document.createElement('i');

    icon.className = `bi ${iconClass}`;

    button.appendChild(icon);

    

    const buttonText = document.createTextNode(` ${text}`);

    button.appendChild(buttonText);

    

    if (clickHandler) {

        button.addEventListener('click', clickHandler);

    }

    

    return button;

}



function formatFieldName(fieldName) {

    if (!fieldName) return '';

    return fieldName

        .split('_')

        .map(word => word.charAt(0).toUpperCase() + word.slice(1))

        .join(' ');

}



function formatValue(value) {

    if (value === null || value === undefined) {

        return '<span class="text-muted fst-italic">NULL</span>';

    }

    

    if (value === '') {

        return '<span class="text-muted fst-italic">Empty</span>';

    }

    

    if (typeof value === 'object') {

        return '<pre class="small mb-0">' + JSON.stringify(value, null, 2) + '</pre>';

    }

    

    if (typeof value === 'boolean') {

        return value ? '<span class="text-success">Yes</span>' : '<span class="text-danger">No</span>';

    }

    

    return value.toString();

}



// Function to show booking details in a modal

async function showBookingDetails(bookingId, requestId = null) {

    const auditDetails = await getBookingAuditDetails(bookingId, requestId);

    console.log("Audit details: ", auditDetails);



    // Get full booking details including stops 

    const booking = await getBookingDetails(bookingId);

    

    if (!booking || !auditDetails) return;



    const bookingDetailsContent = document.getElementById('bookingDetailsContent');

    console.log("booking details: ", booking);

    // Get status color classes

    const statusColors = {

        'Pending': 'warning',

        'Confirmed': 'success',

        'Canceled': 'danger',

        'Rejected': 'secondary',

        'Completed': 'info'

    };

    

    // Merge updated values from audit trail so modal reflects proposed changes
    const displayBooking = Object.assign({}, booking, auditDetails && auditDetails.new_values ? auditDetails.new_values : {});

    const statusColor = statusColors[displayBooking.status] || 'secondary';

    

    bookingDetailsContent.innerHTML = `

        <div class="booking-detail-section mb-3">

            <h6 class="border-bottom pb-2"><i class="bi bi-geo-alt me-2"></i>Booking Information</h6>

            <div class="row">

                <div class="col-md-6">

                    <p><strong>Booking ID:</strong> #${displayBooking.booking_id}</p>

                    <p><strong>Booking Date:</strong> ${formatDate(displayBooking.booked_at)}</p>

                    <p><strong>Status:</strong> <span class="badge bg-${statusColor}">${displayBooking.status}</span></p>

                </div>

                <div class="col-md-6">

                    <p><strong>Client Name:</strong> ${displayBooking.client_name || 'N/A'}</p>

                    <p><strong>Email:</strong> ${displayBooking.email || 'N/A'}</p>

                    <p><strong>Phone:</strong> ${displayBooking.contact_number || 'N/A'}</p>

                </div>

            </div>

        </div>

        <div class="booking-detail-section mb-4">

            <h6 class="border-bottom pb-2"><i class="bi bi-geo-alt me-2"></i>Trip Details</h6>

            <div class="row">

                <div class="col-md-6">

                    <p class="mb-2"><strong>Pickup Point:</strong> ${displayBooking.pickup_point || 'N/A'}</p>

                    <p class="mb-2"><strong>Destination:</strong> 

                        ${displayBooking.stops && displayBooking.stops.length > 0 ? 

                            `${displayBooking.stops.map(stop => 

                                `<span>${stop.location}</span>`

                            ).join('<i class="bi bi-arrow-right mx-1 text-danger"></i>')} 

                            <i class="bi bi-arrow-right mx-1 text-danger"></i>` 

                        : ''}

                        <span>${displayBooking.destination}</span>

                    </p>

                </div>

                <div class="col-md-6">

                    <p class="mb-2"><strong>Tour Date:</strong> ${formatDate(displayBooking.date_of_tour)}${displayBooking.end_of_tour ? ` to ${formatDate(displayBooking.end_of_tour)}` : ''}</p>

                    <p class="mb-2"><strong>Duration:</strong> ${displayBooking.number_of_days} day${displayBooking.number_of_days > 1 ? 's' : ''}</p>

                    <p class="mb-2"><strong>Number of Buses:</strong> ${displayBooking.number_of_buses}</p>

                </div>

            </div>

        </div>

        

        ${['Paid', 'Partially Paid'].includes(displayBooking.payment_status) ? `

            <div class="booking-detail-section mb-3">

                <h6 class="border-bottom pb-2"><i class="bi bi-cash-coin me-2"></i>Payment Information</h6>

                <div class="row">

                    <div class="col-md-6">

                        <p><strong>Total Cost:</strong> ₱${parseFloat(displayBooking.total_cost).toLocaleString('en-PH')}</p>

                        <p><strong>Payment Status:</strong> <span class="badge bg-${displayBooking.payment_status === 'Paid' ? 'success' : displayBooking.payment_status === 'Partially Paid' ? 'warning' : 'danger'}">${displayBooking.payment_status}</span></p>

                    </div>

                    <div class="col-md-6">

                        <p><strong>Payment Date:</strong> ${displayBooking.payments && displayBooking.payments.length > 0 ? formatDate(displayBooking.payments[0].payment_date) : 'No payments yet'}</p>

                        <p><strong>Payment Method:</strong> ${displayBooking.payments && displayBooking.payments.length > 0 ? displayBooking.payments[0].payment_method : 'N/A'}</p>

                    </div>

                </div>

            </div>

        ` : ``}



        <div class="booking-detail-section mb-3">

            <h6 class="border-bottom pb-2"><i class="bi bi-arrow-repeat  me-2"></i>Changed Information</h6>

            <table class="table table-bordered">

                <thead>

                    <th>Field</th>

                    <th>Old Value</th>

                    <th>New Value</th>

                </thead>

                <tbody id="changesTable">

                </tbody>

            </table>

        </div>

        

        <div class="booking-detail-section mb-2">

            <h6 class="text-success mb-3"><i class="bi bi-list-check me-2"></i>Actions</h6>

            <div class="d-flex flex-wrap gap-2">

                ${displayBooking.status === "Pending" ? `

                    <button class="btn btn-sm btn-outline-success confirm-booking-modal" data-booking-id="${displayBooking.booking_id}">

                        <i class="bi bi-check-circle"></i> Confirm Booking

                    </button>

                    <button class="btn btn-sm btn-outline-danger reject-booking-modal" data-booking-id="${displayBooking.booking_id}" data-user-id="${displayBooking.user_id}">

                        <i class="bi bi-x-circle"></i> Reject Booking

                    </button>

                ` : ''}

                

                <button class="btn btn-sm btn-outline-primary view-invoice" data-booking-id="${displayBooking.booking_id}">

                    <i class="bi bi-file-earmark-text"></i> Invoice

                </button>

                <button class="btn btn-sm btn-outline-success view-contract" data-booking-id="${displayBooking.booking_id}">

                    <i class="bi bi-file-earmark-text"></i> Contract

                </button>

            </div>

        </div>

    `;



    const changesTable = bookingDetailsContent.querySelector('#changesTable');



    for (const key in auditDetails.new_values) {

        if (JSON.stringify(auditDetails.old_values[key]) == JSON.stringify(auditDetails.new_values[key])) continue; // Skip unchanged values

        

        if (key === 'booking_costs' || key === 'trip_distances' || key === 'addresses' || key === 'balance') continue; // Skip further processing for booking costs 

        

        if (key === 'date_of_tour' || key === 'end_of_tour') {

            // Format date fields

            const row = document.createElement('tr');

            row.innerHTML = `

                <td class="fw-bold">${formatFieldName(key)}</td>

                <td><span class="text-danger">${formatDate(auditDetails.old_values[key])}</span></td>

                <td><span class="text-success">${formatDate(auditDetails.new_values[key])}</span></td>

            `; 

            changesTable.append(row);

            continue;

        }



        if (key === 'stops') {

            if (!Array.isArray(auditDetails.new_values[key]) || !Array.isArray(auditDetails.old_values[key])) continue; // Skip if stops are not arrays

        

            for (const stop of auditDetails.new_values[key]) {

                const oldStop = auditDetails.old_values[key].find(s => s.booking_stops_id === stop.booking_stops_id);

                if (oldStop && JSON.stringify(oldStop.location) == JSON.stringify(stop.location)) {

                    continue; // Skip unchanged stops

                }

                

                const row = $("<tr>");

                row.append(`<td class="fw-bold">${formatFieldName(key)} (Stop ID: ${stop.booking_stops_id})</td>`);

                row.append(`<td><span class="text-danger">${formatValue(oldStop.location || 'N/A')}</span></td>`);

                row.append(`<td><span class="text-success">${formatValue(stop.location)}</span></td>`);

                changesTable.append(row);

                hasChanges = true;

            }

            continue;

        }



        const row = document.createElement('tr');

        row.innerHTML = `

            <td class="fw-bold">${formatFieldName(key)}</td>

            <td><span class="text-danger">${formatValue(auditDetails.old_values[key])}</span></td>

            <td><span class="text-success">${formatValue(auditDetails.new_values[key])}</span></td>

        `; 

        changesTable.append(row);

    }

    

    // Add event listeners to action buttons

    const confirmBtn = bookingDetailsContent.querySelector('.confirm-booking-modal');

    if (confirmBtn) {

        confirmBtn.addEventListener('click', function() {

            const bookingId = this.getAttribute('data-booking-id');

            

            // Close the modal before showing SweetAlert

            bootstrap.Modal.getInstance(document.getElementById('bookingDetailsModal')).hide();

            

            Swal.fire({

                title: 'Apply Discount?',

                icon: 'question',

                html: `

                    <p>Would you like to apply a discount to this booking?</p>

                    <div class="form-check mb-3">

                        <input type="radio" class="form-check-input" id="noDiscount" name="discountOption" value="none" checked>

                        <label class="form-check-label" for="noDiscount">No discount</label>

                    </div>

                    <div class="form-check mb-3">

                        <input type="radio" class="form-check-input" id="percentageDiscount" name="discountOption" value="percentage">

                        <label class="form-check-label" for="percentageDiscount">Percentage discount</label>

                    </div>

                    <div class="form-check mb-3">

                        <input type="radio" class="form-check-input" id="flatDiscount" name="discountOption" value="flat">

                        <label class="form-check-label" for="flatDiscount">Flat amount discount</label>

                    </div>

                    <div id="discountInputContainer" style="display: none; margin-top: 15px;">

                        <div id="percentageInput" style="display: none;">

                            <label for="percentageValue">Discount percentage (0-100)</label>

                            <div class="input-group">

                                <input type="number" id="percentageValue" class="form-control" min="0" max="100" step="0.01" value="0">

                                <span class="input-group-text">%</span>

                            </div>

                        </div>

                        <div id="flatInput" style="display: none;">

                            <label for="flatValue">Discount amount (PHP)</label>

                            <div class="input-group">

                                <span class="input-group-text">₱</span>

                                <input type="number" id="flatValue" class="form-control" min="0" step="0.01" value="0">

                            </div>

                        </div>

                    </div>

                `,

                showCancelButton: true,

                confirmButtonText: 'Confirm Booking',

                cancelButtonText: 'Cancel',

                confirmButtonColor: '#28a745',

                cancelButtonColor: '#6c757d',

                didOpen: () => {

                    // Show/hide discount inputs based on selection

                    const discountOptions = document.getElementsByName('discountOption');

                    const discountInputContainer = document.getElementById('discountInputContainer');

                    const percentageInput = document.getElementById('percentageInput');

                    const flatInput = document.getElementById('flatInput');

                    

                    discountOptions.forEach(option => {

                        option.addEventListener('change', function() {

                            if (this.value === 'none') {

                                discountInputContainer.style.display = 'none';

                            } else {

                                discountInputContainer.style.display = 'block';

                                if (this.value === 'percentage') {

                                    percentageInput.style.display = 'block';

                                    flatInput.style.display = 'none';

                                } else if (this.value === 'flat') {

                                    percentageInput.style.display = 'none';

                                    flatInput.style.display = 'block';

                                }

                            }

                        });

                    });

                },

                preConfirm: () => {

                    const selectedOption = document.querySelector('input[name="discountOption"]:checked').value;

                    

                    if (selectedOption === 'none') {

                        return { discountValue: null, discountType: null };

                    } else if (selectedOption === 'percentage') {

                        const percentageValue = document.getElementById('percentageValue').value;

                        const numValue = parseFloat(percentageValue);

                        

                        if (isNaN(numValue) || numValue < 0 || numValue > 100) {

                            Swal.showValidationMessage('Percentage must be between 0 and 100');

                            return false;

                        }

                        

                        return { discountValue: numValue, discountType: 'percentage' };

                    } else if (selectedOption === 'flat') {

                        const flatValue = document.getElementById('flatValue').value;

                        const numValue = parseFloat(flatValue);

                        

                        if (isNaN(numValue) || numValue < 0) {

                            Swal.showValidationMessage('Flat amount must be greater than or equal to 0');

                            return false;

                        }

                        

                        return { discountValue: numValue, discountType: 'flat' };

                    }

                }

            }).then((result) => {

                if (result.isConfirmed) {

                    confirmBookingRequest(

                        bookingId, 

                        result.value.discountValue, 

                        result.value.discountType

                    );

                }

            });

        });

    }

    

    const rejectBtn = bookingDetailsContent.querySelector('.reject-booking-modal');

    if (rejectBtn) {

        rejectBtn.addEventListener('click', function() {

            const bookingId = this.getAttribute('data-booking-id');

            const userId = this.getAttribute('data-user-id');

            

            // Close the modal before showing SweetAlert

            bootstrap.Modal.getInstance(document.getElementById('bookingDetailsModal')).hide();

            

            Swal.fire({

                title: 'Reject Booking?',

                text: 'Are you sure you want to reject this booking request?',

                input: 'textarea',

                inputLabel: 'Reason',

                inputPlaceholder: 'Kindly provide the reason here.',

                icon: 'warning',

                showCancelButton: true,

                confirmButtonColor: '#dc3545',

                cancelButtonColor: '#6c757d',

                confirmButtonText: 'Reject',

                cancelButtonText: 'Cancel',

                inputValidator: (value) => {

                    if (!value) {

                        return 'Please provide a reason for rejection!';

                    }

                }

            }).then((result) => {

                if (result.isConfirmed) {

                    rejectBookingRequest(bookingId, result.value, userId);

                }

            });

        });

    }

    

    // Add event listener for the view invoice button

    const viewInvoiceBtn = bookingDetailsContent.querySelector('.view-invoice');

    if (viewInvoiceBtn) {

        viewInvoiceBtn.addEventListener('click', function() {

            const bookingId = this.getAttribute('data-booking-id');

            // Close the modal before opening the invoice

            bootstrap.Modal.getInstance(document.getElementById('bookingDetailsModal')).hide();

            // Navigate to the invoice page

            window.open(`/admin/print-invoice/${bookingId}`, '_blank');

        });

    }



    const viewContractBtn = bookingDetailsContent.querySelector(".view-contract");

    if (viewContractBtn) {

        viewContractBtn.addEventListener("click", function () {

            const bookingId = this.getAttribute("data-booking-id");

            window.open(`/admin/print-contract/${bookingId}`);

        });

    }

    

    // Show the modal

    const bookingDetailsModal = new bootstrap.Modal(document.getElementById('bookingDetailsModal'));

    bookingDetailsModal.show();



  

}



// Function to get detailed booking information including stops

async function getBookingDetails(bookingId) {

    try {

        const response = await fetch("/admin/get-booking-details", {

            method: "POST",

            headers: { "Content-Type": "application/json" },

            body: JSON.stringify({ bookingId })

        });



        if (response.ok) {

            const data = await response.json();

            

            if (data.success) {

                return data.booking;

            }

        }

        

        // If there's an error, fall back to the rebooking requests data

        const data = await getRebookingRequestsPaginated('All', 'asc', 'booking_id', 1, 1000);

        const booking = (data.items || []).find(b => b.booking_id == bookingId);

        console.log("Fallback to current requests:", booking);

        return booking;

        

    } catch (error) {

        console.error("Error fetching booking details:", error);

        

        // If there's an exception, fall back to the rebooking requests data

        const data = await getRebookingRequestsPaginated('All', 'asc', 'booking_id', 1, 1000);

        return (data.items || []).find(b => b.booking_id == bookingId);

    }

}



async function getBookingAuditDetails(bookingId, requestId = null) {

    try {

        const response = await fetch("/admin/get-booking-audit-details", {

            method: "POST",

            headers: { "Content-Type": "application/json" },

            body: JSON.stringify({ bookingId, requestId })

        });



        if (response.ok) {

            const data = await response.json();

            

            if (data.success) {

                return data.auditDetails;

            } else {

                console.error("Error fetching audit details:", data.message);

                return [];

            }

        } else {

            console.error("Failed to fetch audit details:", response.statusText);

            return [];

        }

    } catch (error) {

        console.error("Error fetching booking audit details:", error);  

    }

}



async function confirmBookingRequest(bookingId, discount = null, discountType = null) {

    try {

        const response = await fetch("/admin/confirm-rebooking-request", {

            method: "POST",

            headers: { "Content-Type": "application/json" },

            body: JSON.stringify({ bookingId, discount, discountType })

        });

    

        const data = await response.json();

        

        if (data.success) {

            Swal.fire({

                title: 'Success!',

                text: data.message,

                icon: 'success',

                confirmButtonColor: '#198754'

            });

        } else {

            Swal.fire({

                title: 'Error!',

                text: data.message,

                icon: 'error',

                confirmButtonColor: '#dc3545'

            });

        }

        

        const status = document.getElementById("statusSelect").value;

        const refreshed = await getRebookingRequestsPaginated(status, "asc", "booking_id", 1, getSelectedLimit());

        renderRebookingRequests(refreshed.items || []);

        renderPaginationControls(refreshed.pagination);

        updateRecordInfo(refreshed.pagination);

        updateStatsCounters(refreshed.items || []);

    } catch (error) {

        console.error(error);

        Swal.fire({

            title: 'Error!',

            text: 'An unexpected error occurred.',

            icon: 'error',

            confirmButtonColor: '#dc3545'

        });

    }

}



async function rejectBookingRequest(bookingId, reason, userId) {

    try {

        const response = await fetch("/admin/reject-rebooking", {

            method: "POST",

            headers: { "Content-Type": "application/json" },

            body: JSON.stringify({ bookingId, reason, userId })

        });

    

        const data = await response.json();

        

        if (data.success) {

            Swal.fire({

                title: 'Success!',

                text: data.message,

                icon: 'success',

                confirmButtonColor: '#198754'

            });

        } else {

            Swal.fire({

                title: 'Error!',

                text: data.message,

                icon: 'error',

                confirmButtonColor: '#dc3545'

            });

        }

        

        const status = document.getElementById("statusSelect").value;

        const refreshed = await getRebookingRequestsPaginated(status, "asc", "booking_id", 1, getSelectedLimit());

        renderRebookingRequests(refreshed.items || []);

        renderPaginationControls(refreshed.pagination);

        updateRecordInfo(refreshed.pagination);

        updateStatsCounters(refreshed.items || []);

    } catch (error) {

        console.error(error);

        Swal.fire({

            title: 'Error!',

            text: 'An unexpected error occurred.',

            icon: 'error',

            confirmButtonColor: '#dc3545'

        });

    }

}

function renderPaginationControls(pagination) {
    const container = document.getElementById('paginationContainer');
    if (!container) return;
    container.innerHTML = '';
    if (!pagination || pagination.totalPages <= 1) return;
    const pager = createPagination({
        containerId: 'paginationContainer',
        totalPages: pagination.totalPages,
        currentPage: pagination.currentPage,
        onPageChange: async (page) => {
            const status = document.getElementById('statusSelect').value;
            const activeHeader = document.querySelector('.sort[data-order]');
            const column = activeHeader ? activeHeader.getAttribute('data-column') : 'booking_id';
            const order = activeHeader ? activeHeader.getAttribute('data-order') : 'asc';
            const data = await getRebookingRequestsPaginated(status, order, column, page, getSelectedLimit());
            renderRebookingRequests(data.items || []);
            updateRecordInfo(data.pagination);
        }
    });
}

function updateRecordInfo(pagination) {
    const el = document.getElementById('recordInfo');
    if (!el || !pagination) return;
    const start = (pagination.currentPage - 1) * pagination.limit + 1;
    const end = Math.min(pagination.currentPage * pagination.limit, pagination.total);
    if (pagination.total === 0) {
        el.textContent = 'No records found';
    } else {
        el.textContent = `Showing ${start} to ${end} of ${pagination.total} entries`;
    }
}

function getSelectedLimit() {
    const select = document.getElementById('rowsPerPage');
    return select ? parseInt(select.value, 10) : 10;
}