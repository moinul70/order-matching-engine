# 📈 Order Matching Engine

A high-performance, decoupled trading platform. This system utilizes a **Laravel 11 API** for heavy-duty order matching logic and a **Vue 3 frontend** for a real-time trading experience.

---

## 🚀 Quick Start Guide

Follow these steps to get the project running on your local machine.

### 1. Prerequisites
Ensure you have the following installed:

- PHP 8.3+ & Composer  
- Node.js 20+ & NPM  
- MySQL 8.0+  
- Pusher Account (for real-time updates)  

---

### 2. Backend Setup (Laravel)

Navigate to the backend folder:

```bash
cd backend-api
Install PHP dependencies:

bash
Copy code
composer install
Configure Environment:

bash
Copy code
cp .env.example .env
php artisan key:generate
Database Configuration
Open .env and update your database credentials:

env
Copy code
DB_DATABASE=trading_db
DB_USERNAME=root
DB_PASSWORD=your_password
Broadcasting Configuration
Set up your Pusher credentials in .env:

env
Copy code
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_id
PUSHER_APP_KEY=your_key
PUSHER_APP_SECRET=your_secret
PUSHER_APP_CLUSTER=mt1
Run Migrations & Seeders:

bash
Copy code
php artisan migrate --seed
Start the API Server:

bash
Copy code
php artisan serve
3. Frontend Setup (Vue 3)
Navigate to the frontend folder:

bash
Copy code
cd ../frontend-client
Install Node dependencies:

bash
Copy code
npm install
Configure Environment: Create a .env file in the frontend root:

env
Copy code
VITE_API_BASE_URL=http://127.0.0.1:8000/api
VITE_PUSHER_APP_KEY=your_key
VITE_PUSHER_APP_CLUSTER=mt1
Start the Development Server:

bash
Copy code
npm run dev
🏗 System Architecture & Flow
Key Modules:

MatchingEngineService.php: Handles the logic of matching Buy and Sell orders, managing locked funds, and updating balances.

OrderController.php: Validates requests, initializes transactions, and triggers the matching engine.

Pinia Stores: auth.js and order.js manage global state, ensuring balances update across components when orders are filled or cancelled.

🛠 Features Implemented
Limit Orders: Buy/Sell crypto at specific prices.

Balance Management: Automatic locking of funds during open orders and instant refunds upon cancellation.

Asset Tracking: Dedicated table for tracking multiple crypto holdings (BTC, ETH).

Real-time UI: Notifications via vue-toastification and instant balance refreshes using Axios and onMounted hooks.

🧪 Testing the Engine
Test Users
User A: big@example.com / password123

User B: small@example.com / password123

Test Scenario
Register/login both users in the frontend.

User A: Place a SELL order for 1 BTC @ $60,000.

User B: Place a BUY order for 1 BTC @ $60,000.

Observation: Both orders move to "Filled", User A’s USD balance increases, and User B’s BTC asset count increases.

🆘 Troubleshooting
CORS Issues: Ensure config/cors.php in Laravel allows localhost:5173.

Toast Errors: If ReferenceError: toast is not defined, ensure you run:

bash
Copy code
npm install vue-toastification@next
Balance not updating: Check Network tab to ensure /profile API call returns updated values after an order.

✅ Notes
This project is optimized for full-match only trading (no partial fills).

Commission: 1.5% of the matched USD value is automatically deducted.

Ensure your Pusher credentials are valid for real-time updates.
