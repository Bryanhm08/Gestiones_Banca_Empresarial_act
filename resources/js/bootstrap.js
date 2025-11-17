// resources/js/bootstrap.js
import axios from 'axios'

window.axios = axios

// Todas las peticiones se marcan como AJAX
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Leer el token CSRF del <meta> de Blade
const token = document
  .querySelector('meta[name="csrf-token"]')
  ?.getAttribute('content')

if (token) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token
} else {
  console.error('CSRF token not found')
}

/**
 * 🔁 MANEJO GLOBAL DE ERRORES 419
 *
 * Si por cualquier razón Laravel responde 419 (sesión caducada,
 * token viejo, etc.), recargamos la página para regenerar sesión
 * y token, en lugar de dejar el overlay gris.
 */
window.axios.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error?.response?.status

    if (status === 419) {
      console.warn('Respuesta 419 detectada. Recargando la página...')
      window.location.reload()
      // Nota: retornamos una promesa rechazada por si algún código extra lo espera,
      // pero la recarga se encargará de "resetear" la app.
    }

    return Promise.reject(error)
  }
)
