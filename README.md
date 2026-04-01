<b><h1>Телеграм бот авто барахолки</h1></b>

В данный момент не работает - @avto73ru_bot

**_Суть:
Пользователь путем общения с ботом создаёт объявление, которое публикуется в паблик. Так же пользователь может настроить фильтры и получать уведомления в боте о конкретных объявлениях.
Бот выполняет роль модератора, типизируя объявления и валидируя от спама. Запоминает пользователей для рассылки по фильтрам._**

## **Стек:**
* PHP 8.2
* Фреймворк Laravel 12
* MySQL
* Redis кеш + очереди
* Docker для контеризации
* Библиотека по работе с Telegram API - Telegram Bot SDK
* Используется форматер кода laravel pint



## Архитектурные решения:
* Используется Eloquent ORM (2 таблицы связь 1 к 1)
* Основная бизнес логика вынесена в TelegramBotService
* Используется Redis для асинхронной записи в лог через worker
* Используется Redis для кеширования стадии пользователя и username
* Сервисный слой для координации основной DB и Redis


## Структура проекта:
* `app/Constant` - Константы (стадии пользователя, наименования подкатегорий)
* `app/Http/Controllers/TelegramBotController.php` - Основной контроллер
* `app/Jobs/` - Задачи для очереди
* `app/Models/` - Модели для 2 таблиц
* `app/Providers/AppServiceProvider.php` - (путь к public_gropu_id, связь интерфейсов)
* `app/Repositories/` - Репозитории
  * `app/Repositories/Contracts/` - Интерфейсы репозиториев
  * `CacheRepository.php` - Репозиторий по работе с Redis
  * `DatabaseRepository.php` - Репозиторий по работе с основной БД
* `app/Services/`
  * `AdvValidationService.php` - Сервис кастомной валидации
  * `LoggerService` - Сервис асинхронного логирования
  * `RepositoryService.php` - Сервис координации репозиториев
  * `SenderService.php` - Сервис отправки сообщений при помощи библиотеки Telegram Bot SDK
  * `TelegramBotService.php` - Сервис с основной бизнес логикой
  * `TextMessagesService.php` - Класс хранящий текста и клавиатуры 
* `Dockerfile` - готовый dockerfile для сборки
* `docker-compose.yml` - настройка остальных контейнеров mysql, redis, nginx, ngrok, worker


## Запуск приложения на локальном ПК:
1. Скопировать `.env.example` в `.env`, указав необходимые параметры
    * APP_KEY
    * DB_USERNAME, DB_PASSWORD
    * TELEGRAM_BOT_TOKEN - токен созданного вами бота в @BotFather
    * TELEGRAM_BOT_USERNAME - имя вашего бота
    * TELEGRAM_PUBLIC_GROUP_ID - chat_id телеграм канала для публикации
2. Собрать и запустить Docker-контейнеры `docker-compose up -d`
3. Запустить миграции `docker-compose exec app php artisan migrate`
4. Ngrok пробрасывает публичный адрес на ваш локальный `nginx` (порт 80). После запуска `docker-compose up -d`
   * Откройте в браузере `http://localhost:4040`. Вы увидите интерфейс ngrok
   * Скопируйте публичный HTTPS-адрес, например `https://abc123.ngrok-free.app`.
   * Установите вебхук (замените `ВАШ_ТОКЕН_БОТА` и добавьте ваш путь к эндпоинту
   * `curl -F "url=https://abc123.ngrok-free.app/api/webhook" "https://api.telegram.org/botВАШ_ТОКЕН_БОТА/setWebhook"`
   * Проверка установки вебхука `curl "https://api.telegram.org/botВАШ_ТОКЕН_БОТА/getWebhookInfo"`
