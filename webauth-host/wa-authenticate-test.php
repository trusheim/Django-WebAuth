<?php

$SHARED_SECRET = 'test';

$protocol = "WA_3";

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

$mac = hash_hmac('sha256', $sunetId_64 . '|' . $displayName_64 . '|' . $protocol, $SHARED_SECRET);

$return = $_GET['return'];

$next = $_GET['next'];
$next_64 = base64_encode($next);

$submitted = isset($_GET['continue']);
if (!$submitted) {
    print<<<ENDL
<form method = 'GET' action = ''>
		Return: <input type = 'text' name = 'return' value = '$return' size = 100><br />
		Next: <input type = 'text' name = 'next' value = '$next' size = 100><br />
		Login: <input type = 'text' name = 'login_as' value = '$sunetId' /><br />
		DName: <input type = 'text' name = 'display_name' value = '$displayName' /><br />
		<input type = 'submit' name = 'continue' value = 'Login'>
	</form>

ENDL
    exit;
}

header("Location: $return?WA_prot=$protocol&WA_user=$sunetId_64&WA_mac=$mac&WA_name=$displayName_64&WA_next=$next_64");