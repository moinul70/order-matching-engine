import { ref, onMounted } from 'vue'
import api from '../services/api'
import { echo } from '../services/echo'

export function useMatching() {
    const balance = ref(0)
    const assets = ref([])
    const orders = ref([])
    const loading = ref(false)

    // Fetch initial profile data (USD Balance + Assets)
    const fetchProfile = async () => {
        try {
            const { data } = await api.get('/profile')
            balance.value = data.balance
            assets.value = data.assets // Array of { symbol, amount, locked_amount }
        } catch (err) {
            console.error("Profile fetch failed", err)
        }
    }

    // Place a new Limit Order
    const placeOrder = async (orderData) => {
        loading.value = true
        try {
            await api.post('/orders', orderData)
            await fetchProfile() // Refresh balances immediately
        } catch (err) {
            alert(err.response?.data?.message || "Order failed to place")
        } finally {
            loading.value = false
        }
    }

    // Initialize Listeners
    onMounted(() => {
        fetchProfile()

        // Real-time: Listen for the match event from Laravel
        // Note: Ensure user.id is available via your Auth state
        const userId = window.userId 
        echo.private(`user.${userId}`)
            .listen('OrderMatched', (e) => {
                console.log("Trade Executed!", e)
                fetchProfile() // Update numbers instantly on match
            })
    })

    return { balance, assets, orders, placeOrder, loading }
}