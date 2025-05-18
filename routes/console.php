<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Cache;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:import-open-foods-facts', function () {
    $this->info('Verificando recursos do sistema...');

    $cpuLimit = (float) env('IMPORT_CPU_LIMIT', 0.6);
    $ramLimitMb = (int) env('IMPORT_RAM_LIMIT_MB', 1024);
    $maxLoad = (float) env('IMPORT_TIME_LIMIT_MINUTES', 30);

    $load = sys_getloadavg();
    $cpuCores = (int) trim(shell_exec("nproc"));
    $maxLoad = $cpuCores * $cpuLimit;

    if ($load[0] > $maxLoad) {
        $this->error("Uso de CPU muito alto no momento ({$load[0]}). Tente novamente mais tarde.");
        return 1;
    }

    $memInfo = file_get_contents("/proc/meminfo");
    preg_match("/MemAvailable:\\s+(\\d+)/", $memInfo, $matches);
    $memAvailableKb = isset($matches[1]) ? (int)$matches[1] : 0;
    $memAvailableMb = $memAvailableKb / 1024;

    if ($memAvailableMb < $ramLimitMb) {
        $this->error("Memória RAM disponível insuficiente ({$memAvailableMb} MB). Tente novamente mais tarde.");
        return 1;
    }

    $this->info('Recursos do sistema OK. Iniciando build do importador Go...');

    $script = base_path('cmd/build.sh');
    if (!file_exists($script)) {
        $this->error('Script build.sh não encontrado!');
        return 1;
    }

    $process = new Process(['bash', $script]);
    $process->setTimeout($maxLoad * 60);
    $process->run();

    if (!$process->isSuccessful()) {
        $this->error('Erro ao executar o build: ' . $process->getErrorOutput());
        return 1;
    }

    $this->info('Build do importador Go concluído com sucesso!');

    $this->info('Iniciando importação dos produtos...');

    $process = new Process([base_path('cmd/build/import_openfoodfacts')]);
    $process->setTimeout(600);
    $process->run(function ($type, $buffer) {
        $this->info($buffer);
    });

    if (!$process->isSuccessful()) {
        $this->error('Erro ao executar o importador Go: ' . $process->getErrorOutput());
        return 1;
    }

    Cache::put('cron_last_run', now());

    $this->info('Importação dos produtos concluída com sucesso!');
    return 0;
});

app(Schedule::class)
    ->command('app:import-open-foods-facts')
    ->timezone('America/Sao_Paulo')
    ->withoutOverlapping()
    ->sendOutputTo(storage_path('logs/import-open-foods-facts.log'))
    ->dailyAt('02:00');