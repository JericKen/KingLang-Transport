<!DOCTYPE html>
<html lang="en">
<head>    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Page Management</title>
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-green: #198754;
            --secondary-green: #28a745;
            --light-green: #d1f7c4;
        }

        body { font-family: 'Work Sans', system-ui, -apple-system, Segoe UI, Roboto, Arial, "Helvetica Neue", sans-serif; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
        }

         /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #999;
        }

        @media (max-width: 991.98px) {
            .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 575.98px) {
            .stats-grid { grid-template-columns: 1fr; }
        }

        .stat-item {
            background-color: #ffffff;
            border: 0;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.06);
            padding: 0.75rem 1rem;
        }

        .stat-number { font-weight: 800; font-size: 1.6rem; color: #333; }
        .stat-label { color: #6c757d; font-weight: 500; }

        .stat-row { display: flex; align-items: center; gap: 0.75rem; }
        .stats-icon {
            width: 42px; height: 42px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
        }
        .bg-soft-primary { background-color: rgba(25, 135, 84, 0.15); color: #198754; }
        .bg-soft-warning { background-color: rgba(255, 193, 7, 0.2); color: #856404; }
        .bg-soft-secondary { background-color: rgba(108, 117, 125, 0.15); color: #495057; }

        .nav-tabs .nav-link.active { background-color: var(--light-green); font-weight: 600; }
        .nav-tabs .nav-link { color: #198754; }

        #images-grid, #pt-images-grid { min-height: 60px; }

        .btn-primary { background-color: var(--primary-green); border-color: var(--primary-green); }
        .btn-primary:hover { background-color: #157347; border-color: #146c43; }
        .btn-outline-primary { color: var(--primary-green); border-color: var(--primary-green); }
        .btn-outline-primary:hover { background-color: var(--primary-green); border-color: var(--primary-green); }

        .modal-header.bg-light-green { background-color: var(--light-green); }
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.3) !important; /* Default is 0.5 */
        }
    </style>
</head>
<body>     
    <?php include __DIR__ . '/../assets/admin_sidebar.php'; ?>
    <div class="content collapsed" id="content">
        <div class="container-fluid py-3 px-4 px-xl-4">
            <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap p-0 m-0 mb-2">
                <div class="p-0">
                    <h3><i class="bi bi-images me-2 text-success"></i>Landing Media Management</h3>
                    <p class="text-muted mb-0">Manage slideshow images and past trip images for the home page</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <!-- <button class="btn btn-outline-primary" onclick="refreshData()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button> -->
                    <?php include_once __DIR__ . "/../assets/admin_profile.php"; ?>
                </div>
            </div>
            <ul class="nav nav-tabs mt-4" id="mediaTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="slideshow-tab" data-bs-toggle="tab" data-bs-target="#slideshow-panel" type="button" role="tab">Slideshow</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pasttrips-tab" data-bs-toggle="tab" data-bs-target="#pasttrips-panel" type="button" role="tab">Past Trips</button>
                </li>
            </ul>
            <div class="tab-content pt-3">
                <div class="tab-pane fade show active" id="slideshow-panel" role="tabpanel" aria-labelledby="slideshow-tab">
            
            <!-- Statistics -->
            <div class="stats-grid" id="stats-grid">
                <div class="stat-item">
                    <div class="stat-row">
                        <div class="stats-icon bg-soft-primary"><i class="bi bi-collection"></i></div>
                        <div>
                            <div class="stat-number" id="stat-total"><?= $stats['total_images'] ?? 0 ?></div>
                            <div class="stat-label">Total Images</div>
                        </div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-row">
                        <div class="stats-icon bg-soft-warning"><i class="bi bi-check-circle"></i></div>
                        <div>
                            <div class="stat-number" id="stat-active"><?= $stats['active_images'] ?? 0 ?></div>
                            <div class="stat-label">Active Images</div>
                        </div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-row">
                        <div class="stats-icon bg-soft-secondary"><i class="bi bi-slash-circle"></i></div>
                        <div>
                            <div class="stat-number" id="stat-inactive"><?= $stats['inactive_images'] ?? 0 ?></div>
                            <div class="stat-label">Inactive Images</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Upload Button -->
            <div class="mt-3 mb-4">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadImageModal">
                    <i class="fas fa-plus"></i> Upload Slideshow Image
                </button>
            </div>
            
            <!-- Images Grid -->
            <div class="row mt-4" id="images-grid">
                <!-- Images will be loaded here dynamically -->
            </div>
            
            <!-- Loading Spinner -->
            <div id="loading-spinner" class="text-center py-4" style="display: none;">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            
            <!-- No Images Message -->
            <div id="no-images" class="text-center py-4" style="display: none;">
                <i class="bi bi-images text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2">No slideshow images found. Upload your first image to get started.</p>
            </div>  
                </div>

                <div class="tab-pane fade" id="pasttrips-panel" role="tabpanel" aria-labelledby="pasttrips-tab">
                    <div class="stats-grid" id="pt-stats-grid">
                        <div class="stat-item">
                            <div class="stat-row">
                                <div class="stats-icon bg-soft-primary"><i class="bi bi-collection"></i></div>
                                <div>
                                    <div class="stat-number" id="pt-stat-total">0</div>
                                    <div class="stat-label">Total Images</div>
                                </div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-row">
                                <div class="stats-icon bg-soft-warning"><i class="bi bi-check-circle"></i></div>
                                <div>
                                    <div class="stat-number" id="pt-stat-active">0</div>
                                    <div class="stat-label">Active Images</div>
                                </div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-row">
                                <div class="stats-icon bg-soft-secondary"><i class="bi bi-slash-circle"></i></div>
                                <div>
                                    <div class="stat-number" id="pt-stat-inactive">0</div>
                                    <div class="stat-label">Inactive Images</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Button -->
                    <div class="mt-3 mb-4">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pt-uploadImageModal">
                            <i class="fas fa-plus"></i> Upload Past Trip Image
                        </button>
                    </div>

                    <div class="row mt-4" id="pt-images-grid"></div>

                    <div id="pt-loading-spinner" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <div id="pt-no-images" class="text-center py-4" style="display: none;">
                        <i class="bi bi-images text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No past trip images found. Upload your first image to get started.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Image Modal -->
    <div class="modal fade" id="uploadImageModal" tabindex="-1" aria-labelledby="uploadImageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadImageModalLabel">Upload New Slideshow Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="uploadImageForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="imageFile" class="form-label">Image File *</label>
                                    <input type="file" class="form-control" id="imageFile" name="image" accept="image/*" required>
                                    <div class="form-text">Supported formats: JPG, PNG, GIF. Max size: 5MB</div>
                                </div>
                                <div class="mb-3">
                                    <label for="imageTitle" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="imageTitle" name="title" placeholder="Enter image title">
                                </div>
                                <div class="mb-3">
                                    <label for="imageDescription" class="form-label">Description</label>
                                    <textarea class="form-control" id="imageDescription" name="description" rows="3" placeholder="Enter image description"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="displayOrder" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="displayOrder" name="display_order" value="0" min="0">
                                    <div class="form-text">Lower numbers appear first</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Image Preview</label>
                                    <div id="imagePreview" class="border rounded p-3 text-center" style="min-height: 200px; background-color: #f8f9fa;">
                                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2">Select an image to preview</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="uploadBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Upload Image
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Image Modal -->
    <div class="modal fade" id="editImageModal" tabindex="-1" aria-labelledby="editImageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editImageModalLabel">Edit Slideshow Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editImageForm">
                    <div class="modal-body">
                        <input type="hidden" id="editImageId">
                        <div class="mb-3">
                            <label for="editImageTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="editImageTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="editImageDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editImageDescription" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editDisplayOrder" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="editDisplayOrder" value="0" min="0">
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="editImageActive">
                                <label class="form-check-label" for="editImageActive">
                                    Active (visible in slideshow)
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteImageModal" tabindex="-1" aria-labelledby="deleteImageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteImageModalLabel">Delete Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this image? This action cannot be undone.</p>
                    <p class="text-danger"><strong>Note:</strong> The image file will also be removed from the server.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Image</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Past Trips Upload Modal -->
    <div class="modal fade" id="pt-uploadImageModal" tabindex="-1" aria-labelledby="pt-uploadImageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pt-uploadImageModalLabel">Upload New Past Trip Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="pt-uploadImageForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pt-imageFile" class="form-label">Image File *</label>
                                    <input type="file" class="form-control" id="pt-imageFile" name="image" accept="image/*" required>
                                    <div class="form-text">Supported formats: JPG, PNG, GIF. Max size: 5MB</div>
                                </div>
                                <div class="mb-3">
                                    <label for="pt-imageTitle" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="pt-imageTitle" name="title" placeholder="Enter image title">
                                </div>
                                <div class="mb-3">
                                    <label for="pt-imageDescription" class="form-label">Description</label>
                                    <textarea class="form-control" id="pt-imageDescription" name="description" rows="3" placeholder="Enter image description"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pt-displayOrder" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="pt-displayOrder" name="display_order" value="0" min="0">
                                    <div class="form-text">Lower numbers appear first</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Image Preview</label>
                                    <div id="pt-imagePreview" class="border rounded p-3 text-center" style="min-height: 200px; background-color: #f8f9fa;">
                                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2">Select an image to preview</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="pt-uploadBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Upload Image
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Past Trips Edit Modal -->
    <div class="modal fade" id="pt-editImageModal" tabindex="-1" aria-labelledby="pt-editImageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pt-editImageModalLabel">Edit Past Trip Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="pt-editImageForm">
                    <div class="modal-body">
                        <input type="hidden" id="pt-editImageId">
                        <div class="mb-3">
                            <label for="pt-editImageTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="pt-editImageTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="pt-editImageDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="pt-editImageDescription" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="pt-editDisplayOrder" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="pt-editDisplayOrder" value="0" min="0">
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="pt-editImageActive">
                                <label class="form-check-label" for="pt-editImageActive">
                                    Active (visible on home page)
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Past Trips Delete Modal -->
    <div class="modal fade" id="pt-deleteImageModal" tabindex="-1" aria-labelledby="pt-deleteImageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pt-deleteImageModalLabel">Delete Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this image? This action cannot be undone.</p>
                    <p class="text-danger"><strong>Note:</strong> The image file will also be removed from the server.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="pt-confirmDeleteBtn">Delete Image</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="../../../public/js/admin/slideshow_management.js"></script>
    <script src="../../../public/js/admin/past_trips_management.js"></script>
    <script src="../../../public/js/assets/sidebar.js"></script>
</body>
</html>
