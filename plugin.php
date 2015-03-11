<?php
/*
Plugin Name: Ortho CTSI Profile
Plugin URI: 
Description: UCSF Ortho CTSI Profile
Version: 1.0
Author: EY
Author URI: 
License: GPL2
*/
/* plugin persistent settings:

	ctsi_def_num_rows    10
	ctsi_def_priority    50

	post meta data keys (per post id):

	The faculty_template1 is modified to call ctsi_display_publications();
	
	What this plugin does:
	* allow for configuration of data acquisition from Clinical & Transitional Science Institute (CTSI) UCSF database
	* CTSI API is ReSTful
	* dataformat is XML
	* Implements CTSI Meta Box for admin-side configuration
	* Implements live acquisition of CTSI data on client side.
	* Display on A2 Faculty template: Staff description, grant data, publication data, Award data as acquired from CTSI.
	* other data: full name, position, title
	* CTSI entries can be prioritized such that sort order can be altered.
	* CTSI entries can be hidden, if desired.
 */
$ctsiSourceApp = "orthosurg.ucsf.edu";

if (!class_exists('Ortho_CTSI_Profile')) {

    class Ortho_CTSI_Profile {

        /**
         * Construct the plugin object
         */
        public function __construct() {
            // Initialize Settings
            require_once(sprintf("%s/settings.php", dirname(__FILE__)));
            $Ortho_CTSI_Profile_Settings = new Ortho_CTSI_Profile_Settings();

            // Register custom post types
            //require_once(sprintf("%s/post-types/post_type_template.php", dirname(__FILE__)));
            //$Post_Type_Template = new Post_Type_Template();
            // add filters
            //add_filter('the_content', 'ctsi_profile_script_to_content');

            /* Fire our meta box setup function on the post editor screen. */
            add_action('load-post.php', 'ctsi_meta_boxes_setup');
            add_action('load-post-new.php', 'ctsi_meta_boxes_setup');
            
            //add_action('wp_footer', 'fn_footer_action');    // detect template
            //add_action('faculty_template1.php','ctsi_theme_action');

        }

// END public function __construct

        /**
         * Activate the plugin
         */
        public static function activate() {
            // setup default options
            update_option( "ortho_ctsi_display_lines", "10" );
            update_option( "ortho_ctsi_default_priority", "50");
        }

// END public static function activate

        /**
         * Deactivate the plugin
         */
        public static function deactivate() {
            // Do nothing
        }

// END public static function deactivate
    }

    // END class Ortho_Addthis
} // END if(!class_exists('Ortho_Addthis'))

/*
 * post meta storage class
 */
class ctsi_meta_stor {
    public $key;
    public $init;
    public $def_num_rows;
    public $def_priority;
    public $publist;    // array publications
	public $grantlist;	// array grants
	public $awardlist;	// array awards
	
    function __construct($rows=0,$pri=0,$key="",$init=0) {
        $this->key = $key;
        $this->init = $init;
        $this->def_num_rows = $rows;
        $this->def_priority = $pri;
        $this->publist = array();
        $this->grantlist = array();
        $this->awardlist = array();
        
        if ($rows==0) $this->def_num_rows=intval(get_option('ortho_ctsi_display_lines'));
        if ($pri==0) $this->def_priority=intval(get_option('ortho_ctsi_default_priority'));
    }
	// publication functions
    public function addpub($pubid,$pri=0){
        if ($pri==0) $pri = $this->def_priority;
        $i = "pub".$pubid;
        $this->publist[$i] = array("priority"=>$pri);
    }
    public function delpub($pubid) {
        $i = "pub".$pubid;
        if (array_key_exists($i,$this->publist)) {
            unset($this->publist[$i]);
            return 1;
        }
        return 0;   // didn't find it
    }
    public function pub_isset($pubid){
        $i = "pub".$pubid;
        return isset($this->publist[$i]);
    }
    public function getpub($pubid){
        $i = "pub".$pubid;
        if (array_key_exists($i,$this->publist)) {
            return $this->publist[$i];
        }
        return 0;   // not found
    }
    public function setpubdata($pubid,$key,$val){
        $i = "pub".$pubid;
        if (isset($this->publist[$i])) {
            $this->publist[$i][$key] = $val;
        }
    }
	// grant functions
    public function addgrant($id,$pri=0){
        if ($pri==0) $pri = $this->def_priority;
        $i = "id".$id;
        $this->grantlist[$i] = array("priority"=>$pri);
    }
    public function delgrant($id) {
        $i = "id".$id;
        if (array_key_exists($i,$this->grantlist)) {
            unset($this->grantlist[$i]);
            return 1;
        }
        return 0;   // didn't find it
    }
    public function grant_isset($id){
        $i = "id".$id;
        return isset($this->grantlist[$i]);
    }
    public function getgrant($id){
        $i = "id".$id;
        if (array_key_exists($i,$this->grantlist)) {
            return $this->grantlist[$i];
        }
        return 0;   // not found
    }
    public function setgrantdata($id,$key,$val){
        $i = "id".$id;
        if (isset($this->grantlist[$i])) {
            $this->grantlist[$i][$key] = $val;
        }
    }
	// award functions
    public function addaward($id,$pri=0){
        if ($pri==0) $pri = $this->def_priority;
        $i = "id".$id;
        $this->awardlist[$i] = array("priority"=>$pri);
    }
    public function delaward($id) {
        $i = "id".$id;
        if (array_key_exists($i,$this->awardlist)) {
            unset($this->awardlist[$i]);
            return 1;
        }
        return 0;   // didn't find it
    }
    public function award_isset($id){
        $i = "id".$id;
        return isset($this->awardlist[$i]);
    }
    public function getaward($id){
        $i = "id".$id;
        if (array_key_exists($i,$this->awardlist)) {
            return $this->awardlist[$i];
        }
        return 0;   // not found
    }
    public function setawarddata($id,$key,$val){
        $i = "id".$id;
        if (isset($this->awardlist[$i])) {
            $this->awardlist[$i][$key] = $val;
        }
    }
	
