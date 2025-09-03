document.addEventListener('DOMContentLoaded', function() {

    // Apply entry animation for admin login page

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

            

            fetch('/admin/submit-login', {

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

                    // Add page transition before redirect

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

                    //         window.location.href = data.redirect || '/admin/dashboard';

                    //     }, 300);

                    // });

                    document.body.style.transition = 'opacity 0.5s ease';

                    document.body.style.opacity = '0';

                    

                    setTimeout(() => {

                        window.location.href = data.redirect || '/admin/dashboard';

                    }, 300);

                } else {

                    Swal.fire({

                        icon: 'error',

                        title: 'Login Failed',

                        text: data.message || 'Invalid credentials. Please try again.'

                    });

                }

            })

            .catch(error => {

                console.error('Error:', error);

                Swal.fire({

                    icon: 'error',

                    title: 'Error',

                    text: 'Something went wrong. Please try again later.'

                });

            });

        });

    }

});



