import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig(({ command }) => {
  const isProd = command === 'build';
  const isVercel = !!process.env.VERCEL;
  const isRailway = !!process.env.RAILWAY_ENVIRONMENT;
  const isStandaloneBuild = isVercel || isRailway;

  return {
    plugins: [react()],
    base: isStandaloneBuild ? '/' : (isProd ? '/tumsda.org/admin/' : '/'),
    build: {
      outDir: isStandaloneBuild ? 'dist' : '../admin',
      emptyOutDir: true,
    },
    server: {
      port: 5173,
      proxy: {
        '/api': {
          target: 'http://localhost',
          changeOrigin: true,
          secure: false,
          rewrite: (path) => path.replace(/^\/api/, '/tum/tumsdachurch.org/api'),
          configure: (proxy, options) => {
            proxy.on('proxyReq', (proxyReq, req, res) => {
              console.log('Proxying request:', proxyReq.path);
            });
          },
        },
        '/tumsdachurch.org/assets': {
          target: 'http://localhost',
          changeOrigin: true,
          secure: false,
          rewrite: (path) => `/tum${path}`,
          configure: (proxy, options) => {
            proxy.on('proxyReq', (proxyReq, req, res) => {
              console.log('Proxying asset request:', proxyReq.path);
            });
          },
        },
        '/tumsdachurch.org/webfonts': {
          target: 'http://localhost',
          changeOrigin: true,
          secure: false,
          rewrite: (path) => `/tum${path}`,
        },
        '/webfonts': {
          target: 'http://localhost',
          changeOrigin: true,
          secure: false,
          rewrite: (path) => `/tum/tumsdachurch.org${path}`,
        },
      },
    },
  }
})
