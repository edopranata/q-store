import { defineBoot } from '#q-app/wrappers'
import { useAuthStore } from 'src/stores/auth'

export default defineBoot(async ({ router }) => {
  const authStore = useAuthStore()
  
  // Initialize auth state from localStorage
  authStore.initializeAuth()
  
  // If user has token, try to refresh user data from server
  if (authStore.token) {
    try {
      await authStore.refreshUser()
    } catch (error) {
      console.error('Failed to refresh user data on app initialization:', error)
      // If refresh fails, the auth store will handle logout automatically
    }
  }
  
  // Add router beforeEach hook for additional session validation
  router.beforeEach(async (to, from, next) => {
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
    
    // If route requires auth and user has token, ensure user data is fresh
    if (requiresAuth && authStore.token && !authStore.user) {
      try {
        const success = await authStore.refreshUser()
        if (!success) {
          // If refresh fails, redirect to login
          next({
            name: 'login',
            query: { redirect: to.fullPath, reason: 'session_expired' }
          })
          return
        }
      } catch (error) {
        console.error('Session validation error:', error)
        next({
          name: 'login',
          query: { redirect: to.fullPath, reason: 'validation_error' }
        })
        return
      }
    }
    
    next()
  })
})