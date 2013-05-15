<?php

require_once dirname(__FILE__).'/Interface/ShibbolethUser.php';
require_once dirname(__FILE__).'/Interface/LTIUser.php';
require_once dirname(__FILE__).'/DBQuery.php';

class ShibbolethUser implements Interface_ShibbolethUser
{
    /**
     * Name of Shibboleth user table.
     */
    const SHIBBOLETH_USER_TABLE = 'shibboleth_user';
    
    /**
     * Name of binding table between Shibboleth users and LTI users.
     */
    const SHIBBOLETH_LTI_USER_TABLE = 'shibboleth_lti_user';
    
    /**
     * Primary key ID for Shibboleth user table.
     * 
     * @var int
     */
    protected $id;
    
    /**
     * Construct a Shibboleth user by unique integer identifier.
     * 
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }
    
    /**
     * Return the unique integer identifier for the Shibboleth user.
     * 
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Fetch an attribute associated with the Shibboleth user record.
     * 
     * @param string $attributeName
     * @return mixed
     */
    public function get($attributeName)
    {
        return DBQuery::selectRowColumnValue(self::SHIBBOLETH_USER_TABLE, $attributeName, $this->id);
    }
    
    /**
     * Returns an array of LTI users as associated in the persistence layer 
     * by the ->associateLTIUser() method.
     * 
     * @return Interface_LTIUser array
     */
    public function getLTIUsers()
    {
        $ids = DBQuery::selectRowsColumnValue(self::SHIBBOLETH_LTI_USER_TABLE, 'lti_user_id', $this->getId(), 'shibboleth_user_id');
        
        $ltiUsers = array();
        foreach($ids as $id)
            $ltiUsers[] = new LTIUser($id);
        return $ltiUsers;
    }
    
    /**
     * Bind an LTI user to the Shibboleth user in the persistence layer such 
     * that it is returned by the ->getLTIUsers() method.
     * 
     * @param Interface_LTIUser $ltiUser
     */
    public function associateLTIUser(Interface_LTIUser $ltiUser)
    {
        DBQuery::insertIgnoreDuplicate(self::SHIBBOLETH_LTI_USER_TABLE, array(
            'shibboleth_user_id' => $this->getId(),
            'lti_user_id' => $ltiUser->getId()
        ));
    }
    
    /**
     * Factory to produce a Shibboleth user by a Shibboleth UUID such as
     * eduPersonPrincipalName. If a user does not already exist for this 
     * UUID, then it should be created and returned.
     * 
     * @param string $shibbolethUUID
     * @return Interface_ShibbolethUser
     */
    public static function fromKey($shibbolethUUID)
    {
        $id = DBQuery::selectRowColumnValue(self::SHIBBOLETH_USER_TABLE, 'id', $shibbolethUUID, 'key') 
              ?: DBQuery::insert(self::SHIBBOLETH_USER_TABLE, array('key'=>$shibbolethUUID));
        
        return new ShibbolethUser($id);
    }
}