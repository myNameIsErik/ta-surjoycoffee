<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Journal;
use App\Models\JournalEntry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class JournalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = Account::pluck('id', 'code');
        if ($accounts->isEmpty()) {
            return;
        }

        $today = Carbon::today();

        $samples = [
            [
                'date' => $today->copy()->subDays(20)->toDateString(),
                'description' => 'Setoran modal awal pemilik',
                'lines' => [
                    ['code' => '1102', 'debit' => 50000000, 'credit' => 0, 'memo' => 'Setoran ke rekening bank'],
                    ['code' => '3101', 'debit' => 0, 'credit' => 50000000, 'memo' => 'Modal pemilik'],
                ],
            ],
            [
                'date' => $today->copy()->subDays(18)->toDateString(),
                'description' => 'Pembelian peralatan cafe (mesin kopi, kursi)',
                'lines' => [
                    ['code' => '1301', 'debit' => 15000000, 'credit' => 0, 'memo' => 'Peralatan operasional'],
                    ['code' => '1102', 'debit' => 0, 'credit' => 15000000, 'memo' => 'Pembayaran via bank'],
                ],
            ],
            [
                'date' => $today->copy()->subDays(15)->toDateString(),
                'description' => 'Pembayaran sewa tempat bulanan',
                'lines' => [
                    ['code' => '5103', 'debit' => 3500000, 'credit' => 0, 'memo' => 'Sewa bulan berjalan'],
                    ['code' => '1101', 'debit' => 0, 'credit' => 3500000, 'memo' => 'Bayar tunai'],
                ],
            ],
            [
                'date' => $today->copy()->subDays(10)->toDateString(),
                'description' => 'Penjualan tunai harian',
                'lines' => [
                    ['code' => '1101', 'debit' => 2750000, 'credit' => 0, 'memo' => 'Kas penjualan'],
                    ['code' => '4101', 'debit' => 0, 'credit' => 1250000, 'memo' => 'Penjualan makanan'],
                    ['code' => '4102', 'debit' => 0, 'credit' => 1500000, 'memo' => 'Penjualan minuman'],
                ],
            ],
            [
                'date' => $today->copy()->subDays(7)->toDateString(),
                'description' => 'Pembelian bahan baku',
                'lines' => [
                    ['code' => '5101', 'debit' => 1800000, 'credit' => 0, 'memo' => 'Kopi, susu, gula'],
                    ['code' => '1101', 'debit' => 0, 'credit' => 1800000, 'memo' => 'Bayar tunai'],
                ],
            ],
            [
                'date' => $today->copy()->subDays(5)->toDateString(),
                'description' => 'Pembayaran gaji karyawan',
                'lines' => [
                    ['code' => '5102', 'debit' => 4500000, 'credit' => 0, 'memo' => 'Gaji 3 orang barista'],
                    ['code' => '1102', 'debit' => 0, 'credit' => 4500000, 'memo' => 'Transfer bank'],
                ],
            ],
            [
                'date' => $today->copy()->subDays(2)->toDateString(),
                'description' => 'Penjualan tunai harian',
                'lines' => [
                    ['code' => '1101', 'debit' => 3200000, 'credit' => 0, 'memo' => 'Kas penjualan'],
                    ['code' => '4101', 'debit' => 0, 'credit' => 1400000, 'memo' => 'Penjualan makanan'],
                    ['code' => '4102', 'debit' => 0, 'credit' => 1800000, 'memo' => 'Penjualan minuman'],
                ],
            ],
            [
                'date' => $today->copy()->subDay()->toDateString(),
                'description' => 'Bayar listrik & air',
                'lines' => [
                    ['code' => '5104', 'debit' => 850000, 'credit' => 0, 'memo' => 'Tagihan PLN & PDAM'],
                    ['code' => '1102', 'debit' => 0, 'credit' => 850000, 'memo' => 'Transfer bank'],
                ],
            ],
        ];

        foreach ($samples as $sample) {
            $total = collect($sample['lines'])->sum('debit');
            $journal = Journal::create([
                'reference' => Journal::generateReference(),
                'date' => $sample['date'],
                'description' => $sample['description'],
                'total' => $total,
            ]);

            foreach ($sample['lines'] as $line) {
                if (! isset($accounts[$line['code']])) {
                    continue;
                }
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $accounts[$line['code']],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'memo' => $line['memo'] ?? null,
                ]);
            }
        }
    }
}
