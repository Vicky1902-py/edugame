<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General
            [
                'key' => 'site_name',
                'value' => 'EduGame Indonesia',
                'group' => 'general',
                'autoload' => true,
            ],
            [
                'key' => 'site_tagline',
                'value' => 'Platform Gaming Edukasi Interaktif & Kurikulum Nasional',
                'group' => 'general',
                'autoload' => true,
            ],
            [
                'key' => 'contact_email',
                'value' => 'halo@edugame.id',
                'group' => 'general',
                'autoload' => true,
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'group' => 'general',
                'autoload' => true,
            ],

            // Security Policy
            [
                'key' => 'min_pin_length',
                'value' => '4',
                'group' => 'security',
                'autoload' => true,
            ],
            [
                'key' => 'max_login_attempts',
                'value' => '5',
                'group' => 'security',
                'autoload' => true,
            ],
            [
                'key' => 'lockout_minutes',
                'value' => '15',
                'group' => 'security',
                'autoload' => true,
            ],
            [
                'key' => 'session_lifetime_minutes',
                'value' => '120',
                'group' => 'security',
                'autoload' => true,
            ],
            [
                'key' => 'enforce_2fa_superadmin',
                'value' => '1',
                'group' => 'security',
                'autoload' => true,
            ],

            // Backup & Hosting
            [
                'key' => 'backup_frequency',
                'value' => 'daily',
                'group' => 'backup',
                'autoload' => false,
            ],
            [
                'key' => 'backup_disk',
                'value' => 'local',
                'group' => 'backup',
                'autoload' => false,
            ],
            [
                'key' => 'dump_binary_path',
                'value' => '/usr/bin',
                'group' => 'backup',
                'autoload' => false,
            ],
        ];

        foreach ($settings as $item) {
            DB::table('settings')->updateOrInsert(
                ['key' => $item['key']],
                [
                    'value' => $item['value'],
                    'group' => $item['group'],
                    'autoload' => $item['autoload'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
