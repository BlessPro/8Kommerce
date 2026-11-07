import http from 'k6/http';
import { check, sleep } from 'k6';

const baseUrl = (__ENV.BASE_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');
const storeSlug = __ENV.STORE_SLUG || '';
const productSlug = __ENV.PRODUCT_SLUG || '';
const profile = (__ENV.PROFILE || 'smoke').toLowerCase();

const profiles = {
  smoke: { vus: 2, duration: '30s' },
  steady: {
    stages: [
      { duration: '1m', target: 10 },
      { duration: '3m', target: 25 },
      { duration: '1m', target: 0 },
    ],
  },
  spike: {
    stages: [
      { duration: '30s', target: 20 },
      { duration: '30s', target: 100 },
      { duration: '2m', target: 100 },
      { duration: '1m', target: 0 },
    ],
  },
};

export const options = {
  ...(profiles[profile] || profiles.smoke),
  thresholds: {
    http_req_failed: ['rate<0.01'],
    http_req_duration: ['p(95)<1200', 'p(99)<2500'],
    checks: ['rate>0.99'],
  },
  userAgent: '8Kommerce-controlled-load-test/1.0',
};

function request(path, name) {
  const response = http.get(`${baseUrl}${path}`, {
    tags: { name },
    timeout: '10s',
  });

  check(response, {
    [`${name} returns successfully`]: (res) => res.status >= 200 && res.status < 400,
  });
}

export default function () {
  request('/live', 'live');
  request('/', 'landing');

  if (storeSlug) {
    request(`/s/${encodeURIComponent(storeSlug)}`, 'storefront');
  }

  if (productSlug) {
    request(`/p/${encodeURIComponent(productSlug)}`, 'product');
  }

  sleep(Math.random() * 2 + 0.5);
}
