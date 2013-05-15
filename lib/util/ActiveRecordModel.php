<?php

/**
 * Class definition for ActiveRecordModel.
 * 
 * FOR EXAMPLE USE ONLY.
 * 
 * @package demo
 * @subpackage util
 * @author Eric Bollens
 * @copyright Copyright (c) 2012 UC Regents
 */

require_once dirname(__FILE__).'/ActiveRecordModelException.php';
require_once dirname(__FILE__).'/DB.php';

/**
 * The ActiveRecordModel represents a record as defumed by the primary key
 * $_col with the value $_key in the relation $_table. It makes attributes in 
 * the tuple available via $this->$attr, both for access and mutation.
 * 
 * For performance, it makes several concensions:
 * 
 *      - Lazy Instantiation: Reduces the number of DBq by only making a query
 *        to get tuple data when data is requested.
 * 
 *      - Buffered Writes: All changes to attributes are buffered until either
 *        ->create() or ->update() is called. This also allows for ->revert() to
 *        roll back changes buffered but not yet written to persistence layer.
 * 
 *      - Prefecth: Allows for a result set to be fetched in one query and then
 *        loaded into an array of ActiveRecordModel objects via ->load() so
 *        that each ActiveRecordModel does not cost a DBq.
 * 
 * This class throws ActiveRecordModelException objects on MySQL transaction 
 * failures.
 * 
 * @package service
 * @subpackage database
 * @uses DB
 * @uses Active_Record_Exception
 */
class ActiveRecordModel
{

    /**
     * Relation for the Active Record.
     *
     * @var string
     */
    private $_table;

    /**
     * Value for the unique column that identifies Active Record.
     *
     * @var string
     */
    private $_key;

    /**
     * Unique column to identify Active Record.
     *
     * @var string
     */
     private $_col;

    /**
     * An array that holds values to be inserted/updated.
     *
     * @var array
     */
    private $_buffer = array();

    /**
     * An array with lazy instantiated data current in the tuple (if it exists).
     * This is false if a tuple does not exist, either because it has not been
     * created yet or because it has been changed.
     *
     * @var array|false
     */
    private $_original = false;

    /**
     * Defines the status of the Active Record as bound to a tuple in its bound
     * relation. 0 represents a tuple not yet checked for in the relation, -1 a
     * tuple that was checked for and doesn't exist, and 1 a tuple that does
     * exist in the relation.
     *
     * @var -1|0|1
     */
    private $_status = 0;

    /**
     * Defines a binding between the Active Record instance and a row in a
     * relation defined by $table. Syntax is __construct(table [, key [, col]]).
     *
     * If one defines only a $table in the constructor, then this Active Record
     * is a new tuple that can be inserted into the relation. If one defines a
     * $table and $key, then it binds the Active Record to a tuple with the
     * value $key for column `id`. If the $col parameter is specified, then the
     * binding occurs to a tuple with the value $key for the column $col.
     *
     * @param string $table relation name in the database
     * @param string|null $key key value for unique column $col (default null)
     * @param string $col attribute Active Record is bound to (default "id")
     */
    public function __construct($table, $key = null, $col = 'id')
    {
        $this->_table = $table;
        $this->_key = $key;
        $this->_col = $col;
    }

    /**
     * Magic method that aliases $this->get($column).
     *
     * @param string $column tuple attribute name in relation
     * @uses get() routine to get current data for tuple's attribute $column
     * @return mixed|false mixed if value exists or false otherwise
     */
    public function __get($column)
    {
        return $this->get($column);
    }

    /**
     * Gets the attribute within the bounded tuple as specified by $column.
     * Priority of this goes to the buffer if a new value has been assigned but
     * not yet written for the tuple attribute. If there is no such value, it
     * returns the current value in the relation for the tuple's attribute if
     * it exists, or false otherwise.
     *
     * @param string $column tuple attribute name in relation
     * @uses _read() loads data into the $this->_original array
     * @return mixed|false mixed if value exists or false otherwise
     */
    public function get($column)
    {
        if($column == $this->_col)
            return $this->_key;
        elseif(isset($this->_buffer[$column]))
            return $this->_buffer[$column];
        elseif($this->_read() > 0 && isset($this->_original[$column]))
            return $this->_original[$column];
        else
            return false;
    }

    /**
     * Returns the current value in the relation for the tuple's attribute if
     * it exists, or false otherwise.
     *
     * @param string $column tuple attribute name in relation
     * @uses _read() loads data into the $this->_original array
     * @return mixed|false mixed if value exists or false otherwise
     */
    public function get_original($column)
    {
        if($column == $this->_col)
            return $this->_key;
        elseif($this->_read() && isset($this->_original[$column]))
            return $this->_original[$column];
        else
            return false;
    }

