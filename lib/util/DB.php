<?php

/**
 * Class definition for DB.
 * 
 * FOR EXAMPLE USE ONLY.
 * 
 * @package demo
 * @subpackage util
 * @author Eric Bollens
 * @copyright Copyright (c) 2012 UC Regents
 */

require_once dirname(__FILE__).'/DBException.php';

/**
 * Singleton database accessor class.  Encapsulates a common mysqli object that
 * can be referenced with DB::mysqli(), and also forwards static method calls to
 * the mysqli object so you can make method calls against the mysqli object
 * easily (if PHP 5.3+).
 * 
 * @package foundation
 * @uses DBException
 */
class DB {

    /**
     * The instance of the DB object
     *
     * @var null|DB
     */
    private static $_self = null;

    /**
     * The mysqli object associated with the current database connection.
     *
     * @var null|mysqli
     */
    private static $_mysqli = null;

    /**
     * Constructor is declared private to make the class a singleton.
     */
    private function __construct() {}
    
    /**
     * Constructor is declared private to make the class a singleton.
     */
    private function __clone() {}

    /**
     * __callStatic magic method is used for call forwarding to the encapsulated
     * mysqli object.
     *
     * @param string $name Name of the function to call
     * @param array $arguments Array of arguments the function is called with
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        if(method_exists('mysqli', $name))
            $result = call_user_func_array(array(self::mysqli(), $name), $arguments);
        
        if(!$result)
            throw new DBException('MySQL Error ('.self::mysqli()->errno.'): '.  self::mysqli()->error, self::mysqli()->errno);
        
        return $result;
    }

    /**
     * Singleton accessor method that returns the instance of the mysqli object.
     *
     * @return mysqli
     */
    public static function &mysqli()
    {
        $config = parse_ini_file(dirname(__FILE__).'/DB.ini');
        
        if(self::$_self == null)
            self::$_self = new DB();
        
        if(self::$_mysqli == null)
            @self::$_mysqli = new mysqli($config['hostname'], $config['username'], $config['password'], $config['database']);
        
        if(mysqli_connect_errno())
            throw new DBException('Database Connection Error ('.mysqli_connect_errno().'): '.  mysqli_connect_error());
        
        return self::$_mysqli;
    }
}
