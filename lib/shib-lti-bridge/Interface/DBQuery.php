<?php

interface Interface_DBQuery
{
    /**
     * Perform a raw query $sql and return result.
     * 
     * @param string $sql
     * @return mixed
     */
    public static function raw($sql);
    
    /**
     * Return an array of single value $attributeName from a set of records in 
     * $table where $keyColumn is non-unique with the value $keyValue.
     * 
     * @param string $table
     * @param string $attributeName
     * @param string $keyValue
     * @param string $keyColumn
     */
    public static function selectRowsColumnValue($table, $attributeName, $keyValue, $keyColumn = 'id');
    
    /**
     * Return a single value $attributeName from a record in $table where 
     * $keyColumn has a unique key constraint with the value $keyValue.
     * 
     * @param string $table
     * @param string $attributeName
     * @param string $keyValue
     * @param string $keyColumn
     */
    public static function selectRowColumnValue($table, $attributeName, $keyValue, $keyColumn = 'id');
    
    /**
     * Update a record based on the key-value pairs where $keyColumn has a
     * unique key constraint with the value $keyValue.
     * 
     * @param string $table
     * @param array $attributeUpdates
     * @param string $keyValue
     * @param string $keyColumn
     */
    public static function updateRow($table, $attributeUpdates, $keyValue, $keyColumn = 'id');
    
    /**
     * Inserts a record based on the key-value pairs in $attributes.
     * 
     * @param string $table
     * @param array $attributes
     * @param string $keyColumnForReturn
     */
    public static function insert($table, $attributes, $keyColumnForReturn = 'id');
    
    /**
     * Inserts a record based on the key-value pairs in $attributes. As opposed 
     * to ::insert(), this fails silently if a duplicate key constraint exists.
     * 
     * @param string $table
     * @param array $attributes
     * @param string $keyColumnForReturn
     */
    public static function insertIgnoreDuplicate($table, $attributes, $keyColumnForReturn = 'id');
}