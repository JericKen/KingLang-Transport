$("#signupForm").submit(function (e) {

    e.preventDefault();



    var formData = {

        firstName: $("#firstName").val(),

        lastName: $("#lastName").val(),

        companyName: $("#companyName").val(),

        email: $("#email").val(),

        contactNumber: formatPhoneNumberForDB($("#contactNumber").val()),

        password: $("#password").val(),

        confirmPassword: $("#confirmPassword").val()

    }



    console.log(formData);



    $.ajax({

        url: "/client/signup",

        type: "POST",

        contentType: "application/json",

        data: JSON.stringify(formData),

        success: function (response) {

            if (response.success) {

                Swal.fire({

                    icon: 'success',

                    title: 'Success!',

                    text: response.message,

                    timer: 2000,

                    timerProgressBar: true

                }).then(() => {

                    // Redirect to login page after success message

                    window.location.href = "/home/login";

                });

                $("#signupForm")[0].reset();

            } else {

                Swal.fire({

                    icon: 'error',

                    title: 'Registration Failed',

                    text: response.message,

                    timer: 2000,

                    timerProgressBar: true

                });

            }

        },

        error: function (xhr, status, error) {

            console.error(error);

            Swal.fire({

                icon: 'error',

                title: 'Error',

                text: 'An unexpected error occurred. Please try again.',

                timer: 2000,

                timerProgressBar: true

            });

        }

    });

});



// Helper function to format phone number for database storage

function formatPhoneNumberForDB(value) {

    if (!value || value.trim() === '') return '';

    

    // Remove all non-digits

    const digits = value.replace(/\D/g, '');



    // Expecting a Philippine mobile number starting with 09 and 11 digits total

    // if (digits.length !== 11 || !digits.startsWith('09')) {

    //     return digits;

    // }



    // Convert 09XX to +63 9XX and format as +63 917 123 4567

    return `+63 ${digits.substring(1, 4)} ${digits.substring(4, 7)} ${digits.substring(7, 11)}`;

}