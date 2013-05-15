<?php

require_once dirname(__FILE__).'/LTIResource.php';

interface Interface_LTIContext
{
    /**
     * Construct an LTI context by unique integer identifier.
     * 
     * @param int $id
     */
    public function __construct($id);
    
    /**
     * Return the unique integer identifier for the LTI context.
     * 
     * @return int
     */
    public function getId();
    
    /**
     * Fetch an attribute associated with the LTI context record.
     * 
     * @param string $attributeName
     * @return mixed
     */
    public function get($attributeName);
    
    /**
     * Modify attributes associated with the LTI context record.
     * 
     * @param array $ltiAttributes
     */
    public function updateAttributes(array $ltiAttributes);
    
    /**
     * Returns an array of LTI resources as associated in the persistence layer 
     * by the ->associateLTIResource() method.
     * 
     * @return Interface_LTIResource array
     */
    public function getLTIResources();
    
    /**
     * Bind an LTI-defined resource to the LTI context in the persistence layer.
     * 
     * @param Interface_LTIResource $ltiContext
     */
    public function associateLTIResource(Interface_LTIResource $ltiResource);
    
    /**
     * Factory to produce an LTI context by an LTI Context Key. If a context 
     * does not already exist for this LTI Context Key, then it should be 
     * created and returned.
     * 
     * @param string $ltiResourceKey
     * @return Interface_LTIResource
     */
    public static function fromKey($ltiContextKey);
    
    /**
     * Returns an array mapping association keys as LTI keys and association 
     * values as database values.
     * 
     * @return array
     */
    public static function getAttributeMap();
}