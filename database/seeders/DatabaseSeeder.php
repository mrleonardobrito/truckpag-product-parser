<?php

namespace Database\Seeders;

use Database\Seeders\SQLite\SQLiteProductSeeder;
use Database\Seeders\Mongo\MongoProductSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('testing')) {
            $this->call([
                SQLiteProductSeeder::class,
            ]);
        } else {
            $this->call([
                MongoProductSeeder::class,
            ]);
        }
    }
} 