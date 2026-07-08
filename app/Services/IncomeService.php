<?php

namespace App\Services;

use App\Models\Income;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class IncomeService
{
    protected Client $client;

    protected string $baseUrl;

    protected string $apiKey;

    protected string $endpoint = 'incomes';

    public function __construct()
    {
        $this->baseUrl = Config::get('services.api.base_url', 'http://109.73.206.144:6969/api/');
        $this->apiKey = Config::get('services.api.key');
        $this->client = new Client();
    }

    /**
     * Сбор данных с API по всем страницам
     * @throws GuzzleException
     */
    public function collectIncomes(string $dateFrom, ?string $dateTo = null): void
    {
        $page = 1;
        $hasMorePages = true;

        $dateFrom = $dateFrom ?: '2025-01-01';
        $dateTo = $dateTo ?: date('Y-m-d');

        DB::beginTransaction();
        try {
            while ($hasMorePages) {
                usleep(200000); //5 запросов в секунду
                $response = $this->fetchPage($page, $dateFrom, $dateTo);

                if (!isset($response['data'])) {
                    break;
                }

                $incomes = $response['data'];

                if (empty($incomes)) {
                    $hasMorePages = false;
                    break;
                }

                // Сохраняем данные
                foreach ($incomes as $incomeData) {
                    $this->saveIncome($incomeData);
                }

                echo "Страница $page обработана. Записано " . count($incomes) . " записей.\n";

                // Проверяем, есть ли еще страницы
                if (isset($response['data']['pagination'])) {
                    $pagination = $response['data']['pagination'];
                    if ($pagination['page'] >= $pagination['total_pages']) {
                        $hasMorePages = false;
                    }
                }

                $page++;
            }

            DB::commit();
            echo "Сбор данных завершен. Всего записей: " . Income::count() . "\n";

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Ошибка при сборе данных: " . $e->getMessage());
        }
    }

    /**
     * Получение одной страницы данных
     * @throws GuzzleException
     */
    protected function fetchPage(int $page, string $dateFrom, string $dateTo): array
    {
        $url = $this->baseUrl . $this->endpoint . "?dateTo={$dateTo}&page={$page}&key={$this->apiKey}&dateFrom={$dateFrom}";

        $response = $this->client->request('GET', $url, [
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);

        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Ошибка декодирования JSON: " . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Сохранение одной записи
     */
    protected function saveIncome(array $data): void
    {
        Income::create([
            'income_id' => $data['income_id'],
            'number' => $data['number'] ?? '',
            'date' => $data['date'] ?? null,
            'last_change_date' => $data['last_change_date'] ?? null,
            'supplier_article' => $data['supplier_article'] ?? '',
            'tech_size' => $data['tech_size'] ?? '',
            'barcode' => $data['barcode'] ?? null,
            'quantity' => $data['quantity'] ?? 0,
            'total_price' => $data['total_price'] ?? 0,
            'date_close' => $data['date_close'] ?? null,
            'warehouse_name' => $data['warehouse_name'] ?? '',
            'nm_id' => $data['nm_id'] ?? null,
        ]);
    }
}
