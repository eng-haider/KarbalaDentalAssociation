<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['results' => [], 'stats' => $this->getStats()]);
        }

        $results = Transaction::where('name', 'like', "%{$query}%")
            ->select('name', 'transaction_type', 'status')
            ->with('statusModel:slug,name,color,icon')
            ->limit(10)
            ->get()
            ->map(fn (Transaction $transaction): array => [
                'name' => $transaction->name,
                'transaction_type' => $transaction->transaction_type,
                'status' => $transaction->status,
                'status_label' => $transaction->statusLabel(),
                'status_color' => $transaction->statusModel?->publicColorClass() ?? 'bg-secondary',
                'status_icon' => $transaction->statusModel?->icon ?? 'bi-circle',
            ]);

        return response()->json([
            'results' => $results,
            'stats' => $this->getStats(),
            'total' => $results->count(),
        ]);
    }

    private function getStats(): array
    {
        return [
            'total' => Transaction::count(),
            'clinic' => Transaction::where('transaction_type', 'like', '%عيادة%')->count(),
            'noclinic' => Transaction::where('transaction_type', 'like', '%بدون عيادة%')->count(),
            'join' => Transaction::where('transaction_type', 'like', '%انتماء%')
                ->orWhere('transaction_type', 'like', '%جديد%')->count(),
        ];
    }
}
