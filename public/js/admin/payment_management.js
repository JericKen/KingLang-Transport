const confirmPaymentModal = new bootstrap.Modal(document.getElementById("confirmPaymentModal"));

const rejectPaymentModal = new bootstrap.Modal(document.getElementById("rejectPaymentModal"));

const messageModal = new bootstrap.Modal(document.getElementById("messageModal"));

const recordManualPaymentModal = new bootstrap.Modal(document.getElementById("recordManualPaymentModal"));



const messageTitle = document.getElementById("messageTitle");

const messageBody = document.getElementById("messageBody");



// Add pagination variables

let currentPage = 1;

let limit = 10; // Number of records per page

let currentSort = {

    column: 'payment_id',

    order: 'desc'

};

let currentFilter = 'all';

let searchQuery = '';

let payments = [];



// DOM Elements

const tableBody = document.getElementById('tableBody');

const statusSelect = document.getElementById('statusSelect');

const limitSelect = document.getElementById('limitSelect');

const paginationContainer = document.getElementById('paginationContainer');

const recordManualPaymentBtn = document.getElementById('recordManualPaymentBtn');

const recordManualPaymentForm = document.getElementById('recordManualPaymentForm');

const searchPaymentsInput = document.getElementById('searchPayments');

const noResultsFound = document.getElementById('noResultsFound');

const resetFiltersBtn = document.getElementById('resetFilters');



// Stats Elements

const totalPaymentsCount = document.getElementById('totalPaymentsCount');

const confirmedPaymentsCount = document.getElementById('confirmedPaymentsCount');

const pendingPaymentsCount = document.getElementById('pendingPaymentsCount');

const rejectedPaymentsCount = document.getElementById('rejectedPaymentsCount');



// Record Manual Payment elements

const searchBookingsBtn = document.getElementById('searchBookingsBtn');

const searchClientsBtn = document.getElementById('searchClientsBtn');

const bookingSearch = document.getElementById('bookingSearch');

const clientSearch = document.getElementById('clientSearch');

const bookingResults = document.getElementById('bookingResults');

const clientResults = document.getElementById('clientResults');

const bookingResultsList = document.getElementById('bookingResultsList');

const clientResultsList = document.getElementById('clientResultsList');

const bookingDetailsContainer = document.getElementById('bookingDetailsContainer');

const bookingIdInput = document.getElementById('bookingId');

const clientIdInput = document.getElementById('clientId');



// Event Listeners

document.querySelectorAll('.sort').forEach(header => {

    header.addEventListener('click', () => handleSort(header));

});



statusSelect.addEventListener('change', () => {

    currentFilter = statusSelect.value;

    currentPage = 1;

    loadPayments();

});



limitSelect.addEventListener('change', () => {

    limit = parseInt(limitSelect.value);

    currentPage = 1;

    loadPayments();

});



// Quick filters

document.querySelectorAll('.quick-filter').forEach(btn => {

    btn.addEventListener('click', () => {

        const status = btn.getAttribute('data-status');

        currentFilter = status;

        statusSelect.value = status;

        currentPage = 1;

        loadPayments();

        

        // Update active state

        document.querySelectorAll('.quick-filter').forEach(b => b.classList.remove('active'));

        btn.classList.add('active');

    });

});



// Search input

if (searchPaymentsInput) {

    const debouncedSearch = debounce(function() {

        searchQuery = searchPaymentsInput.value.trim();

        currentPage = 1;

        loadPayments();

    }, 500);

    

    searchPaymentsInput.addEventListener('input', debouncedSearch);

}



// Reset filters

if (resetFiltersBtn) {

    resetFiltersBtn.addEventListener('click', () => {

        searchQuery = '';

        currentFilter = 'all';

        currentPage = 1;

        

        // Reset UI elements

        searchPaymentsInput.value = '';

        statusSelect.value = 'all';

        

        // Remove active state from quick filters

        document.querySelectorAll('.quick-filter').forEach(b => b.classList.remove('active'));

        document.getElementById("statusSelect").value = "all";

        currentFilter = "all";



        const matchingBtn = document.querySelector(`.quick-filter[data-status="${currentFilter}"]`);

        if (matchingBtn) matchingBtn.classList.add("active");

        

        loadPayments();

    });

}



