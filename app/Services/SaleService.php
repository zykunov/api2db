<?php

namespace App\Services;

use App\Models\Sale;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class SaleService
{
    protected Client $client;

    protected string $baseUrl;

    protected string $apiKey;

    protected string $dateTo = '2026-07-06';

    public function __construct()
    {
        $this->baseUrl  = Config::get('services.api.base_url', 'http://109.73.206.144:6969/api/sales').'sales';
        $this->apiKey   = Config::get('services.api.key');
        $this->client   = new Client();
    }

    /**
     * Сбор данных с API по всем страницам
     * @throws GuzzleException
     */
    public function collectSales(string $dateFrom, ?string $dateTo = null): void
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

                $sales = $response['data'];

                if (empty($sales)) {
                    $hasMorePages = false;
                    break;
                }

                // Сохраняем данные
                foreach ($sales as $saleData) {
                    $this->saveSale($saleData);
                }

                echo "Страница $page обработана. Записано " . count($sales) . " записей.\n";

                // Проверяем, есть ли еще страницы
                if (isset($response['data']['pagination'])) {
                    $pagination = $response['data']['pagination'];
                    if ($pagination['page'] >= $pagination['total_pages']) {
                        $hasMorePages = false;
                    }
                } else {
                    // Если пагинации нет — считаем, что это последняя страница
                    $hasMorePages = false;
                }

                $page++;
            }

            DB::commit();
            echo "Сбор данных завершен. Всего записей: " . Sale::count() . "\n";

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
        $url = "{$this->baseUrl}?dateTo={$dateTo}&page={$page}&key={$this->apiKey}&dateFrom={$dateFrom}";

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
    protected function saveSale(array $data): void
    {
        Sale::create([
            'sale_id'                  => $data['sale_id'],
            'g_number'                 => $data['g_number'] ?? '',
            'date'                     => $data['date'] ?? null,
            'last_change_date'         => $data['last_change_date'] ?? null,
            'supplier_article'         => $data['supplier_article'] ?? '',
            'tech_size'                => $data['tech_size'] ?? '',
            'barcode'                  => $data['barcode'] ?? null,
            'total_price'              => $data['total_price'] ?? 0,
            'discount_percent'         => $data['discount_percent'] ?? 0,
            'is_supply'                => $data['is_supply'] ?? false,
            'is_realization'           => $data['is_realization'] ?? false,
            'promo_code_discount'      => $data['promo_code_discount'] ?? null,
            'warehouse_name'           => $data['warehouse_name'] ?? '',
            'country_name'             => $data['country_name'] ?? '',
            'oblast_okrug_name'        => $data['oblast_okrug_name'] ?? '',
            'region_name'              => $data['region_name'] ?? '',
            'income_id'                => $data['income_id'] ?? null,
            'odid'                     => $data['odid'] ?? null,
            'spp'                      => $data['spp'] ?? 0,
            'for_pay'                  => $data['for_pay'] ?? 0,
            'finished_price'         => $data['finished_price'] ?? 0,
            'price_with_disc'          => $data['price_with_disc'] ?? 0,
            'nm_id'                    => $data['nm_id'] ?? null,
            'subject'                  => $data['subject'] ?? '',
            'category'                 => $data['category'] ?? '',
            'brand'                    => $data['brand'] ?? '',
            'is_storno'                => $data['is_storno'] ?? null,
        ]);

    }
}
