document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loginForm');
    const errorBox = document.getElementById('loginError');
    const submitButton = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent normal form submission

        const formData = new FormData(form);

        // Disable the button and show a loading state
        submitButton.disabled = true;
        submitButton.textContent = 'Logging in...';

        fetch('app/views/login.php', { // Make sure this path is correct
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                errorBox.style.display = 'block';
                errorBox.textContent = data.message || 'Login failed';
                submitButton.disabled = false; // Re-enable button on error
                submitButton.textContent = 'Login'; // Reset button text
            }
        })
        .catch(error => {
            console.error('Login error:', error);
            errorBox.style.display = 'block';
            errorBox.textContent = 'Network issue or server error. Please try again later.';
            submitButton.disabled = false; // Re-enable button on failure
            submitButton.textContent = 'Login'; // Reset button text
        });
    });
});
