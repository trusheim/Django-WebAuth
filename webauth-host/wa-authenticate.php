<?php

/******************************************
 * Simple PHP Stanford WebAuth script
 * ---
 * Allows an external server to have trusted Stanford WebAuth authentication without setting up mod_webauth, which
 * is annoying to setup.
 *
 * Be sure to change the $SHARED_SECRET!
 * Be sure to set the $RETURN_URL (e.g. https://django_site.com/webauth/login)
 *
 * Security fixes by: Jack Chen (jackchen@cs.stanford)
 * Maintained by: Stephen Trusheim (tru@sse.stanford)
 * Original Author: Quinn Slack (sqs@cs.stanford)
 */


$SHARED_SECRET = 'FILL_ME';
$RETURN_URL = 'FILL_ME';		  // MUST be HTTPS to be secure

// ---------------------------------------------


$protocol = "WA_3";

$sunetId = $_SERVER['WEBAUTH_USER'];
$sunetId_64 = base64_encode($sunetId);
$displayName_64 = base64_encode($_SERVER['WEBAUTH_LDAP_DISPLAYNAME']);

$mac = hash_hmac('sha256', $sunetId_64 . '|' . $displayName_64 . '|' . $protocol, $SHARED_SECRET);

// v.1.1: this URL is ignored except in development mode.
// Security note: if you change the code to accept arbitrary return URLs,
// then you MUST include the URL in the MAC
// $return = $_GET['return'];

$next = $_GET['next'];
$next_64 = base64_encode($next);

header("Location: $RETURN_URL?WA_prot=$protocol&WA_user=$sunetId_64&WA_mac=$mac&WA_name=$displayName_64&WA_next=$next_64");