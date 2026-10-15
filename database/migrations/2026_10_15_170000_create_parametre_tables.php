<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('password');
            $table->boolean('is_validated')->default(false)->after('role');
        });

        DB::table('users')->update([
            'role' => 'user',
            'is_validated' => true,
        ]);

        DB::table('users')
            ->where('email', 'mbengueboubacar095@gmail.com')
            ->update(['role' => 'admin', 'is_validated' => true]);

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('slt_region');
            $table->timestamps();
        });

        Schema::create('departements', function (Blueprint $table) {
            $table->id();
            $table->string('slt_departement');
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('arrondissements', function (Blueprint $table) {
            $table->id();
            $table->string('slt_arrondissement');
            $table->foreignId('departement_id')->constrained('departements')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('communes', function (Blueprint $table) {
            $table->id();
            $table->string('slt_commune');
            $table->foreignId('arrondissement_id')->constrained('arrondissements')->cascadeOnDelete();
            $table->string('txt_codeCommune');
            $table->timestamps();
        });

        Schema::create('lotissements', function (Blueprint $table) {
            $table->id();
            $table->string('txt_denomination');
            $table->string('txt_nombreParcelle');
            $table->string('txt_surfaceLotissement');
            $table->string('txt_documentsAfferants')->nullable();
            $table->foreignId('slt_departement')->constrained('departements')->cascadeOnDelete();
            $table->foreignId('slt_arrondissement')->constrained('arrondissements')->cascadeOnDelete();
            $table->foreignId('slt_commune')->constrained('communes')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('point_densifiers', function (Blueprint $table) {
            $table->id();
            $table->string('txt_nomPoint');
            $table->string('txt_dateCreationPoint');
            $table->text('txt_descriptionPoint')->nullable();
            $table->text('txt_situationPoint')->nullable();
            $table->string('txt_estPoint')->nullable();
            $table->string('txt_nordPoint')->nullable();
            $table->string('txt_hauteurPoint')->nullable();
            $table->text('fichierPDFPoint')->nullable();
            $table->foreignId('slt_departement')->constrained('departements');
            $table->foreignId('slt_arrondissement')->constrained('arrondissements');
            $table->foreignId('slt_commune')->constrained('communes');
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('txt_numeroNote');
            $table->string('txt_typeNote');
            $table->string('txt_titreNote');
            $table->string('fichierPDF')->nullable();
            $table->timestamps();
        });

        Schema::create('baremes_evaluation', function (Blueprint $table) {
            $table->id();
            $table->string('groupe');
            $table->string('type')->default('');
            $table->string('code');
            $table->unsignedInteger('prix');
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->unique(['groupe', 'type', 'code']);
        });

        Schema::create('activites_utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action', 32);
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });

        $now = now();
        $regionId = DB::table('regions')->insertGetId([
            'slt_region' => 'THIES',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $mbourId = DB::table('departements')->insertGetId([
            'slt_departement' => 'Mbour',
            'region_id' => $regionId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $arrondissements = [
            'Mbour' => ['Mbour', 'Ngaparou', 'Saly Portudal', 'Malicounda', 'Nguékhokh', 'Somone', 'Popenguine'],
            'Fissel' => ['Fissel', 'Ndiaganiao'],
            'Sessène' => ['Sessène', 'Sandiara'],
            'Sindian' => ['Sindian', 'Nguéniène'],
        ];

        $code = 1;
        foreach ($arrondissements as $nom => $communes) {
            $arrondissementId = DB::table('arrondissements')->insertGetId([
                'slt_arrondissement' => $nom,
                'departement_id' => $mbourId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($communes as $commune) {
                DB::table('communes')->insert([
                    'slt_commune' => $commune,
                    'arrondissement_id' => $arrondissementId,
                    'txt_codeCommune' => 'MB'.str_pad((string) $code, 2, '0', STR_PAD_LEFT),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $code++;
            }
        }

        if (DB::table('code_acces')->count() === 0) {
            DB::table('code_acces')->insert([
                ['id' => 1, 'code' => '0101', 'utilise' => false, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 2, 'code' => '0202', 'utilise' => false, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 3, 'code' => 'admin@1234', 'utilise' => false, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activites_utilisateurs');
        Schema::dropIfExists('baremes_evaluation');
        Schema::dropIfExists('services');
        Schema::dropIfExists('point_densifiers');
        Schema::dropIfExists('lotissements');
        Schema::dropIfExists('communes');
        Schema::dropIfExists('arrondissements');
        Schema::dropIfExists('departements');
        Schema::dropIfExists('regions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_validated']);
        });
    }
};
