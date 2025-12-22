<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class MatchingEngineService
{
   public function match(Order $newOrder)
    {
        return DB::transaction(function () use ($newOrder) {
            // Find opposite orders (Buy vs Sell)
            $sideToMatch = $newOrder->side === 'buy' ? 'sell' : 'buy';
            
            $query = Order::where('symbol', $newOrder->symbol)
                ->where('side', $sideToMatch)
                ->where('status', 1) // Open
                ->where('user_id', '!=', $newOrder->user_id);

            // Price Matching Logic
            if ($newOrder->side === 'buy') {
                $query->where('price', '<=', $newOrder->price)->orderBy('price', 'asc');
            } else {
                $query->where('price', '>=', $newOrder->price)->orderBy('price', 'desc');
            }

            $matches = $query->get();

            foreach ($matches as $match) {
                if ($newOrder->amount <= 0) break;

                $execAmount = min($newOrder->amount, $match->amount);
                $execPrice = $match->price; // Take the price of the existing order
                $totalCost = $execAmount * $execPrice;

                // --- START SETTLEMENT ---
                
                if ($newOrder->side === 'buy') {
                    // $newOrder is BUYER, $match is SELLER
                    $this->settleTrade($newOrder->user_id, $match->user_id, $newOrder->symbol, $execAmount, $totalCost);
                } else {
                    // $newOrder is SELLER, $match is BUYER
                    $this->settleTrade($match->user_id, $newOrder->user_id, $newOrder->symbol, $execAmount, $totalCost);
                }

                // --- END SETTLEMENT ---

                // Update Order Amounts
                $newOrder->decrement('amount', $execAmount);
                $match->decrement('amount', $execAmount);

                if ($match->amount <= 0) $match->update(['status' => 2]); // Filled
            }

            if ($newOrder->amount <= 0) $newOrder->status = 2; // Filled
            $newOrder->save();
        });
    }

    private function settleTrade($buyerId, $sellerId, $symbol, $amount, $cost)
    {
        // 1. Give USD to Seller (Buyer's money was already decremented in Controller)
        DB::table('users')->where('id', $sellerId)->increment('balance', $cost);

        // 2. Move Crypto from Seller's Locked to Buyer's Amount
        // Seller side
        $sellerAsset = Asset::where('user_id', $sellerId)->where('symbol', $symbol)->first();
        $sellerAsset->decrement('locked_amount', $amount);

        // Buyer side
        $buyerAsset = Asset::firstOrCreate(
            ['user_id' => $buyerId, 'symbol' => $symbol],
            ['amount' => 0, 'locked_amount' => 0]
        );
        $buyerAsset->increment('amount', $amount);
    }
}
