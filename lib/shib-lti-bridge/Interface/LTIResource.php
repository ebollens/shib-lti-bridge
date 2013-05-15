<?php

interface Interface_LTIResource
{
    /**
     * Construct an LTI resource by unique integer identifier.
     * 
     * @param int $id
     */
    public function __construct($id);
    
    /**
     * Return the unique integer identifier for the LTI resource.
     * 
     * @return int
     */
    public function getId();
    
    /**
     * Fetch an attribute associated with the LTI resource record.
     * 
     * @param string $attributeName
     * @return mixed
     */
    public function get($attributeName);
    
    /**
     * Modify attributes associated with the LTI resource record.
     * 
     * @param array $ltiAttributes
     */
    public function updateAttributes(array $ltiAttributes);
    
    /**
     * Factory to produce an LTI user by an LTI Resource Key. If a resource 
     * does not already exist for this LTI Resource Key, then it should be 
     * created and returned.
     * 
     * @param string $ltiResourceKey
     * @return Interface_LTIResource
     */
    public static function fromKey($ltiResourceKey);
    
    /**
     * Returns an array mapping association keys as LTI keys and association 
     * values as database values.
     * 
     * @return array
     */
    public static function getAttributeMap();
}