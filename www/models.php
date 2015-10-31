<?php
require_once 'settings.php';
require_once 'database/database.php';

function getRootCategories()
{
    $categories = Database::get_data('SELECT * FROM `categories` WHERE `parent` = 0');
    return $categories;
}

function getLastNews()
{
    $lastNews = Database::get_data('SELECT * FROM `news`');
    return $lastNews;
}