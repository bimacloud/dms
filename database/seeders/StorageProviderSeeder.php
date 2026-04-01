<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StorageProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\StorageProvider::create([
            'name' => 'Default MinIO',
            'driver' => 's3',
            'key' => config('filesystems.disks.s3.key'),
            'secret' => config('filesystems.disks.s3.secret'),
            'region' => config('filesystems.disks.s3.region', 'us-east-1'),
            'bucket' => config('filesystems.disks.s3.bucket'),
            'endpoint' => config('filesystems.disks.s3.endpoint'),
            'use_path_style_endpoint' => config('filesystems.disks.s3.use_path_style_endpoint', true),
            'is_active' => true,
            'is_default' => true,
        ]);
    }
}
