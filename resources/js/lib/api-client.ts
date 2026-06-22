const API_BASE = '/api';

interface ApiResponse<T = any> {
    message: string;
    data: T;
}

interface PaginatedResponse<T> {
  current_page: number;
  data: T[];
  last_page: number;
  per_page: number;
  total: number;
}

async function request<T>(method: string, path: string, body?: object): Promise<T> {
    const token = getToken();
    const headers: Record<string, string> = {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    };

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await fetch(`${API_BASE}${path}`, {
        method,
        headers,
        body: body ? JSON.stringify(body) : undefined,
    });

    if (response.status === 401) {
        clearToken();
        window.location.href = '/login';
        throw new Error('Unauthenticated');
    }

    const data = await response.json();

    if (!response.ok) {
        const errorMessage = data.message || data.errors || 'Request failed';
        throw new Error(typeof errorMessage === 'string' ? errorMessage : JSON.stringify(errorMessage));
    }

    return data;
}

export function getToken(): string | null {
    return localStorage.getItem('api_token');
}

export function setToken(token: string): void {
    localStorage.setItem('api_token', token);
}

export function clearToken(): void {
    localStorage.removeItem('api_token');
}

export function setUser(user: object): void {
    localStorage.setItem('api_user', JSON.stringify(user));
}

export function getUser<T = any>(): T | null {
    const raw = localStorage.getItem('api_user');
    if (!raw) return null;
    try {
        return JSON.parse(raw) as T;
    } catch {
        return null;
    }
}

export function clearUser(): void {
    localStorage.removeItem('api_user');
}

export const api = {
    get: <T = any>(path: string) => request<ApiResponse<T>>('GET', path),
    post: <T = any>(path: string, body?: object) => request<ApiResponse<T>>('POST', path, body),
    postRaw: <T = any>(path: string, body?: object) => request<T>('POST', path, body),
    patch: <T = any>(path: string, body?: object) => request<ApiResponse<T>>('PATCH', path, body),
    delete: <T = any>(path: string) => request<ApiResponse<T>>('DELETE', path),
    getPaginated: <T = any>(path: string) => request<PaginatedResponse<T>>('GET', path),
};
