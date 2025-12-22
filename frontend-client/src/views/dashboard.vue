<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import OrderForm from '../components/OrderForm.vue'
import WalletOverview from '../components/WalletOverview.vue'
import OrderBook from '../components/OrderBook.vue'

const auth = useAuthStore()
const router = useRouter()

const handleLogout = () => {
  auth.logout() // Clears token from localStorage and Pinia
  router.push('/login')
}

// Optionally fetch initial data when dashboard loads
onMounted(async () => {
  try {
    // You could call an action here like auth.fetchProfile()
  } catch (error) {
    console.error("Failed to load dashboard data", error)
  }
})
</script>

<template>
  <div class="max-w-7xl mx-auto p-6">
    <header class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Trading Engine</h1>
        <p v-if="auth.user" class="text-sm text-gray-500">Welcome, {{ auth.user.name }}</p>
      </div>
      <button 
        @click="handleLogout"
        class="text-sm text-red-600 font-medium hover:underline"
      >
        Logout
      </button>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-1">
        <OrderForm />
      </div>

      <div class="lg:col-span-2 space-y-8">
        <WalletOverview />
        <OrderBook />
      </div>
    </div>
  </div>
</template>