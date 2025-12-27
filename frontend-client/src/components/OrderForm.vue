<script setup>
import { reactive, computed } from "vue";
import { useAuthStore } from "../stores/auth"; // Required to update balance
import { useOrderStore } from "../stores/orderStore";
import { useMatching } from "../composables/useMatching";
import { useToast } from "vue-toastification";
import axios from "axios"; // Required for the API call

const auth = useAuthStore();
const toast = useToast();
const { refreshData } = useMatching();

const form = reactive({
  symbol: "BTC",
  side: "buy",
  price: null,
  amount: null,
});

// State for local loading (if not using the composable's loading state)
const isSubmitting = reactive({ value: false });

const commissionFee = computed(() => {
  if (!form.price || !form.amount) return 0;
  return (form.price * form.amount * 0.015).toFixed(2);
});

const submitOrder = async () => {
  if (!form.price || !form.amount) {
    toast.warning("Please enter both price and amount");
    return;
  }

  isSubmitting.value = true;

  try {
    // 1. Place the Order - Use toRaw or spread to send a clean object
    await axios.post("/orders", { ...form });

    // 2. Success Notification
    toast.success("Order processed successfully!");

    // 3. REFRESH EVERYTHING
    // Wrapping in a try/catch or checking existence of fetchOrders
    try {
      // 3. REFRESH EVERYTHING via the composable
      // This will update the "Available USD" card automatically
      await refreshData();

      // 4. RESET FORM
      form.price = null;
      form.amount = null;
    } catch (refreshError) {
      console.error("Data refresh failed:", refreshError);
      // We don't show an error toast here because the order DID succeed
    }

    // 4. Reset UI only on success
    form.price = null;
    form.amount = null;
  } catch (err) {
    // This only runs if the axios.post fails (e.g., 400 or 500 error)
    const errorMsg = err.response?.data?.message || "Order failed";
    toast.error(errorMsg);
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<template>
  <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm">
    <h2 class="text-xl font-bold mb-4 text-gray-800">Place Limit Order</h2>

    <div class="flex gap-2 mb-4">
      <button
        @click="form.side = 'buy'"
        :class="
          form.side === 'buy'
            ? 'bg-green-600 text-white'
            : 'bg-gray-100 text-gray-600'
        "
        class="flex-1 py-2 rounded-lg font-semibold transition-all"
      >
        BUY
      </button>
      <button
        @click="form.side = 'sell'"
        :class="
          form.side === 'sell'
            ? 'bg-red-600 text-white'
            : 'bg-gray-100 text-gray-600'
        "
        class="flex-1 py-2 rounded-lg font-semibold transition-all"
      >
        SELL
      </button>
    </div>

    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">Asset</label>
        <select
          v-model="form.symbol"
          class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 outline-none"
        >
          <option value="BTC">Bitcoin (BTC)</option>
          <option value="ETH">Ethereum (ETH)</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700"
          >Price (USD)</label
        >
        <input
          v-model.number="form.price"
          type="number"
          step="0.01"
          placeholder="0.00"
          class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 outline-none"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Amount</label>
        <input
          v-model.number="form.amount"
          type="number"
          step="0.00000001"
          placeholder="0.00000000"
          class="w-full mt-1 p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 outline-none"
        />
      </div>

      <div
        class="p-3 bg-blue-50 rounded-lg text-sm text-blue-800 border border-blue-100"
      >
        Estimated Fee (1.5%):
        <span class="font-bold">${{ commissionFee }} USD</span>
      </div>

      <button
        @click="submitOrder"
        :disabled="isSubmitting.value"
        class="w-full py-3 text-white rounded-lg font-bold transition-all disabled:opacity-50"
        :class="
          form.side === 'buy'
            ? 'bg-green-600 hover:bg-green-700'
            : 'bg-red-600 hover:bg-red-700'
        "
      >
        {{
          isSubmitting.value
            ? "Processing..."
            : "Place " + form.side.toUpperCase() + " Order"
        }}
      </button>
    </div>
  </div>
</template>