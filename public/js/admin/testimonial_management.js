document.addEventListener('DOMContentLoaded', function() {

    let currentStatus = 'all';

    let currentPage = 1;

    let testimonialsPerPage = 20;

    let selectedTestimonials = new Set();



    // Initialize

    loadStats();

    loadTestimonials();

    setupEventListeners();



    function setupEventListeners() {

        // Status filter buttons/tabs
        document.querySelectorAll('[data-status]').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();

                // Update active state on quick filter buttons if present
                document.querySelectorAll('.quick-filter').forEach(btn => btn.classList.remove('active'));
                if (this.classList.contains('quick-filter')) {
                    this.classList.add('active');
                }

                currentStatus = this.dataset.status;
                currentPage = 1;
                selectedTestimonials.clear();
                updateBulkActions();
                loadTestimonials();
            });
        });

        // Refresh button
        const refreshBtn = document.getElementById('refreshBookings');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function(e) {
                e.preventDefault();
                loadStats();
                loadTestimonials();
            });
        }



        // Select all checkbox

        document.getElementById('selectAll').addEventListener('change', function() {

            const checkboxes = document.querySelectorAll('.testimonial-checkbox');

            checkboxes.forEach(checkbox => {

                checkbox.checked = this.checked;

                if (this.checked) {

                    selectedTestimonials.add(checkbox.value);

                } else {

                    selectedTestimonials.delete(checkbox.value);

                }

            });

            updateBulkActions();

        });



        // Sidebar toggle functionality (copied from other admin pages)

        const sidebar = document.getElementById('sidebar');

        const content = document.getElementById('content');

        const toggleBtn = document.getElementById('toggleBtn');



        if (toggleBtn) {

            toggleBtn.addEventListener('click', function() {

                const isCollapsed = sidebar.classList.contains('collapsed');

                

                if (isCollapsed) {

                    sidebar.classList.remove('collapsed');

                    sidebar.classList.add('expanded');

                    content.classList.remove('collapsed');

                    content.classList.add('expanded');

                    localStorage.setItem('sidebarCollapsed', 'false');

                } else {

                    sidebar.classList.remove('expanded');

                    sidebar.classList.add('collapsed');

                    content.classList.remove('expanded');

                    content.classList.add('collapsed');

                    localStorage.setItem('sidebarCollapsed', 'true');

                }

            });

        }

    }



    // Removed legacy setupQuickFilters copied from bookings page to prevent double handlers and undefined references



    async function loadStats() {

        try {

            const response = await fetch('/admin/testimonials/stats');

            const data = await response.json();

            

            if (data.success) {

                const stats = data.stats;

                document.getElementById('totalTestimonialsCount').textContent = stats.total_testimonials || 0;

                document.getElementById('pendingTestimonialsCount').textContent = stats.pending_testimonials || 0;

                document.getElementById('approvedTestimonialsCount').textContent = stats.approved_testimonials || 0;

                document.getElementById('averageRating').textContent = stats.average_rating || '0.0';

            }

        } catch (error) {

            console.error('Error loading stats:', error);

        }

    }



    async function loadTestimonials() {

        const tableBody = document.getElementById('testimonialsTableBody');



        try {

            const offset = (currentPage - 1) * testimonialsPerPage;

            const response = await fetch(`/admin/testimonials/list?status=${currentStatus}&limit=${testimonialsPerPage}&offset=${offset}`);

            const data = await response.json();

            

            if (data.success) {

                renderTestimonials(data.testimonials);

            } else {

                tableBody.innerHTML = `

                    <tr>

                        <td colspan="9" class="text-center text-muted">

                            <i class="bi bi-chat-quote fs-1"></i><br>

                            No testimonials found

                        </td>

                    </tr>

                `;

            }

        } catch (error) {

            console.error('Error loading testimonials:', error);

            tableBody.innerHTML = `

                <tr>

                    <td colspan="9" class="text-center text-danger">

                        <i class="bi bi-exclamation-triangle"></i> Error loading testimonials

                    </td>

                </tr>

            `;

        }

    }



    function renderTestimonials(testimonials) {

        const tableBody = document.getElementById('testimonialsTableBody');

        

        if (testimonials.length === 0) {

            tableBody.innerHTML = `

                <tr>

                    <td colspan="9" class="text-center text-muted">

                        <i class="bi bi-chat-quote fs-1"></i><br>

                        No testimonials found for the selected filter

                    </td>

                </tr>

            `;

            return;

        }



        tableBody.innerHTML = testimonials.map(testimonial => {

            const statusBadge = getStatusBadge(testimonial);

            const ratingStars = getRatingStars(testimonial.rating);

            const formattedDate = formatDate(testimonial.created_at);

            

            return `

                <tr>

                    <td>

                        <input type="checkbox" class="form-check-input testimonial-checkbox" 

                               value="${testimonial.testimonial_id}" 

                               ${selectedTestimonials.has(testimonial.testimonial_id.toString()) ? 'checked' : ''}>

                    </td>

                    <td>

                        <div>

                            <strong>${escapeHtml(testimonial.client_name)}</strong><br>

                            <small class="text-muted">${escapeHtml(testimonial.email)}</small>

                            ${testimonial.company_name ? `<br><small class="text-muted">${escapeHtml(testimonial.company_name)}</small>` : ''}

                        </div>

                    </td>

                    <td>

                        <div class="rating-stars">${ratingStars}</div>

                        <small class="text-muted">${testimonial.rating}/5</small>

                    </td>

                    <td>

                        <div class="testimonial-preview">

                            <strong>${escapeHtml(testimonial.title)}</strong>

                        </div>

                    </td>

                    <td>

                        <div class="testimonial-preview">

                            ${escapeHtml(testimonial.content.substring(0, 50))}${testimonial.content.length > 50 ? '...' : ''}

                        </div>

                    </td>

                    <td>

                        <div class="testimonial-preview">

                            <strong>${escapeHtml(testimonial.destination)}</strong><br>

                            <small class="text-muted">${formatDate(testimonial.date_of_tour)}</small>

                        </div>

                    </td>

                    <td>${statusBadge}</td>

                    <td>

                        <small>${formattedDate}</small>

                    </td>

                    <td>

                        <div class="actions-compact">

                            <button class="btn btn-outline-primary btn-sm" onclick="viewTestimonial(${testimonial.testimonial_id})" title="View Details">

                                <i class="bi bi-info-circle"></i>

                            </button>

                            ${!testimonial.is_approved ? `

                                <button class="btn btn-outline-success btn-sm" onclick="approveTestimonial(${testimonial.testimonial_id})" title="Approve">

                                    <i class="bi bi-check-circle"></i>

                                </button>` : ''}

                            ${testimonial.is_approved ? `

                                <button class="btn btn-outline-warning btn-sm" onclick="rejectTestimonial(${testimonial.testimonial_id})" title="Reject">

                                    <i class="bi bi-x-circle"></i>

                                </button>` : ''}

                            ${testimonial.is_approved ? `

                                <button class="btn btn-outline-info btn-sm" onclick="toggleFeatured(${testimonial.testimonial_id})" title="${testimonial.is_featured ? 'Remove from Featured' : 'Make Featured'}">

                                    <i class="bi bi-star${testimonial.is_featured ? '-fill' : ''}"></i>

                                </button>` : ''}

                            <button class="btn btn-outline-danger btn-sm" onclick="deleteTestimonial(${testimonial.testimonial_id})" title="Delete">

                                <i class="bi bi-trash"></i>

                            </button>

                        </div>

                    </td>

                </tr>

            `;

        }).join('');



        // Add event listeners to checkboxes

        document.querySelectorAll('.testimonial-checkbox').forEach(checkbox => {

            checkbox.addEventListener('change', function() {

                if (this.checked) {

                    selectedTestimonials.add(this.value);

                } else {

                    selectedTestimonials.delete(this.value);

                }

                updateBulkActions();

            });

        });

    }



    function getStatusBadge(testimonial) {

        if (testimonial.is_featured) {

            return '<span class="badge bg-info">Featured</span>';

        } else if (testimonial.is_approved) {

            return '<span class="badge bg-success">Approved</span>';

        } else {

            return '<span class="badge bg-warning text-dark">Pending</span>';

        }

    }   



    function getRatingStars(rating) {

        let stars = '';

        for (let i = 1; i <= 5; i++) {

            if (i <= rating) {

                stars += '<i class="bi bi-star-fill"></i>';

            } else {

                stars += '<i class="bi bi-star"></i>';

            }

        }

        return stars;

    }



    function formatDate(dateString) {

        const date = new Date(dateString);

        return date.toLocaleDateString('en-US', {

            year: 'numeric',

            month: 'short',

            day: 'numeric',

            hour: '2-digit',

            minute: '2-digit'

        });

    }



    function updateBulkActions() {

        const bulkActions = document.getElementById('bulkActions');

        const selectedCount = document.getElementById('selectedCount');

        const selectAllCheckbox = document.getElementById('selectAll');

        

        selectedCount.textContent = selectedTestimonials.size;

        

        if (selectedTestimonials.size > 0) {

            bulkActions.style.display = 'block';

        } else {

            bulkActions.style.display = 'none';

        }



        // Update select all checkbox state

        const checkboxes = document.querySelectorAll('.testimonial-checkbox');

        const checkedCheckboxes = document.querySelectorAll('.testimonial-checkbox:checked');

        

        if (checkedCheckboxes.length === 0) {

            selectAllCheckbox.indeterminate = false;

            selectAllCheckbox.checked = false;

        } else if (checkedCheckboxes.length === checkboxes.length) {

            selectAllCheckbox.indeterminate = false;

            selectAllCheckbox.checked = true;

        } else {

            selectAllCheckbox.indeterminate = true;

        }

    }



    // Global functions for button actions

    window.approveTestimonial = async function(testimonialId) {

        // if (!confirm('Are you sure you want to approve this testimonial?')) return;

        const result = await Swal.fire({

            title: 'Are you sure?',

            text: 'This testimonial will be approved!',

            icon: 'question',

            showCancelButton: true,

            confirmButtonColor: '#28a745',

            cancelButtonColor: '#6c757d',

            confirmButtonText: 'Yes, approve it!'

        });



        if (!result.isConfirmed) return;



        try {

            const response = await fetch('/admin/testimonials/approve', {

                method: 'POST',

                headers: { 'Content-Type': 'application/json' },

                body: JSON.stringify({ testimonial_id: testimonialId })

            });



            const data = await response.json();

            

            if (data.success) {

                Swal.fire('Success!', data.message, 'success');

                loadTestimonials();

                loadStats();

            } else {

                Swal.fire('Error!', data.message, 'error');

            }

        } catch (error) {

            console.error('Error approving testimonial:', error);

            Swal.fire('Error!', 'Failed to approve testimonial', 'error');

        }

    };



    window.rejectTestimonial = async function(testimonialId) {

        // if (!confirm('Are you sure you want to reject this testimonial?')) return;

        const result = await Swal.fire({

            title: 'Are you sure?',

            text: 'This testimonial will be rejected!',

            icon: 'warning',

            showCancelButton: true,

            confirmButtonColor: '#d33',

            cancelButtonColor: '#3085d6',

            confirmButtonText: 'Yes, reject it!'

        });



        if (!result.isConfirmed) return;



        try {

            const response = await fetch('/admin/testimonials/reject', {

                method: 'POST',

                headers: { 'Content-Type': 'application/json' },

                body: JSON.stringify({ testimonial_id: testimonialId })

            });



            const data = await response.json();

            

            if (data.success) {

                Swal.fire('Success!', data.message, 'success');

                loadTestimonials();

                loadStats();

            } else {

                Swal.fire('Error!', data.message, 'error');

            }

        } catch (error) {

            console.error('Error rejecting testimonial:', error);

            Swal.fire('Error!', 'Failed to reject testimonial', 'error');

        }

    };



    window.toggleFeatured = async function(testimonialId) {

        try {

            const response = await fetch('/admin/testimonials/toggle-featured', {

                method: 'POST',

                headers: { 'Content-Type': 'application/json' },

                body: JSON.stringify({ testimonial_id: testimonialId })

            });



            const data = await response.json();

            

            if (data.success) {

                // Swal.fire('Success!', data.message, 'success');

                loadTestimonials();

                loadStats();

            } else {

                Swal.fire('Error!', data.message, 'error');

            }

        } catch (error) {

            console.error('Error toggling featured status:', error);

            Swal.fire('Error!', 'Failed to update featured status', 'error');

        }

    };



    window.deleteTestimonial = async function(testimonialId) {

        const result = await Swal.fire({

            title: 'Are you sure?',

            text: 'This testimonial will be permanently deleted!',

            icon: 'warning',

            showCancelButton: true,

            confirmButtonColor: '#d33',

            cancelButtonColor: '#3085d6',

            confirmButtonText: 'Yes, delete it!'

        });



        if (!result.isConfirmed) return;



        try {

            const response = await fetch('/admin/testimonials/delete', {

                method: 'POST',

                headers: { 'Content-Type': 'application/json' },

                body: JSON.stringify({ testimonial_id: testimonialId })

            });



            const data = await response.json();

            

            if (data.success) {

                Swal.fire('Deleted!', data.message, 'success');

                loadTestimonials();

                loadStats();

            } else {

                Swal.fire('Error!', data.message, 'error');

            }

        } catch (error) {

            console.error('Error deleting testimonial:', error);

            Swal.fire('Error!', 'Failed to delete testimonial', 'error');

        }

    };



    window.viewTestimonial = async function(testimonialId) {

        try {

            const response = await fetch(`/admin/testimonials/details?id=${testimonialId}`);

            const data = await response.json();

            

            if (data.success) {

                const testimonial = data.testimonial;

                const modalBody = document.getElementById('testimonialModalBody');

                

                modalBody.innerHTML = `

                    <div class="row">

                        <div class="col-md-6">

                            <h6>Client Information</h6>

                            <p><strong>Name:</strong> ${escapeHtml(testimonial.client_name)}<br>

                            <strong>Email:</strong> ${escapeHtml(testimonial.email)}<br>

                            ${testimonial.company_name ? `<strong>Company:</strong> ${escapeHtml(testimonial.company_name)}<br>` : ''}

                            <strong>Rating:</strong> ${getRatingStars(testimonial.rating)} (${testimonial.rating}/5)</p>

                        </div>

                        <div class="col-md-6">

                            <h6>Trip Information</h6>

                            <p><strong>Destination:</strong> ${escapeHtml(testimonial.destination)}<br>

                            <strong>Trip Date:</strong> ${formatDate(testimonial.date_of_tour)}<br>

                            <strong>Trip End:</strong> ${formatDate(testimonial.end_of_tour)}</p>

                        </div>

                    </div>

                    <hr>

                    <h6>Review Title</h6>

                    <p>${escapeHtml(testimonial.title)}</p>

                    <h6>Review Content</h6>

                    <div class="testimonial-content border p-3 rounded">

                        ${escapeHtml(testimonial.content)}

                    </div>

                    <hr>

                    <div class="row">

                        <div class="col-md-6">

                            <p><strong>Status:</strong> ${getStatusBadge(testimonial)}</p>

                        </div>

                        <div class="col-md-6">

                            <p><strong>Submitted:</strong> ${formatDate(testimonial.created_at)}</p>

                        </div>

                    </div>

                `;

                

                const modal = new bootstrap.Modal(document.getElementById('testimonialModal'));

                modal.show();

            } else {

                Swal.fire('Error!', data.message, 'error');

            }

        } catch (error) {

            console.error('Error loading testimonial details:', error);

            Swal.fire('Error!', 'Failed to load testimonial details', 'error');

        }

    };



    window.bulkAction = async function(action) {

        if (selectedTestimonials.size === 0) {

            Swal.fire('Warning!', 'Please select testimonials first', 'warning');

            return;

        }



        const actionText = action === 'approve' ? 'approve' : 'reject';

        const result = await Swal.fire({

            title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Selected Testimonials?`,

            text: `This will ${actionText} ${selectedTestimonials.size} testimonial(s)`,

            icon: 'question',

            showCancelButton: true,

            confirmButtonText: `Yes, ${actionText}!`

        });



        if (!result.isConfirmed) return;



        try {

            const response = await fetch('/admin/testimonials/bulk-action', {

                method: 'POST',

                headers: { 'Content-Type': 'application/json' },

                body: JSON.stringify({

                    action: action,

                    testimonial_ids: Array.from(selectedTestimonials).map(id => parseInt(id))

                })

            });



            const data = await response.json();

            

            if (data.success) {

                Swal.fire('Success!', data.message, 'success');

                selectedTestimonials.clear();

                updateBulkActions();

                loadTestimonials();

                loadStats();

            } else {

                Swal.fire('Error!', data.message, 'error');

            }

        } catch (error) {

            console.error('Error performing bulk action:', error);

            Swal.fire('Error!', 'Failed to perform bulk action', 'error');

        }

    };



    window.clearSelection = function() {

        selectedTestimonials.clear();

        document.querySelectorAll('.testimonial-checkbox').forEach(checkbox => {

            checkbox.checked = false;

        });

        document.getElementById('selectAll').checked = false;

        updateBulkActions();

    };



    function escapeHtml(text) {

        if (!text) return '';

        const map = {

            '&': '&amp;',

            '<': '&lt;',

            '>': '&gt;',

            '"': '&quot;',

            "'": '&#039;'

        };

        return text.replace(/[&<>"']/g, function(m) { return map[m]; });

    }

});