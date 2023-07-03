<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $models = ['users', 'usermangements', 'roles', 'notifications',
            'posts', 'categories', 'helps'
        ];
        $maps = ['create', 'update', 'read', 'delete'];

        foreach ($models as $model) {


            foreach ($maps as $map) {

                Permission::create([

                    'name' => $map . '_' . $model,
                    'display_name' => $map . '_' . $model,
                    'description' => $map . '_' . $model
                ]);

            }
        }
    }
}
