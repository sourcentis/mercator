<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('roles')->count() === 0) {
            // Ids explicites : User::isAdmin() identifie l'administrateur par le rôle d'id 1, que
            // l'auto-incrément MySQL ne garantit pas d'un test à l'autre (dérive non transactionnelle).

            $roles = [
                [
                    'id' => 1,
                    'title' => 'Admin',
                ],
                [
                    'id' => 2,
                    'title' => 'User',
                ],
                [
                    'id' => 3,
                    'title' => 'Auditor',
                ],
                [
                    'id' => 4,
                    'title' => 'Cartographer',
                ],
            ];

            Role::query()->insert($roles);
        }
    }
}
