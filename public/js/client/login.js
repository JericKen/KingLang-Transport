    document.addEventListener('DOMContentLoaded', function() {

    // Apply entry animation for client login page

    const contentDiv = document.querySelector('.content');

    if (contentDiv) {

        // Remove animation classes first (in case they're already there)

        contentDiv.classList.remove('animate-in');

        contentDiv.classList.remove('animation-complete');

        

        // Force browser reflow to ensure the animation runs again

        void contentDiv.offsetWidth;

        

        // Add animation class

        contentDiv.classList.add('animate-in');

        

        // After animations complete, add animation-complete class to maintain visibility

        setTimeout(function() {

            contentDiv.classList.add('animation-complete');

        }, 2000);

    }



    // Handle form submission with AJAX

    const loginForm = document.getElementById('loginForm');

    if (loginForm) {

        loginForm.addEventListener('submit', function(e) {

            e.preventDefault();

            

            const email = document.getElementById('email').value;

            const password = document.getElementById('password').value;

            

            fetch('/client/login', {

                method: 'POST',

                headers: {

                    'Content-Type': 'application/json'

                },

                body: JSON.stringify({

                    email: email,

                    password: password

                })

            })

            .then(response => response.json())

            .then(data => {

                if (data.success) {

                    // Show success message with SweetAlert2

                    // Swal.fire({

                    //     icon: 'success',

                    //     title: 'Login Successful',

                    //     text: 'Redirecting you to dashboard...',

                    //     timer: 1500,

                    //     timerProgressBar: true,

                    //     showConfirmButton: false

                    // }).then(() => {

                    //     // Add page transition before redirect

                    //     document.body.style.transition = 'opacity 0.5s ease';

                    //     document.body.style.opacity = '0';

                        

                    //     setTimeout(() => {

                    //         window.location.href = data.redirect || '/home/booking-requests';

                    //     }, 300);

                    // });

                    document.body.style.transition = 'opacity 0.5s ease';

                    document.body.style.opacity = '0';

                    

                    setTimeout(() => {

                        window.location.href = data.redirect || '/home/booking-requests';

                    }, 300);

                } else {

                    // Show error message with SweetAlert2

                    Swal.fire({

                        icon: 'error',

                        title: 'Login Failed',

                        text: data.message || 'Invalid credentials. Please try again.',

                        timer: 1500,

                        timerProgressBar: true,

                        confirmButtonColor: '#28a745'

                    });

                    

                    // Clear password field

                    document.getElementById('password').value = '';

                }

            })

            .catch(error => {

                console.error('Error:', error);

                // Show generic error message with SweetAlert2

                Swal.fire({

                    icon: 'error',

                    title: 'Something went wrong',

                    text: 'Please try again later.',

                    confirmButtonColor: '#28a745'

                });

            });

        });

    }

});