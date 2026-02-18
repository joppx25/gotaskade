interface ApiOptions {
  method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'
  body?: Record<string, unknown>
  params?: Record<string, string>
}

function getXsrfToken(): string {
  if (typeof document === 'undefined') return ''
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  if (!match) return ''
  return decodeURIComponent(match[1])
}

export function useApi() {
  const config = useRuntimeConfig()
  const baseURL = config.public.apiBase as string

  async function getCsrfCookie(): Promise<void> {
    await $fetch('/sanctum/csrf-cookie', {
      baseURL,
      credentials: 'include',
    })
  }

  async function request<T>(endpoint: string, options: ApiOptions = {}): Promise<T> {
    const { method = 'GET', body, params } = options

    const headers: Record<string, string> = {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    }

    const xsrfToken = getXsrfToken()
    if (xsrfToken) {
      headers['X-XSRF-TOKEN'] = xsrfToken
    }

    try {
      return await $fetch<T>(`/api${endpoint}`, {
        baseURL,
        method,
        body,
        params,
        credentials: 'include',
        headers,
      })
    }
    catch (error: any) {
      const status = error?.response?.status || error?.status || 0
      const data = error?.response?._data || error?.data || {}
      const apiError = new Error(data.message || `Request failed with status ${status}`)
      ;(apiError as any).status = status
      ;(apiError as any).errors = data.errors
      throw apiError
    }
  }

  async function get<T>(endpoint: string, params?: Record<string, string>): Promise<T> {
    return request<T>(endpoint, { method: 'GET', params })
  }

  async function post<T>(endpoint: string, body?: Record<string, unknown>): Promise<T> {
    await getCsrfCookie()
    return request<T>(endpoint, { method: 'POST', body })
  }

  async function put<T>(endpoint: string, body?: Record<string, unknown>): Promise<T> {
    await getCsrfCookie()
    return request<T>(endpoint, { method: 'PUT', body })
  }

  async function patch<T>(endpoint: string, body?: Record<string, unknown>): Promise<T> {
    await getCsrfCookie()
    return request<T>(endpoint, { method: 'PATCH', body })
  }

  async function del<T>(endpoint: string): Promise<T> {
    await getCsrfCookie()
    return request<T>(endpoint, { method: 'DELETE' })
  }

  return { get, post, put, patch, del, getCsrfCookie }
}
