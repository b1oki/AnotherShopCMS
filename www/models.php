<?php
require_once 'settings.php';
require_once 'database/database.php';

function getRootCategories()
{
    return Database::get_data('SELECT * FROM `categories` WHERE `parent` = 0');
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

function make_article_preview($text)
{
    $preview = strip_tags($text);
    $preview = substr($preview, 0, 64);
    $preview = rtrim($preview, ':!,.-…');
    return substr($preview, 0, strrpos($preview, ' '));
}