<?php

namespace Database\Seeders;

use App\Models\Transaction;
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

        $scriptPath = base_path('storage/read_excel.py');
        $this->createPythonScript($scriptPath, $filePath);

        $output = shell_exec("python3 $scriptPath 2>&1");
        $data = json_decode($output, true);

        if (!is_array($data)) {
            $this->command->error("Failed to read Excel file: $output");
            return;
        }

        foreach ($data as $row) {
            if (empty($row['name']) || empty($row['transaction_type'])) {
                continue;
            }

            Transaction::create([
                'name' => $row['name'],
                'transaction_type' => $row['transaction_type'],
            ]);
        }

        $this->command->info("Imported " . Transaction::count() . " transactions successfully!");
    }

    private function createPythonScript(string $scriptPath, string $filePath): void
    {
        $script = <<<'PYTHON'
import openpyxl
import json

try:
    wb = openpyxl.load_workbook('%s')
    ws = wb.active
    data = []
    for i, row in enumerate(ws.iter_rows(values_only=True)):
        if i == 0:  # Skip header
            continue
        if row and len(row) > 1 and row[0]:
            data.append({
                'name': str(row[0]).strip(),
                'transaction_type': str(row[1]).strip(),
            })
    print(json.dumps(data))
except Exception as e:
    print(json.dumps([]))
PYTHON;
        file_put_contents($scriptPath, sprintf($script, $filePath));
    }
}
