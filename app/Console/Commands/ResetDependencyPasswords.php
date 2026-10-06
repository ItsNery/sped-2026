<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetDependencyPasswords extends Command
{
    protected $signature = 'sped:reset-dependency-passwords
        {--execute : Restablecer contrasenas; sin esta opcion solo se muestra el alcance}
        {--report= : Ruta opcional para el CSV privado de contrasenas temporales}';

    protected $description = 'Genera contrasenas temporales para enlaces dependencia activos.';

    public function handle(): int
    {
        $users = User::role('Enlace dependencia')
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'name', 'email']);

        $this->line('Enlaces dependencia activos: ' . $users->count());

        if (!$this->option('execute')) {
            $this->warn('Modo DRY-RUN: no se modificaron contrasenas ni se genero un reporte con secretos.');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($users as $user) {
            $password = Str::password(20, letters: true, numbers: true, symbols: false, spaces: false);
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
                'failed_login_attempts' => 0,
            ])->save();

            $rows[] = [$user->id, $user->name, $user->email, $password];
        }

        $reportPath = $this->option('report') ?: storage_path('app/password-resets/enlaces-dependencia-' . now()->format('Ymd-His') . '.csv');
        File::ensureDirectoryExists(dirname($reportPath));
        File::put($reportPath, $this->csv($rows));
        chmod($reportPath, 0600);

        $this->info('Contrasenas restablecidas: ' . count($rows));
        $this->warn('El CSV contiene secretos. Entregalo por un canal seguro y eliminalo despues de distribuirlo.');
        $this->line('Reporte privado: ' . $reportPath);

        return self::SUCCESS;
    }

    /**
     * @param list<array{0: int, 1: string, 2: string, 3: string}> $rows
     */
    private function csv(array $rows): string
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, ['ID', 'Nombre', 'Correo', 'Contraseña temporal']);

        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv === false ? '' : $csv;
    }
}