// Initialize the page with the sort indicators and check for available payment records

document.addEventListener("DOMContentLoaded", async function() {

    // Add initial sort indicator to the default sorted column

    updateSortIcons();

    

    // Load dashboard stats

    loadPaymentStats();

    

    // Check for PENDING payments first

    try {

        const pendingResponse = await fetch("/admin/payments/get", {

            method: 'POST',

            headers: {

                'Content-Type': 'application/json',

            },

            body: JSON.stringify({

                page: 1,

                limit: limit,

                sort: currentSort.column,

                order: currentSort.order,

                filter: 'PENDING',

                search: searchQuery

            })

        });

        

        if (pendingResponse.ok) {

            const pendingData = await pendingResponse.json();

            

            if (pendingData.success && pendingData.total > 0) {

                // If there are pending payments, keep filter as PENDING

                currentFilter = 'PENDING';

                statusSelect.value = 'PENDING';

                

                // Activate the pending quick filter button

                document.querySelectorAll('.quick-filter').forEach(b => b.classList.remove('active'));

                document.querySelector('.quick-filter[data-status="PENDING"]').classList.add('active');

                

                payments = pendingData.payments;

                renderPayments();

                renderPagination(pendingData.total);

                return;

            }

            

            // If no pending payments, check for CONFIRMED payments

            const confirmedResponse = await fetch("/admin/payments/get", {

                method: 'POST',

                headers: {

                    'Content-Type': 'application/json',

                },

                body: JSON.stringify({

                    page: 1,

                    limit: limit,

                    sort: currentSort.column,

                    order: currentSort.order,

                    filter: 'CONFIRMED',

                    search: searchQuery

                })

            });

            

            if (confirmedResponse.ok) {

                const confirmedData = await confirmedResponse.json();

                

                if (confirmedData.success && confirmedData.total > 0) {

                    // If there are confirmed payments, set filter to CONFIRMED

                    currentFilter = 'CONFIRMED';

                    statusSelect.value = 'CONFIRMED';

                    

                    // Activate the confirmed quick filter button

                    document.querySelectorAll('.quick-filter').forEach(b => b.classList.remove('active'));

                    document.querySelector('.quick-filter[data-status="CONFIRMED"]').classList.add('active');

                    

                    payments = confirmedData.payments;

                    renderPayments();

                    renderPagination(confirmedData.total);

                    return;

                }

                

                // If no pending and no confirmed payments, load all payments

                currentFilter = 'all';

                statusSelect.value = 'all';

                

                // Activate the all quick filter button

                document.querySelectorAll('.quick-filter').forEach(b => b.classList.remove('active'));

                document.querySelector('.quick-filter[data-status="all"]').classList.add('active');

                

                loadPayments();

            }

        }

    } catch (error) {

        console.error('Error during initial status check:', error);

        // If any error occurs, fall back to loading all payments

        loadPayments();

    }

});



// Initial load - this will be triggered only if DOMContentLoaded event doesn't handle loading

// Keep this as a fallback

setTimeout(() => {

    if (payments.length === 0) {

        loadPayments();

    }

}, 500);



// Functions

async function loadPayments() {

    try {

        // Store the current content for fallback if there's an error

        const currentContent = tableBody.innerHTML;

        

        // Don't show loading state when just changing pages or sorting

        // Only show loading state if the table is empty

        if (!payments.length) {

            tableBody.innerHTML = '<tr><td colspan="7" class="text-center"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';

        }



        const response = await fetch("/admin/payments/get", {

            method: 'POST',

            headers: {

                'Content-Type': 'application/json',

            },

            body: JSON.stringify({

                page: currentPage,

                limit: limit,

                sort: currentSort.column,

                order: currentSort.order,

                filter: currentFilter,

                search: searchQuery

            })

        });

        

        if (!response.ok) {

            console.log('Response not OK:', response);

            throw new Error(`HTTP error! status: ${response.status}`);

        }

        

        const data = await response.json();

        

        if (data.success) {

            payments = data.payments;

            renderPayments();

            renderPagination(data.total);

            

            // Show/hide no results message

            if (payments.length === 0) {

                noResultsFound.style.display = 'block';

            } else {

                noResultsFound.style.display = 'none';

            }

        } else {

            showMessage('Error', data.message || 'Failed to load payments', 'Error');

            // Restore previous content on failure

            if (currentContent && currentContent !== '') {

                tableBody.innerHTML = currentContent;

            }

        }

    } catch (error) {

        console.error('Error loading payments:', error);

        showMessage('Error', 'Failed to load payments', 'Error');

        tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading payments. Please try again.</td></tr>';

    }

}



