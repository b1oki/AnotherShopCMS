<?php
require_once 'settings.php';
require_once 'models.php';

class Page
{
    protected $title;
    protected $template;
    protected $root_menu;
    protected $data;

    public function __construct()
    {
        $this->data = array();
        $this->data['last-news'] = getLastNews();
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

    public function render($template = null, $title = null)
    {
        if (!empty($template)) {
            $this->setTemplate($template);
        }
        if (!empty($title)) {
            $this->setTitle($title);
        }
        require $this->template;
    }
}

class Index extends Page
{
    public function main()
    {
        $this->data['categories'] = getCategories();
        $this->data['products'] = getProducts();
        $this->data['category-no-image'] = '/static/images/empty_150_100.jpg';
        $this->render('templates/index-main.phtml', 'Главная');
    }

    public function contacts()
    {
        $this->root_menu['contacts']['current'] = true;
        $this->render('templates/index-contacts.phtml', 'Контакты');
    }

    public function company()
    {
        $this->root_menu['company']['current'] = true;
        $this->render('templates/index-company.phtml', 'О компании');
    }
}

class News extends Page
{
    public function __construct()
    {
        parent::__construct();
        $this->root_menu['news']['current'] = true;
    }

    public static function make_article_preview($text)
    {
        $preview = strip_tags($text);
        $preview = substr($preview, 0, 64);
        $preview = rtrim($preview, ':!,.-…');
        return substr($preview, 0, strrpos($preview, ' '));
    }

    public function main()
    {
        $news = getAllNews();

        function fillPreview(&$article)
        {
            if (empty($article['preview'])) {
                $article['preview'] = News::make_article_preview($article['text']);
            }
        }

        array_walk($news, "fillPreview");
        $this->data['all-news'] = $news;
        $this->render('templates/news-main.phtml', 'Новости');
    }

    public function article()
    {
        $article = getNewsArticle($_REQUEST['article_id']);
        $this->data['article'] = $article;
        $this->render('templates/news-article.phtml', $article['title']);
    }
}

class Catalog extends Page
{
    public static function getIdByPath($category_path)
    {
        $categories = preg_split('[/]', $category_path, null, PREG_SPLIT_NO_EMPTY);
        $category = array_pop($categories);
        return intval($category);
    }

    public function __construct()
    {
        parent::__construct();
        $this->root_menu['product']['current'] = true;
    }

    public function main()
    {
        $category_title = 'Каталог';
        $category_id = Catalog::getIdByPath($_REQUEST['category_path']); // парсим id каталога
        if ($category_id != 0) {
            $this->data['category'] = getCategory($category_id);
        } else {
            $this->data['category'] = false;
        }
        $this->data['category-no-image'] = 'http://placehold.it/150x100'; // картинка-заглушка
        $this->data['categories'] = getCategories($category_id); // берем все подразделы с текущим родителем
        // TODO: 1 для корня отображаем все корневые каталоги и, если есть, товары
        // TODO: 2 а можно слева корневые каталоги, а на страничке топ товаров
        $this->data['products'] = getProducts($category_id); // берем все товары с текущим родителем
        $this->render('templates/catalog-main.phtml', $category_title);
    }

    public function item()
    {
        $item_title = 'Продукт';
        $product_id = Catalog::getIdByPath($_REQUEST['item_id']); // парсим id продукта
        $this->data['product'] = getProduct($product_id);
        if ($this->data['product']['category'] != 0) {
            $this->data['category'] = getCategory($this->data['product']['category']);
        } else {
            $this->data['category'] = false;
        }
        $this->data['category-no-image'] = 'http://placehold.it/150x100'; // картинка-заглушка
        // TODO: Получаем товар. Рисуем картинку, описание, таблицу спецификации и т.д.
        $this->render('templates/catalog-item.phtml', $item_title);
    }
}

class Admin extends Page
{
    const ADMIN_AUTH_EMPTY = 0;
    const ADMIN_AUTH_SUCCESS = 1;
    const ADMIN_AUTH_ALREADY = 2;
    const ADMIN_AUTH_WRONG = 3;
    const ADMIN_AUTH_LOGOUT = 4;

    public function auth()
    {
        $title = 'Администраторская панель';
        if (isset($_GET['logout']) and $_GET['logout'] == 'Y') {
            if (isAdmin()) {
                doLogout();
            }
            $is_auth_complete = $this::ADMIN_AUTH_LOGOUT;
            $auth_message = 'Вы не авторизованы';
            $showAuthForm = true;
        }
        else {
            if (isAdmin()) {
                $auth_message = 'Уже авторизован';
                $is_auth_complete = $this::ADMIN_AUTH_ALREADY;
            } else {
                if (empty($_POST['login']) or empty($_POST['password'])) {
                    $auth_message = 'Необходима авторизация';
                    $is_auth_complete = $this::ADMIN_AUTH_EMPTY;
                } else {
                    $login = $_POST['login'];
                    $password = $_POST['password'];
                    if ($login == Settings::admin_login and $password == Settings::admin_password) {
                        $auth_message = 'Авторизация успешна';
                        $is_auth_complete = $this::ADMIN_AUTH_SUCCESS;
                    } else {
                        $auth_message = 'Неверные данные';
                        $is_auth_complete = $this::ADMIN_AUTH_WRONG;
                    }
                }
                if ($is_auth_complete == $this::ADMIN_AUTH_SUCCESS) {
                    doLogin();
                }
            }
            if ($is_auth_complete == $this::ADMIN_AUTH_ALREADY or $is_auth_complete == $this::ADMIN_AUTH_SUCCESS) {
                $showAuthForm = false;
            } else {
                $showAuthForm = true;
            }
        }
        $this->data['admin-auth-result'] = array(
            'status' => $is_auth_complete,
            'message' => $auth_message,
            'showAuthForm' => $showAuthForm
        );
        $this->render('templates/admin-auth.phtml', $title);
    }
}

class Error extends Page
{
    public function not_found()
    {
        header('HTTP/1.0 404 Not Found');
        header('Status: 404 Not Found');
        $this->render('templates/error-404.phtml', 'Страница не найдена (404-я ошибка)');
    }
}