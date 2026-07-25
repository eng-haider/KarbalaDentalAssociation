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
            ->select('name', 'transaction_type')
            ->limit(10)
            ->get();

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
