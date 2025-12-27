import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import './style.css'
import Toast from "vue-toastification"
import "vue-toastification/dist/index.css"

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.use(Toast, {
    // Optional configuration
    position: "top-right",
    timeout: 3000,
    closeOnClick: true
})

app.mount('#app')