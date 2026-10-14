<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemSetting::create([
            'title' => 'Lokaa',
            'system_name' => 'Lokaa',
            'email' => 'info@example.com',
            'number' => '+12120000000',
            'logo' => '/assets/brand/logo.png',
            'favicon' => '/assets/brand/favicon.png',
            'address' => '123 Main Street, Apt 4B, Springfield, IL 62701, USA',
            'copyright_text' => 'Copyright 2026. All Rights Reserved. Powered by Lokaa.',
            'description' => 'Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of "de Finibus Bonorum et Malorum" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
