<?php

require_once dirname(__FILE__).'/Interface/Session.php';
require_once dirname(__FILE__).'/LTIUser.php';
require_once dirname(__FILE__).'/ShibbolethUser.php';

/**
 * Stub implementation of session uses PHP sessions.
 */
session_start();

class Session
{
    /**
     * Name of session variable that stores an array of LTI user ids
     */
    const SESSION_LTI_USERS = '__ltiUserIds';
    
    /**
     * Name of a session variable that stores a Shibboleth user id
     */
    const SESSION_SHIBBOLETH_USER = '__shibbolethUserId';
    
    /**
     * Bind an LTI user into the browser session such that it will be returned 
     * when queried by ::getLTIUsers(). This method may be used to bind one or 
     * more LTI users to a browser session.
     * 
     * @param Interface_LTIUser $user
     */
    public static function bindLTIUser(Interface_LTIUser $user)
    {
        $ltiUsers = array_key_exists(self::SESSION_LTI_USERS, $_SESSION) ? $_SESSION[self::SESSION_LTI_USERS] : array();
        
        if(!in_array($user->getId(), $ltiUsers))
            $ltiUsers[] = $user->getId();
        
        $_SESSION[self::SESSION_LTI_USERS] = $ltiUsers;
    }
    
    /**
     * Return an array of LTI users that have been saved into browser session 
     * persistence by ::bindLTIUser().
     * 
     * @return Interface_LTIUser array
     */
    public static function getLTIUsers()
    {
        $ltiUsers = array();
        
        if(array_key_exists(self::SESSION_LTI_USERS, $_SESSION))
            foreach($_SESSION[self::SESSION_LTI_USERS] as $ltiUserId)
                $ltiUsers[] = new LTIUser($ltiUserId);
        
        return $ltiUsers;
    }
    
    /**
     * Bind a Shibboleth user into the browser session such that it will be 
     * returned when queried by ::getShibbolethUser(). This method may be used 
     * only to bind a single Shibboleth user to a browser session, and if 
     * invoked a second time, it will associate the newly passed Shibboleth 
     * user rather than the formerly bound Shibboleth user.
     * 
     * @param Interface_ShibbolethUser $user
     */
    public static function bindShibbolethUser(Interface_ShibbolethUser $user)
    {
        $_SESSION[self::SESSION_SHIBBOLETH_USER] = $user->getId();
    }
    
    /**
     * Return the Shibboleth user that has been saved into browser session 
     * persistence by ::bindShibbolethUser().
     * 
     * @return Interface_ShibbolethUser array
     */
    public static function getShibbolethUser()
    {
        return array_key_exists(self::SESSION_SHIBBOLETH_USER, $_SESSION) && $_SESSION[self::SESSION_SHIBBOLETH_USER] 
                ? new ShibbolethUser($_SESSION[self::SESSION_SHIBBOLETH_USER]) 
                : false;
    }
    
    /**
     * Drop all bindings of LTI users and any binding of a Shibboleth user, 
     * used such as when performing a logout.
     */
    public static function unbindAll()
    {
        $_SESSION[self::SESSION_LTI_USERS] = array();
        $_SESSION[self::SESSION_SHIBBOLETH_USER] = false;
    }
}