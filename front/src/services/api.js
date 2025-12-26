import axios from 'axios';

const api = axios.create({
    baseURL: '/', // This works because of the Vite proxy we configured
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

export default api;