async function loadPaymentStats() {

    try {

        const response = await fetch("/admin/payments/stats", {

            method: 'GET',

            headers: {

                'Content-Type': 'application/json',

            }

        });

        

        if (!response.ok) {

            throw new Error(`HTTP error! status: ${response.status}`);

        }

        

        const data = await response.json();

        

        if (data.success) {

            // Update stats dashboard

            totalPaymentsCount.textContent = data.total || 0;

            confirmedPaymentsCount.textContent = data.confirmed || 0;

            pendingPaymentsCount.textContent = data.pending || 0;

            rejectedPaymentsCount.textContent = data.rejected || 0;

        } else {

            console.error('Failed to load payment stats:', data.message);

        }

    } catch (error) {

        console.error('Error loading payment stats:', error);

    }

}



function renderPayments() {

    if (!payments || payments.length === 0) {

        tableBody.innerHTML = '';

        return;

    }



    tableBody.innerHTML = payments.map(payment => `

        <tr>

            <td>${payment.booking_id}</td>

            <td style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${payment.client_name}">${payment.client_name}</td>

            <td>${formatCurrency(payment.amount)}</td>

            <td>${payment.payment_method}</td>

            <td title="${payment.payment_date}">${formatDate(payment.payment_date)}</td>

            <td>

                <span class="${getStatusBadgeClass(payment.status)}">

                    ${payment.status}

                </span>

            </td>

            <td>

                <div class="d-flex justify-content-start gap-2">

                    <button class="btn btn-outline-primary btn-sm d-flex align-items-center" onclick="viewProof('${payment.proof_of_payment}')">

                        <i class="bi bi-eye me-1"></i><span>View</span>

                    </button>

                    ${payment.status.toUpperCase() === 'PENDING' ? `

                        <button class="btn btn-outline-success btn-sm d-flex align-items-center" onclick="confirmPayment(${payment.payment_id})">

                            <i class="bi bi-check-circle me-1"></i><span>Confirm</span>

                        </button>

                        <button class="btn btn-outline-danger btn-sm d-flex align-items-center" onclick="rejectPayment(${payment.payment_id})">

                            <i class="bi bi-x-circle me-1"></i><span>Reject</span>

                        </button>

                    ` : ''}

                </div>

            </td>

        </tr>

    `).join('');

}



function renderPagination(total) {

    const totalPages = Math.ceil(total / limit);

    

    // Skip rendering if only one page

    if (totalPages <= 1) {

        paginationContainer.innerHTML = '';

        return;

    }

    

    // Build pagination HTML

    let html = '<nav aria-label="Payment navigation"><ul class="pagination justify-content-center">';

    

    // Previous button

    const prevDisabled = currentPage === 1 ? 'disabled' : '';

    html += `

        <li class="page-item ${prevDisabled}">

            <a class="page-link" href="#" aria-label="Previous" ${currentPage === 1 ? '' : `onclick="changePage(${currentPage - 1}); return false;"`}>

                <span aria-hidden="true">&laquo;</span>

            </a>

        </li>

    `;

    

    // Calculate visible page range

    const maxPagesToShow = 5;

    let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));

    let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

    

    // Adjust if at the end

    if (endPage - startPage + 1 < maxPagesToShow) {

        startPage = Math.max(1, endPage - maxPagesToShow + 1);

    }

    

    // First page and ellipsis

    if (startPage > 1) {

        html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(1); return false;">1</a></li>`;

        if (startPage > 2) {

            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;

        }

    }

    

    // Page numbers

    for (let i = startPage; i <= endPage; i++) {

        const active = i === currentPage ? 'active' : '';

        html += `<li class="page-item ${active}"><a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a></li>`;

    }

    

    // Last page and ellipsis

    if (endPage < totalPages) {

        if (endPage < totalPages - 1) {

            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;

        }

        html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${totalPages}); return false;">${totalPages}</a></li>`;

    }

    

    // Next button

    const nextDisabled = currentPage === totalPages ? 'disabled' : '';

    html += `

        <li class="page-item ${nextDisabled}">

            <a class="page-link" href="#" aria-label="Next" ${currentPage === totalPages ? '' : `onclick="changePage(${currentPage + 1}); return false;"`}>

                <span aria-hidden="true">&raquo;</span>

            </a>

        </li>

    `;

    

    html += '</ul></nav>';

    

    paginationContainer.innerHTML = html;

}



