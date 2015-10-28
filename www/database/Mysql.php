<?php
/**
 * @author Vasiliy Makogon, makogon.vs@gmail.com
 * @link http://www.phpinfo.su/
 *
 *
 * ---------------------------------------------------------------------------------
 *     Класс для удобной и безопасной работы с СУБД MySql на базе расширения PHP mysqli.
 * ---------------------------------------------------------------------------------
 *
 * Данный класс использует технологию placeholders - для формирования корректных SQL-запросов, в строке запроса вместо
 * значений пишутся специальные типизированные маркеры - т.н. "заполнители", а сами данные передаются "позже", в качестве
 * последующих аргументов основного метода, выполняющего SQL-запрос - Krugozor_Database_Mysql::query($sql [, $arg, $...]):
 *
 *     $db->query('SELECT * FROM `table` WHERE `name` = "?s" AND `age` = ?i', $_POST['name'], $_POST['age']);
 *
 * Аргументы SQL-запроса, прошедшие через систему placeholders данного класса, экранируются специальными функциями
 * экранирования, в зависимости от типа заполнителей. Т.е. вам теперь нет необходимости заключать переменные в функции
 * экранирования типа mysqli_real_escape_string($value) или приводить их к числовому типу через (int)$value.
 *
 *
 * ----------------------------------------------------------------------------------
 *    Режимы работы.
 * ----------------------------------------------------------------------------------
 *
 * Существует два режима работы класса:
 * Krugozor_Database_Mysql::MODE_STRICT    - строгий режим соответствия типа заполнителя и типа аргумента.
 * Krugozor_Database_Mysql::MODE_TRANSFORM - режим преобразования аргумента к типу заполнителя при несовпадении
 *                                           типа заполнителя и типа аргумента.
 *
 * Режим Krugozor_Database_Mysql::MODE_TRANSFORM установлен по умолчанию и является основным для большинства приложений.
 * Не изменяйте этот режим, если вам не нужна строгая типизация аргументов в запросах.
 *
 *
 *     MODE_STRICT
 *
 * В "строгом" режиме MODE_STRICT аргументы, передаваемые в основной метод
 * Krugozor_Database_Mysql::query(), должны в ТОЧНОСТИ соответствовать типу заполнителя.
 * Разберем примеры:
 *
 * $db->query('SELECT * FROM `table` WHERE `field` = ?i', '123_string'); - в данном случае будет выброшено исключение
 *     "Попытка записать как int значение "123_string" типа string в запросе ...", т.к.
 * указан тип заполнителя ?i (int - целое число), а в качестве аргумента передается строка '123_string'.
 *
 * $db->query('SELECT * FROM `table` WHERE `field` = "?s"', 123); - будет выброшено исключение
 *     "Попытка записать как string значение 123 типа integer в запросе ...", т.к.
 * указан тип заполнителя ?s (string - строка), а в качестве аргумента передается число 123.
 *
 * $db->query('SELECT * FROM `table` WHERE `field` IN (?as)', array(null, 123, true, 'string')); - будет выброшено исключение
 *     "Попытка записать как string значение "" типа NULL в запросе ...", т.к. заполнитель множества ?as ожидает,
 * что все элементы массива-аргумета будут типа s (string - строка), но на деле все элементы массива представляют собой
 * данные различных типов. Парсер прекратил разбор на первом несоответствии типа заполнителя и типа аргумента - на
 * элементе массива со значением null.
 *
 *
 *     MODE_TRANSFORM
 *
 * Режим MODE_TRANSFORM является "щадящим" режимом и при несоответствии типа заполнителя и типа аргумента не генерирует
 * исключение, а пытается преобразовать аргумент к нужному типу заполнителя посредством самого языка PHP.
 *
 * Допускаются следующие преобразования:
 *
 * К строковому типу приводятся данные типа boolean, numeric, NULL:
 *     - значение boolean TRUE преобразуется в строку "1", а значение FALSE преобразуется в "" (пустую строку).
 *     - значение типа numeric преобразуется в строку согласно правилам преобразования, определенным языком.
 *     - NULL преобразуется в пустую строку.
 * Для массивов, объектов и ресурсов преобразования не допускаются.
 *
 * Пример выражения:
 *     $db->query('SELECT * FROM `table` WHERE f1 = "?s", f2 = "?s", f3 = "?s"', null, 123, true);
 * Результат преобразования:
 *     SELECT * FROM `table` WHERE f1 = "", f2 = "123", f3 = "1"
 *
 * К целочисленному типу приводятся данные типа boolean, string, NULL:
 *     - значение boolean FALSE преобразуется в 0 (ноль), а TRUE - в 1 (единицу).
 *     - значение типа string преобразуется согласно правилам преобразования, определенным языком.
 *     - NULL преобразуется в 0.
 * Для массивов, объектов и ресурсов преобразования не допускаются.
 *
 * Пример выражения:
 *     $db->query('SELECT * FROM `table` WHERE f1 = ?i, f2 = ?i, f3 = ?i, f4 = ?i', null, '123abc', 'abc', true);
 * Результат преобразования:
 *     SELECT * FROM `table` WHERE f1 = 0, f2 = 123, f3 = 0, f4 = 1
 *
 * Заполнитель NULL-типа игнорирует любые сопоставленные с ним аргументы.
 *
 * Пример выражения:
 *     $db->query('INSERT INTO `table` VALUES (?n, ?n, ?n, ?n)', 123, '123', 'string', 1.74);
 * Результат преобразования:
 *     INSERT INTO `table` VALUES (NULL, NULL, NULL, NULL)
 *
 *
 * ----------------------------------------------------------------------------------
 *    Типы маркеров-заполнителей
 * ----------------------------------------------------------------------------------
 *
 * ?f - заполнитель имени таблицы или поля (первая буква слова field).
 *      Данный заполнитель предназначен для случаев, когда имя таблицы или поля передается в запроос через аргумент.
 *
 * ?i - заполнитель целого числа (первая буква слова integer).
 *      В режиме MODE_TRANSFORM любые скалярные аргументы принудительно приводятся к типу integer
 *      согласно правилам преобразования к типу integer в PHP.
 *
 * ?p - заполнитель числа с плавающей точкой (первая буква слова point).
 *      В режиме MODE_TRANSFORM любые скалярные аргументы принудительно приводятся к типу float
 *      согласно правилам преобразования к типу float в PHP.
 *
 * ?s - заполнитель строкового типа (первая буква слова string).
 *      В режиме MODE_TRANSFORM любые скалярные аргументы принудительно приводятся к типу string
 *      согласно правилам преобразования к типу string в PHP
 *      и экранируются с помощью функции PHP mysqli_real_escape_string().
 *
 * ?S - заполнитель строкового типа для подстановки в SQL-оператор LIKE (первая буква слова string).
 *      В режиме MODE_TRANSFORM Любые скалярные аргументы принудительно приводятся к типу string
 *      согласно правилам преобразования к типу string в PHP
 *      и экранируются с помощью функции PHP mysqli_real_escape_string() + экранирование спецсимволов,
 *      используемых в операторе LIKE (%_).
 *
 * ?n - заполнитель NULL типа (первая буква слова null).
 *      В режиме MODE_TRANSFORM любые аргументы игнорируются, заполнители заменяются на строку `NULL` в SQL запросе.
 *
 * ?A* - заполнитель ассоциативного множества для ассоциативного массива-аргумента, генерирующий последовательность
 *       пар ключ => значение.
 *       Пример: "key_1" = "val_1", "key_2" = "val_2", ...
 *
 * ?a* - заполнитель множества из простого (или также ассоциативного) массива-аргумента, генерирующий последовательность
 *       значений.
 *       Пример: "val_1", "val_2", ...
 *
 *       где * после маркера заполнителя - один из типов:
 *       - i (int)
 *       - p (float)
 *       - s (string)
 *       правила преобразования и экранирования такие же, как и для одиночных скалярных аргументов (см. выше).
 *
 * ?A[?n, ?s, ?i, ?p] - заполнитель ассоциативного множества с явным указанием типа и количества аргументов,
 *                      генерирующий последовательность пар ключ => значение.
 *                      Пример: "key_1" = "val_1", "key_2" => "val_2", ...
 *
 * ?a[?n, ?s, ?i, ?p] - заполнитель множества с явным указанием типа и количества аргументов, генерирующий
 *                      последовательность значений.
 *                      Пример: "val_1", "val_2", ...
 *
 *
 * ----------------------------------------------------------------------------------
 *    Ограничивающие кавчки
 * ---------------------------------------------------------------------------------
 *
 * Данный класс при формировании SQL-запроса НЕ занимается проставлением ограничивающих кавычек для одиночных
 * заполнителей скалярного типа, таких как ?i, ?p и ?s. Это сделано по идеологическим соображениям,
 * автоподстановка кавычек может стать ограничением для возможностей SQL.
 * Например, выражение
 *     $db->query('SELECT "Total: ?s"', '200');
 * вернёт строку
 *     'Total: 200'
 * Если бы кавычки, ограничивающие строковой литерал, ставились бы автоматически,
 * то вышеприведённое условие вернуло бы строку
 *     'Total: "200"'
 * что было бы не ожидаемым поведением.
 *
 * Тем не менее, для перечислений ?as, ?ai, ?ap, ?As, ?Ai и ?Ap ограничивающие кавычки ставятся принудительно, т.к.
 * перечисления всегда используются в запросах, где наличие кавчек обязательно или не играет роли:
 *
 *    $db->query('INSERT INTO `test` SET ?As', array('name' => 'Маша', 'age' => '23', 'adress' => 'Москва'));
 *    -> INSERT INTO test SET `name` = "Маша", `age` = "23", `adress` = "Москва"
 *
 *    $db->query('SELECT * FROM table WHERE field IN (?as)', array('55', '12', '132'));
 *    -> SELECT * FROM table WHERE field IN ("55", "12", "132")
 *
 * Также исключения составляют заполнители типа ?f, предназначенные для передачи в запрос имен таблиц и полей.
 * Аргумент заполнителя ?f всегда обрамляется обратными кавычками (`):
 *
 *    $db->query('SELECT ?f FROM ?f', 'my_field', 'my_table');
 *    -> SELECT `my_field` FROM `my_table`
 */
