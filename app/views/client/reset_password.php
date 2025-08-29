<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../../../public/css/login-signup.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Reset Password</title>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">Reset Your Password</h4>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Please enter your new password below.</p>
                        <form method="" action="" id="resetPasswordForm">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">  
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password" id="new_password" placeholder="Enter your new password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm your new password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Reset Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('resetPasswordForm').addEventListener('submit', async function (event) {
            event.preventDefault(); 

            const formData = new FormData(this);
            const token = formData.get('token');
            const newPassword = formData.get('new_password');
            const confirmPassword = formData.get('confirm_password');
            
            // Basic password validation
            if (newPassword !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Passwords Do Not Match',
                    text: 'Please make sure your passwords match',
                    timer: 2000,
                    timerProgressBar: true
                });
                return;
            }

            try {
                const response = await fetch('/update-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ token, newPassword })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Password reset successfully!',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.href = '/home/login';
                    });
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
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An unexpected error occurred. Please try again.',
                    timer: 2000,
                    timerProgressBar: true
                });
            }
        });
    </script>
</body>
</html>