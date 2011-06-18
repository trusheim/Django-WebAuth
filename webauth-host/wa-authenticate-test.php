<?php

$SHARED_SECRET = 'a2d24bf589b1dea3d83f317019e2ff83bf430b8d1c2a3f741dbf7d72f196d8cf6a6d113de356dc77';

function generateNonce($length) {
        $charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $key = '';
        for ($i=0; $i<$length; $i++) {
            $key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
        }
        return $key;
}

$protocol = "WA_1";

$from = $_GET['from'];

$sunetId = $_GET['login_as'];
if ($sunetId == '') {
    $sunetId = $_SERVER['WEBAUTH_USER'];
}
$sunetId_64 = base64_encode($sunetId);

$displayName = $_GET['display_name'];
if ($displayName == '') {
    $displayName = $_SERVER['WEBAUTH_LDAP_DISPLAYNAME'];
}
$displayName_64 = base64_encode($displayName);

if ($sunetId == "") {
    print "<form method = 'GET' action = ''>From: <input type = 'text' name = 'from' value = '$from' length = 100><br />Login: <input type = 'text' name = 'login_as' value='$sunetId'  /><br />DName: <input type = 'text' name = 'display_name' value = '$displayName' /><br /><input type = 'submit'></form>";
    exit;
}

$nonce = generateNonce(16);

$hash = sha1($SHARED_SECRET . $nonce . $sunetId);
$hashStr = $nonce . '$' . $hash;



header("Location: $from&WA_prot=$protocol&WA_user=$sunetId_64&WA_hash=$hashStr&WA_name=$displayName_64");