class Krugozor_Database_Mysql
{
    /**
     * Строгий режим типизации.
     * Если тип заполнителя не совпадает с типом аргумента, то будет выброшено исключение.
     * Пример такой ситуации:
     *
     * $db->query('SELECT * FROM `table` WHERE `id` = ?i', '2+мусор');
     *
     * - в данной ситуации тип заполнителя ?i - число или числовая строка,
     *   а в качестве аргумента передаётся строка '2+мусор' не являющаяся ни числом, ни числовой строкой.
     *
     * @var int
     */
    const MODE_STRICT = 1;

    /**
     * Режим преобразования.
     * Если тип заполнителя не совпадает с типом аргумента, аргумент принудительно будет приведён
     * к нужному типу - к типу заполнителя.
     * Пример такой ситуации:
     *
     * $db->query('SELECT * FROM `table` WHERE `id` = ?i', '2+мусор');
     *
     * - в данной ситуации тип заполнителя ?i - число или числовая строка,
     *   а в качестве аргумента передаётся строка '2+мусор' не являющаяся ни числом, ни числовой строкой.
     *   Строка '2+мусор' будет принудительно приведена к типу int согласно правилам преобразования типов в PHP.
     *
     * @var int
     */
    const MODE_TRANSFORM = 2;

    /**
     * Режим работы. См. описание констант self::MODE_STRICT и self::MODE_TRANSFORM.
     *
     * @var int
     */
    protected $type_mode = self::MODE_TRANSFORM;

