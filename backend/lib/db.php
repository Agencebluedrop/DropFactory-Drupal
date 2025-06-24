<?php
/**
 * Dropfactory Backend - DB Class
 * 
 * Access to database
 * 
 * PHP version 8
 * 
 * @author  Ludovic Poujol <lpoujol@evolix.fr>
 * @author  Gregory Colpart <reg@evolix.fr>
 * @author  Evolix <info@evolix.fr>
 * @license TODO
 * @link    TODO
 */
class DB
{

    public static $pdo;

    public static function connect($CONFIG)
    {
        self::$pdo = new PDO(
            $CONFIG['database']['type'].':host='. $CONFIG['database']['host']. ';dbname='.$CONFIG['database']['database'], 
            $CONFIG['database']['user'], 
            $CONFIG['database']['password']
        );
    }
}