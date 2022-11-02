<?php

namespace MGB\uploadToLabarchives;

use \REDCap as REDCap;

global $Proj;

if (!isset($project_id)) {
    die('Project ID is a required field');
}


foreach (json_decode(stripslashes($_POST['data'])) as $key => $value) {

    switch ($key) {
        case 'nbSelection':
            $nbid = $value;
            break;
        case 'rid':
            $rid = $value;
            break;
    }
}

if(!empty($nbid)){

    $today = date('Y_m_d_H_i');

    $pageName = "REDCapPID_". $project_id . "_" . $today ;
    $caption = "REDCapReport_". $rid ."_" . $project_id . "_" . $today;


    // Check to see if the REDCap folder exists in the selected Lab Archive notebook.

    $userInfo =json_decode(stripslashes($module->getSystemSetting(USERID)), TRUE);
    $userUID = $module->decrypt_config_string($userInfo[USERID]['UID']);
    //// Define constants:
    $selectedRegion = $userInfo[USERID]['region'];
    $module->setLAAKID($selectedRegion);
    $module->setLAAPIURL($selectedRegion);
    $module->setLA_PWD($selectedRegion);
    $module->setLA_SSO_ENTITY_ID($selectedRegion);

    $noteBookFolders = $module->get_specified_folder_tree_id_from_notebook($userUID,
        $nbid, $parent_tree_id=0, $call_type = "notebook");

    $targetFolderExists = FALSE;

    foreach($noteBookFolders as $k=>$nbDetails){
        if($nbDetails["name"] == 'REDCap'){
            $targetFolderExists = TRUE;
            $parent_tree_id = $nbDetails["id"];
        }
    }

    if($targetFolderExists){

            $module->addRECapFolderOrPageToNotebook($userUID, $nbid, $parent_tree_id, $call_type = "notebook", "false", $pageName);
            // Get the page id (which is used for parent_tree_id or pid) of the newly create page in the REDCap Folder
            // Unfortunately, the page id is not returned on the LA API call that creates it and must be found by
            // parsing the existing notebook tree.
            $noteBookFolders2 = $module->get_entire_tree_id_from_notebook($userUID,
                $nbid, $parent_tree_id, $call_type = "notebook");

            $newFolderExists = FALSE;

            if (is_null($noteBookFolders2["level-nodes"]["level-node"][0])) {

                // There is no other page in this folder - add the report (as an attachment) to this page

                if ($noteBookFolders2["level-nodes"]["level-node"]["display-text"] == $pageName && $noteBookFolders2["level-nodes"]["level-node"]["is-page"] == 'true') {

                    $filename = $module->saveReportToTemp($rid, $outputFormat = 'csv', $exportAsLabels = true, $exportCsvHeadersAsLabels = true);

                    $pid = $noteBookFolders2["level-nodes"]["level-node"]["tree-id"];
                    $expires = number_format(microtime(true) * 1000, 0, "", "");
                    $sig = $module->build_signature("add_attachment", $expires, "notebook");
                    $module->labArcAddAttachment($userUID, $filename, $module->LA_AKID, $caption, $nbid, $pid, $expires, $sig);

                }
                return;
            } else if (!is_null($noteBookFolders2["level-nodes"]["level-node"][0])) {
                // There are other pages in this folder and we must find the tree id of the target page

                foreach ($noteBookFolders2["level-nodes"]["level-node"] as $k => $nbDetails) {

                    if($nbDetails["display-text"] == $pageName && $nbDetails["is-page"] == 'true'){

                        $filename = $module->saveReportToTemp($rid, $outputFormat='csv', $exportAsLabels=true, $exportCsvHeadersAsLabels=true);

                        $pid = $nbDetails["tree-id"];
                        $expires = number_format(microtime(true)*1000,0,"","");
                        $sig= $module->build_signature("add_attachment", $expires, "notebook");
                        $module->labArcAddAttachment($userUID, $filename, $module->LA_AKID, $caption, $nbid, $pid, $expires, $sig);

                    }

                }
            }

    } else {
        // If it doesn't, then create it then attach the report

        $addResponse = $module->addRECapFolderOrPageToNotebook($userUID,$nbid,$parent_tree_id = 0,$call_type = "notebook", "true","REDCap");
        // Get the folder id (which is used for parent_tree_id) of the newly create REDCap folder
        // Unfortunately, the folder id is not returned on the LA API call that creates it and must be found by
        // parsing the existing notebook tree.
        $noteBookFolders = $module->get_specified_folder_tree_id_from_notebook($userUID,
            $nbid, $parent_tree_id=0, $call_type = "notebook");

        $newFolderExists = FALSE;

        if($addResponse["display-text"] == 'REDCap'){
            $newFolderExists = TRUE;
            $parent_tree_id = $addResponse["tree-id"];
        }

        if($newFolderExists) {
            // if REDCap folder exists in the Notebook, then add a new page, under the REDCap folder, to host the incoming report.

            $module->addRECapFolderOrPageToNotebook($userUID, $nbid, $parent_tree_id, $call_type = "notebook", "false", $pageName);
            // Get the page id (which is used for parent_tree_id or pid) of the newly create page in the REDCap Folder
            // Unfortunately, the page id is not returned on the LA API call that creates it and must be found by
            // parsing the existing notebook tree.
            $noteBookFolders2 = $module->get_entire_tree_id_from_notebook($userUID,
                $nbid, $parent_tree_id, $call_type = "notebook");
            $newFolderExists = FALSE;

            if (is_null($noteBookFolders2["level-nodes"]["level-node"][0])) {

                // There is no other page in this folder - add the report (as an attachment) to this page

                if ($noteBookFolders2["level-nodes"]["level-node"]["display-text"] == $pageName && $noteBookFolders2["level-nodes"]["level-node"]["is-page"] == 'true') {

                    // make the attachment

                    $filename = $module->saveReportToTemp($rid, $outputFormat = 'csv', $exportAsLabels = true, $exportCsvHeadersAsLabels = true);

                    $pid = $noteBookFolders2["level-nodes"]["level-node"]["tree-id"];
                    $expires = number_format(microtime(true) * 1000, 0, "", "");
                    $sig = $module->build_signature("add_attachment", $expires, "notebook");
                    $module->labArcAddAttachment($userUID, $filename, $module->LA_AKID, $caption, $nbid, $pid, $expires, $sig);

                }
                return;
            } else if (!is_null($noteBookFolders2["level-nodes"]["level-node"][0])) {

                // There are other pages in this folder and we must find the tree id of the target page

                foreach ($noteBookFolders2["level-nodes"]["level-node"] as $k => $nbDetails) {

                        if($nbDetails["display-text"] == $pageName && $nbDetails["is-page"] == 'true'){

                            $filename = $module->saveReportToTemp($rid, $outputFormat='csv', $exportAsLabels=true, $exportCsvHeadersAsLabels=true);

                            $pid = $nbDetails["tree-id"];
                            $expires = number_format(microtime(true)*1000,0,"","");
                            $sig= $module->build_signature("add_attachment", $expires, "notebook");
                            $module->labArcAddAttachment($userUID, $filename, $module->LA_AKID, $caption, $nbid, $pid, $expires, $sig);

                        }

                }
            }
        }
    }
}