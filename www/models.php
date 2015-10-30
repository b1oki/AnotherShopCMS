<?php

class Page
{
    protected $title;
    protected $template;
    protected $root_menu;
    protected $data;

    public function __construct()
    {
        $this->data = array();
        $this->root_menu = array(
            'company' => array('text' => 'Компания', 'current' => false),
            'news' => array('text' => 'Новости', 'current' => false),
            'product' => array('text' => 'Продукция', 'current' => false),
            'contacts' => array('text' => 'Контакты', 'current' => false)
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

    public function render()
    {
        require $this->template;
    }
}

class Index extends Page
{
    public function main()
    {
        $this->setTitle('Главная');
        $this->setTemplate('templates/index-main.phtml');
        $this->data['products'] = array(
            array(
                'category' => 'box',
                'image' => 'http://placehold.it/150x100',
                'title' => 'Коробки'
            ),
            array(
                'category' => 'case',
                'image' => 'http://placehold.it/150x100',
                'title' => 'Ящики'
            ),
            array(
                'category' => 'packet',
                'image' => 'http://placehold.it/150x100',
                'title' => 'Пакеты'
            ),
            array(
                'category' => 'barrel',
                'image' => 'http://placehold.it/150x100',
                'title' => 'Бочки'
            ),
            array(
                'category' => 'container',
                'image' => 'http://placehold.it/150x100',
                'title' => 'Контейнеры'
            )
        );
        $this->render();
    }

    public function contacts()
    {
        $this->root_menu['contacts']['current'] = true;
        echo 'CONTACTS';
    }

    public function company()
    {
        $this->root_menu['company']['current'] = true;
        echo 'COMPANY';
    }
}

class News extends Page
{
    public function __construct()
    {
        parent::__construct();
        $this->root_menu['news']['current'] = true;
    }

    public function newest()
    {
        echo 'NEWEST';
    }

    public function article()
    {
        echo 'NEWS ARTICLE: ' . $_REQUEST['article_id'];
    }
}

class Catalog extends Page
{
    public function __construct()
    {
        parent::__construct();
        $this->root_menu['product']['current'] = true;
    }

    public function main()
    {
        echo 'CATALOG (PATH: ' . $_REQUEST['category_path'] . ')';
    }

    public function item()
    {
        echo 'CATALOG ITEM (PATH: ' . $_REQUEST['category_path'] . '), ID: ' . $_REQUEST['item_id'];
    }
}

class Error extends Page
{
    public function not_found()
    {
        echo 'ERROR 404: PAGE NOT FOUND';
    }
}