function handleSort(header) {

    const column = header.getAttribute('data-column');

    let order = header.getAttribute('data-order');

    

    // Toggle sorting direction

    order = order === 'asc' ? 'desc' : 'asc';

    

    // Update current sort

    currentSort = {

        column: column,

        order: order

    };



    // Update all headers

    document.querySelectorAll('.sort').forEach(h => {

        h.setAttribute('data-order', h === header ? order : 'asc');

    });

    

    // Update sort icons

    updateSortIcons();

    

    // Reload payments with new sort

    loadPayments();

}



function updateSortIcons() {

    document.querySelectorAll('.sort').forEach(header => {

        const column = header.getAttribute('data-column');

        const order = header.getAttribute('data-order');

        const sortIconSpan = header.querySelector('.sort-icon');

        

        if (column === currentSort.column) {

            if (sortIconSpan) {

                sortIconSpan.innerHTML = order === 'asc' ? '↑' : '↓';

            }

        } else {

            if (sortIconSpan) {

                sortIconSpan.innerHTML = '↑';

            }

        }

    });

}



function changePage(page) {

    currentPage = page;

    loadPayments();

}



function formatCurrency(amount) {

    return new Intl.NumberFormat('en-PH', {

        style: 'currency',

        currency: 'PHP'

    }).format(amount);

}



function formatDate(dateString) {

    if (!dateString) return 'N/A';

    

    const date = new Date(dateString);

    return date.toLocaleDateString('en-PH', {

        year: 'numeric',

        month: 'short',

        day: 'numeric',

        hour: '2-digit',

        minute: '2-digit'

    });

}



function getStatusTextClass(status) {

    const statusUpper = status.toUpperCase();

    switch (statusUpper) {

        case 'CONFIRMED':

            return 'success';

        case 'PENDING':

            return 'warning';

        case 'REJECTED':

            return 'danger';

        default:

            return 'secondary';

    }

}



// Helper function for payment status badge styling in search results

function getPaymentStatusBadgeClass(status) {

    switch (status) {

        case 'Paid': return 'badge bg-success';

        case 'Partially Paid': return 'badge bg-warning text-dark';

        case 'Unpaid': return 'badge bg-danger';

        default: return 'badge bg-secondary';

    }

}



// Helper function for payment status badge styling in main table

function getStatusBadgeClass(status) {

    const statusUpper = status.toUpperCase();

    switch (statusUpper) {

        case 'CONFIRMED':

            return 'badge bg-success';

        case 'PENDING':

            return 'badge bg-warning text-dark';

        case 'REJECTED':

            return 'badge bg-danger';

        default:

            return 'badge bg-secondary';

    }

}



// Update confirmPayment function to use SweetAlert

function confirmPayment(paymentId) {

    Swal.fire({

        title: 'Confirm Payment?',

        text: 'Are you sure you want to confirm this payment? This action cannot be undone.',

        icon: 'question',

        showCancelButton: true,

        confirmButtonColor: '#28a745',

        cancelButtonColor: '#6c757d',

        confirmButtonText: 'Yes, confirm it!',

        cancelButtonText: 'Cancel'

    }).then((result) => {

        if (result.isConfirmed) {

            processPaymentConfirmation(paymentId);

        }

    });

}



// Add processing function for payment confirmation

