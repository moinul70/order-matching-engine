import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/Login.vue'
import DashboardView from '../views/Dashboard.vue'

const routes = [
  { 
    path: '/login', 
    name: 'login', 
    component: LoginView 
  },
  { 
    path: '/dashboard', 
    name: 'dashboard', 
    component: DashboardView,
    meta: { requiresAuth: true } // This route is protected
  },
  {
    path: '/',
    redirect: '/dashboard' // Send users to dashboard by default (guard will catch them)
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation Guard
router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('token'); // Check for token

  // 1. If the page requires login and you're not logged in -> go to Login
  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/login');
  } 
  // 2. If you are already logged in and try to go to the Login page -> go to Dashboard
  else if (to.name === 'login' && isAuthenticated) {
    next('/dashboard');
  } 
  // 3. Otherwise, go where you wanted to go
  else {
    next();
  }
})

export default router