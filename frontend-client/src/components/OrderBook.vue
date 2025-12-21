<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../services/api'
import { echo } from '../services/echo'

const orders = ref([])
const selectedSymbol = ref('BTC')

const fetchOrders = async () => {
    try {
        const { data } = await api.get(`/orders?symbol=${selectedSymbol.value}`)
        orders.value = data
    } catch (err) {
        console.error("Could not fetch orders", err)
    }
}

// Group orders into Buy and Sell for the "Orderbook" view
const buyOrders = computed(() => orders.value.filter(o => o.side === 'buy' && o.status === 1))
const sellOrders = computed(() => orders.value.filter(o => o.side === 'sell' && o.status === 1))

const getStatusLabel = (status) => {
    const labels = { 1: 'Open', 2: 'Filled', 3: 'Cancelled' }
    return labels[status] || 'Unknown'
}

onMounted(() => {
    fetchOrders()
    
    // Listen for global order updates to refresh the book for everyone
    echo.channel('order-book')
        .listen('OrderPlaced', () => fetchOrders())
        .listen('OrderMatched', () => fetchOrders())
})
</script>

<template>
  <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
      <h3 class="font-bold text-gray-800">Order Book</h3>
      <select v-model="selectedSymbol" @change="fetchOrders" class="text-sm border rounded px-2 py-1">
        <option value="BTC">BTC/USD</option>
        <option value="ETH">ETH/USD</option>
      </select>
    </div>

    <div class="grid grid-cols-2 gap-0 divide-x divide-gray-100">
      <div class="p-4">
        <h4 class="text-xs font-semibold text-red-500 uppercase mb-2">Sell Orders</h4>
        <table class="w-full text-sm">
          <thead>
            <tr class="text-gray-400 text-left text-xs">
              <th>Price</th>
              <th class="text-right">Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in sellOrders" :key="order.id" class="hover:bg-red-50">
              <td class="text-red-600 font-medium">{{ order.price }}</td>
              <td class="text-right text-gray-600">{{ order.amount }}</td>
            </tr>
            <tr v-if="sellOrders.length === 0">
              <td colspan="2" class="text-center py-4 text-gray-400 text-xs">No sell orders</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="p-4">
        <h4 class="text-xs font-semibold text-green-500 uppercase mb-2">Buy Orders</h4>
        <table class="w-full text-sm">
          <thead>
            <tr class="text-gray-400 text-left text-xs">
              <th>Price</th>
              <th class="text-right">Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in buyOrders" :key="order.id" class="hover:bg-green-50">
              <td class="text-green-600 font-medium">{{ order.price }}</td>
              <td class="text-right text-gray-600">{{ order.amount }}</td>
            </tr>
            <tr v-if="buyOrders.length === 0">
              <td colspan="2" class="text-center py-4 text-gray-400 text-xs">No buy orders</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>