async function processPaymentConfirmation(paymentId) {

    try {

        const response = await fetch('/admin/payments/confirm', {

            method: 'POST',

            headers: {

                'Content-Type': 'application/json',

            },

            body: JSON.stringify({ payment_id: paymentId })

        });

        

        if (!response.ok) {

            throw new Error(`HTTP error! status: ${response.status}`);

        }

        

        const data = await response.json();

        

        if (data.success) {

            Swal.fire({

                title: 'Confirmed!',

                text: 'Payment has been confirmed successfully.',

                icon: 'success',

                confirmButtonColor: '#28a745'

            });

            

            // Refresh payments and stats after confirmation

            loadPayments();

            loadPaymentStats();

        } else {

            showMessage('Error', data.message || 'Failed to confirm payment', 'Error');

        }

    } catch (error) {

        console.error('Error confirming payment:', error);

        showMessage('Error', 'An error occurred while confirming the payment', 'Error');

    }

}



// Update rejectPayment function to use SweetAlert

function rejectPayment(paymentId) {

    Swal.fire({

        title: 'Reject Payment?',

        text: 'Please provide a reason for rejection:',

        input: 'textarea',

        inputPlaceholder: 'Type your reason here...',

        icon: 'warning',

        showCancelButton: true,

        confirmButtonColor: '#dc3545',

        cancelButtonColor: '#6c757d',

        confirmButtonText: 'Yes, reject it!',

        cancelButtonText: 'Cancel',

        inputValidator: (value) => {

            if (!value || value.trim() === '') {

                return 'You need to provide a reason for rejection!';

            }

        }

    }).then((result) => {

        if (result.isConfirmed) {

            processPaymentRejection(paymentId, result.value);

        }

    });

}



// Add processing function for payment rejection

async function processPaymentRejection(paymentId, reason) {

    try {

        const response = await fetch('/admin/payments/reject', {

            method: 'POST',

            headers: {

                'Content-Type': 'application/json',

            },

            body: JSON.stringify({ 

                payment_id: paymentId,

                reason: reason

            })

        });

        

        if (!response.ok) {

            throw new Error(`HTTP error! status: ${response.status}`);

        }

        

        const data = await response.json();

        

        if (data.success) {

            Swal.fire({

                title: 'Rejected!',

                text: 'Payment has been rejected successfully.',

                icon: 'success',

                confirmButtonColor: '#28a745'

            });

            

            // Refresh payments and stats after rejection

            loadPayments();

            loadPaymentStats();

        } else {

            showMessage('Error', data.message || 'Failed to reject payment', 'Error');

        }

    } catch (error) {

        console.error('Error rejecting payment:', error);

        showMessage('Error', 'An error occurred while rejecting the payment', 'Error');

    }

}



// View proof of payment

function viewProof(proofFile) {

    if (!proofFile || proofFile === 'null' || proofFile === 'undefined' || proofFile.trim() === '') {

        showMessage('Information', 'No proof of payment was uploaded for this transaction.', 'info');

        return;

    }

    

    // Open the proof file in a new tab with the correct path

    window.open('/app/uploads/payments/' + proofFile, '_blank');

}



// Replace the showMessage function with SweetAlert

function showMessage(title, message, type = 'info') {

    // Map internal message types to SweetAlert types

    const alertType = type === 'Error' ? 'error' : 

                      type === 'Success' ? 'success' : 

                      type === 'Warning' ? 'warning' : 'info';

    

    Swal.fire({

        title: title,

        text: message,

        icon: alertType,

        confirmButtonColor: '#28a745',

        confirmButtonText: 'OK'

    });

}



// Record Manual Payment Modal

recordManualPaymentBtn.addEventListener('click', () => {

    recordManualPaymentModal.show();

    

    // Reset form

    recordManualPaymentForm.reset();

    bookingDetailsContainer.style.display = 'none';

});



// Handle form submission

