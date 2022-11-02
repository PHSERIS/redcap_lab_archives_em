<?php

namespace MGB\uploadToLabarchives;

use \ExternalModules\ExternalModules as ExternalModules;

require_once APP_PATH_DOCROOT . 'ControlCenter/header.php';
include APP_PATH_VIEWS . 'HomeTabs.php';

$uiLink = $module->getUrl("plugins/adminControl.php", $noAuth = false, $useApiEndpoint = false);
$removeSettings = $module->getUrl("plugins/removeSettings.php", $noAuth = false, $useApiEndpoint = false);


?>

    <html>

<body>
<h4 class="clearfix mt-0 mb-3" style="margin-top: 50px;">
    <div class="float-left"><i class="fas fa-user-cog"></i></i> Admin Control to LabArchives Connection</div>
</h4>

<div>
    The following are a set of functions that can help a REDCap admin in managing te Lab Archives module.
</div>
<hr>

<?php
if($_GET['note']==1){
    ?>
    <div id="actionMsg" class="green" style="max-width: 800px; padding: 15px 25px; margin: 20px 0px; text-align: left; display: none;">
        <span>Request successfully completed</span>
    </div>
    <?php
}
?>

<div class='form-group row' style="padding-left: 15px">
    <h5 style="color: darkred">Remove User Lab Archives App Connection</h5>
    <p>
        A user's connection to Lab Archives is made through a uniquely generated id that belongs to the user and to which the user does not, <u>and should not</u>, have access to. It can only be retrieved by a backend process. Use the following to remove a user's unique Lab Archive id from the module's configuration.
    </p>
    <label for='laUserName' class='col-sm-1 col-form-label'> Username: </label>
    <div class='col-sm-4'>
    <input type='text' class='form-control' id='laUserName'>
    </div>
    <div class='col-sm-4'>
    </div>
    <div class='col-sm-3'>
        <button type='submit' class='btn btn-primary mb-2'  id='removeButton'>
            <span>Remove</span>
            <span id="spiner01" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none"></span>
        </button>
    </div>
    <p>
        Note: the user can re-establish connection to Lab Archives by access the module, in their project, and completing the authentication step.
    </p>
</div>

</body>
<script>
    $(document).ready(function() {
        $(function(){
            setTimeout(function(){
                $("#actionMsg").slideToggle('normal');
            },200);
            setTimeout(function(){
                $("#actionMsg").slideToggle(1200);
            },2400);
        });

        $('#removeButton').click( function() {
            $('#spiner01').css("display", "inline-block");

            var user = $('#laUserName').val();

            var dataset= "{" + "\"" + "userKey" +"\"" + ":\"" + user + "\"}";

            $.ajax({type: "POST",
                data: {data : dataset},
                url:'<?php print $removeSettings;?>',
                success: function (data){
                    window.location.href = window.location.href + '&note=1';
                }, error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert("An Error has occurred\n" +
                        "XMLHttpRequest: "  + XMLHttpRequest +
                        "\ntextStatus: " + textStatus +
                        "\nerrorThrown: " + errorThrown);
                }
            });
            return false;
        } );

    } );
</script>
    <?php
require_once APP_PATH_DOCROOT . 'ControlCenter/footer.php';
?>