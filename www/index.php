<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'settings.php';
require_once 'urls.php';
require_once 'models.php';
require_once 'views.php';

// Назначаем модуль и действие по умолчанию.
$module = 'Error';
$action = 'not_found';

foreach ($routes as $map_key => $map_pattern) {
    /* Для того, что бы через виртуальные адреса можно было также передавать параметры
     * через QUERY_STRING (т.е. через "знак вопроса" - ?param=value),
     * необходимо получить компонент пути - path без QUERY_STRING, т.к. в ином
     * случае виртуальный адрес попросту не совпадет ни с одним паттерном из массива $routes.
     * Данные, переданные через QUERY_STRING, также как и раньше будут содержаться в
     * суперглобальных массивах $_GET и $_REQUEST. */
    $url_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if (preg_match($map_pattern, $url_path, $matches)) {
        // Выталкиваем первый элемент - он содержит всю строку URI запроса и в массиве $params он не нужен.
        array_shift($matches);
        // Массив параметров из URI запроса.
        $params = array();
        // Формируем массив $params с теми названиями ключей переменных, которые мы указали в $routes
        foreach ($matches as $index => $value) {
            if (gettype($index) == 'string') {
                $params[$index] = $value;
            }
        }
        $_REQUEST = array_merge($_REQUEST, $params);
        $args = preg_split('/(?<=\w)(?=[A-Z])/', $map_key);
        $module = $args[0];
        $action = strtolower($args[1]);
        break;
    }
}

$current_page = new $module();
$current_page->$action();