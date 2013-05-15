<?php

/**
 * Class definition for ActiveRecordModelException.
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
 * Exception thrown by ActiveRecordModel when an operation is not possible.
 * 
 * @package foundation
 * @see Active_Record_Model
 */
class ActiveRecordModelException extends DBException
{
    
}