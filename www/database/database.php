<?php
require_once 'Mysql.php';
require_once 'Mysql/Exception.php';
require_once 'Mysql/Statement.php';

class Database
{
    public static function connect()
    {
        $db = Krugozor_Database_Mysql::create(
            Settings::database_address, Settings::database_login, Settings::database_password)
            ->setCharset('utf8')
            ->setDatabaseName(Settings::database_name);
        return $db;
    }

    /**
     * Получение данных из базы данных
     *
     * @param string $query	SQL-запрос
     * @param Krugozor_Database_Mysql $db Соединение с базой данных
     * @param boolean $one Флаг получения одного элемента
     * @return array Результат запроса
     * @access 	public
     * @author 	b1oki
     */
    public static function get_data($query, $db = null, $one = false)
    {
        try {
            if (empty($db)) {
                $db = Database::connect();
            }
            $result = $db->query($query);
            $data = array();
            while ($d = $result->fetch_assoc()) {
                $data[] = $d;
            }
            if ($one) {
                $data_length = count($data);
                if ($data_length > 1) {
                    throw new Krugozor_Database_Mysql_Exception('get_data(one=true) returned more than one');
                } elseif ($data_length == 1) {
                    $data = $data[0];
                }
            }
            return $data;
        } catch (Krugozor_Database_Mysql_Exception $e) {
            return $e->getMessage();
        }
    }
}