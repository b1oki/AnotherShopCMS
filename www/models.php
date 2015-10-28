<?php

class Menu
{
    public $links;

    public function __construct()
    {
        $this->links = array(
            array('link' => '/company/', 'class' => 'Index', 'method' => 'company', 'text' => 'Компания', 'current' => false),
            array('link' => '/news/', 'class' => 'Index', 'method' => 'company', 'text' => 'Новости', 'current' => false),
            array('link' => '/product/', 'class' => 'Index', 'method' => 'company', 'text' => 'Продукция', 'current' => false),
            array('link' => '/contacts/', 'class' => 'Index', 'method' => 'company', 'text' => 'Контакты', 'current' => false)
        );
    }
}

class Page
{
    protected $title;
    public $menu;

    public function __construct()
    {
        $this->menu = new Menu();
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
}