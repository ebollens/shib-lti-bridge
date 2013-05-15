<?php

require_once dirname(__FILE__).'/Interface/LTIUser.php';
require_once dirname(__FILE__).'/Interface/LTIContext.php';
require_once dirname(__FILE__).'/Interface/LTIResource.php';
require_once dirname(__FILE__).'/LTIContext.php';
require_once dirname(__FILE__).'/DBQuery.php';

class LTIUser implements Interface_LTIUser
{
    /**
     * Name of LTI user table.
     */
    const LTI_USER_TABLE = 'lti_user';
    
    /**
     * Name of binding table between LTI users and contexts.
     */
    const LTI_USER_CONTEXT_TABLE = 'lti_user_context';
    
    /**
     * Primary key ID for LTI user table.
     * 
     * @var int
     */
    protected $id;
    
    /**
     * Construct an LTI user by unique integer identifier.
     * 
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }
    
    /**
     * Return the unique integer identifier for the LTI user.
     * 
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Fetch an attribute associated with the LTI user record.
     * 
     * @param string $attributeName
     * @return mixed
     */
    public function get($attributeName)
    {
        return DBQuery::selectRowColumnValue(self::LTI_USER_TABLE, $attributeName, $this->id);
    }
    
    /**
     * Modify attributes associated with the LTI user record.
     * 
     * @param array $ltiAttributes
     */
    public function updateAttributes(array $ltiAttributes)
    {
        $attributeUpdates = array();
        foreach(self::getAttributeMap() as $ltiKey => $siteKey)
            if(isset($ltiAttributes[$ltiKey]))
                $attributeUpdates[$siteKey] = $ltiAttributes[$ltiKey];
        DBQuery::updateRow(self::LTI_USER_TABLE, $attributeUpdates, $this->id);
    }
    
    /**
     * Returns an array of LTI contexts as associated in the persistence layer 
     * by the ->associateLTIContext() method.
     * 
     * @return Interface_LTIContext array
     */
    public function getLTIContexts()
    {
        $result = DB::query('SELECT lti_context_id FROM `'.self::LTI_USER_CONTEXT_TABLE.'` WHERE lti_user_id = '.$this->getId());
        
        $ltiContexts = array();
        while($row = $result->fetch_assoc())
            $ltiContexts[] = new LTIContext($row['lti_context_id']);
        
        return $ltiContexts;
    }
    
    /**
     * Bind an LTI-defined context to the LTI user in the persistence layer
     * such that it is returned by the ->getLTIContexts() method.
     * 
     * @param Interface_LTIContext $ltiContext
     * @param boolean $isInstructor
     * @param string $roles
     */
    public function associateLTIContext(Interface_LTIContext $ltiContext, $isInstructor = false, $roles = '')
    {
        try
        {
            DBQuery::insert(self::LTI_USER_CONTEXT_TABLE, array(
                'lti_user_id' => $this->getId(),
                'lti_context_id' => $ltiContext->getId(),
                'is_instructor' => $isInstructor?1:0,
                'roles' => DB::escape_string($roles)
            ));
        }
        catch(DBException $e)
        {
            if($e->getCode() == 1062)
                DBQuery::raw('UPDATE `'.self::LTI_USER_CONTEXT_TABLE.'`
                           SET `is_instructor` = '.($isInstructor?1:0).', 
                                `roles` = "'.DB::escape_string($roles).'" 
                           WHERE `lti_user_id` = '.$this->getId().' 
                             AND `lti_context_id` = '.$ltiContext->getId().';');
            else
                throw $e;
        }
    }
    
    /**
     * Factory to produce an LTI user by an LTI User Key. If a user does not 
     * already exist for this LTI User Key, then it should be created and 
     * returned.
     * 
     * @param string $ltiUserKey
     * @return Interface_LTIUser
     */
    public static function fromKey($ltiUserKey)
    {
        $id = DBQuery::selectRowColumnValue(self::LTI_USER_TABLE, 'id', $ltiUserKey, 'key') 
              ?: DBQuery::insert(self::LTI_USER_TABLE, array('key'=>$ltiUserKey));
        
        return new LTIUser($id);
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
            'lis_person_name_given'=>'name_given', 
            'lis_person_name_family'=>'name_family',
            'lis_person_name_full'=>'name_full',
            'lis_person_contact_email_primary'=>'email'
        );
    }
}