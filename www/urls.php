<?php
// Конфигурация маршрутов URL проекта.
// ключ массива - CamelCase класс представления и его метод
// паттерн в формате Perl-совместимого реулярного выражения
$routes = array(
    // Главная страница сайта (/)
    'IndexMain' => '~^(/main)?/$~',
    // Страница информации о компании (/company)
    'IndexCompany' => '~^/company/$~',
    // Страница контактов компании (/contacts)
    'IndexContacts' => '~^/contacts/$~',
    // Страница с последними новостями (/news)
    'NewsMain' => '~^/news/$~',
    // Страница с одной новостью (/news/12345)
    'NewsArticle' => '~^/news/(?P<article_id>[0-9]+)/$~',
    // Каталог и подразделы (/product/box/red/)
    'CatalogMain' => '~^/product/(?P<category_path>([a-zA-Z_/\-]+/)*)$~',
    // Товар (/product/box/red/item/3)
    'CatalogItem' => '~^/product/(?P<category_path>([a-zA-Z_/\-]+/)*)(?P<item_id>[0-9]+)/$~',
);