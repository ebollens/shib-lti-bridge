<?php

require_once dirname(__FILE__).'/LTIContext.php';

interface Interface_LTIUser
{
    /**
     * Construct an LTI user by unique integer identifier.
     * 
     * @param int $id
     */
    public function __construct($id);
    
    /**
     * Return the unique integer identifier for the LTI user.
     * 
     * @return int
     */
    public function getId();
    
    /**
     * Fetch an attribute associated with the LTI user record.
     * 
     * @param string $attributeName
     * @return mixed
     */
    public function get($attributeName);
    
    /**
     * Modify attributes associated with the LTI user record.
     * 
     * @param array $ltiAttributes
     */
    public function updateAttributes(array $ltiAttributes);
    
    /**
     * Returns an array of LTI contexts as associated in the persistence layer 
     * by the ->associateLTIContext() method.
     * 
     * @return Interface_LTIContext array
     */
    public function getLTIContexts();
    
    /**
     * Bind an LTI-defined context to the LTI user in the persistence layer
     * such that it is returned by the ->getLTIContexts() method.
     * 
     * @param Interface_LTIContext $ltiContext
     * @param boolean $isInstructor
     * @param string $roles
     */
    public function associateLTIContext(Interface_LTIContext $ltiContext, $isInstructor = false, $roles = '');
    
    /**
     * Factory to produce an LTI user by an LTI User Key. If a user does not 
     * already exist for this LTI User Key, then it should be created and 
     * returned.
     * 
     * @param string $ltiUserKey
     * @return Interface_LTIUser
     */
    public static function fromKey($ltiUserKey);
    
    /**
     * Returns an array mapping association keys as LTI keys and association 
     * values as database values.
     * 
     * @return array
     */
    public static function getAttributeMap();
}