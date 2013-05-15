# Shibboleth-LTI Bridge

This code presents an example of an LTI Provider that bridges Shibboleth and LTI identity provisioning.

## Approach

This approach includes three main components:

1. When a Tool Consumer is going to perform an LTI Launch and it knows that the user is logged in via Shibboleth, it should pass an additional `is_shibboleth` flag to the Tool Provider.
2. When a Tool Provider receives the `is_shibboleth` flag as part of an LTI Launch, it provisions the LTI user-context-resource entities, stores a reference in the browser session and then sends the user to a Shibboleth-protected directory within the Tool Provider.
3. When a user arrives at a Shibboleth-protected directory within the Tool Provider, it associates the browser session with a Shibboleth identity and then binds that Shibboleth identity to any LTI identities bound to the browser session.

The user may thus enter in several ways:

* Via LTI Launch without Shibboleth session, non-persistently binding the launch to a browser session.
* Via LTI Launch with Shibboleth session, persistently binding the launch to the Shibboleth session.
* Via Shibboleth session, retrieving any LTI Launches formerly bound to the Shibboleth session.

This scheme separates the concepts of session, identity and authorization:

* **Session** is a browser interaction with zero or one Shibboleth sessions and zero or more LTI launches
* **Identity** is defined either by a Shibboleth session or implicitly by a collection of LTI launches
* **Authorization** defined by zero or more LTI Launches that provision contexts and resources

When a user has not been identified by Shibboleth, identity is defined implicitly as a n-tuple of LTI launches that offer non-persistent authorization. Once identified by Shibboleth, identity becomes persistent, including both LTI launches within the current session as well as those in previous sessions associated with the Shibboleth identity.

## Scope

The approach presented here is meant as a proof-of-concept that may be used, modified and extended to fit real-world cases.

For example, while it provisions LTI user-context-resource relations and binds LTI launches to a Shibboleth identity, it does not go further with its own domain logic. In the real world, these contexts and resources would likely include its own attributes in addition to the LTI ones; further, to support provisioning outside of LTI, an implementation might use a binding table to link LTI contexts and resources to its own contexts and resources. Neither are presented here, although a foundation is laid that can easily be extended to support this.

Similarly, it stubs a number of features, including: 

1. Database logic is abstracted out into a `DBQuery` class under `/lib/shib-lti-bridge`, as well as sample database access layer under `/lib/util` a configuration file `/lib/util/DB.ini`, and a schema file `/db/schema.sql`. In most cases, the `DBQuery` class should be re-implemented to use the application's own database-access layer instead of the classes under `/lib/util`; in some cases, one may want to go further and directly modify the `LTIContext`, `LTIResource`, `LTIUser` and `ShibbolethUser` classes directly.
2. The Shibboleth attribute map is not known in this sample Tool Provider. As such, it is simply hardcoded into `$shibEppn` within `/www/shibboleth/index.php`. This should be replaced by a reference to the actual server-exposed Shibboleth ePPN value, and the `/www/shibboleth` directory should thus be protected by Shibboleth.
3. The `LTIUser::fromKey()`, `LTIContext::fromKey()` and `LTIResource::fromKey()` methods simply set up LTI users, contexts and resources. However, these should likely be extended with implementation-specific domain logic to set up actual components for tool use.
4. For extensibility, interfaces are provided for `LTIContext`, `LTIResource`, `LTIUser`, `Session` and `ShibbolethUser`. These interfaces may be used so that internals may be completely rewritten to fit with implementation-specific domain logic.
5. URLs are assumed by `$_SERVER` values in `/www/lti/index.php` and `/www/shibboleth/index.php`. These should be replaced by an application's actual pathing rules based on where these launch components exist.

Ultimately, a real-world implementation should discard `/lib/util`, re-implement `/lib/shib-lti-bridge/DBQuery.php` and replace the stubs in `/www/lti/index.php` and `/www/shibboleth/index.php`. Further, for most implementations, the rest of the `/lib/shib-lti-bridge` class definitions should likely be extended or rewritten to support domain logic.

## Routines

### Via LTI Launch

If an LTI Consumer detects that the user is authenticated via Shibboleth, it appends an attribute `is_shibboleth` with value `1` to the LTI Launch. When the launch then occurs, provisioning occurs as usual of the LTI user-context-resource assets, and then this LTI user identity is bound to the browser session.

After the LTI launch,

* if the user is already logged into the tool with Shibboleth, then the user identity from the LTI Launch is bound to the active Shibboleth session;
* else if `is_shibboleth` was passed with value `1`, the user is redirected to a Shibboleth-protected file that sets up the Shibboleth session and then binds the user identity from the LTI Launch to the active Shibboleth session;
* else, user is returned to the tool with an LTI Launch identity only.

### Via Shibboleth

A user enters via Shibboleth by passing through the Shibboleth-protected directory. This is similar to when the LTI Launch redirects them through this directory when the `is_shibboleth` flag is set, yet it is invoked manually.

A user may already have one or more LTI Launch sessions when they attempt to log in through Shibboleth. We are left with a couple of options:

1. Regard Shibboleth authentication as explicit identification of the browser session, which may already include LTI Launches. This approach associates LTI Launches in the browser session with the Shibboleth session in the same way as is initiates a Shibboleth session with the `is_shibboleth` LTI Launch flag.
2. Regard Shibboleth authentication as the start of a new session except when relayed through the LTI Launch. This approach drops LTI Launches bound to the browser session before starting up a Shibboleth session.

This sample code takes the former approach, but the latter alternative may be implemented with a few lines of code.

#### Alternative Method

If one wants to regard a Shibboleth login that's not part of the LTI launch as starting a new session that isn't affiliated with previous LTI Launches already in the browser session, two changes need to be made...

To `/www/lti/index.php`, add a `GET` property to `$rootUrl` that designates it's part of an LTI Launch:

```php
$rootUrl = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://')
               . $_SERVER['HTTP_HOST']
               . dirname(dirname($_SERVER['SCRIPT_NAME']))
               . '?lti';
```

To `/www/shibboleth/index.php`, detect if the `$_GET` property is set; if not, destroy LTI Launch data from the session before the Shibboleth login routine:

```php
if(!isset($_GET['lti']))
    Session::unbindAll();
```

## License

The Shibboleth-LTI Bridge example provider is open-source software licensed under the BSD 3-clause license. The full text of the license may be found in the `LICENSE` file.