    protected $server;

    protected $user;

    protected $password;

    protected $port;

    protected $socket;

    /**
     * Имя текущей БД.
     *
     * @var string
     */
    protected $database_name;

    /**
     * Стандартный объект соединения сервером MySQL.
     *
     * @var mysqli
     */
    protected $mysqli;

    /**
     * Строка последнего SQL-запроса до преобразования.
     *
     * @var string
     */
    private $original_query;

    /**
     * Строка последнего SQL-запроса после преобразования.
     *
     * @var string
     */
    private $query;

    /**
     * Массив со всеми запросами, которые были выполнены объектом.
     * Ключи - SQL после преобразования, значения - SQL до преобразования.
     *
     * @var array
     */
    private $queries = array();

    /**
     * Создает инстанс данного класса.
     *
     * @param string $server имя сервера
     * @param string $username имя пользователя
     * @param string $password пароль
     * @param string $port порт
     * @param string $socket сокет
     */
    public static function create($server, $username, $password, $port=null, $socket=null)
    {
        return new self($server, $username, $password, $port, $socket);
    }

    /**
     * Задает набор символов по умолчанию.
     *
     * @param string $charset
     * @return Krugozor_Database_Mysql
     */
    public function setCharset($charset)
    {
        if (!$this->mysqli->set_charset($charset))
        {
            throw new Krugozor_Database_Mysql_Exception(__METHOD__ . ': ' . $this->mysqli->error);
        }

        return $this;
    }

