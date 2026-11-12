<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoInformationTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        \DB::table('information')->delete();

        \DB::table('information')->insert([
            0 => [
                'id' => 1,
                'name' => 'Information 1',
                'description' => '<p>Description de l\'information 1</p>',
                'owner' => 'Luc',
                'administrator' => null,
                'storage' => 'externe',
                'security_need_c' => 1,
                'sensitivity' => 'Donnée à caractère personnel',
                'constraints' => '<p>Description des contraintes règlementaires et normatives</p>',
                'created_at' => '2020-06-13 02:06:43',
                'updated_at' => '2021-11-04 08:43:27',
                'deleted_at' => null,
                'security_need_i' => 3,
                'security_need_a' => 2,
                'security_need_t' => 2,
                'retention_period' => null,
            ],
            1 => [
                'id' => 2,
                'name' => 'information 2',
                'description' => '<p>Description de l\'information</p>',
                'owner' => 'Nestor',
                'administrator' => 'Nom de l\'administrateur',
                'storage' => 'externe',
                'security_need_c' => 2,
                'sensitivity' => 'Donnée à caractère personnel',
                'constraints' => null,
                'created_at' => '2020-06-13 02:09:13',
                'updated_at' => '2021-08-19 18:42:53',
                'deleted_at' => null,
                'security_need_i' => 1,
                'security_need_a' => 1,
                'security_need_t' => 1,
                'retention_period' => null,
            ],
            2 => [
                'id' => 3,
                'name' => 'information 3',
                'description' => '<p>Descripton de l\'information 3</p>',
                'owner' => 'Paul',
                'administrator' => 'Jean',
                'storage' => 'Local',
                'security_need_c' => 4,
                'sensitivity' => 'Donnée à caractère personnel',
                'constraints' => null,
                'created_at' => '2020-06-13 02:10:07',
                'updated_at' => '2021-09-28 19:42:07',
                'deleted_at' => null,
                'security_need_i' => 4,
                'security_need_a' => 3,
                'security_need_t' => 4,
                'retention_period' => null,
            ],
            3 => [
                'id' => 4,
                'name' => 'Information de test',
                'description' => '<p>decription du test</p>',
                'owner' => 'RSSI',
                'administrator' => 'Paul',
                'storage' => 'Local',
                'security_need_c' => 1,
                'sensitivity' => 'Technical',
                'constraints' => null,
                'created_at' => '2020-07-01 17:00:37',
                'updated_at' => '2021-08-19 18:45:52',
                'deleted_at' => null,
                'security_need_i' => 1,
                'security_need_a' => 1,
                'security_need_t' => 1,
                'retention_period' => null,
            ],
            4 => [
                'id' => 5,
                'name' => 'Données du client',
                'description' => '<p>Données d\'identification du client</p>',
                'owner' => 'Nestor',
                'administrator' => 'Paul',
                'storage' => 'Local',
                'security_need_c' => 2,
                'sensitivity' => 'Donnée à caractère personnel',
                'constraints' => '<p>RGPD</p>',
                'created_at' => '2021-05-14 12:50:09',
                'updated_at' => '2022-03-21 18:12:30',
                'deleted_at' => null,
                'security_need_i' => 2,
                'security_need_a' => 2,
                'security_need_t' => 2,
                'retention_period' => null,
            ],
        ]);

    }
}
