<?php
require_once 'settings.php';
require_once 'database/database.php';

function debug($var, $tag='')
{
    echo '<pre style="position:absolute; bottom: 5px; right: 5px; border: 1px solid black;">';
    echo "Debug $tag\n";
    var_dump($var);
    echo '</pre>';
}

function makeFingerprint() {
    $fingerprint = 'ARE_YOU_ADMIN::' . $_SERVER['HTTP_USER_AGENT'] . '::' . Settings::admin_login . '::' . $_SERVER['REMOTE_ADDR'] . '::' . session_id();
    return md5($fingerprint);
}

function isAdmin() {
    # Если есть подпись и она верная, то это админ
    if (isset($_SESSION['admin']['fingerprint'])) {
        return makeFingerprint() == $_SESSION['admin']['fingerprint'];
    } else {
        return false;
    }
}

function doLogin() {
    $_SESSION['admin'] = array('fingerprint' => makeFingerprint());
}

function doLogout() {
    $_SESSION['admin'] = array();
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