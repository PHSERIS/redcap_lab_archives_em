<?php
namespace MGB\uploadToLabarchives;
require_once APP_PATH_DOCROOT."Config/init_functions.php"; // just in case

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;
use \Logging as Logging;
use \DataExport as DataExport;
use \REDCap as REDCap;
class uploadToLabarchives extends \ExternalModules\AbstractExternalModule
{
    /***
     * set of functions for encrypting UID
     */
    function redcap_module_system_enable($version){
        $this->set_module_salt();
    }

    function set_module_salt() {
        $em_salt = $this->getSystemSetting('la-salty-salt');
        if ( !isset($em_salt) || is_null($em_salt) || strlen(trim($em_salt))<1 ) {
            // need to set a new salt
            $em_salt = $this->generate_crypto_hash();
            $this->setSystemSetting('la-salty-salt',$em_salt);
        }
        return true;
    }

    function generate_crypto_hash ( ) {
        return hash("sha256", $this->get_random_string(32));
    }

    function get_random_string ( $n = 6 ) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    function get_module_salt() {
        $em_salt = $this->getSystemSetting('la-salty-salt');
        if ( !isset($em_salt) || is_null($em_salt) || strlen(trim($em_salt))<1 ) {
            // need to set a new salt
            $em_salt = $this->generate_crypto_hash();
            $this->setSystemSetting('la-salty-salt',$em_salt);
        }
        return $em_salt;
    }

    /**
     * Encrypt the data and return it
     * @param $string_to_encrypt
     * @param $project_id
     * @return false|string
     */
    function encrypt_config_string ( $string_to_encrypt) {
        $salt = $this->get_module_salt(); // get the salt
        $scrambled = encrypt($string_to_encrypt, $salt);
        return $scrambled;
    }

    /**
     * Decrypt
     * @param $string_to_decrypt
     * @param $project_id
     * @return mixed
     */
    function decrypt_config_string ( $string_to_decrypt) {
        $salt = $this->get_module_salt(); // get the salt
        $unscrambled = decrypt($string_to_decrypt, $salt);
        return $unscrambled;
    }

    /***
     * End of encryption functions ------------------------------------------
     */

    /***
     * Defining Getters and Setters for maintenance sake!
     */
    public function setLAAPIURL($item){
        $this->LA_API_URL = $this->getSubSettings('la-api-region-list')[$item]['region-api-url'];
    }

    public function getLAAPIURL(){
        return $this->LA_API_URL;
    }

    public function setLAAKID($item){
        $this->LA_AKID = $this->getSubSettings('la-api-region-list')[$item]['region-akid'];
    }

    public function getLAAKID(){
        return $this->LA_AKID;
    }

    public function setLA_PWD($item){
        $this->LA_PWD = $this->getSubSettings('la-api-region-list')[$item]['region-inst-password'];
    }

    public function getLA_PWD(){
        return $this->LA_PWD;
    }

    public function setLA_SSO_ENTITY_ID($item){
        $this->LA_SSO_ENTITY_ID = urlencode($this->getSubSettings('la-api-region-list')[$item]['region-sso-entity-id']);
    }

    public function getLA_SSO_ENTITY_ID(){
        return $this->LA_SSO_ENTITY_ID;
    }

    // End of getters and setters


