import tailwindcss from '@tailwindcss/vite'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  ssr: false,

  modules: [
    '@nuxtjs/google-fonts',
    'shadcn-nuxt',
    '@pinia/nuxt',
  ],

  css: ['~/assets/css/tailwind.css'],

  vite: {
    plugins: [
      tailwindcss() as any,
    ],
  },

  shadcn: {
    prefix: '',
    componentDir: './app/components/ui',
  },

  googleFonts: {
    families: {
      Inter: [400, 500, 600, 700],
    },
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000',
    },
  },

  pinia: {
    storesDirs: ['./app/stores'],
  },
})