<b><h1>Телеграм бот авто барахолки</h1></b>

В данный момент не работает - @avto73ru_bot

**_Суть:
Пользователь путем общения с ботом создаёт объявление, которое публикуется в паблик. Так же пользователь может настроить фильтры и получать уведомления в боте о конкретных объявлениях.
Бот выполняет роль модератора, типизируя объявления и валидируя от спама. Запоминает пользователей для рассылки по фильтрам._**

## **Стек:**
* PHP 8.2
* Фреймворк Laravel 12
* В качестве БД используется MySQL
* Библиотека по работе с Telegram API - Telegram Bot SDK
* Используется форматер кода laravel pint



## Архитектурные решения:
* Использует Eloquent ORM (2 таблицы связь 1 к 1)
* Основная бизнес логика вынесена в TelegramBotService


## Структура проекта:
* `app/Constant` - Константы (стадии пользователя, наименования подкатегорий)
* `app/Http/Controllers/TelegramBotController.php` - Основной контроллер
* `app/Models/` - Модели для 2 таблиц
* `app/Providers/AppServiceProvider.php` - Глобальные настойки приложения (путь к public_gropu_id)
* `app/Repositories/Contracts/UserRepositoryInterface.php` - Интерфейс по работе с бд
* `app/Repositories/EloquentUserRepository.php` - Класс описывающий методы по работе с таблицами
* `app/Services/AdvValidationService.php` - Сервис кастомной валидации
* `app/Services/SenderService.php` - Сервис отправки сообщений при попощи библиотеки Telegram Bot SDK
* `app/Services/TelegramBotService.php` - Сервис с основной бизнес логикой (Надо бы разбить на другие сервисы, но сомневаюсь)
* `app/Services/TextMessagesService.php` - Класс хранящий текста и клавиатуры 
