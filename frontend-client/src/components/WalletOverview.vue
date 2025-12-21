<script setup>
import { useMatching } from '../composables/useMatching'

// We destructure balance and assets from our shared logic
const { balance, assets } = useMatching()

// Helper to format currency
const formatUSD = (val) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(val)
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="p-5 bg-white border border-gray-200 rounded-xl shadow-sm">
      <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Available USD</p>
      <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatUSD(balance) }}</p>
    </div>

    <div v-for="asset in assets" :key="asset.symbol" 
         class="p-5 bg-white border border-gray-200 rounded-xl shadow-sm">
      <div class="flex justify-between items-start">
        <div>
          <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">{{ asset.symbol }} Balance</p>
          <p class="text-2xl font-bold text-gray-900 mt-1">{{ asset.amount }}</p>
        </div>
        <span class="px-2 py-1 text-xs font-bold bg-blue-100 text-blue-700 rounded uppercase">
          {{ asset.symbol }}
        </span>
      </div>
      <p class="text-xs text-gray-400 mt-2">
        Locked in orders: <span class="font-semibold">{{ asset.locked_amount || 0 }}</span>
      </p>
    </div>
  </div>
</template>