    function redcap_every_page_top($project_id)
    {
        global $Proj;

        foreach($this->getSubSettings('la-api-region-list') as $k=>$v){
            $regionList[$k] = $v["region-name"];
        }

        $cssFile = $this->getUrl("css/laModal.css", $noAuth = false, $useApiEndpoint = false);

        if (strlen(strstr(PAGE,"DataExport/index.php")) > 0
            and $_GET['pid'] == $project_id
            and !is_null( htmlspecialchars($_GET['report_id'],ENT_QUOTES) )){

            if(htmlspecialchars($_GET['report_id'],ENT_QUOTES) == "ALL") return;

            $modalContent = $this->minifier($this->modalContent($project_id));

            $autoOpenModal = $_GET['s'] == 1 && is_null($_GET['t']) ? '' : 'closed';

            $scriptExport = <<<SCRIPT
<script type="text/javascript"> 
function waitForElm(selector) {
    return new Promise(resolve => {
        if (document.querySelector(selector)) {
            return resolve(document.querySelector(selector));
        }

        const observer = new MutationObserver(mutations => {
            if (document.querySelector(selector)) {
                resolve(document.querySelector(selector));
                observer.disconnect();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });
}

waitForElm('#report_div').then((elm) => {
    $('#sub-nav').after("<div style=\"\"><div class=\"\" style=\"float:left;width:348px;padding-bottom:15px;\"><div class=\"font-weight-bold\">Additional funtionalities:</div></div><div class=\"d-print-none\" style=\"float:left;\"><div><button id=\"open-button\" class=\"report_btn jqbuttonmed ui-button ui-corner-all ui-widget\" style=\"font-size:12px; margin-left: 2px;\"><i class=\"fas fa-upload\"></i> Upload to LabArchives</button>		</div>	</div>	<div class=\"clear\"></div></div>");
    
   $('head').append('<link rel="stylesheet" type="text/css" href="{$cssFile}">');
    
    var newdiv1 = $('<div class="modal-overlay {$autoOpenModal}" id="modal-overlay"></div><div class="modal {$autoOpenModal}" id="modal"><div class="ui-dialog-titlebar ui-corner-all ui-widget-header ui-helper-clearfix ui-draggable-handle header-padding"> <span id="ui-id-2" class="ui-dialog-title"><span style="vertical-align:middle;font-size:15px;"><i class=\"fas fa-upload\"></i> Upload to LabArchives			</span>		</span>		  <button class="close-button ui-button ui-corner-all ui-widget ui-button-icon-only ui-dialog-titlebar-close" id="close-button">		<span class="ui-button-icon ui-icon ui-icon-closethick"></span>		<span class="ui-button-icon-space"> </span>	  </button>	</div>  {$modalContent}</p> </div></div>');
    
    $( "body" ).append(newdiv1);    
    
    $("#this_report_title").after('<div id="actionMsg" class="green" style="max-width: 800px; padding: 15px 25px; margin: 20px 0px; text-align: left; display: none;"><span>The requested report has been uploaded to LabArchives.</span></div>');
       
   var modal = document.querySelector("#modal");
   var modalOverlay = document.querySelector("#modal-overlay");
   var closeButton = document.querySelector("#close-button");
   var openButton = document.querySelector("#open-button");
//
closeButton.addEventListener("click", function() {
 modal.classList.toggle("closed");
 modalOverlay.classList.toggle("closed");
});
//
openButton.addEventListener("click", function() {
 modal.classList.toggle("closed");
 modalOverlay.classList.toggle("closed");
});
    
});                     
</script>
SCRIPT;
            print $scriptExport;

            if($_GET['t']==2){
                $this->successfulTransfer();
            }
        }
    }


    function get_uid($sso = false){

        $browser = "chrome";

        $urls = $this->get_institutional_login_urls_via_api();

        $xml = simplexml_load_string(trim($this->curlShell('GET', $urls)), "SimpleXMLElement", LIBXML_NOCDATA);

        $login_url = $xml->{'login-url'};

        $akid = $xml->request->akid;

        $location = $this->build_remote_login_url($login_url, $akid);
        if($sso){
            $this->requestSSO($location);
        }

        return $location;
    }

    function get_institutional_login_urls_via_api(){
        $api_class = "utilities";
        $api_call =  "institutional_login_urls";
        $request_params = null;
        $email = null;
        $call_type = "notebook";

        $args = $this->format_args($api_class, $api_call, $email, $call_type, $request_params);
        $result = $this->get_connection($args);

      return $result;
    }

    function format_args($api_class, $api_call, $email, $call_type, $request_params){

        $args = [];
        $args["api_class"] = $api_class;
        $args["api_call"] = $api_call;

        if($email != null){
            $args["email"] = $email;
        }

        $args["call_type"] = $call_type;
        $args["request_params"] = $request_params;

        return $args;
    }

    function get_connection($args){
      $email_param = isset($args["email"]) ? $args["email"] : null;
      $call_type_param = isset($args["call_type"]) ? $args["call_type"] : 'notebook';
      $additional_params = isset($args["request_params"]) ? $args["request_params"] : null;


      if (is_null($email_param)) {
          return $this->lab_archives_call($args["api_class"], $args["api_call"], null, $call_type_param, $additional_params);
      } else{
          return$this->lab_archives_call($args["api_class"], $args["api_call"], $email_param, $call_type_param, $additional_params);
      }
    }

    function lab_archives_call($api_class, $api_call, $user_email='', $call_type=null, $request_params=null)
    {
        $request_expires = number_format(microtime(true)*1000,0,"","");
        $signature = $this->build_signature($api_call, $request_expires, $call_type);

        $user_signature = $this->build_signature('user_access_info', $request_expires, $call_type);

        $auth_params = $this->build_auth_params($request_expires, $signature, $call_type);

        $email_string = ($user_email == "") ? '' : "email={$user_email}";

        if($request_params == '' ){
            $additional_params = '';
        } else {
            if($user_email == '' && substr($request_params, 0, 1) == "&") { /// work on getting the first character from request param and compare it to &
                $request_params[0] = '';
            }
            $additional_params =  utf8_encode($request_params); // look into this line of code - what is the PHP URI encode command
      }

        $this->getLAAPIURL();
        return "{$this->getLAAPIURL()}{$api_class}/{$api_call}?{$email_string}{$additional_params}{$auth_params}";
    }

    function build_signature($api_call, $request_expires, $call_type = null){

        $akid = $this->get_akid($call_type);
        $pwd = $this->get_pwd($call_type);

        $base = "{$akid}{$api_call}{$request_expires}";
        $key = $pwd;

        $sha1 = hash_hmac('SHA1', $base, $key, TRUE);
        $base64 = base64_encode($sha1);

        return urlencode($base64);
    }

    function  build_auth_params($request_expires, $signature, $call_type = null)
    {
        $akid = $this->getLAAKID();
        return "&akid={$akid}&expires={$request_expires}&sig={$signature}";
      }

      function get_akid($call_type = null){
        if(is_null($call_type) || strtolower($call_type) == "notebook"){
            return $this->getLAAKID();
        }

        if(strtolower($call_type) == "redcap"){
            return $this->REDCAP_AKID;
        }
      }

      function get_pwd($call_type = null)
      {

          if (is_null($call_type) || strtolower($call_type) == "notebook"){
              return $this->getLA_PWD();
          }

          if (strtolower($call_type) == "redcap"){
              return $this->REDCAP_PWD;
          }

      }

      function build_remote_login_url($login_url, $akid){
          $browser = "Chrome";

          $url = $this->get_institutional_login_urls_via_api();

          $xml = simplexml_load_string(trim($this->curlShell('GET', $url)), "SimpleXMLElement", LIBXML_NOCDATA);

          $login_url = $xml->{'login-url'};
          $response_url = $xml->{'response-url'};
          $akid = $xml->request->akid;

          $expires = number_format(microtime(true)*1000,0,"","");
          $sig = $this->build_signature("shibboleth", $expires, "notebook");

           return $location = "{$login_url}?akid={$akid}&expires={$expires}&sig={$sig}";
      }

      function requestSSO($location){
          $redirect =<<<SCRIPT
<script type="text/javascript">
  $(document).ready(function(){
      window.open("{$location}");      
  });
</script>
SCRIPT;
          print $redirect;
      }

    function  user_notebook_names_and_ids($uid, $la_user_or_email){
      $nb_list = $this->get_notebook_list($uid, $la_user_or_email);
      $names_and_ids = [];

          foreach($nb_list['notebooks']['notebooks'] as $k=>$notebook){
              $name = $notebook['name'];
              $names_and_ids[$name] = $notebook['id'];
          }
      return $names_and_ids;
    }

    function get_notebook_list($uid, $email){
        $api_class = "notebooks";
      $api_call = "notebooks_with_user";
      $call_type = "notebook";

      $uid = $uid;

      $request_params = "&uid={$uid}&entityID={$this->getLA_SSO_ENTITY_ID()}";

      $args = $this->format_args($api_class, $api_call, $email, $call_type, $request_params);

      return $this->get_connection($args);
    }

    function user_account_info($email,$appPassword){
        $api_class = 'user_access_info';
        $api_call = 'user_access_info';

        $request_expires = number_format(microtime(true)*1000,0,"","");
        $call_type = "notebook";

        $akid = $this->getLAAKID();
        $user_signature = $this->build_signature('user_access_info', $request_expires, $call_type);

        $args = $this->format_args($api_class, $api_call, $email, $call_type, '');

        $la_api_url = $this->getLAAPIURL();

        $url_UI = $la_api_url . "users/user_access_info?" .
            "login_or_email={$email}&password={$appPassword}".
            "&akid={$akid}&sig={$user_signature}&expires={$request_expires}";

        return $url_UI;
    }

    /**
     * Encode data to Base64URL
     * @param string $data
     * @return boolean|string
     */
    function base64url_encode($data)
    {
        // First of all you should encode $data to Base64 string
        $b64 = base64_encode($data);

        // Make sure you get a valid result, otherwise, return FALSE, as the base64_encode() function do
        if ($b64 === false) {
            return false;
        }

        // Convert Base64 to Base64URL by replacing “+” with “-” and “/” with “_”
        $url = strtr($b64, '+/', '-_');

        return str_replace("==", "=", $url);
    }

    /**
     * Decode data from Base64URL
     * @param string $data
     * @param boolean $strict
     * @return boolean|string
     */
    function base64url_decode($data, $strict = false)
    {
        // Convert Base64URL to Base64 by replacing “-” with “+” and “_” with “/”
        $b64 = strtr($data, '-_', '+/');

        // Decode Base64 string and return the original data
        return base64_decode($b64, $strict);
    }

    function requestXML($url){

        $xml = $this->curlShell('GET', $url);

    }

    function dropdownMenu($name, $dropdownArray, $label){

        $dropdownElement = "<div class=\"class=\"x-form-text x-form-field\" >
  <label for=\"$name\">$label</label>
  <select id=\"$name\" name=\"$name\"> ";

        foreach($dropdownArray as $k=>$v){
            $dropdownElement .= "<option value=\"{$v['id']}\">" . addslashes($v['name']) . "</option>";
        }

        $dropdownElement .= "
  </select>
</div>";

        return $dropdownElement;

    }

    function get_specified_folder_tree_id_from_notebook($uid, $nbid, $parent_tree_id=0, $call_type = "notebook")
    {
        $api_class = "tree_tools";
        $api_call = "get_tree_level";
        $email = NULL;
        $request_params = "uid={$uid}&nbid={$nbid}&parent_tree_id={$parent_tree_id}";

        $args = $this->format_args($api_class, $api_call, $email, $call_type, $request_params);

        $urls = $this->get_connection($args);

        $xml = simplexml_load_string(trim($this->curlShell('GET', $urls)), "SimpleXMLElement", LIBXML_NOCDATA);

        $json = json_encode($xml);
        $array = json_decode($json,TRUE);

        if(!is_null($array["level-nodes"]["level-node"][0])) {
            foreach ($array["level-nodes"]["level-node"] as $k => $v) {
                if ($v["is-page"] == "false" && $v["user-access"]["can-write"] == "true") {
                    $folderList[$k] = array("id" => $v["tree-id"], "name" => $v["display-text"]);
                }
            }
        } else { // $array contains the info of the only item in it.
            $folderList[0] = array("id" => $array["level-nodes"]["level-node"]["tree-id"],
                "name" => $array["level-nodes"]["level-node"]["display-text"]);
        }

      # returns either the folder's ID or nil if not found, if called with
      # REDCap, then you'll know you need to create the folder if it doesn't
      # exist.

        return $folderList;
    }

    function saveReportToTemp($report_id, $outputFormat='csv', $exportAsLabels=true, $exportCsvHeadersAsLabels=true){
        $csv_data = REDCap::getReport($report_id, $outputFormat, $exportAsLabels, $exportCsvHeadersAsLabels);

        $reportName = DataExport::getReportNames($report_id, $applyUserAccess=false, $fixOrdering=true, $useFolderOrdering=true);

        $report_file_identifier = substr(str_replace(" ", "", ucwords(preg_replace("/[^a-zA-Z0-9 ]/", "", html_entity_decode($reportName, ENT_QUOTES)))), 0, 20);

        $today = date('Y_m_d_H_i');
        $rand_str = $this->get_random_string(4); // generate a random string for uniqueness

        global $Proj;
        $tmp_file_short = $report_file_identifier."_".$report_id ."_".$Proj->project_id."_".$today.".csv";
        $tmp_file = APP_PATH_TEMP.$tmp_file_short;
        file_put_contents($tmp_file,$csv_data);

        return $tmp_file_short;

    }

    function labArcAddAttachment($uid, $filename, $akid, $caption, $nbid = '', $pid = '', $expires, $sig){
        $fileContent =file_get_contents(APP_PATH_TEMP . $filename);
        $la_api_url = $this->getLAAPIURL();
        if($nbid != '' && $pid != ''){
            $url = $la_api_url . "entries/add_attachment?uid={$uid}&filename={$filename}&akid={$akid}&caption={$caption}&nbid={$nbid}&pid={$pid}&expires={$expires}&sig={$sig}";
        } else {
            $url = $la_api_url . "entries/add_attachment?uid={$uid}&filename={$filename}&akid={$akid}&caption={$caption}&expires={$expires}&sig={$sig}";
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $fileContent,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/octet-stream'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    }

    function addRECapFolderOrPageToNotebook($uid,$nbid,$parent_tree_id = 0,$call_type = "notebook",$is_folder,$display_text){
        $api_class = "tree_tools";
        $api_call = "insert_node";
        $email = NULL;
        $request_params = "uid={$uid}&nbid={$nbid}&parent_tree_id={$parent_tree_id}&display_text={$display_text}&is_folder={$is_folder}";

        $args = $this->format_args($api_class, $api_call, $email, $call_type, $request_params);

        $urls = $this->get_connection($args);

        $xml = simplexml_load_string(trim($this->curlShell('GET', $urls)), "SimpleXMLElement", LIBXML_NOCDATA);

        $json = json_encode($xml);
        $arrayResponse = json_decode($json,TRUE);

        return $arrayResponse["node"];

    }


    function get_entire_tree_id_from_notebook($uid, $nbid, $parent_tree_id=0, $call_type = "notebook")
    {
        $api_class = "tree_tools";
        $api_call = "get_tree_level";
        $email = NULL;
        $request_params = "uid={$uid}&nbid={$nbid}&parent_tree_id={$parent_tree_id}";

        $args = $this->format_args($api_class, $api_call, $email, $call_type, $request_params);

        $urls = $this->get_connection($args);

        $xml = simplexml_load_string(trim($this->curlShell('GET', $urls)), "SimpleXMLElement", LIBXML_NOCDATA);

        $json = json_encode($xml);
        $array = json_decode($json,TRUE);

        return $array;
    }

    function minifier($code) {
        $search = array(

            // Remove whitespaces after tags
            '/\>[^\S ]+/s',

            // Remove whitespaces before tags
            '/[^\S ]+\</s',

            // Remove multiple whitespace sequences
            '/(\s)+/s',

            // Removes comments
            '/<!--(.|\s)*?-->/'
        );
        $replace = array('>', '<', '\\1');
        $code = preg_replace($search, $replace, $code);
        return $code;
    }

    function modalContent($project_id){
        $userInfo =json_decode(stripslashes($this->getSystemSetting(USERID)), TRUE);
        $userUID = $this->decrypt_config_string($userInfo[USERID]['UID']);

        $noticeLanguage = "<div style=\'bottom: -10px;position: relative;\'>
        <div class=\'notice\'>
        Having trouble? <button type=\"button\" class=\"btn btn-primary notice-button\" id=\'disconnect\' onClick=\'openNotice()\'>Disconnect</button> from LabArchives, then re-enter your LabArchives email and token. 
        </div>    
        <hr>
    </div>";

        $disconnectModal = "<div class=\'closed2\' id=\'modal2\'>
            <div class=\"container notice-modal\" >
  <div class=\"row\">
    <div class=\"col-sm\" style=\'text-align:center;\'>
  <span style=\'font-size:15px;font-weight: bold\' id=\'discFirstLabel\'>Disconnect from LabArchives?</span>
  <span class=\"closed\"style=\'font-size:15px;font-weight: bold\' id=\'discSecondLabel\'>You have disconnected from LabArchives</span>
    </div>
  </div>
  <div class=\"row\">
      <div class=\"col-sm\">      
    </div>
    <div class=\"col-sm\" style=\"padding-top: 15px;\">
      <button type=\"button\" class=\"btn btn-danger btn-sm\" onClick=\'disconnectNow()\' id=\'discYes\'>Yes</button>
    </div>
    <div class=\"col-sm\" style=\'padding: 0.5rem 0.5rem;\'>
        <div class=\"col-sm closed\" id=\'disconnectSpinner\'>
        <i class=\"fas fa-circle-notch fa-spin\" style=\'font-size: 30px;\'></i>
        </div>    
        <div class=\"closed\" id=\"discOk\">
        <button type=\"button\" class=\"btn btn-info btn-sm\" onClick=\'backToReport()\'>OK</button>
        </div>  
    </div>
    <div class=\"col-sm\" style=\"padding-top: 15px;\">
      <button type=\"button\" class=\"btn btn-info btn-sm\" id=\"discNo\" onClick=\'openNotice()\'>No</button>
    </div>
        <div class=\"col-sm\">      
    </div>
  </div>
</div>
</div>";

        // UI for Basic Account Setup
        if(is_null($userInfo) || $userUID == "") {
            $email = $userInfo[USERID]['userEmail'];
            $pass = $userInfo[USERID]['appPas'];
            $RegionSelect = $this->dropdownMenu('regionSelect', $this->getRegionList(), 'Select Region: ');;
            $setup = "<div class=\'modal-guts\'>    <div>		
        Setup a link to your LabArchives account. Follow the steps below:</div> <hr><p>
        <div id=\'basicSetup\'>
        <div class=\'instructions\'>    
        <ol class=\'leftTab\'>
            <li>Sign-in to your <a href=\'https://mynotebook.labarchives.com/login\' target=\'_blank\'> LabArchives account <i class=\"fas fa-external-link-alt\"></i></a> (opens a new tab) </li>
            <li>Go to Profile >> \'External App Authentication\' <i class=\"fas fa-info-circle\"></i> </li>            
            <li>Copy the \'Email Address\' and \'External App Authentication\' and paste them below:</li>
            
        <div class=\'row\'>
            <label for=\'staticEmail\' class=\'col-sm-1 \'>Email:</label>
            <div class=\'col-sm-4\'>
                <input type=\'text\' class=\'form-control\' id=\'laEmail\' value=\'{$email}\'>
            </div>
        
            <label for=\'inputPassword\' class=\'col-sm-1 \'>App Token:</label>
            <div class=\'col-sm-4\'>
                <input type=\'password\' class=\'form-control\' id=\'laToken\' value=\'{$pass}\' >
            </div>
        </div>               
            <li> {$RegionSelect} </li>
        </ol>        
                </div>
    </div>
    {$noticeLanguage}
            <div class=\'submit-button\'>
                <button type=\'button\' class=\'btn btn-primary mb-2\'  id=\'saveButton\' onClick=\'linkNow()\'> 
                    <span>Connect</span>
                    <span id=\'spiner222\' class=\'spinner-border spinner-border-sm\' role=\'status\' aria-hidden=\'true\' style=\'display: none\'></span>
                </button>                
            </div>
            {$disconnectModal}";

            $saveLink = $this->getUrl("plugins/saveSettings.php", $noAuth = false, $useApiEndpoint = false);
            $removeSettings = $this->getUrl("plugins/removeSettings.php", $noAuth = false, $useApiEndpoint = false);

            $tempID = USERID;
            $linkScript = <<<SCRIPT
<script>
function linkNow(){
            $('#spiner222').css("display", "inline-block");
            
            var laEmail = $('#laEmail').val();
            var laToken = $('#laToken').val();
            var region = $('#regionSelect').val();
            var tmpUser = '{$tempID}';
            console.log("This is the current user: " + tmpUser);
            var dataset= "{" + "\"" + tmpUser +"\"" + ":" +
                "{\"" + tmpUser + "\":" +
                "{" +
                "" + "\"region\":" + "\"" +region + "\"" +  "," +
                "" + "\"userEmail\":" + "\"" +laEmail + "\"" +  "," +
                "" + "\"appPass\":" + "\"" +laToken + "\"" +
                "}}}";

            $.ajax({type: "POST",
                data: {data : dataset},
                url:'{$saveLink}',
                success: function (data){  
                    $('#spiner222').css("display", "none");                    
                    window.location.href = window.location.href + '&s=1';
                    console.log("Success");                    
                }, error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert("An Error has occurred. Please contact your administrator.");
                }
            });
            return false;
        }
        
function openNotice(){
    var modal2 = document.querySelector("#modal2");
    modal2.classList.toggle("closed2");    
}

function getURL(){
    var url = window.location.href;
    var urlSource = url.split("&");
    return urlSource[0] + "&" + urlSource[1];
}

function backToReport(){
    window.location.href = getURL();
}

function disconnectNow(){    
            $('#disconnectSpinner').css("display", "inline-block");
            $('#discYes').css("display", "none");
            $('#discNo').css("display", "none");

            var sourceURL = getURL();
            var user = "{$tempID}";
            var dataset= "{" + "\"" + "userKey" +"\"" + ":\"" + user + "\"}";

            $.ajax({type: "POST",
                data: {data : dataset},
                url:'{$removeSettings}',
                success: function (data){
                $('#discFirstLabel').css("display", "none");
                $('#disconnectSpinner').css("display", "none");
                $('#discSecondLabel').css("display", "inline-block");
                $('#discOk').css("display", "inline-block");
                console.log(sourceURL);
                    // window.location.reload();
                }, error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert("An Error has occurred");
                }
            });
            
            return false;        
}
</script>
SCRIPT;
;
            print $linkScript;

            return $setup;
        } else {
            //
            $selectedRegion = $userInfo[USERID]['region'];
            // Set required variables
            $this->setLAAPIURL($selectedRegion);
            $this->setLAAKID($selectedRegion);
            $this->setLA_SSO_ENTITY_ID($selectedRegion);
            $this->setLA_PWD($selectedRegion);

            $notebookURL = $this->get_notebook_list($userUID,$userInfo[USERID]['userEmail']);

            $xml = simplexml_load_string(trim($this->curlShell('GET', $notebookURL)), "SimpleXMLElement", LIBXML_NOCDATA);

            $notebooks = $xml->children();
            $i=0;
            foreach($notebooks->children() as $n){
                $laNotebooks[$i] = array("id" => strval($n->id), "name"=> strval($n->name));
                $i++;
            }
            //        } // comment this out.

            // Select notebook and transfer
            if($laNotebooks == '' || is_null($laNotebooks)){
                $laNotebooks[0] = array("id"=> "noNotebook", "name"=>"Error - disconnect and try again.");
            }

            $nBDropdown = $this->dropdownMenu('laNotebook', $laNotebooks, 'Select Notebook: ');
            $reportID = (int) $_GET['report_id'];
            $reportName = DataExport::getReportNames($reportID, $applyUserAccess=false, $fixOrdering=true, $useFolderOrdering=true);

            $transferLink = $this->getUrl("plugins/transfer.php", $noAuth = false, $useApiEndpoint = false);
            $removeSettings = $this->getUrl("plugins/removeSettings.php", $noAuth = false, $useApiEndpoint = false);
            $tempID = USERID;

            $transferScript = <<<SCRIPT
<script type="text/javascript">   
    function transferNow(){
        $('#spiner333').css("display", "inline-block");
        
        var laNotebook = $('#laNotebook').val();
        var rid = "$reportID";

        var dataset= "{" +
            "\"nbSelection\":" + "\"" +laNotebook + "\"" + "," +
            "\"rid\":" + "\"" + rid + "\"" +
            "}";

        $.ajax({type: "POST",
            data: {data : dataset},
            url:'{$transferLink}',
            success: function (data){
                $('#spiner333').css("display", "none");  
                // console.log(data);
                window.location.href = window.location.href + '&t=2';
                console.log("Success")
            }, error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert("An error occurred. Please contact your administrator.");
            }
        });
        return false;
    }
    
    function openNotice(){
    var modal2 = document.querySelector("#modal2");
    modal2.classList.toggle("closed2");    
}

function getURL(){
    var url = window.location.href;
    var urlSource = url.split("&");
    return urlSource[0] + "&" + urlSource[1];
}

function backToReport(){
    window.location.href = getURL();
}

function disconnectNow(){    
            $('#disconnectSpinner').css("display", "inline-block");
            $('#discYes').css("display", "none");
            $('#discNo').css("display", "none");

            var sourceURL = getURL();
            var user = "{$tempID}";
            var dataset= "{" + "\"" + "userKey" +"\"" + ":\"" + user + "\"}";

            $.ajax({type: "POST",
                data: {data : dataset},
                url:'{$removeSettings}',
                success: function (data){
                $('#discFirstLabel').css("display", "none");
                $('#disconnectSpinner').css("display", "none");
                $('#discSecondLabel').css("display", "inline-block");
                $('#discOk').css("display", "inline-block");
                console.log(sourceURL);
                    // window.location.reload();
                }, error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert("An Error has occurred");
                }
            });
            
            return false;        
}
</script>
SCRIPT;
            print $transferScript;


            $selectAndTransfer="
<div class=\'modal-guts\'>    <div>		
        You can now specify where to upload your report.</div> <hr><p> 
<div id=\'selectNotebook\'>    
<p> <span class=\'status-connected\'> Ready! </span> You may now upload to your LabArchives account: </p>
    <div class=\'instructions\'>
    <legend style=\'margin-left:5px;color:#800000;font-weight:bold;font-size:13px;\'>Select and Review </legend>
    <p>
        1. The selected report is the following: 
        <div class=\'leftTab\'> Report Name:  $reportName </div>
        <div class=\'leftTab\'> Report id: $reportID</div>
    </p>
    <p>
        2. Select your LabArchives notebook. 
        <div class=\'leftTab\'>{$nBDropdown}</div>
    </p>
</div>
    {$noticeLanguage}
    <div class=\'submit-button\'>
        <button type=\'submit\' class=\'btn btn-primary mb-2\'  id=\'transferNow\' onClick=\'transferNow()\'>
            <span>Upload</span>
            <span id=\'spiner333\' class=\'spinner-border spinner-border-sm\' role=\'status\' aria-hidden=\'true\' style=\'display: none\'></span>
        </button>
    </div> 

{$disconnectModal}";
            return $selectAndTransfer;

        }

    }

    function successfulTransfer(){

        $successElements = "<div id=\'actionMsg\' class=\'green\' style=\'max-width: 800px; padding: 15px 25px; margin: 20px 0px; text-align: left; display: none;\'>
        <span>Request successfully completed</span>
    </div>";

        $successScript= <<<SCRIPT
<script type="text/javascript">
 $(document).ready(function() {
        $(function(){
            setTimeout(function(){
                $("#actionMsg").slideToggle('normal');
            },1200);
            setTimeout(function(){
                $("#actionMsg").slideToggle('normal');
            },200);
            setTimeout(function(){
                $("#actionMsg").slideToggle(2200);
            },2400);
        });
    } );
 </script>
SCRIPT;

        print $successScript;

    }

    function getRegionList(){
        foreach($this->getSubSettings('la-api-region-list') as $k=>$v){
            $regionList[$k]['id'] = $k;
            $regionList[$k]['name'] = $v["region-name"];
        }
        return $regionList;
    }

    function curlShell($customRequest, $URL){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $customRequest,
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
}