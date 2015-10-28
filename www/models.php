<?php

class Page
{
    protected $title;
    public $menu;

    public function __construct()
    {
        $this->menu = array(
            array('link' => '/company/', 'class' => 'Index', 'method' => 'about', 'text' => 'Компания', 'current' => false),
            array('link' => '/news/', 'class' => 'News', 'method' => 'newest', 'text' => 'Новости', 'current' => false),
            array('link' => '/product/', 'class' => 'Catalog', 'method' => 'main', 'text' => 'Продукция', 'current' => false),
            array('link' => '/contacts/', 'class' => 'Index', 'method' => 'contacts', 'text' => 'Контакты', 'current' => false)
        );
    }

    /**
     * Получение и установка свойств объекта через вызов магического метода вида:
     * $object->(get|set)PropertyName($prop);
     *
     * @see __call
     * @param $method_name : имя метода СamelCase
     * @param $argument : переданные параметры
     * @return mixed
     */
    public function __call($method_name, $argument)
    {
        $args = preg_split('/(?<=\w)(?=[A-Z])/', $method_name);
        $action = array_shift($args);
        $property_name = strtolower(implode('_', $args));

        switch ($action) {
            case 'get':
                return isset($this->$property_name) ? $this->$property_name : null;

            case 'set':
                $this->$property_name = $argument[0];
                return $this;

            default:
                return null;
        }
    }

    public function getPageTitle()
    {
        return $this->title . ' - ' . Settings::title;
    }
}

class Index
{
    public $page;

    public function __construct()
    {
        $this->page = new Page();
    }

    public function main()
    {
        $this->page->setTitle('Главная');
        require_once 'templates/index-main.phtml';
    }
    public function contacts()
    {
        echo 'CONTACTS!';
    }
    public function about()
    {
        echo 'ABOUT!';
    }
}

class News
{
    public function newest()
    {
        echo 'NEWEST!';
    }
    public function article()
    {
        echo 'NEWS ARTICLE: ' . $_REQUEST['article_id'];
    }
}

class Catalog
{
    public function main()
    {
        echo 'CATALOG (PATH: ' . $_REQUEST['category_path'] . ')';
    }
    public function item()
    {
        echo 'CATALOG ITEM (PATH: ' . $_REQUEST['category_path'] . '), ID: ' . $_REQUEST['item_id'];
    }
}