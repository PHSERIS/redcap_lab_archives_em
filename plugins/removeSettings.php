<?php

namespace MGB\uploadToLabarchives;


foreach (json_decode(stripslashes($_POST['data'])) as $key => $value) {

    $module->removeSystemSetting($value);

}

