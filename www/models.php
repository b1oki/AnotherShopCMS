<?php
require_once 'settings.php';

class Product
{

}

class Category
{
    protected $image;
    protected $title;
}

function getCategories()
{
    return array(
        array(
            'category' => 'box',
            'image' => 'http://placehold.it/1',
            'title' => 'Коробки'
        ),
        array(
            'category' => 'case',
            'title' => 'Ящики'
        ),
        array(
            'category' => 'packet',
            'title' => 'Пакеты'
        ),
        array(
            'category' => 'barrel',
            'title' => 'Бочки'
        ),
        array(
            'category' => 'container',
            'title' => 'Контейнеры'
        )
    );
}