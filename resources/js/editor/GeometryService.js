/**
 * GeometryService.js — API REST para o editor de mapas.
 * Inclui endpoint de sincronização para colaboração em tempo real.
 */

const BASE = '/api/maps';

function csrf() {
    // Try meta tag first, then XSRF-TOKEN cookie (Laravel always sets it)
    const meta = document.querySelector('meta[name="csrf-token"]')?.content;
    if (meta) return meta;
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

function csrfHeaderName() {
    const meta = document.querySelector('meta[name="csrf-token"]')?.content;
    return meta ? 'X-CSRF-TOKEN' : 'X-XSRF-TOKEN';
}

async function request(method, url, body = null) {
    const token = csrf();
    const headerName = csrfHeaderName();
    const opts = {
        method,
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            [headerName]: token,
            'Accept': 'application/json',
        },
    };
    if (body !== null) opts.body = JSON.stringify(body);

    const res = await fetch(url, opts);
    if (res.status === 204) return null;
    const json = await res.json();
    if (!res.ok) throw new Error(json.message ?? `HTTP ${res.status}`);
    return json;
}

// ─── Camadas ───────────────────────────────────────────────

export function fetchLayers(municipioId) {
    return request('GET', `${BASE}/${municipioId}/layers`);
}

export function ensureLayer(municipioId, tipo, extras = {}) {
    return request('POST', `${BASE}/${municipioId}/layers`, { tipo, ...extras });
}

export function updateLayer(layerId, patch) {
    return request('PATCH', `${BASE}/layers/${layerId}`, patch);
}

// ─── Features ──────────────────────────────────────────────

export function fetchFeatures(municipioId, params = {}) {
    const qs = new URLSearchParams(params).toString();
    return request('GET', `${BASE}/${municipioId}/features${qs ? '?' + qs : ''}`);
}

export function createFeature(municipioId, payload) {
    return request('POST', `${BASE}/${municipioId}/features`, payload);
}

export function updateFeature(featureId, patch) {
    return request('PUT', `${BASE}/features/${featureId}`, patch);
}

export function deleteFeature(featureId) {
    return request('DELETE', `${BASE}/features/${featureId}`);
}

// ─── Sincronização (Tempo Real via Polling) ────────────────

export function syncFeatures(municipioId, params = {}) {
    const qs = new URLSearchParams(params).toString();
    return request('GET', `${BASE}/${municipioId}/sync${qs ? '?' + qs : ''}`);
}

// ─── Exportação ────────────────────────────────────────────

export function exportGeoJson(municipioId) {
    return request('GET', `${BASE}/${municipioId}/export`);
}

// ─── Informações do Município ──────────────────────────────

export function fetchMunicipioInfo(municipioId) {
    return request('GET', `${BASE}/${municipioId}/info`);
}
