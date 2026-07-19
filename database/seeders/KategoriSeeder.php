<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriData = [
            [
                'nama_kategori' => 'Hidrologi',
                'slug' => 'hidrologi',
                'logo' => 'logo_kategori/default-kategori.png',
                'deskripsi' => 'Kajian aliran dan ketersediaan air di permukaan, interaksi dengan air hujan serta air tanah, untuk mendukung perencanaan sumber daya air yang berkelanjutan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kategori' => 'Hidrometeorologi',
                'slug' => 'hidrometeorologi',
                'logo' => 'logo_kategori/default-kategori.png',
                'deskripsi' => 'Pemantauan dan analisis hujan serta fenomena atmosfer yang memengaruhi debit sungai, banjir, dan ketersediaan air di berbagai wilayah.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kategori' => 'Hidrogeologi',
                'slug' => 'hidrogeologi',
                'logo' => 'logo_kategori/default-kategori.png',
                'deskripsi' => 'Studi air bawah tanah pada zona jenuh, dinamika akuifer, serta keterkaitannya dengan air permukaan untuk pemenuhan kebutuhan air bersih.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($kategoriData as $kategori) {
            \App\Models\Kategori::updateOrCreate(
                ['slug' => $kategori['slug']],
                [
                    'nama_kategori' => $kategori['nama_kategori'],
                    'logo' => $kategori['logo'],
                    'deskripsi' => $kategori['deskripsi'],
                ]
            );
        }
    }
}
