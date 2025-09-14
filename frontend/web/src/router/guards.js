import { Notify } from 'quasar'
import { useAuthStore } from 'src/stores/auth'

/**
 * Check if user is authenticated
 * @returns {boolean} Authentication status
 */
export const isAuthenticated = () => {
  const authStore = useAuthStore()
  return authStore.isLoggedIn
}

/**
 * Authentication guard for protected routes
 * @param {Object} to - Target route
 * @param {Object} from - Source route
 * @param {Function} next - Navigation function
 */
export const authGuard = (to, from, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const requiresGuest = to.matched.some(record => record.meta.requiresGuest)
  const authenticated = isAuthenticated()

  if (requiresAuth && !authenticated) {
    // User needs to be authenticated but isn't
    const reason = to.query.reason
    let message = 'Please login to access this page'
    
    if (reason === 'session_expired') {
      message = 'Your session has expired. Please login again.'
    } else if (reason === 'validation_error') {
      message = 'Session validation failed. Please login again.'
    }
    
    Notify.create({
      type: 'warning',
      message,
      position: 'top'
    })
    next({
      name: 'login',
      query: { redirect: to.fullPath }
    })
  } else if (requiresGuest && authenticated) {
    // User should be guest but is authenticated
    next({ name: 'dashboard' })
  } else {
    // All good, proceed
    next()
  }
}

/**
 * Permission guard for role-based access
 * @param {Array} requiredPermissions - Required permissions
 * @returns {Function} Guard function
 */
export const permissionGuard = (requiredPermissions = []) => {
  return (to, from, next) => {
    if (!isAuthenticated()) {
      next({ name: 'login' })
      return
    }

    // Get user permissions from auth store
    const authStore = useAuthStore()
    const userPermissions = authStore.getPermissions
    
    const hasPermission = requiredPermissions.every(permission => 
      userPermissions.includes(permission)
    )

    if (requiredPermissions.length > 0 && !hasPermission) {
      Notify.create({
        type: 'negative',
        message: 'You do not have permission to access this page',
        position: 'top'
      })
      next({ name: 'dashboard' })
    } else {
      next()
    }
  }
}

/**
 * Navigation guard to set page title
 * @param {Object} to - Target route
 * @param {Object} from - Source route
 * @param {Function} next - Navigation function
 */
export const titleGuard = (to, from, next) => {
  // Set page title based on route meta or name
  const title = to.meta.title || to.name || 'Q-POS'
  document.title = `${title} - Q-POS`
  next()
}

/**
 * Loading guard to show/hide loading indicator
 * @param {Object} to - Target route
 * @param {Object} from - Source route
 * @param {Function} next - Navigation function
 */
export const loadingGuard = (to, from, next) => {
  // Show loading for route changes
  if (to.name !== from.name) {
    // Loading will be handled by individual components
  }
  next()
}