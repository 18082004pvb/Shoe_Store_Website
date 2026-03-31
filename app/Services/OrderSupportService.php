<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class OrderSupportService
{
    public function getCurrentUserOrders(): array
    {
        if (!Auth::check()) {
            return [
                'logged_in' => false,
                'orders' => [],
                'message' => 'Người dùng chưa đăng nhập',
            ];
        }

        $transactions = Transaction::with(['orders.product'])
            ->where('tst_user_id', Auth::id())
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $orders = [];

        foreach ($transactions as $transaction) {
            $products = [];

            foreach ($transaction->orders as $order) {
                $products[] = [
                    'order_id' => $order->id,
                    'product_name' => optional($order->product)->pro_name,
                    'qty' => $order->od_qty ?? null,
                    'price' => $order->od_price ?? null,
                    'sale' => $order->od_sale ?? null,
                ];
            }

            $statusInfo = method_exists($transaction, 'getStatus') ? $transaction->getStatus() : null;

            $orders[] = [
                'transaction_id' => $transaction->id,
                'amount' => $transaction->tst_total_money ?? null,
                'name' => $transaction->tst_name ?? null,
                'email' => $transaction->tst_email ?? null,
                'phone' => $transaction->tst_phone ?? null,
                'address' => $transaction->tst_address ?? null,
                'status_code' => $transaction->tst_status ?? null,
                'status_text' => $statusInfo['name'] ?? null,
                'created_at' => optional($transaction->created_at)->format('Y-m-d H:i:s'),
                'products' => $products,
            ];
        }

        return [
            'logged_in' => true,
            'orders' => $orders,
        ];
    }
}