<?php
require_once dirname(__FILE__).'/LTIUser.php';
require_once dirname(__FILE__).'/ShibbolethUser.php';

interface Interface_Session
{
    /**
     * Bind an LTI user into the browser session such that it will be returned 
     * when queried by ::getLTIUsers(). This method may be used to bind one or 
     * more LTI users to a browser session.
     * 
     * @param Interface_LTIUser $user
     */
    public static function bindLTIUser(Interface_LTIUser $user);
    
    /**
     * Return an array of LTI users that have been saved into browser session 
     * persistence by ::bindLTIUser().
     * 
     * @return Interface_LTIUser array
     */
    public static function getLTIUsers();
    
    /**
     * Bind a Shibboleth user into the browser session such that it will be 
     * returned when queried by ::getShibbolethUser(). This method may be used 
     * only to bind a single Shibboleth user to a browser session, and if 
     * invoked a second time, it will associate the newly passed Shibboleth 
     * user rather than the formerly bound Shibboleth user.
     * 
     * @param Interface_ShibbolethUser $user
     */
    public static function bindShibbolethUser(Interface_ShibbolethUser $user);
    
    /**
     * Return the Shibboleth user that has been saved into browser session 
     * persistence by ::bindShibbolethUser().
     * 
     * @return Interface_ShibbolethUser array
     */
    public static function getShibbolethUser();
    
    /**
     * Drop all bindings of LTI users and any binding of a Shibboleth user, 
     * used such as when performing a logout.
     */
    public static function unbindAll();
}