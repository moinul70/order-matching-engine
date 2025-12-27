<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Services\MatchingEngineService;

class OrderController extends Controller
{

    public function __construct(protected MatchingEngineService $matchingService) {}
    // GET /api/orders
    public function index(): JsonResponse
    {
        return response()->json(
            Order::where('user_id', Auth::id())->latest()->get()
        );
    }

    // POST /api/orders
    public function store(Request $request)
    {
        $request->validate([
            'symbol' => 'required|string',
            'side' => 'required|in:buy,sell',
            'price' => 'required|numeric|min:0.00000001',
            'amount' => 'required|numeric|min:0.00000001',
        ]);

        $key = $request->header('Idempotency-Key');

        if (!$key) {
            return response()->json(['error' => 'Idempotency-Key header is required'], 400);
        }


        $user = $request->user();
        $totalCost = $request->price * $request->amount;

        return DB::transaction(function () use ($user, $request, $totalCost) {
            // 1. Validation and Locking Balance
            if ($request->side === 'buy') {
                if ($user->balance < $totalCost) {
                    return response()->json(['message' => 'Insufficient USD balance'], 400);
                }
                $user->decrement('balance', $totalCost);
            } else {
                $asset = $user->assets()->where('symbol', $request->symbol)->first();
                if (!$asset || $asset->amount < $request->amount) {
                    return response()->json(['message' => 'Insufficient asset balance'], 400);
                }
                $asset->decrement('amount', $request->amount);
                // We lock the crypto so the user can't sell the same coins twice
                $asset->increment('locked_amount', $request->amount);
            }

            // 2. Create the Order
            $order = $user->orders()->create([
                'symbol' => $request->symbol,
                'side'   => $request->side,
                'price'  => $request->price,
                'amount' => $request->amount,
                'remaining_amount' => $request->amount, // You need this for partial fills
                'status' => 1, // Open
            ]);

            // 3. TRIGGER MATCHING ENGINE
            // This will find matches and move the "locked" money/assets to the winner
            $this->matchingService->match($order);

            return response()->json($order->refresh(), 201);
        });

        return response()->json($order->refresh(), 201);
    }

    // DELETE /api/orders/{id} (Cancel Order)
    public function destroy(Order $order)
    {
        if ($order->user_id !== Auth::id() || $order->status !== 1) {
            return response()->json(['message' => 'Cannot cancel'], 403);
        }

        return DB::transaction(function () use ($order) {
            $user = Auth::user();
            if ($order->side === 'buy') {
                // Refund USD
                $user->increment('balance', $order->price * $order->amount);
            } else {
                // Unlock Crypto
                $asset = $user->assets()->where('symbol', $order->symbol)->first();
                $asset->decrement('locked_amount', $order->amount);
                $asset->increment('amount', $order->amount);
            }

            $order->update(['status' => 3]); // Cancelled
            return response()->json(['message' => 'Order cancelled and funds returned']);
        });
    }
}
