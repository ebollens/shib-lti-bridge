<?php

require_once dirname(__FILE__).'/Interface/LTIResource.php';
require_once dirname(__FILE__).'/DBQuery.php';

class LTIResource implements Interface_LTIResource
{
    /**
     * Name of LTI resource table.
     */
    const LTI_RESOURCE_TABLE = 'lti_resource';
    
    /**
     * Primary key ID for LTI user table.
     * 
     * @var int
     */
    protected $id;
    
    /**
     * Construct an LTI resource by unique integer identifier.
     * 
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }
    
    /**
     * Return the unique integer identifier for the LTI resource.
     * 
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Fetch an attribute associated with the LTI resource record.
     * 
     * @param string $attributeName
     * @return mixed
     */
    public function get($attributeName)
    {
        return DBQuery::selectRowColumnValue(self::LTI_RESOURCE_TABLE, $attributeName, $this->id);
    }
    
    /**
     * Modify attributes associated with the LTI resource record.
     * 
     * @param array $ltiAttributes
     */
    public function updateAttributes(array $ltiAttributes)
    {
        $attributeUpdates = array();
        foreach(self::getAttributeMap() as $ltiKey => $siteKey)
            if(isset($ltiAttributes[$ltiKey]))
                $attributeUpdates[$siteKey] = $ltiAttributes[$ltiKey];
        DBQuery::updateRow(self::LTI_RESOURCE_TABLE, $attributeUpdates, $this->id);
    }
    
    /**
     * Factory to produce an LTI user by an LTI Resource Key. If a resource 
     * does not already exist for this LTI Resource Key, then it should be 
     * created and returned.
     * 
     * @param string $ltiResourceKey
     * @return Interface_LTIResource
     */
    public static function fromKey($ltiResourceKey)
    {
        $id = DBQuery::selectRowColumnValue(self::LTI_RESOURCE_TABLE, 'id', $ltiResourceKey, 'key') 
              ?: DBQuery::insert(self::LTI_RESOURCE_TABLE, array('key'=>$ltiResourceKey));
        
        return new LTIResource($id);
    }
    
    /**
     * Returns an array mapping association keys as LTI keys and association 
     * values as database values.
     * 
     * @return array
     */
    public static function getAttributeMap()
    {
        return array(
            'resource_link_title' => 'title', 
            'resource_link_description' => 'description'
        );
    }
}