recordManualPaymentForm.addEventListener('submit', async (e) => {

    e.preventDefault();

    

    const formData = new FormData(recordManualPaymentForm);

    const data = Object.fromEntries(formData.entries());

    

    // First confirm with SweetAlert before proceeding

    Swal.fire({

        title: 'Record Payment?',

        text: `Are you sure you want to record a payment of ${formatCurrency(data.amount)} for booking #${data.booking_id}?`,

        icon: 'question',

        showCancelButton: true,

        confirmButtonColor: '#28a745',

        cancelButtonColor: '#6c757d',

        confirmButtonText: 'Yes, record it!',

        cancelButtonText: 'Cancel'

    }).then(async (result) => {

        if (result.isConfirmed) {

            try {

                const response = await fetch('/admin/payments/record-manual', {

                    method: 'POST',

                    headers: {

                        'Content-Type': 'application/json',

                    },

                    body: JSON.stringify(data)

                });

                

                if (!response.ok) {

                    throw new Error(`HTTP error! status: ${response.status}`);

                }

                

                const result = await response.json();

                

                if (result.success) {

                    recordManualPaymentModal.hide();

                    

                    Swal.fire({

                        title: 'Payment Recorded!',

                        text: 'Payment has been recorded successfully.',

                        icon: 'success',

                        confirmButtonColor: '#28a745'

                    });

                    

                    // Refresh payments after recording

                    loadPayments();

                    loadPaymentStats();

                } else {

                    showMessage('Error', result.message || 'Failed to record payment', 'Error');

                }

            } catch (error) {

                console.error('Error recording payment:', error);

                showMessage('Error', 'An error occurred while recording the payment', 'Error');

            }

        }

    });

});



// Booking search

searchBookingsBtn.addEventListener('click', async () => {

    const searchTerm = bookingSearch.value.trim();

    

    if (searchTerm.length < 2) {

        showMessage('Warning', 'Please enter at least 2 characters for search', 'Warning');

        return;

    }

    

    try {

        const response = await fetch('/admin/payments/search-bookings', {

            method: 'POST',

            headers: {

                'Content-Type': 'application/json',

            },

            body: JSON.stringify({ search: searchTerm })

        });

        

        if (!response.ok) {

            throw new Error(`HTTP error! status: ${response.status}`);

        }

        

        const data = await response.json();

        

        if (data.success && data.bookings.length > 0) {

            // Show results container

            bookingResults.style.display = 'block';

            

            // Clear previous results

            bookingResultsList.innerHTML = '';

            

            // Add results to list

            data.bookings.forEach(booking => {

                const listItem = document.createElement('a');

                listItem.href = '#';

                listItem.className = 'list-group-item list-group-item-action';

                listItem.innerHTML = `

                    <div class="d-flex justify-content-between align-items-center w-100">

                        <strong>#${booking.booking_id}: ${booking.destination}</strong>

                        <span class="badge ${getPaymentStatusBadgeClass(booking.payment_status)}">${booking.payment_status}</span>

                    </div>

                    <small>Client: ${booking.client_name} | Date: ${formatDate(booking.date_of_tour)}</small>

                `;

                

                // Add click handler

                listItem.addEventListener('click', (e) => {

                    e.preventDefault();

                    selectBooking(booking);

                });

                

                bookingResultsList.appendChild(listItem);

            });

        } else {

            bookingResults.style.display = 'block';

            // bookingResultsList.innerHTML = '<div class="list-group-item">No bookings found</div>';

        }

    } catch (error) {

        console.error('Error searching bookings:', error);

        showMessage('Error', 'An error occurred while searching for bookings', 'Error');

    }

});



// Client search

searchClientsBtn.addEventListener('click', async () => {

    const searchTerm = clientSearch.value.trim();

    

    if (searchTerm.length < 2) {

        showMessage('Warning', 'Please enter at least 2 characters for search', 'Warning');

        return;

    }

    

    try {

        const response = await fetch('/admin/payments/search-clients', {

            method: 'POST',

            headers: {

                'Content-Type': 'application/json',

            },

            body: JSON.stringify({ search: searchTerm })

        });

        

        if (!response.ok) {

            throw new Error(`HTTP error! status: ${response.status}`);

        }

        

        const data = await response.json();

        

        if (data.success && data.clients.length > 0) {

            // Show results container

            clientResults.style.display = 'block';

            

            // Clear previous results

            clientResultsList.innerHTML = '';

            

            // Add results to list

            data.clients.forEach(client => {

                const listItem = document.createElement('a');

                listItem.href = '#';

                listItem.className = 'list-group-item list-group-item-action';

                listItem.innerHTML = `

                    <strong>#${client.user_id}: ${client.client_name}</strong>

                    <div>${client.email} | ${client.contact_number || 'No contact number'}</div>

                `;

                

                // Add click handler

                listItem.addEventListener('click', (e) => {

                    e.preventDefault();

                    selectClient(client);

                });

                

                clientResultsList.appendChild(listItem);

            });

        } else {

            clientResults.style.display = 'block';

            clientResultsList.innerHTML = '<div class="list-group-item">No clients found</div>';

        }

    } catch (error) {

        console.error('Error searching clients:', error);

        showMessage('Error', 'An error occurred while searching for clients', 'Error');

    }

});



