<script setup>
import { reactive, computed } from 'vue'
import { useMatching } from '../composables/useMatching'

const { placeOrder, loading } = useMatching()

const form = reactive({
    symbol: 'BTC',
    side: 'buy',
    price: null,
    amount: null
})

const commissionFee = computed(() => {
    if (!form.price || !form.amount) return 0
    return (form.price * form.amount * 0.015).toFixed(2)
})

const submitOrder = () => {
    if (!form.price || !form.amount) return alert("Please fill all fields")
    placeOrder({ ...form })
}
</script>

<template>
  <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm">
    <h2 class="text-xl font-bold mb-4">Place Limit Order</h2>
    
    <div class="flex gap-2 mb-4">
      <button @click="form.side = 'buy'" 
        :class="form.side === 'buy' ? 'bg-green-600 text-white' : 'bg-gray-100'"
        class="flex-1 py-2 rounded-lg font-semibold transition">BUY</button>
      <button @click="form.side = 'sell'" 
        :class="form.side === 'sell' ? 'bg-red-600 text-white' : 'bg-gray-100'"
        class="flex-1 py-2 rounded-lg font-semibold transition">SELL</button>
    </div>

    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">Asset</label>
        <select v-model="form.symbol" class="w-full mt-1 p-2 border rounded-md">
          <option value="BTC">Bitcoin (BTC)</option>
          <option value="ETH">Ethereum (ETH)</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Price (USD)</label>
        <input v-model.number="form.price" type="number" placeholder="0.00" 
               class="w-full mt-1 p-2 border rounded-md focus:ring-2 focus:ring-blue-500" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Amount</label>
        <input v-model.number="form.amount" type="number" placeholder="0.00000000" 
               class="w-full mt-1 p-2 border rounded-md" />
      </div>

      <div class="p-3 bg-blue-50 rounded-lg text-sm text-blue-800">
        Estimated Fee (1.5%): <span class="font-bold">${{ commissionFee }} USD</span>
      </div>

      <button @click="submitOrder" :disabled="loading"
        class="w-full py-3 bg-blue-700 text-white rounded-lg font-bold hover:bg-blue-800 disabled:opacity-50">
        {{ loading ? 'Processing...' : 'Place ' + form.side.toUpperCase() + ' Order' }}
      </button>
    </div>
  </div>
</template>