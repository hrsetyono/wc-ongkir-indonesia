/**
 * Simple GET and POST functions that return Promise.
 *
 * Example:
 *   api.get(url).then((result) => { .. });
 *   api.post(url, data).then((result) => { ... });
 */
const baseURL = ongkirLocalize.ONGKIR_API;
const api = {
  get(endpoint) {
    return window.fetch(`${baseURL}${endpoint}`, {
      method: 'GET',
      headers: { Accept: 'application/json' },
    })
      .then(this.handleError)
      .then(this.handleContentType)
      .catch(this.throwError);
  },

  post(endpoint, body) {
    return window.fetch(`${baseURL}${endpoint}`, {
      method: 'POST',
      headers: { 'content-type': 'application/json' },
      body: JSON.stringify(body),
    })
      .then(this.handleError)
      .then(this.handleContentType)
      .catch(this.throwError);
  },

  handleError(err) {
    return err.ok ? err : Promise.reject(err.statusText);
  },

  handleContentType(res) {
    const contentType = res.headers.get('content-type');
    if (contentType && contentType.includes('application/json')) {
      return res.json();
    }
    return Promise.reject('Oops, we haven\'t got JSON!');
  },

  throwError(err) {
    throw new Error(err);
  },
};

export default api;