    public function save($postid) {
        global $wpdb; // this is how you get access to the database
        return update_post_meta($postid,"ctsi_data",serialize($this));
    }
    public function load($postid) {
        global $wpdb; // this is how you get access to the database
        $sdata = get_post_meta($postid,"ctsi_data",true);
        if ($sdata==false) return 0;     // didn't get anything
        $data = unserialize($sdata);
        $this->key = $data->key;
        $this->init = $data->init;
        $this->def_num_rows = $data->def_num_rows;
        $this->def_priority = $data->def_priority;
        $this->publist = $data->publist;
        $this->grantlist = $data->grantlist;
        $this->awardlist = $data->awardlist;
        return $sdata;   //success
    }
};

/*
 * setup separate javascript file ctsi_script.js
 */
add_action( 'init', 'ctsi_script_enqueuer' );

function ctsi_script_enqueuer() {
   //wp_register_script( "ctsi_script", WP_PLUGIN_URL.'/ortho-ctsi-profile/ctsi_script.js', array('jquery') );
   wp_register_script( "ctsi_script", '/wp-content/plugins/ortho-ctsi-profile/ctsi_script.js', array('jquery') );
   wp_localize_script( 'ctsi_script', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        

   wp_enqueue_script( 'jquery' );
   wp_enqueue_script( 'ctsi_script' );

}

/* setup AJAX callback */
add_action("wp_ajax_ctsi_publication_list", "ctsi_publication_list");
add_action("wp_ajax_ctsi_update_data", "ctsi_update_data");

/*
 * AJAX callback: retrieve CTSI publication list
 */
function ctsi_publication_list() {
    global $wpdb; // this is how you get access to the database
    
    //if ( !wp_verify_nonce( $_REQUEST['nonce'], "my_user_vote_nonce")) {
    //   exit("Not logged in.");
    //} 

    //echo getcwd()."<br/>";
    $postid = $_REQUEST['post_id'];
    $key = $_REQUEST['key'];
    $lookup = "";
    
    //echo "post_id=$postid<br/>";
    
    $postdat = new ctsi_meta_stor();
    $postdat->load($postid);
    
	// reinit $postdat if a different key is being used.
    if ($postdat->key != $key) $postdat = new ctsi_meta_stor();
    $postdat->key = $key;

	//echo "key = ".$postdat->key." ";
    
    $json_data = ctsi_lookup($key);
	//echo "json = ". $json_data;
    if (strlen($json_data)==0) {
        echo "CTSI profile not found: $key<br>";
        exit();
    }

	// tune data before decoding from JSON to PHP object
	$json_data = str_replace("NIHGrants_beta","NIHGrants",$json_data);		
	
	
    $data = json_decode($json_data);
    $cnt = 0;
    $top = 10;
	
    echo "CTSI Profile Found: ".$data->Profiles[0]->FirstName." ".$data->Profiles[0]->LastName;

	// if key is found, modify postmeta publications - make sure there is a <ul> in there.
	$pubdata = get_post_meta( $postid,"publications",true);
	$pubdata1 = strtolower($pubdata);
	if (strpos($pubdata1,"<ul>") === FALSE) {
		$pubdata .= "<ul><li style='display:none;'>ABC</li>";
		update_post_meta($postid,"publications",$pubdata);
	}
	
    usort($data->Profiles[0]->Publications,"ctsi_sort_cmp");
	
    //echo $json_data." (len=".strlen($json_data).")<br>";
	echo "  [Publication Count: ".$data->Profiles[0]->PublicationCount."]";
	echo "  [Grant Count: ".count($data->Profiles[0]->NIHGrants)."]";
	echo "  [Award Count: ".count($data->Profiles[0]->AwardOrHonors)."]<br/>";
    //print_r($data);
    //echo "items ".$data->Profiles[0]->Publications.length;
	
	// publications
	
	echo "<h3>Publications</h3>";
    echo '<div style="width:100%;height:200px;overflow-y:scroll;overflow-x:hidden;resize:vertical;border:1px solid black;padding:3px;margin-bottom:5px;">';
    echo '<table>';
    foreach ($data->Profiles[0]->Publications as $pub){
        $pubID = getPublicationID($pub->PublicationID);
        $checked = "";
        $pub_pri = $postdat->def_priority;
        $display_pri = "display:none;";
        if ($postdat->init) {
            if ($postdat->pub_isset($pubID)) {
                $checked = "checked";
                $display_pri = "";
                $pub1 = $postdat->getpub($pubID);
                $pub_pri = $pub1['priority'];
            }
        }
        /* automatically checks the top 10 - we don't auto check anything now.
        else if ($cnt < 10) {
            $checked = "checked";
            $display_pri = "";
            $postdat->addpub($pubID,$pub_pri);   
        }
        */
        $title_msg = "Priority (10-99) - Higher numbers appear on top.";
		$check_msg = "Check to prioritize this item";
        $cnt++;
        echo '<tr>';
		echo '<td valign="top">'.$cnt.'</td>';
        echo '<td valign="top"><input type="checkbox" id="ctsi_chk_'.$pubID.'"name="'.$pubID.'" '.$checked.' onclick="clickcheck('.$pubID.')" title="'.$check_msg.'" alt="'.$check_msg.'" /></td>';
        echo '<td valign="top"><input  class="ctsi-priority-class" type="text" id="ctsi_pri_'.$pubID.'" value="'.$pub_pri.'" name="priority" title="'.$title_msg.'" alt="'.$title_msg.'" style="width: 30px;'.$display_pri.'" maxlength="2" onchange="ctsiChangePriority('.$pubID.')" /></td>';
        echo '<td valign="top" width="100">'.ctsi_parse_date($pub->Date).'</td>';
        echo '<td style="border-bottom:1px solid #aaa;padding-bottom:2px;" valign="top">'.$pub->PublicationTitle.'</td>';
        echo '</tr>';

    }
    echo "</table>";
    echo "</div>";
    $postdat->init = 1;
    $postdat->save($postid);

	// Awards
	
	echo "<h3>Awards</h3>";
    echo '<div style="width:100%;height:200px;overflow-y:scroll;overflow-x:hidden;resize:vertical;border:1px solid black;padding:3px;margin-bottom:5px;">';
    echo '<table>';
    foreach ($data->Profiles[0]->AwardOrHonors as $pub){
        $pubID = getAwardID($pub);
        $checked = "";
        $pub_pri = $postdat->def_priority;
        $display_pri = "display:none;";
        if ($postdat->init) {
            if ($postdat->award_isset($pubID)) {
                $checked = "checked";
                $display_pri = "";
                $pub1 = $postdat->getaward($pubID);
                $pub_pri = $pub1['priority'];
            }
        }
        $title_msg = "Priority (10-99) - Higher numbers appear on top.";
		$check_msg = "Check to prioritize this item";
        $cnt++;
        echo '<tr>';
		echo '<td valign="top">'.$cnt.'</td>';
        echo '<td valign="top"><input type="checkbox" id="ctsi_chk_'.$pubID.'"name="'.$pubID.'" '.$checked.' onclick="clickcheck_award('."'".$pubID."'".')" title="'.$check_msg.'" alt="'.$check_msg.'" /></td>';
        echo '<td valign="top"><input  class="ctsi-priority-class" type="text" id="ctsi_pri_'.$pubID.'" value="'.$pub_pri.'" name="priority" title="'.$title_msg.'" alt="'.$title_msg.'" style="width: 30px;'.$display_pri.'" maxlength="2" onchange="ctsiChangePriority_award('."'".$pubID."'".')" /></td>';
        echo '<td valign="top" width="100">'.$pub->AwardStartDate."-".$pub->AwardEndDate.'</td>';
        echo '<td style="border-bottom:1px solid #aaa;padding-bottom:2px;" valign="top">'.$pub->Summary."<br/>[".$pub->AwardConferredBy.']</td>';
        echo '</tr>';

    }
    echo "</table>";
    echo "</div>";
	
	// Grants
	
	echo "<h3>Grants</h3>";
    echo '<div style="width:100%;height:200px;overflow-y:scroll;overflow-x:hidden;resize:vertical;border:1px solid black;padding:3px;margin-bottom:5px;">';
    echo '<table>';
	
    foreach ($data->Profiles[0]->NIHGrants as $pub){
        $pubID = $pub->NIHProjectNumber;
        $checked = "";
        $pub_pri = $postdat->def_priority;
        $display_pri = "display:none;";
        if ($postdat->init) {
            if ($postdat->grant_isset($pubID)) {
                $checked = "checked";
                $display_pri = "";
                $pub1 = $postdat->getgrant($pubID);
                $pub_pri = $pub1['priority'];
            }
        }
        /* automatically checks the top 10 - we don't auto check anything now.
        else if ($cnt < 10) {
            $checked = "checked";
            $display_pri = "";
            $postdat->addpub($pubID,$pub_pri);   
        }
        */
        $title_msg = "Priority (10-99) - Higher numbers appear on top.";
		$check_msg = "Check to prioritize this item";
        $cnt++;
        echo '<tr>';
		echo '<td valign="top">'.$cnt.'</td>';
        echo '<td valign="top"><input type="checkbox" id="ctsi_chk_'.$pubID.'"name="'.$pubID.'" '.$checked.' onclick="clickcheck_grant('."'".$pubID."'".')" title="'.$check_msg.'" alt="'.$check_msg.'" /></td>';
        echo '<td valign="top"><input  class="ctsi-priority-class" type="text" id="ctsi_pri_'.$pubID.'" value="'.$pub_pri.'" name="priority" title="'.$title_msg.'" alt="'.$title_msg.'" style="width: 30px;'.$display_pri.'" maxlength="2" onchange="ctsiChangePriority_grant('."'".$pubID."'".')" /></td>';
        echo '<td valign="top" width="100">'.$pub->NIHFiscalYear.'</td>';
        echo '<td style="border-bottom:1px solid #aaa;padding-bottom:2px;" valign="top">'.$pub->Title.'</td>';
        echo '</tr>';

    }
    echo "</table>";
    echo "</div>";

	
	// display variable list
	echo "<h3>Other Profile Data</h3>";
    echo '<div style="width:100%;height:200px;overflow-y:scroll;overflow-x:hidden;resize:vertical">';
    echo '<table border="1">';
	echo '<tr><td>CTSI_Title</td><td>'.$data->Profiles[0]->Title.'</td></tr>';
	echo '<tr><td>CTSI_Name</td><td>'.$data->Profiles[0]->Name.'</td></tr>';
	echo '<tr><td>CTSI_Department</td><td>'.$data->Profiles[0]->Department.'</td></tr>';
	echo '<tr><td>CTSI_School</td><td>'.$data->Profiles[0]->School.'</td></tr>';
	echo '<tr><td>CTSI_PhotoURL</td><td>'.$data->Profiles[0]->PhotoURL;
	if (!empty($data->Profiles[0]->PhotoURL))echo '<br/><img src="'.$data->Profiles[0]->PhotoURL.'" style="height:150px" />';
	echo '</td></tr>';
	echo '<tr><td>CTSI_Narrative</td><td>'.$data->Profiles[0]->Narrative.'</td></tr>';
	echo '</table';
	echo '</div>';
	
    die();
}

function ctsi_sort_cmp($a,$b){
    return strcmp($b->Date, $a->Date);
}
function ctsi_parse_date($thedate){
    $dat = split("T",$thedate);
    return $dat[0];
}
// reload CTSI data
function ctsi_initialize($post) {

	$ctsi = false;
	$ctsi_data = null;
	
	// get profile data
	$postdat = new ctsi_meta_stor();
	$postdat->load($post->ID);

	if (!empty($postdat->key)) {
		$key = $postdat->key;

		$json_data = ctsi_lookup($key);
		
		if (strlen($json_data) != 0) $ctsi = true;
	}
	if ($ctsi) {
	
		// tune data before decoding from JSON to PHP object
		$json_data = str_replace("NIHGrants_beta","NIHGrants",$json_data);		
		
		// convert to php object
		$ctsi_data = json_decode($json_data);
	}
	return $ctsi_data;
}

// depending on the input key, lookup the CTSI data in 3 different ways.
function ctsi_lookup($key) {
    global $ctsiSourceApp;
    $lookup = "";
    if (strpos($key,'@') !== false) {
        $lookup = "http://api.profiles.ucsf.edu/json/v2/?FNO=$key&publications=full&source=$ctsiSourceApp";
    }
    elseif(strpos($key,'.') !== false) {
        $lookup = "http://api.profiles.ucsf.edu/json/v2/?ProfilesURLName=$key&publications=full&source=$ctsiSourceApp";
    }
    else {
        $lookup = "http://api.profiles.ucsf.edu/json/v2/?source=$ctsiSourceApp&ProfilesURLName=$key&publications=full";
    }    
	//echo $lookup."<br/>";
    return file_get_contents($lookup);
}
// return only the number portion of the PublicationID
function getPublicationID($fullID) {
    $a = split("/",$fullID);
	$n = count($a);
    return $a[$n-1];
}
function getAwardID($obj) {
    $h = md5($obj->Summary);
    return $h;
}
/*
 * AJAX function
 */
function ctsi_update_data () {
    global $wpdb; // this is how you get access to the database
    
    $postid = intval($_REQUEST['postid']);
    $op = $_REQUEST['op'];  //select,pri 
    $pubid = $_REQUEST['pubid'];
    $val = $_REQUEST['val'];
    
    print_r($_REQUEST);
    
    $postdat = new ctsi_meta_stor();
    $postdat->load($postid);
    
    switch($op) {
        case "select":
            if ($val==0) {
                $postdat->delpub($pubid);
            } else {
                $postdat->addpub($pubid);
            }
        break;
        case "select_grant":
            if ($val==0) {
                $postdat->delgrant($pubid);
            } else {
                $postdat->addgrant($pubid);
            }
        break;
        case "select_award":
            if ($val==0) {
                $postdat->delaward($pubid);
            } else {
                $postdat->addaward($pubid);
            }
        break;
        case "change_priority":
            if ($postdat->pub_isset($pubid)) {
                $postdat->setpubdata($pubid,"priority",$val);
            }
            break;
        case "chg_grant_pri":
            if ($postdat->grant_isset($pubid)) {
                $postdat->setgrantdata($pubid,"priority",$val);
            }
            break;
        case "chg_award_pri":
            if ($postdat->award_isset($pubid)) {
                $postdat->setawarddata($pubid,"priority",$val);
            }
            break;
        case "change_def_lines":
            $postdat->def_num_rows = intval($val);
            break;
        default:
            echo "unknown opcode";
            die();
    }
    $postdat->save($postid);
    echo "ok";
    die();
}

/*
 * BEGIN - Plugin meta box setup
 */

/* Meta box setup function. */
function ctsi_meta_boxes_setup() {

	/* Add meta boxes on the 'add_meta_boxes' hook. */
	add_action( 'add_meta_boxes', 'ctsi_add_post_meta_boxes' );
}
/* Create one or more meta boxes to be displayed on the post editor screen. */
function ctsi_add_post_meta_boxes() {

	add_meta_box(
		'ctsi-post-class',			// Unique ID
		esc_html__( 'CTSI Profile'),		// Title
		'ctsi_post_class_meta_box',		// Callback function
		'page',					// Admin page (or post type)
		'advanced',				// Context
		'default'				// Priority
	);
}
/* 
 * Display the post meta box. Javascript
 */
function ctsi_post_class_meta_box( $object, $box ) {
	
    global $post;
    // setup AJAX
    wp_nonce_field( basename( __FILE__ ), 'ctsi_post_class_nonce' ); 
    $nonce = wp_create_nonce("ctsi_nonce");
    $link = admin_url('admin-ajax.php?action=ctsi_publicationlist&post_id='.$post->ID.'&nonce='.$nonce);
    echo '<a style="display:none;" id="ctsi_thing" data-nonce="'.$nonce. '" data-postid="'.$post->ID .'" href="'.$link.'">ctsi</a>';

    $postdat = new ctsi_meta_stor();
    $postdat->load($post->ID);
    $key = "";
    if (!empty($postdat->key)) $key = $postdat->key;
	
?>
	<div id="theform" action="javascript:void(0);"  style="display:none">
            <div id="foundit" style="display:none"></div>
            <label for="ctsi-search-value"><?php _e( 'Specify UCSF ID (ie "richard.schneider", or a Profile Node ID):' ); ?></label>
            <?php //echo getcwd()." "; echo dirname(__FILE__)." "; echo plugins_url( '' , __FILE__ ); ?>
            <br />
            <input style="width:300px;" type="text" name="ctsi-post-class" id="ctsi-search-value" value="<?php echo $key; ?>" size="30" />
            <input id="ctsi-search" type="button" value="Search/Refresh" onclick="ctsiSearch();" />
            <label> Default Rows:</label>
            <input style="width:30px;" type="text" name="ctsi-post-class" id="ctsi-def-rows" value="<?php echo $postdat->def_num_rows ?>" onchange="ctsiChangeDefLines();" />
            <input type="button" value="Reset" onclick="ctsiReset();" id="ctsi-reset" disabled />
            <div id="ctsi-pub-list-admin"></div>
	</div>
	<script>
		jQuery( document ).ready(function($) {
			var display_meta = 0;
			//$("#foundit").html(jQuery("#page_template option:selected").text());
			if ($("#page_template option:selected").text() == "Faculty Template Detail" ||
			$("#page_template option:selected").text() == "A2 Faculty Detail" ) {
				$("#theform").show();
				display_meta = 1;
			}
			
			//$("#ctsi-save").button().button("disable");	// if enabled, caused "Add Media" button to stop working
			//$("#ctsi-reset").button().button("disable");
			//$("#ctsi-search").button().button("enable");
			$("#ctsi-save").prop("disabled",true);
			$("#ctsi-reset").prop("disabled",true);
			$("#ctsi-search").prop("disabled",false);
			
			$(".ctsi-priority-class").keypress(function(event){
				if(event.which === 13){
					$(".ctsi-priority").change();
					return false;
				}
			});            
			$("#ctsi-search-value").keypress(function(event){
				if(event.which === 13){
					$("#ctsi-search").click();
					return false;
				}
			});            
			$("#ctsi-def-rows").keypress(function(event){
				if(event.which === 13){
					$("#ctsi-def-rows").change();
					return false;
				}
			}); 
			if (display_meta)  ctsiSearch();
		});            
		function ctsiSearch() {
			(function( $ ) {
				$(function() {
					var val = $('#ctsi-search-value').val();
					$('#ctsi-pub-list-admin').html("Searching...");
					
					if (val.length == 0) return;

					//var req = "<?php echo plugins_url( '' , __FILE__ )."/ctsilist.php?key="; ?>"+val;
					//$.get(req, function(data) {
					//    $('#ctsi-pub-list').html(data);
					//});
					
					nonce = $("#ctsi_thing").attr("data-nonce");
					
					var data = {
							action: 'ctsi_publication_list',
							key: val,
							post_id: <?php echo $post->ID; ?>,
							nonce: nonce
					};

					// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
					$.post(ajaxurl, data, function(response) {
						//alert('Got this from the server: ' + response);
						$('#ctsi-pub-list-admin').html(response);
					});
				});
			})(jQuery);
		}
		function clickcheck(n) {    
			(function( $ ) {
				$(function() {
					var val = 0;
					if ($("#ctsi_chk_"+n).is(':checked')) {
						$("#ctsi_pri_"+n).show();
						val = 1;
					} else {
						$("#ctsi_pri_"+n).hide();
					}
					var data = {
							action: 'ctsi_update_data',
							op: "select",
							postid: <?php echo $post->ID; ?>,
							pubid: n,
							val: val,
							nonce: nonce
					};
					$.post(ajaxurl, data, function(response) {
						//console.log("ctsi_update_data "+response);
					});
				});
			})(jQuery);
		}
		function clickcheck_grant(n) {    
			(function( $ ) {
				$(function() {
					var val = 0;
					if ($("#ctsi_chk_"+n).is(':checked')) {
						$("#ctsi_pri_"+n).show();
						val = 1;
					} else {
						$("#ctsi_pri_"+n).hide();
					}
					var data = {
							action: 'ctsi_update_data',
							op: "select_grant",
							postid: <?php echo $post->ID; ?>,
							pubid: n,
							val: val,
							nonce: nonce
					};
					$.post(ajaxurl, data, function(response) {
						//console.log("ctsi_update_data "+response);
					});
				});
			})(jQuery);
		}
		function clickcheck_award(n) {    
			(function( $ ) {
				$(function() {
					var val = 0;
					if ($("#ctsi_chk_"+n).is(':checked')) {
						$("#ctsi_pri_"+n).show();
						val = 1;
					} else {
						$("#ctsi_pri_"+n).hide();
					}
					var data = {
							action: 'ctsi_update_data',
							op: "select_award",
							postid: <?php echo $post->ID; ?>,
							pubid: n,
							val: val,
							nonce: nonce
					};
					$.post(ajaxurl, data, function(response) {
						//console.log("ctsi_update_data "+response);
					});
				});
			})(jQuery);
		}
		function ctsiChangePriority(pubid) {
			(function( $ ) {
				$(function() {
					var val = $('#ctsi_pri_'+pubid).val();
					var data = {
							action: 'ctsi_update_data',
							op: "change_priority",
							postid: <?php echo $post->ID; ?>,
							pubid: pubid,
							val: val,
							nonce: nonce
					};
					$.post(ajaxurl, data, function(response) {
						//console.log("ctsi_update_data "+response);
					});
				});
			})(jQuery);
		}
		function ctsiChangePriority_grant(pubid) {
			(function( $ ) {
				$(function() {
					var val = $('#ctsi_pri_'+pubid).val();
					var data = {
							action: 'ctsi_update_data',
							op: "chg_grant_pri",
							postid: <?php echo $post->ID; ?>,
							pubid: pubid,
							val: val,
							nonce: nonce
					};
					$.post(ajaxurl, data, function(response) {
						//console.log("ctsi_update_data "+response);
					});
				});
			})(jQuery);
		}
		function ctsiChangePriority_award(pubid) {
			(function( $ ) {
				$(function() {
					var val = $('#ctsi_pri_'+pubid).val();
					var data = {
							action: 'ctsi_update_data',
							op: "chg_award_pri",
							postid: <?php echo $post->ID; ?>,
							pubid: pubid,
							val: val,
							nonce: nonce
					};
					$.post(ajaxurl, data, function(response) {
						//console.log("ctsi_update_data "+response);
					});
				});
			})(jQuery);
		}
		function ctsiChangeDefLines() {
			(function( $ ) {
				$(function() {
					var val = $('#ctsi-def-rows').val();
					var data = {
							action: 'ctsi_update_data',
							op: "change_def_lines",
							postid: <?php echo $post->ID; ?>,
							val: val,
							nonce: 0
					};
					$.post(ajaxurl, data, function(response) {
					});
				});
			})(jQuery);
		}
	</script>
<?php }
/*
 * END plugin meta box
 */

if(class_exists('Ortho_CTSI_Profile'))
{
	// Installation and uninstallation hooks
	register_activation_hook(__FILE__, array('Ortho_CTSI_Profile', 'activate'));
	register_deactivation_hook(__FILE__, array('Ortho_CTSI_Profile', 'deactivate'));

	// instantiate the plugin class
	$ortho_ctsi_Profile = new Ortho_CTSI_Profile();
	
    // Add a link to the settings page onto the plugin page
    
    if(isset($ortho_ctsi_profile))
    {
        // Add the settings link to the plugins page
        function ctsi_profile_plugin_settings_link($links)
        { 
            $settings_link = '<a href="options-general.php?page=ortho_ctsi_profile">Settings</a>'; 
            array_unshift($links, $settings_link); 
            return $links; 
        }

        $plugin = plugin_basename(__FILE__); 
        add_filter("plugin_action_links_$plugin", 'ctsi_profile_plugin_settings_link');
    }
    
}
function ctsi_profile_title_check($title)
{
    
    global $ctsi_profile_did_filters_added;
    
    if (!isset ($ctsi_profile_did_filters_added) || $ctsi_profile_did_filters_added != true)
    { 
        addthis_add_content_filters(); 
        add_filter('the_content', 'ctsi_profile_script_to_content');
    }
    else
    {
    }

    return $title;
}


/**
 */
/*
function ctsi_profile_script_to_content($content)
{
    global $ctsi_profile_did_script_output;

   
    $postid = get_the_ID();

    $urlList = '"'.get_permalink($postid).'"';
    $count = 1;
    //$blog_set_json = get_post_meta( $postid, 'HMMultipostMU_set', true );
    $count_threshold = get_option("ortho_addthis_count_threshold","10");

    if (!empty($blog_set_json)) { 
        $blog_set = json_decode( $blog_set_json,true );
        foreach($blog_set as $k=>$v) {
            if ($k=="source") continue; // ignore the "source" element
            if ($postid==$v) {          // ignore current blog
            }
            else {
                // switch blog context
                if (switch_to_blog($k) === true) {
                    $urlList .= ',"'.get_permalink( $v ).'"';
                    restore_current_blog();
                    $count++;
                }
            }
        }
    }
    $script = '
        <div>hey hey hey</div>
        <script type="text/javascript">
        jQuery(document).ready( function() {
            console.log("post ID='.$postid.'");
        })        
        </script>
    ';
    

    if (!isset($addthis_did_script_output) )
    {
        $addthis_did_script_output = true;
        $content = 
            $content.
            $script;
    }
    return $content ;
}
*/
/*
 * client-side list renderer - called from functions_ucsf.php
 */
function ctsi_display_publications($postid) {
    //echo "Post ID = ".$postid;
    
    $postdat = new ctsi_meta_stor();
    $postdat->load($postid);

    if (empty($postdat->key)) {
        //echo "no key";
        return;
    }
    $key = $postdat->key;
    
    $json_data = ctsi_lookup($key);
    
    if (strlen($json_data)==0) {
        //echo $key.' CTSI profile was not found.';
        echo '<p id="hidden-count" style="display:none" value="0"></p>'; // flag tells jscript how many hidden pubs there are
        return;
    }

    $data = json_decode($json_data);
    
	if (count($data->Profiles[0]->Publications)==0)
		return;
	
    usort($data->Profiles[0]->Publications,"ctsi_sort_cmp");

    //echo "hello world";
    echo "<ul id='ctsi-pub-list'>";
    $rows = $postdat->def_num_rows;
    $row = 0;
    $count_hidden = 0;
    foreach ($data->Profiles[0]->Publications as $pub){
        $pubID = getPublicationID($pub->PublicationID);
        $hidden = 'ctsi-hide';
        if ($postdat->pub_isset($pubID) || $row < $rows) $hidden = "";
        else $count_hidden++;
        $psrc = $pub->PublicationSource;
        $linktext = $psrc[0]->PublicationSourceName;
        $linkurl = $psrc[0]->PublicationSourceURL;
        $pub1 = $postdat->getpub($pubID);
        $priority = $pub1['priority'];
        if (strlen($priority)==0) $priority = (string)$postdat->def_priority;

        echo '<li priority="'.$priority." ".$pub->Date.'" class="'.$hidden.'">';
        echo $pub->PublicationTitle;
        if (strlen($linktext)) {
            echo ' <a class="ctsi-pub-link" title="View" alt="View" href="'.$linkurl.'" target="_blank">'.$linktext.'</a>';
        }
        echo '</li>';
        $row++;
    }
    echo "</ul>";
    echo '<p id="hidden-count" style="display:none" value="'.$count_hidden.'"></p>'; // flag tells jscript how many hidden pubs there are

}
function ctsi_display_awards($postid) {
    //echo "Post ID = ".$postid;
    
    $postdat = new ctsi_meta_stor();
    $postdat->load($postid);

    if (empty($postdat->key)) {
        //echo "no key";
        return;
    }
    $key = $postdat->key;
    
    $json_data = ctsi_lookup($key);
    
    if (strlen($json_data)==0) {
        //echo $key.' CTSI profile was not found.';
        echo '<p id="hidden-award-count" style="display:none" value="0"></p>'; // flag tells jscript how many hidden pubs there are
        return;
    }

    $data = json_decode($json_data);
    
    usort($data->Profiles[0]->Publications,"ctsi_sort_cmp");

    echo "<ul id='ctsi-award-list'>";
    $rows = $postdat->def_num_rows;
    $row = 0;
    $count_hidden = 0;
    foreach ($data->Profiles[0]->AwardOrHonors as $pub){
        $pubID = getAwardID($pub);
        $hidden = 'ctsi-hide';
        if ($postdat->award_isset($pubID) || $row < $rows) $hidden = "";
        else $count_hidden++;
		//$hidden = "";
        $pub1 = $postdat->getaward($pubID);
        $priority = $pub1['priority'];
        if (strlen($priority)==0) $priority = (string)$postdat->def_priority;

        echo '<li priority="'.$priority." ".$pub->AwardStartDate.'" class="'.$hidden.'">';
        echo $pub->Summary;
        echo '</li>';
        $row++;
    }
    echo "</ul>";
    echo '<p id="hidden-award-count" style="display:none" value="'.$count_hidden.'"></p>'; // flag tells jscript how many hidden pubs there are

}
function ctsi_display_grants($postid) {
    
    $postdat = new ctsi_meta_stor();
    $postdat->load($postid);

    if (empty($postdat->key)) {
        //echo "no key";
        return;
    }
    $key = $postdat->key;
    
    $json_data = ctsi_lookup($key);
    
    if (strlen($json_data)==0) {
        //echo $key.' CTSI profile was not found.';
        echo '<p id="hidden-grant-count" style="display:none" value="0"></p>'; // flag tells jscript how many hidden pubs there are
        return;
    }
	$json_data = str_replace("NIHGrants_beta","NIHGrants",$json_data);		

    $data = json_decode($json_data);
    
    usort($data->Profiles[0]->AwardOrHonors,"ctsi_sort_cmp");

    echo "<ul id='ctsi-grant-list'>";
    $rows = $postdat->def_num_rows;
    $row = 0;
    $count_hidden = 0;
    foreach ($data->Profiles[0]->NIHGrants as $pub){
        $pubID = $pub->NIHProjectNumber;
        $hidden = 'ctsi-hide';
        if ($postdat->grant_isset($pubID) || $row < $rows) $hidden = "";
        else $count_hidden++;
		//$hidden = "";
        $pub1 = $postdat->getgrant($pubID);
        $priority = $pub1['priority'];
        if (strlen($priority)==0) $priority = (string)$postdat->def_priority;

        echo '<li priority="'.$priority." ".$pub->NIHFiscalYear.'" class="'.$hidden.'">';
        echo $pub->Title.", ".$pub->NIHFiscalYear;
        echo '</li>';
        $row++;
    }
    echo "</ul>";
    echo '<p id="hidden-grant-count" style="display:none" value="'.$count_hidden.'"></p>'; // flag tells jscript how many hidden pubs there are

}

// renders award list on the customer side - 
function ctsi_display_award_data($data) {
    $str = "<ul id='ctsi-award-list'>";
    $row = 0;
    $count_hidden = 0;
	
	if (empty($data->Profiles[0]->AwardOrHonors)) return "";
	
    foreach ($data->Profiles[0]->AwardOrHonors as $item){
        $hidden = 'ctsi-hide';
        $count_hidden++;
        $awardLabel = $item->AwardLabel;
		$awardStartDate = $item->AwardStartDate;
		$awardEndDate = $item->AwardEndDate;
		$awardSummary = $item->Summary;
		$awardConferredBy = $item->AwardConferredBy;
		
		$priority = "50";
		$awardDate = $awardStartDate;
		$hidden = "";
		
        $str .= '<li priority="'.$priority." ".$awardDate.'" class="ctsi-award-item '.$hidden.'">';
        $str .= $awardSummary;
        $str .= '</li>';
        $row++;
    }
    $str .= "</ul>";
	return $str;
}

function ctsi_display_grant_data($data) {

	$str = "<ul id='ctsi-grant-list'>";
	
    $row = 0;
    $count_hidden = 0;
	
	if (empty($data->Profiles[0]->NIHGrants)) return "";
	
    foreach ($data->Profiles[0]->NIHGrants as $item){
        $hidden = 'ctsi-hide';
        $count_hidden++;
        $grantProjNum = $item->NIHProjectNumber;
		$grantYear = $item->NIHFiscalYear;
		$grantTitle = $item->Title;
		
		$priority = "50";
		$grantDate = $grantYear;
		$hidden = "";
		
        $str .= '<li priority="'.$priority." ".$grantDate.'" class="ctsi-grant-item '.$hidden.'">';
        $str .= $grantTitle.", ".$grantDate;
        $str .= '</li>';
        $row++;
    }
	
    $str .= "</ul>";
	return $str;
}
/*
 * wp_footer - action
 * detect template and modify publication list.
 */
function fn_footer_action () {
    if ( ! is_page_template('faculty_template1.php') ) return;
    echo "faculty_template detected";
}
function ctsi_theme_action () {
    echo "theme hook detected";
}
/*
 * intercept the get_post_meta
 */
function ctsi_process_publications($metadata, $object_id, $meta_key, $single) {
    if (!is_page_template('faculty_template1.php')) return $metadata;

    if ( isset($meta_key) && 'publications' === $meta_key ) {
            $tmp = $metadata;
            return strlen($tmp);
    }
    return $metadata;

}

//Specify 4 arguments for this filter in the last parameter.
//add_filter('get_post_metadata', 'ctsi_process_publications', true, 4);
    