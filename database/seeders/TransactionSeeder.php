<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        Transaction::truncate();

        $filePath = base_path('معاملات نقابة أسنان كربلاء المقدسة.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("Excel file not found at: {$filePath}");
            return;
        }

        $rows = Excel::toArray([], $filePath)[0];

        $imported = 0;
        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Skip header

            $name = trim($row[0] ?? '');
            $type = trim($row[1] ?? '');

            if (empty($name) || empty($type)) {
                continue;
            }

            Transaction::create([
                'name' => $name,
                'transaction_type' => $type,
                'status' => rand(1, 100) <= 25 ? 'pending' : 'completed',
            ]);
            $imported++;
        }

        $this->command->info("Imported {$imported} transactions successfully!");
    }
}
