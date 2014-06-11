from hashlib import sha256
import hmac
from django.conf import settings
from models import WebauthUser
from django.contrib.auth.models import User

WEBAUTH_VERSION = "WA_3"

class WebauthVersionNotSupported(Exception):
    """
    Error occurs only when the server providing login services had a version that was not equal to ours.
    """
    pass

def WebauthLogin(request, mac, webauth_version, username_base64, username, name_base64, name):
    """
    Completes the login process for a Webauth user; checks the MAC to ensure the user should be able to login,
    then sets the appropriate parameter so that the middleware logs in the user.

    The session will not be active until the middleware runs again (i.e., the next page load.)
    """
    if webauth_version != WEBAUTH_VERSION:
        raise WebauthVersionNotSupported

    expected_mac = hmac.new(settings.WEBAUTH_SHARED_SECRET,
                            username_base64 + '|' + name_base64 + '|' + webauth_version,
                            sha256).hexdigest()

    # Security note: to avoid timing attacks, you should use crypto-specific functions like
    # hmac.compare_digest to check equality. However, if you're using older versions of Python,
    # this isn't available, so you'll probably just use == to check equality. If you're using this
    # code and worried about timing attacks, you're probably doing it wrong.
    if hmac.compare_digest(mac, expected_mac):
        # create Django user for the WebAuth'd person if they don't exist
        # session will not be active till next pageload
        if WebauthUser.objects.filter(username__exact=username).count() == 0:
            WebauthCreate(username, name)

        request.session['wa_username'] = username
        return True
    else:
        return False

def WebauthCreate(username, full_name):
    """
    Creates a WebauthUser from the provided username + full name. If the given username already exists, it converts the user
    into a WebauthUser.
    """
    if WebauthUser.objects.filter(username__exact=username).count() > 0:
        return
        
    authuser_obj = User.objects.filter(username__exact=username)
    if not authuser_obj.exists():
        user = WebauthUser()
        user.new_webauth(username, full_name)
    else:
        user = authuser_obj.get()
        user.__class__ = WebauthUser
        user.new_webauth(username, full_name)

def WebauthLogout(request):
    """
    Logs out a WebauthUser session.
    """
    del request.session['wa_username']
