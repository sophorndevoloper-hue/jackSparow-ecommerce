<?php

namespace Database\Seeders;

use App\Models\DeliveryAgent;
use Illuminate\Database\Seeder;

class DeliveryAgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agents = [
            [
                'name' => 'Sophea Rith',
                'code' => 'AGT-001',
                'phone' => '+855 12 345 678',
                'email' => 'sophea.rith@courier.local',
                'vehicle_type' => 'motorcycle',
                'license_plate' => '1AB-1234 PP',
                'zone' => 'Phnom Penh - Daun Penh & Doun Penh',
                'status' => 'available',
                'is_active' => true,
                'notes' => 'Primary express dispatcher for downtown districts.',
            ],
            [
                'name' => 'Marcus Vance',
                'code' => 'AGT-002',
                'phone' => '+855 10 987 654',
                'email' => 'marcus.vance@courier.local',
                'vehicle_type' => 'van',
                'license_plate' => '2CD-5678 PP',
                'zone' => 'Phnom Penh - Chamkarmon & BKK',
                'status' => 'on_delivery',
                'is_active' => true,
                'notes' => 'Dedicated van driver for bulk hardware orders and large packages.',
            ],
            [
                'name' => 'David Chen',
                'code' => 'AGT-003',
                'phone' => '+855 99 222 333',
                'email' => 'david.chen@courier.local',
                'vehicle_type' => 'truck',
                'license_plate' => '3EF-9012 PP',
                'zone' => 'Greater Phnom Penh & Outskirts',
                'status' => 'available',
                'is_active' => true,
                'notes' => 'Heavy freight truck driver for warehouse bulk pallet deliveries.',
            ],
            [
                'name' => 'Dara Sam',
                'code' => 'AGT-004',
                'phone' => '+855 88 555 123',
                'email' => 'dara.sam@courier.local',
                'vehicle_type' => 'bicycle',
                'license_plate' => null,
                'zone' => 'Riverside & Night Market Area',
                'status' => 'available',
                'is_active' => true,
                'notes' => 'Eco-friendly bicycle courier for compact and urgent documents.',
            ],
            [
                'name' => 'Vannak Ouk',
                'code' => 'AGT-005',
                'phone' => '+855 77 444 888',
                'email' => 'vannak.ouk@courier.local',
                'vehicle_type' => 'motorcycle',
                'license_plate' => '1GH-4321 PP',
                'zone' => 'Toul Kork & Sen Sok',
                'status' => 'inactive',
                'is_active' => false,
                'notes' => 'Currently on medical leave.',
            ],
        ];

        foreach ($agents as $agent) {
            DeliveryAgent::updateOrCreate(
                ['code' => $agent['code']],
                $agent
            );
        }
    }
}
