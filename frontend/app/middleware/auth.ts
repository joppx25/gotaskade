import { useAuthStore } from '~/stores/auth'

export default defineNuxtRouteMiddleware(async (to) => {
  const authStore = useAuthStore()

  if (!authStore.isAuthenticated) {
    try {
      await authStore.fetchUser()
    }
    catch {
      // Session invalid or expired
    }
  }

  if (!authStore.isAuthenticated && to.path !== '/login') {
    return navigateTo('/login', { replace: true })
  }
})
