<?php

require_once dirname(__FILE__).'/LTIUser.php';

interface Interface_ShibbolethUser
{
    /**
     * Construct a Shibboleth user by unique integer identifier.
     * 
     * @param int $id
     */
    public function __construct($id);
    
    /**
     * Return the unique integer identifier for the Shibboleth user.
     * 
     * @return int
     */
    public function getId();
    
    /**
     * Fetch an attribute associated with the Shibboleth user record.
     * 
     * @param string $attributeName
     * @return mixed
     */
    public function get($attributeName);
    
    /**
     * Returns an array of LTI users as associated in the persistence layer 
     * by the ->associateLTIUser() method.
     * 
     * @return Interface_LTIUser array
     */
    public function getLTIUsers();
    
    /**
     * Bind an LTI user to the Shibboleth user in the persistence layer such 
     * that it is returned by the ->getLTIUsers() method.
     * 
     * @param Interface_LTIUser $ltiUser
     */
    public function associateLTIUser(Interface_LTIUser $ltiUser);
    
    /**
     * Factory to produce a Shibboleth user by a Shibboleth UUID such as
     * eduPersonPrincipalName. If a user does not already exist for this 
     * UUID, then it should be created and returned.
     * 
     * @param string $shibbolethUUID
     * @return Interface_ShibbolethUser
     */
    public static function fromKey($shibbolethUUID);
}