<?php

namespace App\Console\Commands;

use App\Services\StockService;
use Illuminate\Console\Command;

class CollectStocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stocks:collect
                            {--page= : Начальная страница для сбора}
                            {--dateFrom= : Дата начала (по умолчанию 2025-01-01)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Сбор данных о складских остатках с внешнего API';

    /**
     * Execute the console command.
     */
    public function handle(StockService $stockService): int
    {
        $this->info('Начало сбора данных...');


        $dateFrom = $this->option('dateFrom') ?: now()->format('Y-m-d');

        $this->info("Период: {$dateFrom}");

        try {
            $stockService->collectStocks($dateFrom);
            $this->info('Данные успешно собраны!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Ошибка: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
