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

$accountInfoURL = $module->user_account_info($userInfo[USERID]['userEmail'],$userInfo[USERID]['appPass']);

$xml = simplexml_load_string(trim($module->curlShell('GET', $accountInfoURL)), "SimpleXMLElement", LIBXML_NOCDATA);

$UI=$xml->id[0];

$userInfo[USERID]['UID']= $module->encrypt_config_string(strval($UI));

$module->setSystemSetting(USERID, json_encode($userInfo));