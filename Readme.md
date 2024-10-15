# Инструкция по разворачиванию проекта
запустите команду make up

# Описание тестового задания
Тестовое задание нужно выполнить максимально подходящим под требования заказчика:
Задание:
Заказчик владеет ИТ бизнесом и просит разработать систему управления проектов. Со слов заказчика получены требования:
IT-компания ведет разработку нескольких проектов. В каждом проекте своя команда разработчиков, но некоторые разработчики могут участвовать в нескольких проектах.

1. Разработать сущности «Разработчик» и «Проект». Минимально необходимыми полями для «Разработчика» являются:
◦ ФИО разработчика *;
◦ должность *; (возможные должности программист, администратор, devops, дизайнер)
◦ email ;
◦ контактный телефон;
◦ проект, над которым он работает.

Для «Проекта»:
◦ название проекта;
◦ команда разработчиков;
◦ заказчик.
1. Добавить валидацию заполненности полей со стороны сервера, проверять, что проект, добавляемый разработчику существует.
3. Создать миграцию сущностей для БД
4. Реализовать возможность нанимать/увольнять/переводить (на проект, новую должность) разработчиков, создавать/закрывать проекты.
5. Сформировать запросы в SQL по сбору статистики (количество проектов, сотрудников, средний возраст сотрудников и т.д.)


Критерий проверки:
Для разработки использовать фреймворк Symfony 7.1.3 с Doctrine ORM. Код проекта должен отслеживаться через git. В git log должно быть минимум 3 коммита. (коммиты можно делать по пунткам с сообщениями “пункт № 1 выполнен”).Для сервера БД допускается PostgreSQL >= 16 версии или mysql8 (или её аналог mariadb последних версий).
Будет плюсом использование docker и docker-compose.