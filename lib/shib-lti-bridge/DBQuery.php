<?php

/**
 * Class definition for a set of DAL static methods.
 */

require_once dirname(__FILE__).'/Interface/DBQuery.php';
require_once dirname(dirname(__FILE__)).'/util/ActiveRecordModel.php';
require_once dirname(dirname(__FILE__)).'/util/DB.php';

/**
 * Class that wraps a set of database access methods. These should be 
 * reimplemented using the database access layer of an application using
 * this functionality.
 * 
 * @todo for implementation, rewrite using application's database access layer.
 */
class DBQuery implements Interface_DBQuery
{
    /**
     * Perform a raw query $sql and return result.
     * 
     * @param string $sql
     * @return mixed
     */
    public static function raw($sql)
    {
        return DB::query($sql);
    }
    
    /**
     * Return an array of single value $attributeName from a set of records in 
     * $table where $keyColumn is non-unique with the value $keyValue.
     * 
     * @param string $table
     * @param string $attributeName
     * @param string $keyValue
     * @param string $keyColumn
     */
    public static function selectRowsColumnValue($table, $attributeName, $keyValue, $keyColumn = 'id')
    {
        $val = is_numeric($keyValue) ? $keyValue : '"'.DB::escape_string($keyValue).'"';
        
        $result = DB::query('SELECT `'.$attributeName.'` FROM `'.$table.'` WHERE `'.$keyColumn.'` = '.$keyValue);
        
        $rows = array();
        while($row = $result->fetch_assoc())
            $rows[] = $row[$attributeName];
        
        return $rows;
    }
    
    /**
     * Return a single value $attributeName from a record in $table where 
     * $keyColumn has a unique key constraint with the value $keyValue.
     * 
     * @param string $table
     * @param string $attributeName
     * @param string $keyValue
     * @param string $keyColumn
     */
    public static function selectRowColumnValue($table, $attributeName, $keyValue, $keyColumn = 'id')
    {
        $rec = new ActiveRecordModel($table, $keyValue, $keyColumn);
        return $rec->$attributeName;
    }
    
    /**
     * Update a record based on the key-value pairs where $keyColumn has a
     * unique key constraint with the value $keyValue.
     * 
     * @param string $table
     * @param array $attributeUpdates
     * @param string $keyValue
     * @param string $keyColumn
     */
    public static function updateRow($table, $attributeUpdates, $keyValue, $keyColumn = 'id')
    {
        $rec = new ActiveRecordModel($table, $keyValue, $keyColumn);
        foreach($attributeUpdates as $key => $value)
            $rec->$key = $value;
        return $rec->update();
    }
    
    /**
     * Inserts a record based on the key-value pairs in $attributes.
     * 
     * @param string $table
     * @param array $attributes
     * @param string $keyColumnForReturn
     */
    public static function insert($table, $attributes, $keyColumnForReturn = 'id')
    {
        $rec = new ActiveRecordModel($table);
        foreach($attributes as $key => $value)
            $rec->$key = $value;
        $rec->create();
        return $rec->$keyColumnForReturn;
    }
    
    /**
     * Inserts a record based on the key-value pairs in $attributes. As opposed 
     * to ::insert(), this fails silently if a duplicate key constraint exists.
     * 
     * @param string $table
     * @param array $attributes
     * @param string $keyColumnForReturn
     */
    public static function insertIgnoreDuplicate($table, $attributes, $keyColumnForReturn = 'id')
    {
        try
        {
            return self::insert($table, $attributes, $keyColumnForReturn);
        }
        catch(DBException $e)
        {
            if($e->getCode() != 1062)
                throw $e;
        }
        
        return true;
    }
}