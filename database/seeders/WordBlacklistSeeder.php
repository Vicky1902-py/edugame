<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WordBlacklistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $words = [
            'anjing', 'babi', 'monyet', 'bangsat', 'kontol', 'memek', 'jembut', 'pantek',
            'bajingan', 'tolol', 'goblok', 'idiot', 'perek', 'lonte', 'bejad', 'kampret',
            'bego', 'bodoh', 'sialan', 'setan', 'iblis', 'fuck', 'shit', 'bitch', 'asshole'
        ];

        foreach ($words as $word) {
            DB::table('word_blacklists')->updateOrInsert(
                ['word' => strtolower($word)],
                [
                    'severity' => 'block',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
