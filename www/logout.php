<?php

/**
 * Placeholder script that unbinds any active identities from the browser 
 * session, effectuating a logout.
 * 
 * @see /www/lti/index.php
 * @see /www/shibboleth/index.php
 * @todo replace with actual tool logout (still using Session::unbindAll())
 */

require_once dirname(dirname(__FILE__)).'/lib/shib-lti-bridge/Session.php';

Session::unbindAll();

echo '<h1>Example Provider with Shibboleth-LTI Bridge</h1>';

echo '<p>Logout successful!</p>';

echo '<p><a href="shibboleth">Shibboleth Login</a></p>';
echo '<p><a href="index.php">Unauthenticated View</a></p>';