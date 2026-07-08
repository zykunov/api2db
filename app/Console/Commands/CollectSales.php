<?php

namespace App\Console\Commands;

use App\Services\SaleService;
use Illuminate\Console\Command;

class CollectSales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:collect
                            {--page= : Начальная страница для сбора}
                            {--dateFrom= : Дата начала (по умолчанию 2025-01-01)}
                            {--dateTo= : Дата окончания (по умолчанию текущая)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Сбор данных о продажах с внешнего API';

    /**
     * Execute the console command.
     */
    public function handle(SaleService $saleService): int
    {
        $this->info('Начало сбора данных...');

        $dateFrom = $this->option('dateFrom') ?: '2025-01-01';
        $dateTo = $this->option('dateTo');

        $this->info("Период: {$dateFrom} - " . ($dateTo ?: 'текущая дата'));

        try {
            $saleService->collectSales($dateFrom, $dateTo);
            $this->info('Данные успешно собраны!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Ошибка: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
