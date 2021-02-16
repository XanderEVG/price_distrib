## Шпора
- Запуск локального сервера: `symfony server:start`
- Создание сущности: `php bin/console make:entity`
- Создание контроллера: `php bin/console make:controller`
- Создание консольной команды: `php bin/console make:command`
- Создание миграции: `php bin/console make:migration`
- Миграция: `php bin/console doctrine:migrations:migrate`
- Очистка кэша: `php bin/console cache:clear`
- Очистка базы и загрузка тестовых данных: `php bin/console doctrine:fixtures:load`

## Запуск
 - БД
   - Запускаем контейнеры `docker-compose up`
   - Прописываем в env-файле в параметры подключения к БД
 - Устанавливаем пакеты back-end `composer install`
 - Подготовка БД
    - Создаем миграции `php bin/console make:migration`
    - Запускаем миграции `bin/console doctrine:migrations:migrate`
    - Генерируем тестовые данные `bin/console doctrine:fixtures:load`
 - Собираем JS 
    - `yarn install`
    - `yarn dev --watch`
 - Запускаем веб-сервер back-end `symfony server:start`
  
