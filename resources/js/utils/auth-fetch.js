export async function authFetch(url, options = {}) {
    const token = localStorage.getItem('auth_token');

    if (!options.headers) options.headers = {};
    options.headers['Authorization'] = token ? `Bearer ${token}` : '';
    options.headers['Accept'] = 'application/json';

    const response = await fetch(url, options);

    if (response.status === 401) {
        localStorage.removeItem('auth_token');
        window.location.href = '/e-daily-report/login';
    }

    return response;
}
