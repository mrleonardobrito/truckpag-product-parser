<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatusController extends Controller
{
    private $healthCheckConnection;

    public function __construct()
    {
        if (config('database.default') === 'mongodb') {
            $this->healthCheckConnection = DB::connection('mongodb')->table('health_check');
        } else {
            $this->healthCheckConnection = DB::connection('sqlite')->table('health_check');
        }
    }

    /**
     * @OA\Get(
     *     path="/api",
     *     summary="Get status of the API",
     *     tags={"Produtos"},
     *     @OA\Response(
     *         response=200,
     *         description="Status of the API",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="api", type="string", example="TruckPag Product Parser"),
     *             @OA\Property(property="db_read", type="string", example="OK"),
     *             @OA\Property(property="db_write", type="string", example="OK"),
     *             @OA\Property(property="cron_last_run", type="string", example="2021-01-01 00:00:00"),
     *             @OA\Property(property="uptime_seconds", type="integer", example=100000),
     *             @OA\Property(property="memory_usage_mb", type="integer", example=1000000),
     *         )
     *     )
     * )
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $healthCheck = [];
        $cronLastRun = Cache::get('cron_last_run');
        $uptime = @file_get_contents('/proc/uptime');
        $uptime = $uptime ? intval(explode(' ', $uptime)[0]) : null;
        $mem = memory_get_usage(true) / 1024 / 1024;
        $mem = round($mem, 2);
        try {
            $healthCheck['api'] = 'TruckPag Product Parser';
            $healthCheck['db_read'] = 'OK';
            $healthCheck['db_write'] = 'OK';
            $healthCheck['cron_last_run'] = $cronLastRun;
            $healthCheck['uptime_seconds'] = $uptime;
            $healthCheck['memory_usage_mb'] = $mem;
            $healthCheck['created_at'] = now()->toDateTimeString('minute');
            $this->healthCheckConnection->updateOrInsert([  'created_at' => $healthCheck['created_at']], $healthCheck);
        } catch (\Exception $e) {
            $healthCheck['db_read'] = 'Erro: ' . $e->getMessage();
            $healthCheck['db_write'] = 'Erro: ' . $e->getMessage();
        }

        return response()->json([
            'api' => 'TruckPag Product Parser',
            'db_read' => $healthCheck['db_read'],
            'db_write' => $healthCheck['db_write'],
            'cron_last_run' => $healthCheck['cron_last_run'],
            'uptime_seconds' => $healthCheck['uptime_seconds'],
            'memory_usage_mb' => $healthCheck['memory_usage_mb'],
            'created_at' => $healthCheck['created_at'],
        ]);
    }
}
