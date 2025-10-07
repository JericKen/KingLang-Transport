<?php 
// Require authentication check (included from controller)
require_once __DIR__ . "/../../models/admin/Settings.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Kinglang Booking</title>
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-green: #198754;
            --secondary-green: #28a745;
            --light-green: #d1f7c4;
            --hover-green: #20c997;
        }
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
        .nav-pills .nav-link {
            color: #212529;
        }
        .setting-card {
            transition: all 0.3s ease;
        }
        /* .setting-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        } */
        .badge-public {
            background-color: #0d6efd;
            color: white;
        }
        .badge-private {
            background-color: #6c757d;
            color: white;
        }
        @media (min-width: 1400px) {
            .container-fluid {
                max-width: 98%;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</head>
<body>
    <?php include_once __DIR__ . "/../assets/admin_sidebar.php"; ?>

    <div class="content collapsed" id="content">
        <div class="container-fluid py-3 px-3 px-xl-4">
            <!-- Header with admin profile styled like payment management -->
            <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap p-0 m-0 mb-2">
                <div class="p-0">
                    <h2><i class="fas fa-cogs me-2 text-success"></i>System Settings</h2>
                    <p class="text-muted mb-0">Manage application settings and configurations</p>
                </div>
                <?php include_once __DIR__ . "/../assets/admin_profile.php"; ?>
            </div>
            <hr>

            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5>Setting Groups</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                <?php
                                $first = true;
                                foreach (array_keys($groupedSettings) as $group) {
                                    $groupName = ucfirst($group);
                                    $active = $first ? 'active' : '';
                                    echo "<a class='nav-link $active' id='v-pills-$group-tab' data-bs-toggle='pill' href='#v-pills-$group' role='tab' aria-controls='v-pills-$group' aria-selected='true'>
                                            <i class='fas fa-layer-group me-2'></i>$groupName
                                        </a>";
                                    $first = false;
                                }
                                ?>
                                <a class="nav-link" id="v-pills-new-tab" data-bs-toggle="pill" href="#v-pills-new" role="tab" aria-controls="v-pills-new" aria-selected="false">
                                    <i class="fas fa-plus-circle me-2"></i>Add New Setting
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="card">
                        <div class="card-body">
                            <div class="tab-content" id="v-pills-tabContent">
                                <?php
                                $first = true;
                                foreach ($groupedSettings as $group => $settings) {
                                    $active = $first ? 'show active' : '';
                                    echo "<div class='tab-pane fade $active' id='v-pills-$group' role='tabpanel' aria-labelledby='v-pills-$group-tab'>";
                                    echo "<h4 class='mb-3'>" . ucfirst($group) . " Settings</h4>";
                                    echo "<div class='alert alert-info'><i class='fas fa-info-circle me-2'></i>These settings control " . strtolower(ucfirst($group)) . " functionality of the application.</div>";
                                    echo "<form id='form-$group'>";
                                    
                                    foreach ($settings as $setting) {
                                        $inputType = 'text';
                                        $value = htmlspecialchars($setting['setting_value']);
                                        $isPublic = $setting['is_public'] ? 'true' : 'false';
                                        $publicBadge = $setting['is_public'] ? '<span class="badge badge-public ms-2">Public</span>' : '<span class="badge badge-private ms-2">Private</span>';
                                        
                                        // Determine input type based on value or key name
                                        if (is_numeric($value) && strpos($value, '.') === false) {
                                            $inputType = 'number';
                                        } elseif ($value === '0' || $value === '1' || $setting['setting_key'] === 'enable_email_notifications' || $setting['setting_key'] === 'enable_sms_notifications' || $setting['setting_key'] === 'allow_rebooking') {
                                            $inputType = 'checkbox';
                                            $checked = $value == '1' ? 'checked' : '';
                                        } elseif (stripos($setting['setting_key'], 'password') !== false) {
                                            $inputType = 'password';
                                        } elseif (stripos($setting['setting_key'], 'email') !== false) {
                                            $inputType = 'email';
                                        } elseif (strlen($value) > 100) {
                                            $inputType = 'textarea';
                                        }
                                        
                                        echo "<div class='mb-3 card setting-card'>";
                                        echo "<div class='card-body'>";
                                        echo "<label class='form-label'>" . ucwords(str_replace('_', ' ', $setting['setting_key'])) . $publicBadge . "</label>";
                                        
                                        if ($inputType === 'textarea') {
                                            echo "<textarea class='form-control' name='{$setting['setting_key']}' rows='3'>$value</textarea>";
                                        } elseif ($inputType === 'checkbox') {
                                            echo "<div class='form-check form-switch'>";
                                            echo "<input class='form-check-input' type='checkbox' id='{$setting['setting_key']}' name='{$setting['setting_key']}' value='1' $checked>";
                                            echo "</div>";
                                        } else if ($setting['setting_key'] === 'diesel_price') {
                                            // Special handling for diesel price to allow decimals
                                            echo "<input type='number' step='0.01' class='form-control' name='{$setting['setting_key']}' value='$value'>";
                                        } else {
                                            echo "<input type='$inputType' class='form-control' name='{$setting['setting_key']}' value='$value'>";
                                        }
                                        
                                        echo "<small class='form-text text-muted mt-2'>Setting ID: {$setting['setting_key']}</small>";
                                        echo "</div>";
                                        echo "</div>";
                                    }
                                    
                                    echo "<div class='d-flex justify-content-end mt-3'>";
                                    echo "<button type='submit' class='btn btn-primary'><i class='fas fa-save me-2'></i>Save " . ucfirst($group) . " Settings</button>";
                                    echo "</div>";
                                    echo "</form>";
                                    echo "</div>";
                                    
                                    $first = false;
                                }
                                ?>
                                
                                <!-- Add New Setting Tab -->
                                <div class="tab-pane fade" id="v-pills-new" role="tabpanel" aria-labelledby="v-pills-new-tab">
                                    <h4 class="mb-3">Add New Setting</h4>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Adding new settings should be done with care. Use existing settings when possible.
                                    </div>
                                    <form id="form-new-setting">
                                        <div class="mb-3">
                                            <label class="form-label">Setting Key</label>
                                            <input type="text" class="form-control" name="key" required placeholder="e.g. new_feature_enabled">
                                            <small class="form-text text-muted">Use lowercase letters, numbers, and underscores only.</small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Setting Value</label>
                                            <input type="text" class="form-control" name="value" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Setting Group</label>
                                            <select class="form-select" name="group" required>
                                                <?php foreach (array_keys($groupedSettings) as $group): ?>
                                                    <option value="<?= $group ?>"><?= ucfirst($group) ?></option>
                                                <?php endforeach; ?>
                                                <option value="custom">Create New Group</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 d-none" id="custom-group-container">
                                            <label class="form-label">New Group Name</label>
                                            <input type="text" class="form-control" name="custom_group" placeholder="e.g. security">
                                        </div>
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" name="is_public" id="is_public">
                                            <label class="form-check-label" for="is_public">Make this setting public</label>
                                            <small class="form-text text-muted d-block">Public settings are accessible to the frontend/client-side.</small>
                                        </div>
                                        <div class="d-flex justify-content-end mt-3">
                                            <button type="submit" class="btn btn-success"><i class="fas fa-plus-circle me-2"></i>Add Setting</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../../../public/js/assets/sidebar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show/hide custom group input when 'Create New Group' is selected
            const groupSelect = document.querySelector('select[name="group"]');
            const customGroupContainer = document.getElementById('custom-group-container');
            
            if (groupSelect) {
                groupSelect.addEventListener('change', function() {
                    if (this.value === 'custom') {
                        customGroupContainer.classList.remove('d-none');
                        document.querySelector('input[name="custom_group"]').required = true;
                    } else {
                        customGroupContainer.classList.add('d-none');
                        document.querySelector('input[name="custom_group"]').required = false;
                    }
                });
            }
            
            // Handle saving settings for each group
            <?php foreach (array_keys($groupedSettings) as $group): ?>
            document.getElementById('form-<?= $group ?>').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const form = this;
                const formData = new FormData(form);
                const settings = {};
                
                for (const [key, value] of formData.entries()) {
                    settings[key] = value;
                }
                
                // Handle checkboxes that aren't checked (they won't be in FormData)
                const checkboxes = form.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach(checkbox => {
                    if (!formData.has(checkbox.name)) {
                        settings[checkbox.name] = '0';
                    }
                });
                
                try {
                    const response = await fetch('/admin/update-settings', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ settings }),
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: `${data.message}`,
                            timer: 2000,
                            timerProgressBar: true
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to update settings'
                        });
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An unexpected error occurred'
                    });
                }
            });
            <?php endforeach; ?>
            
            // Handle adding a new setting
            document.getElementById('form-new-setting').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const key = formData.get('key');
                const value = formData.get('value');
                let group = formData.get('group');
                
                if (group === 'custom') {
                    group = formData.get('custom_group');
                    if (!group) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Please enter a group name'
                        });
                        return;
                    }
                }
                
                const isPublic = formData.get('is_public') ? true : false;
                
                try {
                    const response = await fetch('/admin/add-setting', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ 
                            key, 
                            value, 
                            group, 
                            is_public: isPublic 
                        }),
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Setting added successfully',
                            confirmButtonText: 'Reload Page'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to add setting'
                        });
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An unexpected error occurred'
                    });
                }
            });
        });
    </script>
</body>
</html> 