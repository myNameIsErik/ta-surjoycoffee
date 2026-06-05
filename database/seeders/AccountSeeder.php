<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // Aset (1xxx)
            ['1101', 'Kas',                            'aset',       'debit'],
            ['1102', 'Bank',                           'aset',       'debit'],
            ['1103', 'Piutang Usaha',                  'aset',       'debit'],
            ['1201', 'Persediaan Bahan Baku',          'aset',       'debit'],
            ['1202', 'Persediaan Minuman',             'aset',       'debit'],
            ['1301', 'Peralatan Cafe',                 'aset',       'debit'],
            ['1302', 'Akumulasi Penyusutan Peralatan', 'aset',       'kredit'],

            // Kewajiban (2xxx)
            ['2101', 'Utang Usaha',                    'kewajiban',  'kredit'],
            ['2102', 'Utang Gaji',                     'kewajiban',  'kredit'],
            ['2201', 'Utang Bank',                     'kewajiban',  'kredit'],

            // Modal (3xxx)
            ['3101', 'Modal Pemilik',                  'modal',      'kredit'],
            ['3102', 'Prive Pemilik',                  'modal',      'debit'],
            ['3201', 'Laba Ditahan',                   'modal',      'kredit'],

            // Pendapatan (4xxx)
            ['4101', 'Pendapatan Penjualan Makanan',   'pendapatan', 'kredit'],
            ['4102', 'Pendapatan Penjualan Minuman',   'pendapatan', 'kredit'],
            ['4103', 'Pendapatan Lain-lain',           'pendapatan', 'kredit'],

            // Beban (5xxx)
            ['5101', 'Beban Pembelian Bahan Baku',     'beban',      'debit'],
            ['5102', 'Beban Gaji Karyawan',            'beban',      'debit'],
            ['5103', 'Beban Sewa Tempat',              'beban',      'debit'],
            ['5104', 'Beban Listrik & Air',            'beban',      'debit'],
            ['5105', 'Beban Internet & Telepon',       'beban',      'debit'],
            ['5106', 'Beban Pemasaran',                'beban',      'debit'],
            ['5107', 'Beban Perlengkapan',             'beban',      'debit'],
            ['5108', 'Beban Penyusutan Peralatan',     'beban',      'debit'],
            ['5109', 'Beban Lain-lain',                'beban',      'debit'],
            ['5110', 'Harga Pokok Penjualan',          'beban',      'debit'],
        ];

        foreach ($accounts as [$code, $name, $type, $normal]) {
            Account::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'type' => $type,
                    'normal_balance' => $normal,
                    'is_active' => true,
                ]
            );
        }
    }
}
