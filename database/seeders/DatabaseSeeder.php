<?php

namespace Database\Seeders;

use App\Models\Gasto;
use App\Models\GastoDivision;
use App\Models\Invitacion;
use App\Models\Opcion;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Parámetros de usuarios configurables por variables de entorno (para aislamiento en la nube sin exponer en repo)
        $user1Email = env('SEED_ORGANIZADOR_EMAIL', 'test@example.com');
        $user1Pass = env('SEED_ORGANIZADOR_PASSWORD', 'password');
        $user1Name = env('SEED_ORGANIZADOR_NAME', 'Test User');

        $user2Email = env('SEED_PARTICIPANTE_EMAIL', 'test2@example.com');
        $user2Pass = env('SEED_PARTICIPANTE_PASSWORD', 'password');
        $user2Name = env('SEED_PARTICIPANTE_NAME', 'Test User 2');

        $user3Email = env('SEED_USER1_EMAIL', 'usuario1@example.com');
        $user3Pass = env('SEED_USER1_PASSWORD', 'password123');
        $user3Name = env('SEED_USER1_NAME', 'Usuario 1');

        $user4Email = env('SEED_USER2_EMAIL', 'usuario2@example.com');
        $user4Pass = env('SEED_USER2_PASSWORD', 'password123');
        $user4Name = env('SEED_USER2_NAME', 'Carlos');

        // Si se configuraron correos personalizados para producción, eliminar cuentas viejas de prueba estándar
        if ($user1Email !== 'test@example.com') {
            User::whereIn('email', ['test@example.com', 'test2@example.com', 'usuario1@example.com', 'usuario2@example.com'])->delete();
        }

        // Usuario 1: Organizador principal
        $user1 = User::updateOrCreate(
            ['email' => $user1Email],
            [
                'name' => $user1Name,
                'password' => Hash::make($user1Pass),
                'email_verified_at' => now(),
            ]
        );

        // Usuario 2: Participante y organizador alternativo
        $user2 = User::updateOrCreate(
            ['email' => $user2Email],
            [
                'name' => $user2Name,
                'password' => Hash::make($user2Pass),
                'email_verified_at' => now(),
            ]
        );

        // Usuario 3
        $user3 = User::updateOrCreate(
            ['email' => $user3Email],
            [
                'name' => $user3Name,
                'password' => Hash::make($user3Pass),
                'email_verified_at' => now(),
            ]
        );

        // Usuario 4
        $user4 = User::updateOrCreate(
            ['email' => $user4Email],
            [
                'name' => $user4Name,
                'password' => Hash::make($user4Pass),
                'email_verified_at' => now(),
            ]
        );

        // Datos precargados para entorno de producción o demostración
        if (app()->environment('production') || env('SEED_DEMO_DATA', false)) {
            // 1. Plan organizado por test@example.com (Rol Organizador)
            $plan1 = Plan::firstOrCreate(
                ['nombre' => 'Cena de Fin de Año 2026', 'user_id' => $user1->id],
                [
                    'organizador' => $user1->name,
                    'fecha_limite' => now()->addDays(15),
                    'estado' => 'pendiente',
                ]
            );

            Opcion::firstOrCreate([
                'plan_id' => $plan1->id,
                'tipo' => 'fecha',
                'valor' => '2026-12-20',
                'user_id' => $user1->id,
            ]);

            Opcion::firstOrCreate([
                'plan_id' => $plan1->id,
                'tipo' => 'lugar',
                'valor' => 'Restaurante El Fogón',
                'user_id' => $user1->id,
            ]);

            Invitacion::firstOrCreate(
                ['plan_id' => $plan1->id, 'email' => $user2->email],
                ['estado' => 'aceptada', 'user_id' => $user2->id, 'token' => Str::random(40)]
            );

            Invitacion::firstOrCreate(
                ['plan_id' => $plan1->id, 'email' => $user3->email],
                ['estado' => 'aceptada', 'user_id' => $user3->id, 'token' => Str::random(40)]
            );

            // 2. Plan organizado por test2@example.com donde test@example.com es Participante (Rol Participante)
            $plan2 = Plan::firstOrCreate(
                ['nombre' => 'Viaje al Lago Titicaca', 'user_id' => $user2->id],
                [
                    'organizador' => $user2->name,
                    'fecha_limite' => now()->addDays(25),
                    'estado' => 'confirmado',
                    'fecha_confirmacion' => now(),
                ]
            );

            Invitacion::firstOrCreate(
                ['plan_id' => $plan2->id, 'email' => $user1->email],
                ['estado' => 'aceptada', 'user_id' => $user1->id, 'token' => Str::random(40)]
            );

            Invitacion::firstOrCreate(
                ['plan_id' => $plan2->id, 'email' => $user4->email],
                ['estado' => 'aceptada', 'user_id' => $user4->id, 'token' => Str::random(40)]
            );

            // Gastos en el plan 2 para ver división y liquidación
            $gasto = Gasto::firstOrCreate(
                ['plan_id' => $plan2->id, 'concepto' => 'Transporte y Lancha'],
                [
                    'pagador_id' => $user2->id,
                    'monto_total' => 300.00,
                    'tipo_division' => 'equitativa',
                ]
            );

            GastoDivision::firstOrCreate(
                ['gasto_id' => $gasto->id, 'user_id' => $user1->id],
                ['monto_asignado' => 100.00]
            );

            GastoDivision::firstOrCreate(
                ['gasto_id' => $gasto->id, 'user_id' => $user2->id],
                ['monto_asignado' => 100.00]
            );

            GastoDivision::firstOrCreate(
                ['gasto_id' => $gasto->id, 'user_id' => $user4->id],
                ['monto_asignado' => 100.00]
            );

            // 3. Invitación pendiente para test@example.com para probar aceptar/rechazar
            $plan3 = Plan::firstOrCreate(
                ['nombre' => 'Torneo de Pádel Universitario', 'user_id' => $user3->id],
                [
                    'organizador' => $user3->name,
                    'fecha_limite' => now()->addDays(10),
                    'estado' => 'pendiente',
                ]
            );

            Invitacion::firstOrCreate(
                ['plan_id' => $plan3->id, 'email' => $user1->email],
                ['estado' => 'pendiente', 'user_id' => null, 'token' => Str::random(40)]
            );
        }
    }
}
