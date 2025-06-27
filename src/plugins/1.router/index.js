import { setupLayouts } from 'virtual:generated-layouts'
import Component from 'vue-flatpickr-component'
import { createRouter, createWebHistory } from 'vue-router/auto'
//import{redirects, routes} from './additional-routes'
import { setupGuards } from './guards'
//import { redirects, routes } from './additional-routes'


function recursiveLayouts(route) {
  if (route.children) {
    for (let i = 0; i < route.children.length; i++)
      route.children[i] = recursiveLayouts(route.children[i])
    
    return route
  }
  
  return setupLayouts([route])[0]
}

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  scrollBehavior(to) {
    if (to.hash)
      return { el: to.hash, behavior: 'smooth', top: 60 }
    
    return { top: 0 }
  },
  extendRoutes: pages => [
    ...[
      {
        path: '/',
        name: 'index',
        redirect: to => {
          // TODO: Get type from backend
          const userData = localStorage.getItem('user')
          
          if (userData)
            return { name: 'dashboard'};
          return { name: 'login', query: to.query }

        },
      },
      // Definición explícita de la ruta register
      {
        path: '/register',
        name: 'register',
        component: () => import('@/pages/register.vue'),
        meta: {
          public: true,
          unauthenticatedOnly: true,
          layout: 'blank',
        }
      },
    ],
    ...[...pages,...[
      
      {
        path:'/documentos-lista',
        name:'documentos',
        component:()=>import('@/pages/tercera-page.vue'),
        meta:{
          not_authenticated:false,
        }
      }
    ]].map(route => recursiveLayouts(route)),
  ],
})

setupGuards(router)
//setupGuards(router)
export { router }
export default function (app) {
  app.use(router)
}