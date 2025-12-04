<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\ProductionLine;
use App\Models\Tool;
use App\Models\Employee;
use App\Models\Assignment;
use App\Models\Audit;
use App\Models\AuditItem;
use App\Models\AuditPhoto;
use App\Models\AuditReview;


class AuditSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Supervisor
        $supervisor = User::updateOrCreate(
            ['email' => 'supervisor@example.com'],
            [
                'name' => 'Supervisor Demo',
                'password' => Hash::make('password'),
                'employee_number' => 'SUP1001',
                'role' => 'supervisor',
                'active' => true,
            ]
        );

        // 2. Técnico
        $technician = User::updateOrCreate(
            ['email' => 'technician@example.com'],
            [
                'name' => 'Technician Demo',
                'password' => Hash::make('password'),
                'employee_number' => 'TEC2001',
                'role' => 'technician',
                'active' => true,
            ]
        );
    }
}
