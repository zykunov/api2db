# API2DB - Сбор данных из API и сохранение в БД

## Описание

Приложение сохраняет следующие типы данных:
- **Доходы** (incomes) - информация о поступлении товаров на склады
- **Продажи** (sales) - информация о проданных товарах
- **Заказы** (orders) - информация о заказах клиентов
- **Остатки** (stocks) - информация о складских остатках

## Структура проекта

### Модели

| Модель | Таблица | Описание |
|--------|---------|----------|
| `App\Models\Income` | `incomes` | Данные о доходах |
| `App\Models\Sale` | `sales` | Данные о продажах |
| `App\Models\Order` | `orders` | Данные о заказах |
| `App\Models\Stock` | `stocks` | Данные об остатках |

### Сервисы

| Сервис | Файл | Описание |
|--------|------|----------|
| `IncomeService` | `app/Services/IncomeService.php` | Сбор данных о доходах |
| `SaleService` | `app/Services/SaleService.php` | Сбор данных о продажах |
| `OrderService` | `app/Services/OrderService.php` | Сбор данных о заказах |
| `StockService` | `app/Services/StockService.php` | Сбор данных об остатках |

Каждый сервис:
- Использует Guzzle для HTTP-запросов
- Автоматически перебирает страницы API
- Сохраняет данные через Eloquent модели
- Обрабатывает ошибки и управляет транзакциями

## Команды artisan

Все команды (кроме stocks:collect) поддерживают следующие опции:
- `--dateFrom=YYYY-MM-DD` - дата начала периода (по умолчанию: 2025-01-01)
- `--dateTo=YYYY-MM-DD` - дата окончания периода (по умолчанию: текущая дата)

### Сбор данных о доходах

```bash
php artisan incomes:collect
php artisan incomes:collect --dateFrom=2025-01-01 --dateTo=2026-07-06
```


### Сбор данных о продажах

```bash
php artisan sales:collect
php artisan sales:collect --dateFrom=2025-01-01 --dateTo=2026-07-06
```


### Сбор данных о заказах

```bash
php artisan orders:collect
php artisan orders:collect --dateFrom=2025-01-01 --dateTo=2026-07-06
```

### Сбор данных об остатках

```bash
php artisan stocks:collect
php artisan stocks:collect --dateFrom=2025-01-01
```

## Миграции
В проекте используются следующие миграции:
1. `2024_01_01_000000_create_incomes_table` - таблица доходов
2. `2024_01_01_000001_create_sales_table` - таблица продаж
3. `2024_01_01_000002_create_orders_table` - таблица заказов
4. `2024_01_01_000003_create_stocks_table` - таблица остатков


