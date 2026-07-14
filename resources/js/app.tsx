import './bootstrap'

import { createInertiaApp } from '@inertiajs/react'
import { createRoot } from 'react-dom/client'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';

createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.tsx', { eager: true })
    const page = pages[`./Pages/${name}.tsx`] as { default: unknown } | undefined

    if (!page) {
      throw new Error(`Page not found: ${name}`)
    }

    return page.default
  },
  setup({ el, App, props }) {
    createRoot(el).render(<App {...props} />)
  },
})
