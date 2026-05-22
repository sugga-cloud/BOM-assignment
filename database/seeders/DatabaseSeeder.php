<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\Inventory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Default Admin User
        |--------------------------------------------------------------------------
        */

        User::updateOrCreate(
            ['email' => 'admin@bom-manager.local'],
            [
                'name' => 'BOM Administrator',
                'password' => Hash::make('password'),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Sample Projects
        |--------------------------------------------------------------------------
        */

        Project::updateOrCreate(
            ['name' => 'Oil Cooler Manufacturing Project'],
            [
                'description' => 'Industrial heat exchanger and oil cooler BOM management project.'
            ]
        );

        Project::updateOrCreate(
            ['name' => 'Heat Exchanger Fabrication Unit'],
            [
                'description' => 'Procurement and fabrication workflow for industrial exchanger systems.'
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Inventory Seed Data
        |--------------------------------------------------------------------------
        */
$items = [

    [
        'item_code' => 'P25.267.TSM',
        'description' => 'TUBE SHEET FOR MACHING',
        'available_qty' => 10,
    ],

    [
        'item_code' => 'P25.267.TPH',
        'description' => 'TOP PLATE HEADER',
        'available_qty' => 3,
    ],

    [
        'item_code' => 'P25.267.EPH',
        'description' => 'END PLATE FOR HEADER',
        'available_qty' => 0,
    ],

    [
        'item_code' => 'P25.267.PSM',
        'description' => 'PLUG SHEET FOR MACHING',
        'available_qty' => 5,
    ],

    [
        'item_code' => 'P25.267.BPH',
        'description' => 'BOTTOM PLATE FOR HEADER',
        'available_qty' => 1,
    ],

    [
        'item_code' => 'P25.267.EFTT',
        'description' => 'FINNED TUBE',
        'available_qty' => 200,
    ],

    [
        'item_code' => 'P25.267.NNP',
        'description' => 'NOZZLE NECK PIPE',
        'available_qty' => 0,
    ],

    [
        'item_code' => 'P25.267.NF',
        'description' => 'NOZZLE FLANGE',
        'available_qty' => 12,
    ],

    [
        'item_code' => 'P25.267.GR',
        'description' => 'GASKET RING',
        'available_qty' => 50,
    ],

    [
        'item_code' => 'P25.267.SB',
        'description' => 'SUPPORT BRACKET',
        'available_qty' => 4,
    ],
];

/*
|--------------------------------------------------------------------------
| Generate Additional Industrial Products
|--------------------------------------------------------------------------
*/

$industrialItems = [
    'PIPE CONNECTOR',
    'INDUSTRIAL VALVE',
    'STEEL FLANGE',
    'HEAT EXCHANGER COIL',
    'THERMAL SHIELD',
    'PRESSURE CAP',
    'COOLANT CHAMBER',
    'STEEL FASTENER',
    'FLOW REGULATOR',
    'MOUNTING FRAME',
    'TUBE SUPPORT',
    'COUPLING RING',
    'SEALING COVER',
    'HYDRAULIC JOINT',
    'WELDING SOCKET',
    'EXPANSION BELLOWS',
    'CONTROL PANEL',
    'PRESSURE SENSOR',
    'THERMAL GASKET',
    'PIPE ELBOW',
];

for ($i = 11; $i <= 50; $i++) {

    $qty = match (true) {

        // Out of stock
        $i % 5 === 0 => 0,

        // Partial stock
        $i % 3 === 0 => rand(1, 5),

        // Healthy stock
        default => rand(10, 250),
    };

    $items[] = [
        'item_code' => 'P25.267.' . strtoupper(substr(md5($i), 0, 4)),
        'description' => $industrialItems[array_rand($industrialItems)] . ' ' . $i,
        'available_qty' => $qty,
    ];
}
        /*
        |--------------------------------------------------------------------------
        | Insert / Update Inventory
        |--------------------------------------------------------------------------
        */

        foreach ($items as $item) {

            Inventory::updateOrCreate(
                [
                    'item_code' => $item['item_code']
                ],
                [
                    'description' => $item['description'],
                    'available_qty' => $item['available_qty'],
                ]
            );
        }
    }
}