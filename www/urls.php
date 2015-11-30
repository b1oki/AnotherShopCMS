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
    // Каталог и подразделы (/product/1/)
    'CatalogMain' => '~^/product/(?P<category_path>([0-9]+/)?)$~',
    // Товар (/product/1/2/)
    'CatalogItem' => '~^/product/(?P<category_path>([0-9]+/)?)(?P<item_id>[0-9]+)/$~',
);