class PastTripsManager {
    constructor() {
        this.images = [];
        this.currentImageId = null;
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadImages();
        this.initializeSortable();
    }

    bindEvents() {
        const uploadForm = document.getElementById('pt-uploadImageForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.uploadImage();
            });
        }

        const editForm = document.getElementById('pt-editImageForm');
        if (editForm) {
            editForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.updateImage();
            });
        }

        const fileInput = document.getElementById('pt-imageFile');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.showImagePreview(e.target.files[0]));
        }

        const deleteBtn = document.getElementById('pt-confirmDeleteBtn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => this.deleteImage());
        }

        const uploadModal = document.getElementById('pt-uploadImageModal');
        if (uploadModal) {
            uploadModal.addEventListener('hidden.bs.modal', () => this.resetUploadForm());
        }

        const editModal = document.getElementById('pt-editImageModal');
        if (editModal) {
            editModal.addEventListener('hidden.bs.modal', () => this.resetEditForm());
        }
    }

    initializeSortable() {
        const grid = document.getElementById('pt-images-grid');
        if (grid) {
            new Sortable(grid, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: () => this.updateDisplayOrder()
            });
        }
    }

    async loadImages() {
        try {
            this.showLoading(true);
            const response = await fetch('/admin/past-trips/list');
            const data = await response.json();
            if (data.success) {
                this.images = data.images || [];
                this.updateStats(data.stats || {});
                this.renderImages();
            } else {
                this.showError('Failed to load past trip images');
            }
        } catch (e) {
            console.error(e);
            this.showError('Failed to load past trip images');
        } finally {
            this.showLoading(false);
        }
    }

    renderImages() {
        const grid = document.getElementById('pt-images-grid');
        const noImages = document.getElementById('pt-no-images');
        if (!grid || !noImages) return;

        if (this.images.length === 0) {
            grid.style.display = 'none';
            noImages.style.display = 'block';
            return;
        }
        grid.style.display = 'flex';
        noImages.style.display = 'none';
        grid.innerHTML = this.images.map(img => this.createImageCard(img)).join('');
    }

    createImageCard(image) {
        const statusClass = image.is_active ? 'success' : 'secondary';
        const statusText = image.is_active ? 'Active' : 'Inactive';
        return `
            <div class="col-md-6 col-lg-4 mb-4" data-image-id="${image.id}">
                <div class="card h-100">
                    <div class="card-img-top position-relative" style="height: 200px; overflow: hidden;">
                        <img src="../../../public/images/past-trips/${image.filename}" class="w-100 h-100" style="object-fit: cover;" alt="${image.title || 'Past Trip Image'}">
                        <div class="position-absolute top-0 end-0 p-2">
                            <span class="badge bg-${statusClass}">${statusText}</span>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title">${image.title || 'Untitled'}</h6>
                        <p class="card-text text-muted small">${image.description || 'No description'}</p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">Order: ${image.display_order}</small>
                                <small class="text-muted">${this.formatDate(image.created_at)}</small>
                            </div>
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="pastTripsManager.editImage(${image.id})"><i class="bi bi-pencil"></i> Edit</button>
                                <button type="button" class="btn btn-outline-${image.is_active ? 'warning' : 'success'} btn-sm" onclick="pastTripsManager.toggleStatus(${image.id})"><i class="bi bi-${image.is_active ? 'eye-slash' : 'eye'}"></i> ${image.is_active ? 'Deactivate' : 'Activate'}</button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="pastTripsManager.confirmDelete(${image.id})"><i class="bi bi-trash"></i> Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    async uploadImage() {
        const form = document.getElementById('pt-uploadImageForm');
        const formData = new FormData(form);
        const uploadBtn = document.getElementById('pt-uploadBtn');
        const spinner = uploadBtn.querySelector('.spinner-border');
        try {
            uploadBtn.disabled = true;
            spinner.classList.remove('d-none');
            const response = await fetch('/admin/past-trips/upload', { method: 'POST', body: formData });
            const data = await response.json();
            if (data.success) {
                // this.showSuccess(data.message);
                const modal = bootstrap.Modal.getInstance(document.getElementById('pt-uploadImageModal'));
                modal.hide();
                // Remove any lingering backdrop
                setTimeout(() => {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => backdrop.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }, 300);
                this.loadImages();
            } else {
                this.showError(data.message);
            }
        } catch (e) {
            console.error(e);
            this.showError('Failed to upload image');
        } finally {
            uploadBtn.disabled = false;
            spinner.classList.add('d-none');
        }
    }

    editImage(id) {
        const image = this.images.find(i => i.id == id);
        if (!image) return;
        this.currentImageId = id;
        document.getElementById('pt-editImageId').value = image.id;
        document.getElementById('pt-editImageTitle').value = image.title || '';
        document.getElementById('pt-editImageDescription').value = image.description || '';
        document.getElementById('pt-editDisplayOrder').value = image.display_order;
        document.getElementById('pt-editImageActive').checked = image.is_active == 1;
        new bootstrap.Modal(document.getElementById('pt-editImageModal')).show();
    }

    async updateImage() {
        if (!this.currentImageId) return;
        const payload = {
            id: this.currentImageId,
            title: document.getElementById('pt-editImageTitle').value,
            description: document.getElementById('pt-editImageDescription').value,
            display_order: parseInt(document.getElementById('pt-editDisplayOrder').value),
            is_active: document.getElementById('pt-editImageActive').checked ? 1 : 0
        };
        try {
            const response = await fetch('/admin/past-trips/update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (data.success) {
                // this.showSuccess(data.message);
                const modal = bootstrap.Modal.getInstance(document.getElementById('pt-editImageModal'));
                modal.hide();
                // Remove any lingering backdrop
                setTimeout(() => {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => backdrop.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }, 300);
                this.loadImages();
            } else {
                this.showError(data.message);
            }
        } catch (e) {
            console.error(e);
            this.showError('Failed to update image');
        }
    }

    async toggleStatus(id) {
        try {
            const response = await fetch('/admin/past-trips/toggle-status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await response.json();
            if (data.success) {
                // this.showSuccess(data.message);
                this.loadImages();
            } else {
                this.showError(data.message);
            }
        } catch (e) {
            console.error(e);
            this.showError('Failed to update image status');
        }
    }

    confirmDelete(id) {
        this.currentImageId = id;
        new bootstrap.Modal(document.getElementById('pt-deleteImageModal')).show();
    }

    async deleteImage() {
        if (!this.currentImageId) return;
        try {
            const response = await fetch('/admin/past-trips/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: this.currentImageId })
            });
            const data = await response.json();
            if (data.success) {
                // this.showSuccess(data.message);
                const modal = bootstrap.Modal.getInstance(document.getElementById('pt-deleteImageModal'));
                modal.hide();
                // Remove any lingering backdrop
                setTimeout(() => {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => backdrop.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }, 300);
                this.loadImages();
            } else {
                this.showError(data.message);
            }
        } catch (e) {
            console.error(e);
            this.showError('Failed to delete image');
        }
    }

    async updateDisplayOrder() {
        const cards = document.querySelectorAll('#pt-images-grid [data-image-id]');
        const orders = {};
        cards.forEach((card, index) => { orders[card.dataset.imageId] = index; });
        try {
            const response = await fetch('/admin/past-trips/update-order', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ orders })
            });
            const data = await response.json();
            if (data.success) {
                // this.showSuccess('Display order updated successfully');
                this.loadImages();
            } else {
                this.showError(data.message);
            }
        } catch (e) {
            console.error(e);
            this.showError('Failed to update display order');
        }
    }

    showImagePreview(file) {
        const preview = document.getElementById('pt-imagePreview');
        if (!file) {
            preview.innerHTML = `
                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2">Select an image to preview</p>
            `;
            return;
        }
        if (!file.type.startsWith('image/')) {
            preview.innerHTML = `
                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                <p class="text-warning mt-2">Please select an image file</p>
            `;
            return;
        }
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.innerHTML = `
                <img src="${e.target.result}" class="img-fluid" style="max-height: 180px; object-fit: contain;">
                <p class="text-muted mt-2 small">${file.name}</p>
            `;
        };
        reader.readAsDataURL(file);
    }

    updateStats(stats) {
        document.getElementById('pt-stat-total').textContent = stats.total_images || 0;
        document.getElementById('pt-stat-active').textContent = stats.active_images || 0;
        document.getElementById('pt-stat-inactive').textContent = stats.inactive_images || 0;
    }

    showLoading(show) {
        const el = document.getElementById('pt-loading-spinner');
        if (el) el.style.display = show ? 'block' : 'none';
    }

    resetUploadForm() {
        const form = document.getElementById('pt-uploadImageForm');
        if (form) form.reset();
        const preview = document.getElementById('pt-imagePreview');
        if (preview) {
            preview.innerHTML = `
                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2">Select an image to preview</p>
            `;
        }
    }

    resetEditForm() {
        const form = document.getElementById('pt-editImageForm');
        if (form) form.reset();
        this.currentImageId = null;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString();
    }

    showSuccess(message) {
        if (window.Swal) {
            Swal.fire({ icon: 'success', title: 'Success!', text: message, timer: 2000, timerProgressBar: true, confirmButtonColor: '#28a745' });
        } else {
            alert(message);
        }
    }

    showError(message) {
        if (window.Swal) {
            Swal.fire({ icon: 'error', title: 'Error!', text: message, confirmButtonColor: '#dc3545' });
        } else {
            alert(message);
        }
    }
    
    // Global function to clean up modal backdrops
    static cleanupModalBackdrops() {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    window.pastTripsManager = new PastTripsManager();
    
    // Add global event listeners for modal cleanup
    document.addEventListener('hidden.bs.modal', function(e) {
        PastTripsManager.cleanupModalBackdrops();
    });
});


