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

    public static function get_data($query, $db = null)
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
            return $data;
        } catch (Krugozor_Database_Mysql_Exception $e) {
            return $e->getMessage();
        }
    }
}