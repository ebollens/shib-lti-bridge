<?php

/**
 * Shibboleth session initiation with bindings to any existing LTI Launch
 * user-context-resource bindings already associated with the browser session.
 * See /www/lti/index.php for the relaying LTI Launch script, when the
 * Shibboleth session is initiated as part of the LTI Launch.
 * 
 * @see /www/lti/index.php
 * @todo replace stubs for $shibEppn and $indexUrl
 */

/**
 * STUB for Shibboleth eduPersonPrincipalName
 */
$shibEppn = 'test@localhost';

/**
 * STUB for URL to tool index 
 */
$indexUrl = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://')
               . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME'])) . '/index.php';

/**
 * Required libraries
 */
require_once dirname(dirname(dirname(__FILE__))).'/lib/shib-lti-bridge/Session.php';
require_once dirname(dirname(dirname(__FILE__))).'/lib/shib-lti-bridge/ShibbolethUser.php';

/**
 * Initialize the Shibboleth user
 */
$shibUser = ShibbolethUser::fromKey($shibEppn);

/**
 * Bind Shibboleth user to browser session
 */
Session::bindShibbolethUser($shibUser);

/**
 * Associate Shibboleth user with LTI users bound to session
 */
foreach(Session::getLTIUsers() as $ltiUser)
    $shibUser->associateLTIUser($ltiUser);

/**
 * Return to the tool index
 */
header('Location: '.$indexUrl);
