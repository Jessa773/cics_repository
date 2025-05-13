document.addEventListener('DOMContentLoaded', function () {
    /*** LOGIN FORM VALIDATION ***/
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            let isValid = true;

            // Remove existing errors
            document.querySelectorAll('.alert-danger').forEach(el => el.remove());

            if (!username) {
                isValid = false;
                showError('Username is required');
            }

            if (!password) {
                isValid = false;
                showError('Password is required');
            }

            if (!isValid) {
                e.preventDefault();
            }

            function showError(message) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger mt-2';
                errorDiv.textContent = message;
                loginForm.insertBefore(errorDiv, loginForm.firstChild);
            }
        });
    }

    /*** LIVE SEARCH FUNCTIONALITY ***/
    let searchTimeout;

    function debounce(func, wait) {
        return (...args) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    function displayResults(results) {
        const resultsContainer = document.getElementById('search-results');
        if (!resultsContainer) return;

        if (!results.length) {
            resultsContainer.innerHTML = '<p>No results found</p>';
            return;
        }

        const html = results.map(item => `
            <div class="search-item">
                <h3>${item.title}</h3>
                <p>${item.description || ''}</p>
            </div>
        `).join('');
        resultsContainer.innerHTML = html;
    }

    function searchSourceCode() {
        const searchInput = document.getElementById('search-input');
        const title = searchInput?.value.trim();
        const loadingIndicator = document.getElementById('loading-indicator');

        if (!title || title.length < 2) {
            document.getElementById('search-results').innerHTML = '<p>Please enter at least 2 characters</p>';
            return;
        }

        loadingIndicator?.classList.remove('hidden');

        fetch(`/controller/source_code/search?title=${encodeURIComponent(title)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                displayResults(data);
            })
            .catch(error => {
                console.error('Search error:', error);
                document.getElementById('search-results').innerHTML =
                    '<p class="error">An error occurred while searching. Please try again.</p>';
            })
            .finally(() => {
                loadingIndicator?.classList.add('hidden');
            });
    }

    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(searchSourceCode, 300));
    }
});
