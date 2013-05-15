<?php

require_once dirname(__FILE__).'/Interface/LTIContext.php';
require_once dirname(__FILE__).'/LTIResource.php';
require_once dirname(__FILE__).'/DBQuery.php';

class LTIContext implements Interface_LTIContext
{
    /**
     * Name of LTI context table.
     */
    const LTI_CONTEXT_TABLE = 'lti_context';
    
    /**
     * Name of binding table between LTI contexts and resources.
     */
    const LTI_CONTEXT_RESOURCE_TABLE = 'lti_context_resource';
    
    /**
     * Primary key ID for LTI context table.
     * 
     * @var int
     */
    protected $id;
    
    /**
     * Construct an LTI context by unique integer identifier.
     * 
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }
    
    /**
     * Return the unique integer identifier for the LTI context.
     * 
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Fetch an attribute associated with the LTI context record.
     * 
     * @param string $attributeName
     * @return mixed
     */
    public function get($attributeName)
    {
        return DBQuery::selectRowColumnValue(self::LTI_CONTEXT_TABLE, $attributeName, $this->id);
    }
    
    /**
     * Modify attributes associated with the LTI context record.
     * 
     * @param array $ltiAttributes
     */
    public function updateAttributes(array $ltiAttributes)
    {
        $attributeUpdates = array();
        foreach(self::getAttributeMap() as $ltiKey => $siteKey)
            if(isset($ltiAttributes[$ltiKey]))
                $attributeUpdates[$siteKey] = $ltiAttributes[$ltiKey];
        DBQuery::updateRow(self::LTI_CONTEXT_TABLE, $attributeUpdates, $this->id);
    }
    
    /**
     * Returns an array of LTI resources as associated in the persistence layer 
     * by the ->associateLTIResource() method.
     * 
     * @return Interface_LTIResource array
     */
    public function getLTIResources()
    {
        $result = DB::query('SELECT lti_resource_id FROM `'.self::LTI_CONTEXT_RESOURCE_TABLE.'` WHERE lti_context_id = '.$this->getId());
        
        $ltiResources = array();
        while($row = $result->fetch_assoc())
            $ltiResources[] = new LTIResource($row['lti_resource_id']);
        
        return $ltiResources;
    }
    
    /**
     * Bind an LTI-defined resource to the LTI context in the persistence layer.
     * 
     * @param Interface_LTIResource $ltiContext
     */
    public function associateLTIResource(Interface_LTIResource $ltiResource)
    {
        DBQuery::insertIgnoreDuplicate(self::LTI_CONTEXT_RESOURCE_TABLE, array(
            'lti_context_id' => $this->getId(),
            'lti_resource_id' => $ltiResource->getId()
        ));
    }
    
    /**
     * Factory to produce an LTI context by an LTI Context Key. If a context 
     * does not already exist for this LTI Context Key, then it should be 
     * created and returned.
     * 
     * @param string $ltiResourceKey
     * @return Interface_LTIResource
     */
    public static function fromKey($ltiContextKey)
    {
        $id = DBQuery::selectRowColumnValue(self::LTI_CONTEXT_TABLE, 'id', $ltiContextKey, 'key') 
              ?: DBQuery::insert(self::LTI_CONTEXT_TABLE, array('key'=>$ltiContextKey));
        
        return new LTIContext($id);
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
            'context_title'=>'title', 
            'context_label'=>'label'
        );
    }
}