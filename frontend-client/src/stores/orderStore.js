import { defineStore } from 'pinia'
import axios from 'axios'

export const useOrderStore = defineStore('order', {
  state: () => ({
    orders: []
  }),
  actions: {
    async fetchOrders() {
      try {
        const { data } = await axios.get('/orders')
        this.orders = data
      } catch (error) {
        console.error("Could not fetch orders", error)
      }
    }
  }
})