<?php

namespace App\Services;

use App\Models\Order;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class OrderService
{
    protected Client $client;

    protected string $baseUrl;

    protected string $apiKey;

    protected string $dateTo = '2026-07-06';

    public function __construct()
    {
        $this->baseUrl  = Config::get('services.api.base_url', 'http://109.73.206.144:6969/api/orders').'orders';
        $this->apiKey   = Config::get('services.api.key');
        $this->client   = new Client();
    }

    /**
     * Сбор данных с API по всем страницам
     * @throws GuzzleException
     */
    public function collectOrders(string $dateFrom, ?string $dateTo = null): void
    {
        $page = 1;
        $hasMorePages = true;

        $dateFrom = $dateFrom ?: '2025-01-01';
        $dateTo = $dateTo ?: $this->dateTo;

        DB::beginTransaction();
        try {
            while ($hasMorePages) {
                $response = $this->fetchPage($page, $dateFrom, $dateTo);

                if (!isset($response['data'])) {
                    break;
                }

                $orders = $response['data'];

                if (empty($orders)) {
                    $hasMorePages = false;
                    break;
                }

                // Сохраняем данные
                foreach ($orders as $orderData) {
                    $this->saveOrder($orderData);
                }

                echo "Страница $page обработана. Записано " . count($orders) . " записей.\n";

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
            echo "Сбор данных завершен. Всего записей: " . Order::count() . "\n";

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
        $url = "{$this->baseUrl}?dateTo={$dateTo}&key={$this->apiKey}&dateFrom={$dateFrom}&page={$page}";

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
    protected function saveOrder(array $data): void
    {
        Order::create([
            'g_number'               => $data['g_number'],
            'date'                   => $data['date'] ?? null,
            'last_change_date'       => $data['last_change_date'] ?? null,
            'supplier_article'       => $data['supplier_article'] ?? '',
            'tech_size'              => $data['tech_size'] ?? '',
            'barcode'                => $data['barcode'] ?? null,
            'total_price'            => $data['total_price'] ?? 0,
            'discount_percent'       => $data['discount_percent'] ?? 0,
            'warehouse_name'         => $data['warehouse_name'] ?? '',
            'oblast'                 => $data['oblast'] ?? '',
            'income_id'              => $data['income_id'] ?? null,
            'odid'                   => $data['odid'] ?? null,
            'nm_id'                  => $data['nm_id'] ?? null,
            'subject'                => $data['subject'] ?? '',
            'category'               => $data['category'] ?? '',
            'brand'                  => $data['brand'] ?? '',
            'is_cancel'              => $data['is_cancel'] ?? false,
            'cancel_dt'              => $data['cancel_dt'] ?? null,
        ]);

    }
}