    /**
     * Возвращает кодировку по умолчанию, установленную для соединения с БД.
     *
     * @param void
     * @return string
     */
    public function getCharset()
    {
        return $this->mysqli->character_set_name();
    }

    /**
     * Устанавливает имя используемой СУБД.
     *
     * @param string имя базы данных
     * @return Krugozor_Database_Mysql
     */
    public function setDatabaseName($database_name)
    {
        if (!$database_name)
        {
            throw new Krugozor_Database_Mysql_Exception(__METHOD__ . ': Не указано имя базы данных');
        }

        $this->database_name = $database_name;

        if (!$this->mysqli->select_db($this->database_name))
        {
            throw new Krugozor_Database_Mysql_Exception(__METHOD__ . ': ' . $this->mysqli->error);
        }

        return $this;
    }

    /**
     * Возвращает имя текущей БД.
     *
     * @param void
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->database_name;
    }

    /**
     * Устанавливает режим поведения при несовпадении типа заполнителя и типа аргумента.
     *
     * @param $value int
     * @return Krugozor_Database_Mysql
     */
    public function setTypeMode($value)
    {
        if (!in_array($value, array(self::MODE_STRICT, self::MODE_TRANSFORM)))
        {
            throw new Krugozor_Database_Mysql_Exception(__METHOD__ . ': Указан неизвестный тип режима');
        }

        $this->type_mode = $value;

        return $this;
    }

    /**
     * Выполняет SQL-запрос.
     * Принимает обязательный параметр - SQL-запрос и, в случае наличия,
     * любое количество аргументов - значения заполнителей.
     *
     * @param string строка SQL-запроса
     * @param mixed аргументы для заполнителей
     * @return bool|Krugozor_Database_Mysql_Statement false в случае ошибки, в обратном случае объект результата
     */
    public function query()
    {
        if (!func_num_args())
        {
            return false;
        }

        $args = func_get_args();

        $query = $this->original_query = array_shift($args);

        $this->query = $this->parse($query, $args);

        $result = $this->mysqli->query($this->query);

        $this->queries[$this->query] = $this->original_query;

        if ($result === false)
        {
            throw new Krugozor_Database_Mysql_Exception(__METHOD__ . ': ' . $this->mysqli->error . '; SQL: ' . $this->query);
        }

        if (is_object($result) && $result instanceof mysqli_result)
        {
            return new Krugozor_Database_Mysql_Statement($result);
        }

        return $result;
    }

    /**
     * Поведение аналогично методу self::query(), только метод принимает только два параметра -
     * SQL запрос $query и массив аргументов $arguments, которые и будут заменены на заменители в той
     * последовательности, в которой они представленны в массиве $arguments.
     *
     * @param string
     * @param array
     * @return bool|Krugozor_Database_Mysql_Statement
     */
    public function queryArguments($query, array $arguments=array())
    {
        array_unshift($arguments, $query);

        return call_user_func_array(array($this, 'query'), $arguments);
    }

    /**
     * Обёртка над методом $this->parse().
     * Применяется для случаев, когда SQL-запрос формируется частями.
     *
     * Пример:
     *     $db->prepare('WHERE `name` = "?s" OR `id` IN(?ai)', 'Василий', array(1, 2));
     * Результат:
     *     WHERE `name` = "Василий" OR `id` IN(1, 2)
     *
     * @param string SQL-запрос или его часть
     * @param mixed аргументы заполнителей
     * @return boolean|string
     */
    public function prepare()
    {
        if (!func_num_args())
        {
            return false;
        }

        $args = func_get_args();
        $query = array_shift($args);

        return $this->parse($query, $args);
    }

    /**
     * Получает количество рядов, задействованных в предыдущей MySQL-операции.
     * Возвращает количество рядов, задействованных в последнем запросе INSERT, UPDATE или DELETE.
     * Если последним запросом был DELETE без оператора WHERE,
     * все записи таблицы будут удалены, но функция возвратит ноль.
     *
     * @see mysqli_affected_rows
     * @param void
     * @return int
     */
    public function getAffectedRows()
    {
        return $this->mysqli->affected_rows;
    }