// Select a booking from search results

function selectBooking(booking) {

    bookingIdInput.value = booking.booking_id;

    clientIdInput.value = booking.user_id;

    

    // Update booking details display

    document.getElementById('detailDestination').textContent = booking.destination;

    document.getElementById('detailClient').textContent = booking.client_name;

    document.getElementById('detailTotalCost').textContent = formatCurrency(booking.total_cost);

    document.getElementById('detailBalance').textContent = formatCurrency(booking.balance);

    document.getElementById('detailPaymentStatus').textContent = booking.payment_status;

    

    // Show booking details container

    bookingDetailsContainer.style.display = 'block';

    

    // Set suggested payment amount to the balance if it exists

    if (booking.balance > 0) {

        document.getElementById('amount').value = booking.balance;

    }

    

    // Hide search results

    bookingResults.style.display = 'none';

}



// Select a client from search results

function selectClient(client) {

    clientIdInput.value = client.user_id;

    

    // Hide search results

    clientResults.style.display = 'none';

}



// Get booking details when booking ID is entered manually

bookingIdInput.addEventListener('change', async () => {

    const bookingId = bookingIdInput.value.trim();

    

    if (!bookingId) {

        return;

    }

    

    try {

        const response = await fetch('/admin/payments/get-booking-details', {

            method: 'POST',

            headers: {

                'Content-Type': 'application/json',

            },

            body: JSON.stringify({ booking_id: bookingId })

        });

        

        if (!response.ok) {

            throw new Error(`HTTP error! status: ${response.status}`);

        }

        

        const data = await response.json();

        

        if (data.success && data.booking) {

            selectBooking(data.booking);

        } else {

            bookingDetailsContainer.style.display = 'none';

            showMessage('Error', 'Booking not found', 'Error');

        }

    } catch (error) {

        console.error('Error getting booking details:', error);

        bookingDetailsContainer.style.display = 'none';

    }

});



// Close search results when clicking outside

document.addEventListener('click', (e) => {

    if (bookingResults.style.display === 'block' && !bookingResults.contains(e.target) && e.target !== bookingSearch && e.target !== searchBookingsBtn) {

        bookingResults.style.display = 'none';

    }

    

    if (clientResults.style.display === 'block' && !clientResults.contains(e.target) && e.target !== clientSearch && e.target !== searchClientsBtn) {

        clientResults.style.display = 'none';

    }

});



// Utility Functions

function debounce(func, wait) {

    let timeout;

    

    return function executedFunction(...args) {

        const later = () => {

            clearTimeout(timeout);

            func(...args);

        };

        

        clearTimeout(timeout);

        timeout = setTimeout(later, wait);

    };

}



// Add input event for booking search

bookingSearch.addEventListener('input', debounce(function() {

    const searchTerm = bookingSearch.value.trim();

    if (searchTerm.length >= 2) {

        searchBookingsBtn.click();

    }

}, 300));



// Add input event for client search

clientSearch.addEventListener('input', debounce(function() {

    const searchTerm = clientSearch.value.trim();

    if (searchTerm.length >= 2) {

        searchClientsBtn.click();

    }

}, 300));



function getPaymentStatusClass(status) {

    const statusLower = status.toLowerCase();

    if (statusLower.includes('paid') || statusLower === 'complete' || statusLower === 'completed') {

        return 'badge bg-success';

    } else if (statusLower.includes('partial')) {

        return 'badge bg-warning text-dark';

    } else {

        return 'badge bg-danger';

    }

}