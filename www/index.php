<?php

/**
 * Placeholder script that shows active identities and context/resource
 * awareness. For actual LTI and Shibboleth functionality, see 
 * /www/lti/index.php and /www/shibboleth/index.php.
 * 
 * @see /www/lti/index.php
 * @see /www/shibboleth/index.php
 * @todo replace with actual tool index
 */

require_once dirname(dirname(__FILE__)).'/lib/shib-lti-bridge/Session.php';

echo '<h1>Example Provider with Shibboleth-LTI Bridge</h1>';

echo '<h2>User Identities</h2>';

echo '<h3>Shibboleth User Identity</h3>';

if($shibbolethUser = Session::getShibbolethUser())
{
    echo '<p>'.$shibbolethUser->get('key').' ['.$shibbolethUser->getId().']</p>';
    if($shibbolethUser)
    {
        $ltiBoundUsers = $shibbolethUser->getLTIUsers();
        if(count($ltiBoundUsers) > 0)
        {
            echo '<ul>';
            foreach($ltiBoundUsers as $ltiUser)
                echo '<li>'.$ltiUser->get('key').'</li>';
            echo '</ul>';
        }
        else
        {
            echo '<p><em>No LTI user bindings.</em></p>';
        }
    }
}
else
{
    echo '<p><em>No Shibboleth session initialized.</em></p>';
}

echo '<h3>LTI Launch Identities</h3>';

$ltiLaunchUsers = Session::getLTIUsers();
if(count($ltiLaunchUsers) > 0)
{
    echo '<ul>';
    foreach($ltiLaunchUsers as $ltiUser)
        echo '<li>'.$ltiUser->get('key').'</li>';
    echo '</ul>';
}
else
{
    echo '<p><em>No LTI user bindings launched in this session.</em></p>';
}

echo '<h2>Bound Resources and Identities</h2>';

$ltiContextUsers = isset($ltiBoundUsers) ? $ltiBoundUsers : $ltiLaunchUsers;

if(count($ltiContextUsers) > 0)
{
    echo '<table>';
    echo '<thead><tr><th>Resource Title</th><th>Resource Key</th><th>Context Key</th><th>User Key</th></tr></thead>';
    echo '<tbody>';
    foreach($ltiContextUsers as $ltiUser)
        foreach($ltiUser->getLTIContexts() as $ltiContext)
            foreach($ltiContext->getLTIResources() as $ltiResource)
                echo '<tr>'
                   . '<td style="padding:0 10px;">'.$ltiResource->get('title').'</td>'
                   . '<td style="padding:0 10px;">'.$ltiResource->get('key').'</td>'
                   . '<td style="padding:0 10px;">'.$ltiContext->get('key').'</td>'
                   . '<td style="padding:0 10px;">'.$ltiUser->get('key').'</td>'
                   . '</tr>';
    echo '</tbody>';
    echo '</table>';
}
else
{
    echo '<p><em>No launched resources.</em></p>';
}


if(!$shibbolethUser)
{
    echo '<p><a href="shibboleth">Shibboleth Login</a></p>';
}
    
if($shibbolethUser || isset($ltiBoundUsers) && count($ltiBoundUsers) > 0 || count($ltiLaunchUsers) > 0)
{
    echo '<p><a href="logout.php">Logout</a></p>';
}
