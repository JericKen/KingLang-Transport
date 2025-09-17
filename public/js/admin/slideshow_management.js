// Slideshow Management JavaScript
class SlideshowManager {
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
        // Upload form submission
        document.getElementById('uploadImageForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.uploadImage();
        });
        
        // Edit form submission
        document.getElementById('editImageForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.updateImage();
        });
        
        // File input change for preview
        document.getElementById('imageFile').addEventListener('change', (e) => {
            this.showImagePreview(e.target.files[0]);
        });
        
        // Delete confirmation
        document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
            this.deleteImage();
        });
        
        // Modal cleanup
        document.getElementById('uploadImageModal').addEventListener('hidden.bs.modal', () => {
            this.resetUploadForm();
        });
        
        document.getElementById('editImageModal').addEventListener('hidden.bs.modal', () => {
            this.resetEditForm();
        });
    }
    
    initializeSortable() {
        const imagesGrid = document.getElementById('images-grid');
        if (imagesGrid) {
            new Sortable(imagesGrid, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: (evt) => {
                    this.updateDisplayOrder();
                }
            });
        }
    }
    
    async loadImages() {
        try {
            this.showLoading(true);
            
            const response = await fetch('/admin/slideshow/list');
            const data = await response.json();
            
            if (data.success) {
                this.images = data.images;
                this.updateStats(data.stats);
                this.renderImages();
            } else {
                this.showError('Failed to load images');
            }
        } catch (error) {
            console.error('Error loading images:', error);
            this.showError('Failed to load images');
        } finally {
            this.showLoading(false);
        }
    }
    
    renderImages() {
        const grid = document.getElementById('images-grid');
        const noImages = document.getElementById('no-images');
        
        if (this.images.length === 0) {
            grid.style.display = 'none';
            noImages.style.display = 'block';
            return;
        }
        
        grid.style.display = 'flex';
        noImages.style.display = 'none';
        
        grid.innerHTML = this.images.map(image => this.createImageCard(image)).join('');
    }
    
    createImageCard(image) {
        const statusClass = image.is_active ? 'success' : 'secondary';
        const statusText = image.is_active ? 'Active' : 'Inactive';
        
        return `
            <div class="col-md-6 col-lg-4 mb-4" data-image-id="${image.id}">
                <div class="card h-100">
                    <div class="card-img-top position-relative" style="height: 200px; overflow: hidden;">
                        <img src="../../../public/images/slideshow/${image.filename}" 
                             class="w-100 h-100" 
                             style="object-fit: cover;" 
                             alt="${image.title || 'Slideshow Image'}">
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
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="slideshowManager.editImage(${image.id})">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button type="button" class="btn btn-outline-${image.is_active ? 'warning' : 'success'} btn-sm" onclick="slideshowManager.toggleStatus(${image.id})">
                                    <i class="bi bi-${image.is_active ? 'eye-slash' : 'eye'}"></i> ${image.is_active ? 'Deactivate' : 'Activate'}
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="slideshowManager.confirmDelete(${image.id})">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    async uploadImage() {
        const form = document.getElementById('uploadImageForm');
        const formData = new FormData(form);
        const uploadBtn = document.getElementById('uploadBtn');
        const spinner = uploadBtn.querySelector('.spinner-border');
        
        try {
            uploadBtn.disabled = true;
            spinner.classList.remove('d-none');
            
            const response = await fetch('/admin/slideshow/upload', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(data.message);
                bootstrap.Modal.getInstance(document.getElementById('uploadImageModal')).hide();
                this.loadImages();
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            console.error('Error uploading image:', error);
            this.showError('Failed to upload image');
        } finally {
            uploadBtn.disabled = false;
            spinner.classList.add('d-none');
        }
    }
    
    editImage(imageId) {
        const image = this.images.find(img => img.id == imageId);
        if (!image) return;
        
        this.currentImageId = imageId;
        
        document.getElementById('editImageId').value = image.id;
        document.getElementById('editImageTitle').value = image.title || '';
        document.getElementById('editImageDescription').value = image.description || '';
        document.getElementById('editDisplayOrder').value = image.display_order;
        document.getElementById('editImageActive').checked = image.is_active == 1;
        
        const modal = new bootstrap.Modal(document.getElementById('editImageModal'));
        modal.show();
    }
    
    async updateImage() {
        if (!this.currentImageId) return;
        
        const formData = {
            id: this.currentImageId,
            title: document.getElementById('editImageTitle').value,
            description: document.getElementById('editImageDescription').value,
            display_order: parseInt(document.getElementById('editDisplayOrder').value),
            is_active: document.getElementById('editImageActive').checked ? 1 : 0
        };
        
        try {
            const response = await fetch('/admin/slideshow/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(data.message);
                bootstrap.Modal.getInstance(document.getElementById('editImageModal')).hide();
                this.loadImages();
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            console.error('Error updating image:', error);
            this.showError('Failed to update image');
        }
    }
    
    async toggleStatus(imageId) {
        try {
            const response = await fetch('/admin/slideshow/toggle-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: imageId })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // this.showSuccess(data.message);
                this.loadImages();
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            console.error('Error toggling status:', error);
            this.showError('Failed to update image status');
        }
    }
    
    confirmDelete(imageId) {
        this.currentImageId = imageId;
        const modal = new bootstrap.Modal(document.getElementById('deleteImageModal'));
        modal.show();
    }
    
    async deleteImage() {
        if (!this.currentImageId) return;
        
        try {
            const response = await fetch('/admin/slideshow/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: this.currentImageId })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(data.message);
                bootstrap.Modal.getInstance(document.getElementById('deleteImageModal')).hide();
                this.loadImages();
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            console.error('Error deleting image:', error);
            this.showError('Failed to delete image');
        }
    }
    
    async updateDisplayOrder() {
        const imageCards = document.querySelectorAll('[data-image-id]');
        const orders = {};
        
        imageCards.forEach((card, index) => {
            const imageId = card.dataset.imageId;
            orders[imageId] = index;
        });
        
        try {
            const response = await fetch('/admin/slideshow/update-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ orders })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // this.showSuccess('Display order updated successfully');
                this.loadImages();
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            console.error('Error updating display order:', error);
            this.showError('Failed to update display order');
        }
    }
    
    showImagePreview(file) {
        const preview = document.getElementById('imagePreview');
        
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
        document.getElementById('stat-total').textContent = stats.total_images || 0;
        document.getElementById('stat-active').textContent = stats.active_images || 0;
        document.getElementById('stat-inactive').textContent = stats.inactive_images || 0;
    }
    
    showLoading(show) {
        document.getElementById('loading-spinner').style.display = show ? 'block' : 'none';
    }
    
    resetUploadForm() {
        document.getElementById('uploadImageForm').reset();
        document.getElementById('imagePreview').innerHTML = `
            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-2">Select an image to preview</p>
        `;
    }
    
    resetEditForm() {
        document.getElementById('editImageForm').reset();
        this.currentImageId = null;
    }
    
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString();
    }
    
    showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: message,
            timer: 2000,
            timerProgressBar: true,
            confirmButtonColor: '#28a745'
        });
    }
    
    showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: message,
            confirmButtonColor: '#dc3545'
        });
    }
}

// Initialize slideshow manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.slideshowManager = new SlideshowManager();
});

// Global function for refresh
function refreshData() {
    if (window.slideshowManager) {
        window.slideshowManager.loadImages();
    }
}
