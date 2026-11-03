<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class CodeAcces extends Model
{
    protected $table = 'code_acces';

    protected $fillable = [
        'code',
        'utilise',
    ];

    protected $casts = [
        'utilise' => 'boolean',
    ];

    public static function correspond(string $code, ?int $id = null): bool
    {
        $code = trim($code);
        if ($code === '') {
            return false;
        }

        $requete = static::query()->where('code', $code);
        if ($id !== null) {
            $requete->where('id', $id);
        }

        // Un code valide doit toujours passer (même après des échecs)
        if ($requete->exists()) {
            self::effacerEchecs();

            return true;
        }

        if (! self::tentativesEpuisees()) {
            self::enregistrerEchec();
        }

        return false;
    }

    public static function estBloque(): bool
    {
        return self::tentativesEpuisees();
    }

    public static function consommer(string $code): bool
    {
        $code = trim($code);
        if ($code === '' || self::tentativesEpuisees()) {
            return false;
        }

        $consomme = false;

        DB::transaction(function () use ($code, &$consomme) {
            $enregistrement = static::query()
                ->where('code', $code)
                ->where('utilise', false)
                ->lockForUpdate()
                ->first();

            if (! $enregistrement) {
                return;
            }

            $enregistrement->update(['utilise' => true]);
            $consomme = true;
        });

        if (! $consomme) {
            self::enregistrerEchec();

            return false;
        }

        self::effacerEchecs();

        return true;
    }

    private static function cleTentatives(): string
    {
        return 'code-acces|'.request()->ip();
    }

    private static function tentativesEpuisees(): bool
    {
        return RateLimiter::tooManyAttempts(self::cleTentatives(), 5);
    }

    private static function enregistrerEchec(): void
    {
        RateLimiter::hit(self::cleTentatives(), 300);
    }

    private static function effacerEchecs(): void
    {
        RateLimiter::clear(self::cleTentatives());
    }
}
