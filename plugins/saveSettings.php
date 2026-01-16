<?php

namespace MGB\uploadToLabarchives;

use \REDCap as REDCap;

global $Proj;

if (!isset($project_id)) {
    die('Project ID is a required field');
}


foreach (json_decode(stripslashes($_POST['data'])) as $key => $value) {
    $val= $value;
    $module->setSystemSetting($key, json_encode($value));
}

$userInfo =json_decode(stripslashes($module->getSystemSetting(USERID)), TRUE);

$selectedRegion = $userInfo[USERID]['region'];
$module->setLAAKID($selectedRegion);
$module->setLAAPIURL($selectedRegion);
$module->setLA_PWD($selectedRegion);
$module->setLA_SSO_ENTITY_ID($selectedRegion);

$akid = $module->getLAAKID();
$endpoint = "user_access_info";
$time = strtotime(date('Y-m-d H:i:s', strtotime(' +5 minutes ')));
$time = $time * 1000;

$key = $module->getLA_PWD(); // <<-- Add the MGB Password here This is the MGB / Partners Lab Archvies PWD

$user = $userInfo[USERID]['userEmail']; // <<--- Add your Email here
$user_token = $userInfo[USERID]['appPass']; // <<-- ADD YOUR Token HERE

$sha512_value = hash_hmac("sha512", $akid.$endpoint.$time, $key, true);
$base64 = base64_encode($sha512_value);;

$params = [
    "login_or_email"    => $user,
    "password"          => $user_token,
    "akid"              => $akid,
    "expires"           => $time,
    "sig"               => $base64
];

$url = "https://api.labarchives.com/api/users/user_access_info?".http_build_query($params);

$xml = simplexml_load_string(trim($module->curlShell('GET', $url)), "SimpleXMLElement", LIBXML_NOCDATA);

$UI=$xml->id[0];

$userInfo[USERID]['UID']= $module->encrypt_config_string(strval($UI));

$module->setSystemSetting(USERID, json_encode($userInfo));