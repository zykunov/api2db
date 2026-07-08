<?php

namespace App\Services;

use App\Models\Stock;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class StockService
{
    protected Client $client;

    protected string $baseUrl;

    protected string $apiKey;

    protected string $endpoint = 'stocks';

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
    public function collectStocks(string $dateFrom): void
    {
        $page = 1;
        $hasMorePages = true;

        $dateFrom = $dateFrom ?: '2025-01-01';

        DB::beginTransaction();
        try {
            while ($hasMorePages) {
                $response = $this->fetchPage($page, $dateFrom);

                if (!isset($response['data'])) {
                    break;
                }

                $stocks = $response['data'];

                if (empty($stocks)) {
                    $hasMorePages = false;
                    break;
                }

                // Сохраняем данные
                foreach ($stocks as $stockData) {
                    $this->saveStock($stockData);
                }

                echo "Страница $page обработана. Записано " . count($stocks) . " записей.\n";

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
            echo "Сбор данных завершен. Всего записей: " . Stock::count() . "\n";

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Ошибка при сборе данных: " . $e->getMessage());
        }
    }

    /**
     * Получение одной страницы данных
     * @throws GuzzleException
     */
    protected function fetchPage(int $page, string $dateFrom): array
    {
        $url = $this->baseUrl . $this->endpoint . "?dateFrom={$dateFrom}&key={$this->apiKey}&page={$page}";

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
    protected function saveStock(array $data): void
    {
        Stock::create([
            'date' => $data['date'] ?? null,
            'last_change_date' => $data['last_change_date'] ?? null,
            'supplier_article' => $data['supplier_article'] ?? '',
            'tech_size' => $data['tech_size'] ?? '',
            'barcode' => $data['barcode'] ?? null,
            'quantity' => $data['quantity'] ?? 0,
            'is_supply' => $data['is_supply'] ?? false,
            'is_realization' => $data['is_realization'] ?? false,
            'quantity_full' => $data['quantity_full'] ?? 0,
            'warehouse_name' => $data['warehouse_name'] ?? '',
            'in_way_to_client' => $data['in_way_to_client'] ?? 0,
            'in_way_from_client' => $data['in_way_from_client'] ?? 0,
            'nm_id' => $data['nm_id'] ?? null,
            'subject' => $data['subject'] ?? '',
            'category' => $data['category'] ?? '',
            'brand' => $data['brand'] ?? '',
            'sc_code' => $data['sc_code'] ?? null,
            'price' => $data['price'] ?? 0,
            'discount' => $data['discount'] ?? 0,
        ]);
    }
}
