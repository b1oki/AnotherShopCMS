<?php
// Конфигурация маршрутов URL проекта.
$routes = array(
    // Главная страница сайта (/)
    array(
        // паттерн в формате Perl-совместимого реулярного выражения
        'pattern' => '~^(/main)?/$~',
        // Имя класса обработчика
        'class' => 'Index',
        // Имя метода класса обработчика
        'method' => 'main'
    ),
    // Страница информации о компании (/company)
    array(
        'pattern' => '~^/company/$~',
        'class' => 'Index',
        'method' => 'company',
    ),
    // Страница контактов компании (/contacts)
    array(
        'pattern' => '~^/contacts/$~',
        'class' => 'Index',
        'method' => 'contacts',
    ),
    // Страница с последними новостями (/news)
    array(
        'pattern' => '~^/news/$~',
        'class' => 'News',
        'method' => 'main',
    ),
    // Страница с одной новостью (/news/12345)
    array(
        'pattern' => '~^/news/(?P<article_id>[0-9]+)/$~',
        'class' => 'News',
        'method' => 'article',
    ),
    // Каталог и подразделы (/product/box/red/)
    array(
        'pattern' => '~^/product/(?P<category_path>([a-zA-Z_/\-]+/)*)$~',
        'class' => 'Catalog',
        'method' => 'main',
    ),
    // Товар (/product/box/red/item/3)
    array(

        'pattern' => '~^/product/(?P<category_path>([a-zA-Z_/\-]+/)*)(?P<item_id>[0-9]+)/$~',
        'class' => 'Catalog',
        'method' => 'item',
    ),
);