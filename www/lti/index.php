<?php

/**
 * LTI user-context-resource provisioning into the browser session, relaying to 
 * Shibboleth session initiation in /www/shibboleth/index.php if the LTI Launch 
 * includes the is_shibboleth parameter with value 1.
 * 
 * @see /www/shibboleth/index.php
 * @todo replace stubs for $rootUrl and $shibbolethUrl
 */

/**
 * STUB for URL to tool web root directory 
 */
$rootUrl = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://')
               . $_SERVER['HTTP_HOST']
               . dirname(dirname($_SERVER['SCRIPT_NAME']));

/**
 * STUB for URL to Shibboleth-protected directory
 */
$shibbolethUrl = $rootUrl.'/shibboleth';

/**
 * Required libraries
 */
require_once dirname(dirname(dirname(__FILE__))).'/lib/ims-blti/blti.php';
require_once dirname(dirname(dirname(__FILE__))).'/lib/shib-lti-bridge/LTIUser.php';
require_once dirname(dirname(dirname(__FILE__))).'/lib/shib-lti-bridge/LTIContext.php';
require_once dirname(dirname(dirname(__FILE__))).'/lib/shib-lti-bridge/LTIResource.php';
require_once dirname(dirname(dirname(__FILE__))).'/lib/shib-lti-bridge/Session.php';

/**
 * Use BLTI library to initialize LTI context
 */
$context = new BLTI("secret", false, false);

if($context->valid) 
{
    /**
     * SGenerate LTI resource (existing or new)
     */
    $ltiResource = LTIResource::fromKey($context->getResourceKey());
    $ltiResource->updateAttributes(array_intersect_key($_POST, LTIResource::getAttributeMap()));
    
    /**
     * Generate LTI context (existing or new) and associate resource with it
     */
    $ltiContext = LTIContext::fromKey($context->getCourseKey());
    $ltiContext->updateAttributes(array_intersect_key($_POST, LTIContext::getAttributeMap()));
    $ltiContext->associateLTIResource($ltiResource);
    
    /**
     * Generate LTI user (existing or new) and associate context with it
     */
    $ltiUser = LTIUser::fromKey($context->getUserKey());
    $ltiUser->updateAttributes(array_intersect_key($_POST, LTIUser::getAttributeMap()));
    $ltiUser->associateLTIContext($ltiContext, $context->isInstructor(), $_POST['roles']);
    
    /**
     * Bind Shibboleth user to browser session
     */
    Session::bindLTIUser($ltiUser);
    
    /**
     * Associate LTI user with Shibboleth user if already authenticated
     */
    if($shibbolethUser = Session::getShibbolethUser())
        $shibbolethUser->associateLTIUser($ltiUser);
    
    /**
     * Redirect to the Shibboleth-protected directory to initialize a 
     * Shibboleth session, if the tool provider includes an is_shibboleth
     * attribute in it's LTI call; otherwise, return to the tool root.
     */
    if(!$shibbolethUser && isset($_POST['is_shibboleth']) && $_POST['is_shibboleth'] == '1')
        header('Location: '.$shibbolethUrl);
    else
        header('Location: '.$rootUrl);
} 
else
{
    /**
     * Throw error if BLTI fails to launch LTI context
     */
    echo "An error was encountered during LTI launch.";
}