    /**
     * Magic method that aliases $this->set($column, $value).
     *
     * @param string $column tuple attribute name in relation
     * @param mixed $value new value to be write buffered for tuple attribute
     */
    public function __set($column, $value)
    {
        return $this->set($column, $value);
    }

    /**
     * Sets the attribute $column to $value in the write buffer for the Active
     * Record.
     *
     * @param string $column tuple attribute name in relation
     * @param mixed $value new value to be write buffered for tuple attribute
     * @return Active_Record
     */
    public function &set($column, $value)
    {
        $this->_buffer[$column] = $value;
        return $this;
    }

    /**
     * Trigger instantiation directly rather than let Active Record do lazy
     * instantiation.
     *
     * @return -1|1 1 if bounded tuple exists or -1 otherwise
     */
    public function prefetch()
    {
        return $this->_read();
    }

    /**
     * Create a new tuple from the data in the write buffer.
     *
     * @return int|false insert id of new tuple if a success or false otherwise
     */
    public function create()
    {
        return $this->_create();
    }

    /**
     * Update the bound tuple in the relation with the data in the write buffer.
     *
     * @param true|false $force optional param to bypass exists/buffer checks
     * @return true|false true if update was successful or false otherwise
     */
    public function update($force = false)
    {
        return $this->_update($force);
    }

    /**
     * Delete the bound tuple in the relation.
     *
     * @param true|false $force optional param to bypass exists/buffer checks
     * @return true|false true if delete was successful or false otherwise
     */
    public function delete($force = false)
    {
        return $this->_delete($force);
    }

    /**
     * If a tuple exists with the value for key column, this returns true.
     *
     * @return true|false
     */
    public function exists()
    {
        return ($this->_read() === 1);
    }
    
    /**
     * True if any values have been stored in the buffer to be updated or false
     * otherwise.
     * 
     * @return true|false
     */
    public function is_modified()
    {
        return count($this->_buffer) > 0;
    }

    /**
     * Drops all values in the write buffer.
     */
    public function revert()
    {
        $this->_buffer = array();
    }

    /**
     * Drops all values in the write buffer and clears the read cache. This
     * causes the Active Record instance to again fetch data from the database.
     */
    public function refresh()
    {
        $this->_buffer = array();
        $this->_original = false;
        $this->_status = 0;
    }

    /**
     * Returns the current attributes of the tuple. If bounded to an existing
     * tuple, this is a merge of the read cache with the write buffer, where
     * the write buffer attributes override the read cache, whereas if it is
     * a new row, it is just the write buffer.
     *
     * @return array merge of read cache and write buffer
     */
    public function to_array()
    {
        return $this->_read() === 1 ?
                array_merge($this->_original, $this->_buffer) :
                $this->_buffer ;
    }

    /**
     * Returns the read cache if the tuple exists or false otherwise.
     *
     * @return array|false array if tuple exists or false otherwise
     */
    public function original_to_array()
    {
        return $this->_read() === 1 ? $this->_original : false ;
    }

    /**
     * Returns the write buffer.
     *
     * @return array array of elements in the write cache
     */
    public function modified_to_array()
    {
        return $this->_buffer;
    }

    /**
     * Load an array of values into the read cache as though it was fetched
     * from the relation; this simulates a query without requiring one. This is
     * intended for cases where a script has already fetched a set of tuples and
     * now seeks to build a set of Active_Record objects. Rather than force each
     * Active Record instantiated from having to access the database with
     * another query, this allows the instances to be directly populated from
     * the existing data about the relation.
     *
     * @param array $array an array of values to load into Active Record read
     * cache
     * @param string|null $key key value for unique column $col (default null)
     * @param string $col attribute Active Record is bound to (default "id")
     */
    public function load($array, $key = null, $col = 'id')
    {
        if($key){
            $this->_key = $key;
            $this->_col = $col;
        }

        if(is_object($array))
            $array = get_object_vars($array);

        $this->_original = $array;
        $this->_status = 1;
    }

    /**
     * Determine if a tuple exists as defined by its value for key column
     *
     * @param string $column
     * @param string $value
     * @throws ActiveRecordModelException
     * @return true|false true if a matching row does not already exist
     */
    public function check_unique($column, $value)
    {
        $query = 'SELECT * FROM `'.$this->_table.'` WHERE `'.DB::mysqli()->real_escape_string($column)."`='".DB::mysqli()->real_escape_string($value)."'";

        $result = DB::mysqli()->query($query);

        if ($result === false)
            throw new ActiveRecordModelException('MySQL Error: '.DB::mysqli()->error, DB::mysqli()->errno);

        return $this->_read() === 1 ?
        $result->num_rows === 1 :
        $result->num_rows === 0 ;
    }
    