    /**
     * Возвращает последний оригинальный SQL-запрос до преобразования.
     *
     * @param void
     * @return string
     */
    public function getOriginalQueryString()
    {
        return $this->original_query;
    }

    /**
     * Возвращает последний выполненный MySQL-запрос (после преобразования).
     *
     * @param void
     * @return string
     */
    public function getQueryString()
    {
        return $this->query;
    }

    /**
     * Возвращает массив со всеми исполненными SQL-запросами в рамках текущего объекта.
     *
     * @param void
     * @return array
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * Возвращает id, сгенерированный предыдущей операцией INSERT.
     *
     * @param void
     * @return int
     */
    public function getLastInsertId()
    {
        return $this->mysqli->insert_id;;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @param string $server
     * @param string $username
     * @param string $password
     * @param string $port
     * @param string $socket
     * @return void
     */
    private function __construct($server, $user, $password, $port, $socket)
    {
        $this->server   = $server;
        $this->user = $user;
        $this->password = $password;
        $this->port = $port;
        $this->socket = $socket;

        $this->connect();
    }

    /**
     * Устанавливает соеденение с базой данных.
     *
     * @param void
     * @return void
     */
    private function connect()
    {
        if (!is_object($this->mysqli) || !$this->mysqli instanceof mysqli)
        {
        	$this->mysqli = @new mysqli($this->server, $this->user, $this->password, null, $this->port, $this->socket);

        	if ($this->mysqli->connect_error)
            {
                throw new Krugozor_Database_Mysql_Exception(__METHOD__ . ': ' . $this->mysqli->connect_error);
            }
        }
    }

    /**
     * Закрывает MySQL-соединение.
     *
     * @param void
     * @return Krugozor_Database_Mysql
     */
    private function close()
    {
        if (is_object($this->mysqli) && $this->mysqli instanceof mysqli)
        {
            @$this->mysqli->close();
        }

        return $this;
    }

    /**
     * Возвращает экранированную строку для placeholder-а поиска LIKE (?S).
     *
     * @param string $var строка в которой необходимо экранировать спец. символы
     * @param string $chars набор символов, которые так же необходимо экранировать.
     *                      По умолчанию экранируются следующие символы: `'"%_`.
     * @return string
     */
    private function escapeLike($var, $chars = "%_")
    {
        $var = str_replace('\\', '\\\\', $var);
        $var = $this->mysqlRealEscapeString($var);

        if ($chars)
        {
            $var = addCslashes($var, $chars);
        }

        return $var;
    }

    /**
     * Экранирует специальные символы в строке для использования в SQL выражении,
     * используя текущий набор символов соединения.
     *
     * @see mysqli_real_escape_string
     * @param string
     * @return string
     */
    private function mysqlRealEscapeString($value)
    {
        return $this->mysqli->real_escape_string($value);
    }

    /**
     * Возвращает строку описания ошибки при несовпадении типов заполнителей и аргументов.
     *
     * @param string $type тип заполнителя
     * @param mixed $value значение аргумента
     * @param string $original_query оригинальный SQL-запрос
     * @return string
     */
    private function createErrorMessage($type, $value, $original_query)
    {
        return __CLASS__ . ': Попытка записать как ' . $type . ' значение "' . print_r($value, true) . '" типа ' .
               gettype($value) . ' в запросе ' . $original_query;
    }

    /**
     * Парсит запрос $query и подставляет в него аргументы из $args.
     *
     * @param string $query SQL запрос или его часть (в случае парсинга условия в скобках [])
     * @param array $args аргументы заполнителей
     * @param string $original_query "оригинальный", полный SQL-запрос
     * @return string SQL запрос для исполнения
     */
    private function parse($query, array $args, $original_query=null)
    {
        $original_query = $original_query ? $original_query : $query;

        $offset = 0;

        while (($posQM = strpos($query, '?', $offset)) !== false)
        {
            $offset = $posQM;

            if (!isset($query[$posQM + 1]))
            {
                continue;
            }
            else
            {
                // Если найден просто знак ?, парсим строку дальше.
                if (!in_array($query[$posQM + 1], array('i', 'p', 's', 'S', 'n', 'A', 'a', 'f')))
                {
                    $offset += 1;
                    continue;
                }
            }

            if (!$args)
            {
                throw new Krugozor_Database_Mysql_Exception(__METHOD__ . ': количество заполнителей в запросе ' . $original_query .
                                    ' не соответствует переданному количеству аргументов');
            }

            $value = array_shift($args);

            switch ($query[$posQM + 1])
            {
                // `LIKE` search escaping
                case 'S':
                    $is_like_escaping = true;

                // Simple string escaping
                // В случае установки MODE_TRANSFORM режима, преобразование происходит согласно правилам php типизации
                // http://php.net/manual/ru/language.types.string.php#language.types.string.casting
                // для bool, null и numeric типа.
                case 's':
                    $value = $this->getValueStringType($value, $original_query);
                    $value = !empty($is_like_escaping) ? $this->escapeLike($value) : $this->mysqlRealEscapeString($value);
                    $query = substr_replace($query, $value, $posQM, 2);
                    $offset += strlen($value);
                    break;

                // Integer
                // В случае установки MODE_TRANSFORM режима, преобразование происходит согласно правилам php типизации
                // http://php.net/manual/ru/language.types.integer.php#language.types.integer.casting
                // для bool, null и string типа.
                case 'i':
                    $value = $this->getValueIntType($value, $original_query);
                    $query = substr_replace($query, $value, $posQM, 2);
                    $offset += strlen($value);
                    break;

                // Floating point
                case 'p':
                    $value = $this->getValueFloatType($value, $original_query);
                    $query = substr_replace($query, $value, $posQM, 2);
                    $offset += strlen($value);
                    break;

                // NULL insert
                case 'n':
                    $value = $this->getValueNullType($value, $original_query);
                    $query = substr_replace($query, $value, $posQM, 2);
                    $offset += strlen($value);
                    break;

                // field or table name
                case 'f':
                    $value = '`' . $this->escapeFieldName($value) . '`';
                    $query = substr_replace($query, $value, $posQM, 2);
                    $offset += strlen($value);
                    break;

                // Парсинг массивов.

                // Associative array
                case 'A':
                    $is_associative_array = true;

                // Simple array
                case 'a':
                    $value = $this->getValueArrayType($value, $original_query);

                    if (isset($query[$posQM+2]) && preg_match('#[sip\[]#', $query[$posQM+2], $matches))
                    {
                        // Парсим выражение вида ?a[?i, "?s", "?s"]
                        if ($query[$posQM+2] == '[' and ($close = strpos($query, ']', $posQM+3)) !== false)
                        {
                            // Выражение между скобками [ и ]
                            $array_parse = substr($query, $posQM+3, $close - ($posQM+3));
                            $array_parse = trim($array_parse);
                            $placeholders = array_map('trim', explode(',', $array_parse));

                            if (count($value) != count($placeholders))
                            {
                                throw new Krugozor_Database_Mysql_Exception('Несовпадение количества аргументов и заполнителей в массиве, запрос ' . $original_query);
                            }

                            reset($value);
                            reset($placeholders);

                            $replacements = array();

                            foreach ($placeholders as $placeholder)
                            {
                                list($key, $val) = each($value);
                                $replacements[$key] = $this->parse($placeholder, array($val), $original_query);
                            }

                            if (!empty($is_associative_array))
                            {
                                foreach ($replacements as $key => $val)
                                {
                                    $values[] = ' `' . $this->escapeFieldName($key) . '` = ' . $val;
                                }

                                $value = implode(',', $values);
                            }
                            else
                            {
                                $value = implode(', ', $replacements);
                            }

                            $query = substr_replace($query, $value, $posQM, 4 + strlen($array_parse));
                            $offset += strlen($value);
                        }
                        // Выражение вида ?ai, ?as, ?ap
                        else if (preg_match('#[sip]#', $query[$posQM+2], $matches))
                        {
                            $sql = '';
                            $parts = array();

                            foreach ($value as $key => $val)
                            {
                                switch ($matches[0])
                                {
                                    case 's':
                                        $val = $this->getValueStringType($val, $original_query);
                                        $val = $this->mysqlRealEscapeString($val);
                                        break;
                                    case 'i':
                                        $val = $this->getValueIntType($val, $original_query);
                                        break;
                                    case 'p':
                                        $val = $this->getValueFloatType($val, $original_query);
                                        break;
                                }

                                if (!empty($is_associative_array))
                                {
                                    $parts[] = ' `' . $this->escapeFieldName($key) . '` = "' . $val . '"';
                                }
                                else
                                {
                                    $parts[] = '"' . $val . '"';
                                }
                            }

                            $value = implode(', ', $parts);
                            $query = substr_replace($query, $value, $posQM, 3);
                            $offset += strlen($value);
                        }
                    }
                    else
                    {
                        throw new Krugozor_Database_Mysql_Exception('Попытка воспользоваться заполнителем массива без указания типа данных его элементов');
                    }
                    break;
            }
        }

        return $query;
    }

    /**
     * В зависимости от типа режима возвращает либо строковое значение $value,
     * либо кидает исключение.
     *
     * @param mixed $value
     * @param string $original_query оригинальный SQL запрос
     * @throws Exception
     * @return string
     */
    private function getValueStringType($value, $original_query)
    {
        if (!is_string($value))
        {
            if ($this->type_mode == self::MODE_STRICT)
            {
                throw new Krugozor_Database_Mysql_Exception($this->createErrorMessage('string', $value, $original_query));
            }
            else if ($this->type_mode == self::MODE_TRANSFORM)
            {
                if (is_numeric($value) || is_null($value) || is_bool($value))
                {
                    $value = (string)$value;
                }
                else
                {
                    throw new Krugozor_Database_Mysql_Exception($this->createErrorMessage('string', $value, $original_query));
                }
            }
        }

        return $value;
    }

    /**
     * В зависимости от типа режима возвращает либо строковое значение числа $value,
     * либо кидает исключение.
     *
     * @param mixed $value
     * @param string $original_query оригинальный SQL запрос
     * @throws Exception
     * @return string
     */
    private function getValueIntType($value, $original_query)
    {
        if (!is_numeric($value))
        {
            if ($this->type_mode == self::MODE_STRICT)
            {
                throw new Krugozor_Database_Mysql_Exception($this->createErrorMessage('int', $value, $original_query));
            }
            else if ($this->type_mode == self::MODE_TRANSFORM)
            {
                if (is_string($value) || is_null($value) || is_bool($value))
                {
                    $value = (int)$value;
                }
                else
                {
                    throw new Krugozor_Database_Mysql_Exception($this->createErrorMessage('int', $value, $original_query));
                }
            }
        }

        return (string)$value;
    }

    /**
     * В зависимости от типа режима возвращает либо строковое значение числа $value,
     * либо кидает исключение.
     *
     * @param mixed $value
     * @param string $original_query оригинальный SQL запрос
     * @throws Exception
     * @return string
     */
    private function getValueFloatType($value, $original_query)
    {
        if (!is_numeric($value))
        {
            if ($this->type_mode == self::MODE_STRICT)
            {
                throw new Krugozor_Database_Mysql_Exception($this->createErrorMessage('float', $value, $original_query));
            }
            else if ($this->type_mode == self::MODE_TRANSFORM)
            {
                if (is_string($value) || is_null($value) || is_bool($value))
                {
                    $value = (float)$value;
                }
                else
                {
                    throw new Krugozor_Database_Mysql_Exception($this->createErrorMessage('float', $value, $original_query));
                }
            }
        }

        return (string)$value;
    }

    /**
     * В зависимости от типа режима возвращает либо строковое значение 'NULL',
     * либо кидает исключение.
     *
     * @param mixed $value
     * @param string $original_query оригинальный SQL запрос
     * @throws Exception
     * @return string
     */
    private function getValueNullType($value, $original_query)
    {
        if ($value !== null)
        {
            if ($this->type_mode == self::MODE_STRICT)
            {
                throw new Krugozor_Database_Mysql_Exception($this->createErrorMessage('NULL', $value, $original_query));
            }
        }

        return 'NULL';
    }

    /**
     * Всегда генерирует исключение, если $value не является массивом.
     * Первоначально была идея в режиме self::MODE_TRANSFORM приводить к типу array
     * скалярные данные, но на данный момент я считаю это излишним послаблением для клиентов,
     * которые будут использовать данный класс.
     *
     * @param mixed $value
     * @param string $original_query
     * @throws Exception
     * @return array
     */
    private function getValueArrayType($value, $original_query)
    {
        if (!is_array($value))
        {
            throw new Krugozor_Database_Mysql_Exception($this->createErrorMessage('array', $value, $original_query));
        }

        return $value;
    }

    /**
     * Экранирует имя поля таблицы в случае использования маркеров множества.
     *
     * @param string $value
     * @return string $value
     */
    private function escapeFieldName($value)
    {
        return str_replace("`", "``", $value);
    }
}