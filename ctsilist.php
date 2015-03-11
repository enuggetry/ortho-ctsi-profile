<?php
header('Content-Type: text/html; charset=utf-8');
require_once("../../../wp-load.php");
$app = "orthosurg.ucsf.edu";

echo getcwd()."<br/>";

$key = $_GET['key'];
$lookup = "";
if (strpos($key,'@') !== false) {
    $lookup = "http://profiles.ucsf.edu/CustomAPI/v1/JSONProfile.aspx?source=$app&FNO=$key&publications=full";
}
elseif(strpos($key,'.') !== false) {
    $lookup = "http://base.ctsi.ucsf.edu/profiles/json_api_v2_beta/?ProfilesURLName=$key&publications=full&source=$app";
}
else {
    $lookup = "http://profiles.ucsf.edu/CustomAPI/v1/JSONProfile.aspx?source=$app&Person=$key&publications=full";
}    

$json_data = file_get_contents($lookup);

if ($json_data=="{}") {
    echo $key.' CTSI profile was not found.';
    exit();
}

// ToDo: save the key in post meta

$data = json_decode($json_data);
$cnt = 0;
$top = 10;

echo "CTSI Profile Found: ".$data->Profiles[0]->Name;
function sort_cmp($a,$b){
    return strcmp($b->Date_beta, $a->Date_beta);
}
usort($data->Profiles[0]->Publications,"sort_cmp");
//echo "items ".$data->Profiles[0]->Publications.length;

echo '<table>';
foreach ($data->Profiles[0]->Publications as $pub){
    $pubID = getPublicationID($pub->PublicationID);
    $checked = "";
    if ($cnt < 10) $checked = "checked";
    $display_pri = "display:none;";
    if ($cnt < 10) $display_pri = "";
    $cnt++;
    echo '<tr>';
    echo '<td valign="top"><input type="checkbox" id="ctsi_chk_'.$pubID.'"name="'.$pubID.'" '.$checked.' onclick="clickcheck('.$pubID.')" /></td>';
    echo '<td valign="top"><input type="text" id="ctsi_pri_'.$pubID.'" value="10" name="priority" title="Priority (0-99)" alt="Priority (0-99)" style="width: 30px;'.$display_pri.'" maxlength="2" /></td>';
    echo '<td  valign="top" width="100">'.$pub->Date_beta.'</td>';
    echo '<td valign="top">'.$pub->PublicationTitle.'</td>';
    echo '</tr>';

}
echo "</table>";

//echo $json_data;

//print_r($data);

// return only the number portion of the PublicationID
function getPublicationID($fullID){
	echo $fullID;
	$a = str_replace(":","",$fullID);
    $a = split("/",$a);
	$n = count($a);
    return $a[$n-1];
}
?>