    public static function build_all($table, $key_col)
    {
        $query = 'SELECT * FROM `'.$table.'`';
        
        $result = DB::mysqli()->query($query);
        
        if($result === false)
            throw new ActiveRecordModelException('MySQL Error: '.DB::mysqli()->error, DB::mysqli()->errno);
        
        $classname = get_called_class();
        
        $arr = array();
        while($row = $result->fetch_assoc())
        {
            $obj = new $classname($row[$key_col]);
            $obj->load($row);
            $arr[$row[$key_col]] = $obj;
        }
        
        return $arr;
    }

    /**
     * Create a new tuple from the data in the write buffer.
     *
     * @throws ActiveRecordModelException
     * @return int|false insert id of new tuple if a success or false otherwise
     */
    protected function _create()
    {
        if($this->exists())
            return false;

        $fields = '(';
        $values = '(';
        
        if(count($this->_buffer) > 0)
        {
            foreach ($this->_buffer as $key => $value)
            {
                $fields .= '`'.DB::mysqli()->real_escape_string($key).'`, ';
                $values .= "'".DB::mysqli()->real_escape_string($value)."', ";
            }

            $fields = substr($fields, 0, -2);
            $values = substr($values, 0, -2);
        }
        
        $fields .= ')';
        $values .= ')';

        $query = 'INSERT INTO `'.$this->_table.'` '.$fields.' VALUES '.$values;

        if (DB::mysqli()->query($query) === false)
            throw new ActiveRecordModelException('MySQL Error: '.DB::mysqli()->error, DB::mysqli()->errno);
        
        $this->_key = DB::mysqli()->insert_id;
        $this->refresh();
        
        return $this->_key;
    }

    /**
     * If data does not already exist in the read cache ($this->_original), this
     * method does a fetch on the database and sets the status var. If no key
     * is defined, then it just sets the status var to -1. In all cases, it then
     * returns the status of the DActive Record as bounded to a tuple.
     *
     * @throws ActiveRecordModelException
     * @return -1|1 1 if bounded tuple exists or -1 otherwise
     */
    protected function _read()
    {
        if($this->_key === null && $this->_status === 0) {
            $this->_status = -1;
        } elseif($this->_status === 0) {
            $query = 'SELECT * FROM `'.$this->_table.'` WHERE `'.$this->_col."`='".DB::mysqli()->real_escape_string($this->_key)."'";
            $result = DB::mysqli()->query($query);

            if ($result === false)
                throw new ActiveRecordModelException('MySQL Error: '.DB::mysqli()->error, DB::mysqli()->errno);

            if($result->num_rows > 0)
            {
                $this->_original = $result->fetch_assoc();
                $result->close();
            }

            if($this->_original !== false)
                $this->_status = 1;
            else
                $this->_status = -1;
        }

        return $this->_status;
    }

    /**
     * Update the bound tuple in the relation with the data in the write buffer.
     *
     * @param true|false $force optional param to bypass exists/buffer checks
     * @throws ActiveRecordModelException
     * @return true|false true if update was successful or false otherwise
     */
    protected function _update($force = false)
    {
        if(!$force && (!$this->exists() || count($this->_buffer) == 0))
            return false;
        
        $new = '';
        
        foreach ($this->_buffer as $field => $value)
            $new .= '`'.DB::mysqli()->real_escape_string($field)."`='".DB::mysqli()->real_escape_string($value)."', ";
        
        $new = substr($new, 0, -2);
        $query = 'UPDATE `'.$this->_table.'` SET '.$new.' WHERE `'.$this->_col."`='".DB::mysqli()->real_escape_string($this->_key)."'";

        if(DB::mysqli()->query($query) === false)
            throw new ActiveRecordModelException('MySQL Error: '.DB::mysqli()->error, DB::mysqli()->errno);

        $this->refresh();
            
        return true;
    }

    /**
     * Delete the bound tuple in the relation.
     *
     * @param true|false $force optional param to bypass exists/buffer checks
     * @throws ActiveRecordModelException
     * @return true|false true if delete was successful or false otherwise
     */
    protected function _delete($force = false)
    {
        if(!$force && !$this->exists())
            return false;

        $query = 'DELETE FROM `'.$this->_table.'` WHERE `'.$this->_col."`='".DB::mysqli()->real_escape_string($this->_key)."'";

        if (DB::mysqli()->query($query) === false)
            throw new ActiveRecordModelException('MySQL Error: '.DB::mysqli()->error, DB::mysqli()->errno);

        $this->_key = null;
        $this->refresh();
        
        return true;
    }
}
