<template>
  <div class="max-w-7xl mx-auto p-6">
    <header class="flex justify-between items-center mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Trading Engine</h1>
      <button class="text-sm text-red-600 font-medium">Logout</button>
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

<script setup>
import OrderForm from '../components/OrderForm.vue'
import WalletOverview from '../components/WalletOverview.vue'
import OrderBook from '../components/OrderBook.vue'
</script>