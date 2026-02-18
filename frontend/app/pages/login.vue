<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '~/stores/auth'

definePageMeta({
  layout: 'auth',
})

const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const isLoading = ref(false)
const error = ref('')

// Redirect to dashboard if already authenticated
onMounted(async () => {
  if (authStore.isAuthenticated) {
    await navigateTo('/')
  }
})

async function handleLogin() {
  error.value = ''

  if (!email.value.trim()) {
    error.value = 'Please enter your email address.'
    return
  }

  if (!password.value) {
    error.value = 'Please enter your password.'
    return
  }

  isLoading.value = true

  try {
    await authStore.login(email.value, password.value)
    // Hard redirect ensures the new page load picks up session cookies cleanly
    window.location.href = '/'
    return
  }
  catch (e: any) {
    if (e.status === 422) {
      error.value = e.message || 'The provided credentials are incorrect.'
    }
    else {
      error.value = 'An error occurred. Please try again.'
    }
  }
  finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="w-full max-w-md mx-4">
    <!-- Logo -->
    <div class="flex justify-center mb-8">
      <AppLogo size="lg" />
    </div>

    <!-- Login Card -->
    <Card class="border-border/40 shadow-sm">
      <CardHeader class="text-center space-y-1 pb-4">
        <CardTitle class="text-2xl font-bold">
          Sign In
        </CardTitle>
        <CardDescription>
          Login to continue using this app
        </CardDescription>
      </CardHeader>

      <CardContent>
        <form class="space-y-4" @submit.prevent="handleLogin">
          <!-- Error message -->
          <div
            v-if="error"
            class="text-sm text-destructive bg-destructive/10 px-3 py-2 rounded-md"
          >
            {{ error }}
          </div>

          <!-- Email -->
          <div class="space-y-2">
            <Label for="email">Email</Label>
            <Input
              id="email"
              v-model="email"
              type="email"
              placeholder="Enter your email"
              :disabled="isLoading"
            />
          </div>

          <!-- Password -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <Label for="password">Password</Label>
              <button
                type="button"
                class="text-xs text-muted-foreground hover:text-foreground transition-colors"
              >
                Forgot your password?
              </button>
            </div>
            <Input
              id="password"
              v-model="password"
              type="password"
              placeholder="Enter your password"
              :disabled="isLoading"
            />
          </div>

          <!-- Login Button -->
          <Button
            type="submit"
            class="w-full h-10 rounded-full"
            :disabled="isLoading"
          >
            {{ isLoading ? 'Signing in...' : 'Login' }}
          </Button>
        </form>
      </CardContent>
    </Card>
  </div>
</template>
