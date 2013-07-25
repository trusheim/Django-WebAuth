# Django-Webauth 1.1

## About
This set of scripts is designed to allow Django apps to use Stanford WebAuth without actually having to have WebAuth installed on the local server. The script uses hashing with symmetric keys (which must be kept secret for security) to make sure a user has been validated on the remote end.

This request allows a developer to use standard Django authorization means - including the @login_required decorator, standard user account control for permissions, etc., to authorize users, and uses a script located on a remote server to basically provide the login services.

The middleware is setup to automatically create user accounts for anyone who logs into the site with an SUID. If this is not desired behavior (e.g., you want a site to be closed to approved users, and no one else can login), you'll have to change the middleware so that it does not auto-create user accounts, or alternatively give your subset of authorized users a specific privilege that is basically "global site privilege".

## Important update
v. 1.0 of this script had a **security flaw** that permitted session hijacking attacks. The code has been fixed, and new installation instructions are included below. Please update immediately.

## Installation

* Choose a SHARED_SECRET and put that in the webauth-host/wa-authenticate.php script
* **v.1.1:** choose a RETURN_URL and put that in the webauth-host/wa-authenticate.php script. The return URL provided from the server is ignored starting in version 1.1.  
* Install the wa-authenticate.php script in a Stanford WWW cgi-bin directory (you'll have to request this from Stanford, and you'll have to put it in the cgi-bin root)
* Make sure you add the .htaccess file (rename to .htaccess)
* Edit your Django settings to add:
	* INSTALLED_APPS = ( ... 'webauth' ... )
	* MIDDLEWARE_CLASSES = ( ... 'webauth.middleware.WebauthMiddleware' ... )
	* SESSION_EXPIRE_AT_BROWSER_CLOSE = True
	* LOGIN_URL = '/webauth/login/'
	* WEBAUTH_SHARED_SECRET = 'YOUR_SECRET_KEY'
	* WEBAUTH_URL = 'https://www.stanford.edu/~(YOUR USER ACCOUNT)/cgi-bin/wa-authenticate.php'
	* BASE_URL = ' .... '
* Remove the standard Django auth middleware.
* In urls.py, add:
	* admin.site.login_template = 'webauth/admin_redirect.html'
	* urlpatterns = patterns('', ... (r'^webauth/', include('webauth.urls')), ... )
* Important: if you are in a production environment, DO NOT install wa-authenticate-test.php, and DO NOT install your shared secret into that script; otherwise, malicious users can use that script to login as anyone!

## Examples
Here's a typical request cycle:

* Django site: User requests a protected resource (@login_required decorator)
* Django site: Request is caught in the decorator, and passed to the login action
* Django site: Redirects user's browser to the hosted PHP script
* Hosted PHP script: Is protected by a .htaccess file that forces login, so it redirects to the central WebAuth service
* WebAuth service: redirects back to the hosted PHP script, giving us the info we seek (and it's impossible to forge, or WebAuth is broken)
* Hosted PHP script: redirects back to Django server with the hashed information
* Django site: checks hash with shared secret, and logs user in if they're all cool.

## TODO
* Support LDAP user access group passing (i.e. suPrivilegeGroup and suAffiliation)
* Support arbitrary LDAP data passing (data is very limited by Stanford www-data user privileges)

## Copyright & License
Copyright 2010-11 Stephen Trusheim. All rights reserved. No warranties granted. This work is licensed under the Creative Commons Attribution 3.0 Unported License. To view a copy of this license, visit http://creativecommons.org/licenses/by/3.0/ or send a letter to Creative Commons, 444 Castro Street, Suite 900, Mountain View, California, 94041, USA.