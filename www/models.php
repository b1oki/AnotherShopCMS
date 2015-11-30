<?php
require_once 'settings.php';
require_once 'database/database.php';

function debug($var)
{
    echo '<pre style="position:absolute; bottom: 0; right: 0; border: 1px solid black;">';
    var_dump($var);
    echo '</pre>';
}

function getCategories($parent_id = 0)
{
    $db = Database::connect();
    $query = 'SELECT * FROM `categories` ' . $db->prepare('WHERE `parent` = ?i', $parent_id);
    $categories = Database::get_data($query, $db);
    return $categories;
}

function getCategory($category_id)
{
    $db = Database::connect();
    $query = 'SELECT * FROM `categories` ' . $db->prepare('WHERE `id` = ?i LIMIT 1', $category_id);
    $category = Database::get_data($query, $db, true);
    return $category;
}

function getProducts($category_id = 0)
{
    $db = Database::connect();
    $query = 'SELECT * FROM `products` ' . $db->prepare('WHERE `category` = ?i', $category_id);
    $products = Database::get_data($query, $db);
    return $products;
}

function getProduct($item_id)
{
    $db = Database::connect();
    $query = 'SELECT * FROM `products` ' . $db->prepare('WHERE `id` = ?i LIMIT 1', $item_id);
    $product = Database::get_data($query, $db, true);
    return $product;
}

function getLastNews()
{
    return Database::get_data('SELECT * FROM `news` ORDER BY `created` DESC LIMIT 5;');
}

function getNewsArticle($article_id)
{
    $db = Database::connect();
    $query = 'SELECT * FROM `news` ' . $db->prepare('WHERE `id` = ?i LIMIT 1;', $article_id);
    $articles = Database::get_data($query, $db);
    return $articles[0];
}

function getAllNews()
{
    return Database::get_data('SELECT * FROM `news` ORDER BY `created` DESC;');
}