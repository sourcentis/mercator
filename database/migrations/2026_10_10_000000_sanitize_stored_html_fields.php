<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

// Les champs riches sont affichés sans échappement : assainir ceux enregistrés
// avant l'assainissement en entrée (voir mercator:sanitize-html).
return new class extends Migration
{
    public function up(): void
    {
        Artisan::call('mercator:sanitize-html');
    }

    public function down(): void
    {
        // Irréversible : le HTML d'origine n'est pas conservé
    }
};
