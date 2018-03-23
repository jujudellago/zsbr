<?php

class Wp_framework_Youtube_wpress
{
	
	static $_settings;
	
	function init($criteria=array()) {
		self::$_settings = $criteria['settings'];
		
		add_action('wp_enqueue_scripts', array(__CLASS__, 'wp_enqueue_scripts'));
		
		add_action('wp_footer', array(__CLASS__, 'wp_footer') );
		//add_action( 'wp_print_scripts', array(__CLASS__', 'wp_footer') );
		
		add_action('wp_ajax_nopriv_listener_'.self::$_settings['plugin_class_name'], array(__CLASS__, 'ajax_listeners_front_end') );
		add_action('wp_ajax_listener_'.self::$_settings['plugin_class_name'], array(__CLASS__, 'ajax_listeners_front_end') );
		
		if(is_admin()) {
			//JS & CSS
			add_action('admin_init', array(__CLASS__, 'wp_enqueue_scripts_admin')); //admin_head, admin_init, admin_print_scripts
			//Settings
			if(self::$_settings['settings_page_link']!='') {
				add_action('admin_menu', array(__CLASS__, 'settings_page_init') );
				add_filter('plugin_action_links', array(__CLASS__, 'settings_link_display'), 10, 2);
				add_action('wp_ajax_admin_listener_'.self::$_settings['plugin_class_name'], array(__CLASS__, 'ajax_listeners') );
			}
			//activation
			register_activation_hook(self::$_settings['full_file_path'], array(__CLASS__, 'plugin_activation'));
		}
		else {
			//AJAX
			//add_action('wp_ajax_nopriv_listener_'.self::$_settings['plugin_class_name'], array(__CLASS__, 'ajax_listeners_front_end') );
			//add_action('wp_ajax_listener_'.self::$_settings['plugin_class_name'], array(__CLASS__, 'ajax_listeners_front_end') );
		}
	}
	
	function ajax_listeners_front_end() {
		$s1 = new self::$_settings['plugin_class_name']();
		$s1->ajax_listeners();
	}
	
	function plugin_activation() {
		$s1 = new self::$_settings['plugin_class_name']();
		$s1->on_plugin_activation();
	}
	
	function wp_footer() {
		$s1 = new self::$_settings['plugin_class_name']();
		$s1->add_scripts_wp_footer();
	}
	
	/*
	START #############################
	Admin settings
	*/
	
	function settings_page_init() {
		if (function_exists('add_submenu_page')) {
			add_submenu_page('plugins.php', self::$_settings['menu_title'], self::$_settings['menu_title'], 'manage_options', self::$_settings['settings_page_link'], array(self::$_settings['plugin_class_name'], 'settings_page_display'));
		}
	}
	
	function settings_link_display($links, $file) {
		if(self::$_settings['settings_page_link']!='') {
			if ( $file == plugin_basename( self::$_settings['dirname'].'/'.self::$_settings['main_file_name'] ) ) {
				$links[] = '<a href="plugins.php?page='.self::$_settings['settings_page_link'].'">Settings</a>';
			}
		}
		return $links;
	}
	
	function ajax_listeners() {
		$method = $_POST['method'];
		$id = $_POST['id'];
		$data = $_POST['data'];
		
		if($method=='update_settings_form') {
			
			//token used for unique storage
			$token = self::$_settings['plugin_token'].'_'.$id;
			
			foreach($_POST as $value => $ind) {
				if($value!='method' && $value!='id' && $value!='action') {
					if(is_array($ind)) {
						$form_data[$value] = json_encode($ind);
					}
					else {
						$form_data[$value] = stripslashes($ind);
					}
				}
			}
			update_option($token, $form_data);
		}
		else if($method=='export_csv') {
			$table_name = $_POST['tn'];
			$fields = $_POST['fields'];
			
			global $wpdb;
			$table_name = $wpdb->prefix.$table_name;
			
			$query = "SELECT $fields FROM ". $table_name ." WHERE 1";
			$results = $wpdb->get_results($query, 'ARRAY_A');
			
			$fieldsTab = explode(',', $fields);
			
			if(count($results)>0) {
				$d .= $fields."\n";
				for($i=0; $i<count($results); $i++) {
					$value_tab = array();
					foreach($results[$i] as $ind => $value) {
						$value_tab[] = $value;
					}
					$value = implode(',', $value_tab);
					$d .= $value;
					if($i<count($results)-1) $d .= "\n";
				}
			}
			else {
				$d .= 'No results found';
			}
			
			echo $d;
		}
		
		exit;
	}
	
	function settings_page_display($criteria=array()) {
		$sections = $criteria['sections'];
		echo '
		<script>
		jQuery(function() {
			jQuery( "#accordion" ).accordion({
				collapsible: true,
				autoHeight: false
			});
		});
		</script>';
		?>
		
		<style>
		.normal h3 { padding: 0 0 0px; font-size: 1.2em; }
		</style>
		
		<?php
		echo '<div class="wrap"><div class="metabox-holder">';
		echo '<h2>'.self::$_settings['plugin_title'].' <small><font size="-1">(v'.self::$_settings['plugin_version'].')</font></small></h2>
		<hr style="background:#ddd;color:#ddd;height:1px;border:none;">
		<br>';
		echo '<div id="accordion" class="normal">';
			
			if(count($sections)>0) {
				foreach($sections as $ind => $section) {
					
					$update_btn=0; //init
					$options = get_option(self::$_settings['plugin_token'].'_'.$ind);
					
					echo '<h3><a href="#">'.$section['title'].'</a></h3>';
					echo '<div>';
						echo '<form id="'.$ind.'" class="wpress_form">';
						for($j=0; $j<count($section['form']); $j++) {
							
							if($section['form'][$j]['type']=='checkbox') {
								$value = $options[$section['form'][$j]['name']];
								if($value!='') $value = json_decode($value, true);
								if($value=='') $value = array();
							}
							else {
								$value = $options[$section['form'][$j]['name']];
							}
							
							self::display_form_entries($section['form'][$j], $value);
							
							if($section['form'][$j]['type']=='text' || $section['form'][$j]['type']=='textarea'
							|| $section['form'][$j]['type']=='select' || $section['form'][$j]['type']=='radio'
							|| $section['form'][$j]['type']=='checkbox' || $section['form'][$j]['type']=='colorpicker') {
								$update_btn = 1;
							}
						}
						
						if($update_btn) {
							echo '<p><input type="submit" id="wp_framework_update_btn" value="Update"> <span class="saving_msg"></span></p>';
						}
						
						echo '</form>';
					echo '</div>';
				}
			}
			
		echo '</div>';
		echo '</div></div>';
	}
	
	function display_form_entries($criteria, $value="") {
		$type = $criteria['type'];
		$name = $criteria['name'];
		$title = $criteria['title'];
		$content = $criteria['content'];
		$size = $criteria['size'];
		$cols = $criteria['cols'];
		$rows = $criteria['rows'];
		$values = $criteria['values'];
		
		if($size=='') $size=60;
		if($cols=='') $cols=80;
		if($rows=='') $rows=3;
		
		if($type=='content') {
			echo '<p>'.$content.'</p>';
		}
		elseif($type=='text') {
			echo '<p><div style="color:#504f4f; margin-bottom:5px;">'.$title.'</div>';
			echo '<input type="text" id="'.$name.'" name="'.$name.'" value="'.$value.'" size="'.$size.'"></p>';
		}
		elseif($type=='textarea') {
			echo '<p><div style="color:#504f4f; margin-bottom:5px;">'.$title.'</div>';
			echo '<textarea id="'.$name.'" name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'">'.$value.'</textarea></p>';
		}
		elseif($type=='select') {
			echo '<p><div style="color:#504f4f; margin-bottom:5px;">'.$title.'</div>';
			echo '<select id="'.$name.'" name="'.$name.'">';
			foreach($values as $ind=>$v) {
				if($ind==$value) echo '<option selected value="'.$ind.'">'.$v.'</option>';
				else echo '<option value="'.$ind.'">'.$v.'</option>';
			}
			echo '</select></p>';
		}
		elseif($type=='radio') {
			echo '<p><div style="color:#504f4f; margin-bottom:5px;">'.$title.'</div>';
			foreach($values as $ind=>$v) {
				echo '<span style="margin-right:10px;">';
				if($value==$ind) echo '<label><input type="radio" id="'.$name.'" name="'.$name.'" value="'.$ind.'" checked> '.$v.'</label>';
				else echo '<label><input type="radio" id="'.$name.'" name="'.$name.'" value="'.$ind.'"> '.$v.'</label>';
				echo '</span>';
			}
			echo '</p>';
		}
		elseif($type=='checkbox') {
			echo '<p><div style="color:#504f4f; margin-bottom:5px;">'.$title.'</div>';
			
			foreach($values as $ind=>$v) {
				echo '<span style="margin-right:15px;">';
				if(in_array($ind, $value)) echo '<label><input type="checkbox" id="'.$name.'[]" name="'.$name.'[]" value="'.$ind.'" checked> '.$v.'</label>';
				else echo '<label><input type="checkbox" id="'.$name.'[]" name="'.$name.'[]" value="'.$ind.'"> '.$v.'</label>';
				echo '</span>';
			}
			echo '</p>';
		}
		elseif($type=='colorpicker') {
			echo "<script>
			jQuery(document).ready(function () {
				jQuery('#".$name."').ColorPicker({
					color: '#0000ff',
					onShow: function (colpkr) {
						jQuery(colpkr).fadeIn(500);
						return false;
					},
					onHide: function (colpkr) {
						jQuery(colpkr).fadeOut(500);
						return false;
					},
					onChange: function (hsb, hex, rgb) {
						jQuery('#".$name."').val('#' + hex);
					}
				});
			});
			</script>";
			echo '<p><div style="color:#504f4f; margin-bottom:5px;">'.$title.'</div>';
			echo '<input type="text" id="'.$name.'" name="'.$name.'" value="'.$value.'" maxlength="6" size="6"></p>';
		}
		elseif($type=='custom') {
			echo '<p>'.$content.'</p>';
		}
	}
	
	//####################################################################
	
	/*
	START #############################
	JS & CSS files
	*/
	
	//Include JS or CSS files
	function wp_enqueue_scripts() {
		
		wp_register_script('wp_wpress_js_'.self::$_settings['plugin_class_name'], self::$_settings['plugin_dir_url'].'include/wp_framework/js/wp.js', array('jquery'));
		wp_enqueue_script('wp_wpress_js_'.self::$_settings['plugin_class_name']);
		wp_localize_script('wp_wpress_js_'.self::$_settings['plugin_class_name'], 'Wpress_framework', array('ajaxurl'=>admin_url('admin-ajax.php')));
		
		//JS files
		if(count(self::$_settings['js_files'])>0) {
			foreach(self::$_settings['js_files'] as $value) {
				if(substr($value, 0, 4)!='http') $url = self::$_settings['plugin_dir_url'].$value;
				else $url = $value;
				wp_register_script($value, $url, array('jquery'));
				wp_enqueue_script($value);
			}
		}
		
		//CSS files
		if(count(self::$_settings['css_files'])>0) {
			foreach(self::$_settings['css_files'] as $value) {
				if(substr($value, 0, 4)!='http') $url = self::$_settings['plugin_dir_url'].$value;
				else $url = $value;
				wp_register_style($value, $url);
				wp_enqueue_style($value);
			}
		}
	}
	
	//JS & CSS files in Admin
	function wp_enqueue_scripts_admin() {
		
		//Load framework JS & CSS
		if(self::$_settings['settings_page_link']!='' && $_GET['page']==self::$_settings['settings_page_link']) {
			wp_register_script('jqueryui_js', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js', array('jquery'));
			wp_enqueue_script('jqueryui_js');
			wp_register_script('wp_wpress_js', self::$_settings['plugin_dir_url'].'include/wp_framework/js/wp_admin.js', array('jquery'));
			wp_enqueue_script('wp_wpress_js');
			wp_localize_script('wp_wpress_js', 'Wpress_framework', array('ajaxurl'=>admin_url('admin-ajax.php'), 'action'=>'admin_listener_'.self::$_settings['plugin_class_name']));
			wp_register_style('jqueryui_css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.0/themes/base/jquery-ui.css');
			wp_enqueue_style('jqueryui_css');
			/*
			//Bootstrap
			wp_register_script('bootstrap_js', self::$_settings['plugin_dir_url'].'include/wp_framework/js/bootstrap.js', array('jquery'));
			wp_enqueue_script('bootstrap_js');
			wp_register_style('bootstrap_css', self::$_settings['plugin_dir_url'].'include/wp_framework/css/bootstrap.css');
			wp_enqueue_style('bootstrap_css');
			*/
			//color picker
			wp_register_script('colorpicker_js', self::$_settings['plugin_dir_url'].'include/wp_framework/js/colorpicker/js/colorpicker.js', array('jquery'));
			wp_enqueue_script('colorpicker_js');
			wp_register_style('colorpicker_css', self::$_settings['plugin_dir_url'].'include/wp_framework/js/colorpicker/css/colorpicker.css');
			wp_enqueue_style('colorpicker_css');
			//wp_framework_css
			wp_register_style('wp_framework_css', self::$_settings['plugin_dir_url'].'include/wp_framework/css/style.css');
			wp_enqueue_style('wp_framework_css');
		}
		
		//JS files
		if(count(self::$_settings['js_files_admin'])>0) {
			foreach(self::$_settings['js_files_admin'] as $value) {
				if(substr($value, 0, 4)!='http') $url = self::$_settings['plugin_dir_url'].$value;
				else $url = $value;
				wp_register_script($value, $url, array('jquery'));
				wp_enqueue_script($value);
			}
		}
		
		//CSS files
		if(count(self::$_settings['css_files_admin'])>0) {
			foreach(self::$_settings['css_files_admin'] as $value) {
				if(substr($value, 0, 4)!='http') $url = self::$_settings['plugin_dir_url'].$value;
				else $url = $value;
				wp_register_style($value, $url);
				wp_enqueue_style($value);
			}
		}
	}
	
	/*
	START Facebook functions
	*/
	
	function getFbJsSDK($criteria=array()) {
		$fb_app_id = $criteria['fb_app_id'];
		$lang = $criteria['lang'];
		
		if($lang=='') $lang='en_US';
		
		$d .= '<div id="fb-root"></div>';
		$d .= '<script>';
		
		$d .= 'window.fbAsyncInit = function() {';
		$d .= 'FB.init({appId: '.$fb_app_id.', status: true, cookie: true, xfbml: true, oauth: true});';
		
		$d .= '};';
		  
		$d .= '(function() {';
			$d .= 'var e = document.createElement(\'script\'); e.async = true;';
		    $d .= 'e.src = document.location.protocol +';
		    $d .= '\'//connect.facebook.net/'.$lang.'/all.js\';';
		    $d .= 'document.getElementById(\'fb-root\').appendChild(e);';
		$d .= '}());';
		  
		$d .= '</script>';
		
		return $d;
	}
	
	function getFbCookie() {
		$cookie = self::get_facebook_cookie(self::$_settings['fb_app_id'], self::$_settings['fb_app_secret']);
		return $cookie;
	}
	
	function getFbUserData() {
		$fb_cookie = self::getFbCookie();
		if($fb_cookie) {
			$url = 'https://graph.facebook.com/me?access_token='.$fb_cookie['access_token'];
			$data = json_decode(self::getDataFromUrl($url));
			$fb['id'] = $data->id;
			$fb['name'] = $data->name;
			$fb['username'] = $data->username;
			$fb['first_name'] = $data->first_name;
			$fb['last_name'] = $data->last_name;
			$fb['link'] = $data->link;
			$fb['birthday'] = $data->birthday;
			$fb['gender'] = $data->gender;
			$fb['email'] = $data->email;
			$fb['timezone'] = $data->timezone;
			$fb['locale'] = $data->locale;
			$fb['updated_time'] = $data->updated_time;
			$fb['picture'] = 'https://graph.facebook.com/'.$data->id.'/picture'; //?type=large
			$fb['birthday'] = $data->birthday;
			$fb['bio'] = $data->bio;
			$fb['hometown'] = $data->hometown->name;
			$fb['location'] = $data->location->name;
			//tokens
			$fb['token'] = $fb_cookie['access_token'];
			$fb['token_expires'] = $fb_cookie['expires'];
			return $fb;
		}
	}
	
	function parse_signed_request($signed_request, $secret) {
		list($encoded_sig, $payload) = explode('.', $signed_request, 2); 
		
		// decode the data
		$sig = self::base64_url_decode($encoded_sig);
		$data = json_decode(self::base64_url_decode($payload), true);
		
		if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
			return null;
		}
		
		// check sig
		$expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
		if ($sig !== $expected_sig) {
			return null;
		}
		
		return $data;
	}
	
	function base64_url_decode($input) {
		return base64_decode(strtr($input, '-_', '+/'));
	}
	
	function get_facebook_cookie($app_id, $app_secret) {
	    $signed_request = self::parse_signed_request($_COOKIE['fbsr_' . $app_id], $app_secret);
	    //$signed_request[uid] = $signed_request[user_id]; // for compatibility 
	    if (!is_null($signed_request)) {
	    	$url = "https://graph.facebook.com/oauth/access_token?client_id=$app_id&redirect_uri=&client_secret=$app_secret&code=$signed_request[code]";
	    	$access_token_response = self::getDataFromUrl($url);
	        parse_str($access_token_response);
	        $signed_request[access_token] = $access_token;
	        if($expires==0) $signed_request[expires] = 0;
	        else $signed_request[expires] = time() + $expires;
	    }
	    return $signed_request;
	}
	
	function getDataFromUrl($url) {
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
	function updateFacebookStatus($criteria, $token='') {
		$fb_id = $criteria['fb_id'];
		$message = $criteria['message'];
		$link = $criteria['link'];
		$picture = $criteria['picture'];
		$name = $criteria['name'];
		$caption = $criteria['caption'];
		$description = $criteria['description'];
		$source = $criteria['source'];
		
		if($fb_id=='') $fb_id = 'me';
		
		$criteriaString = '&message='.$message;
		if($link!='') $criteriaString .= '&link='.$link;
		if($picture!='') $criteriaString .= '&picture='.$picture;
		if($name!='') $criteriaString .= '&name='.$name;
		if($caption!='') $criteriaString .= '&caption='.$caption;
		if($description!='') $criteriaString .= '&description='.$description;
		if($source!='') $criteriaString .= '&source='.$source;
		
		if($token=='') $token = $this->getAccessToken();
		$postParms = "access_token=".$token.$criteriaString;
		
		$ch = curl_init('https://graph.facebook.com/'.$fb_id.'/feed');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postParms);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //to make it support SSL calls on some servers
		$results = curl_exec($ch);
		curl_close($ch);
	}
	
	/*
	START DB Functions
	*/
	
	function sqlInsert($table_name, $fields=array()) {
		global $wpdb;
		$table_name = $wpdb->prefix.$table_name;
		
		$query = "INSERT INTO ". $table_name." (";
		$s = "";
		foreach($fields as $key => $value) {
			if($value || is_numeric($value)) {
				$query.= $s.$key;
				$s = ", ";
			}
		}
		$query .= ") VALUES (";
		$s = "";
		foreach($fields as $value) {
			if($value || is_numeric($value)) {
				$query.= $s."'".$value."'";
				$s = ", ";
			}
		}
		$query .= ")";
		
		$wpdb->query($wpdb->prepare($query));
	}
	
	/*
	function sqlUpdate($table_name, $condition=array(), $fields=array()) {
		global $wpdb;
		$table_name = $wpdb->prefix.$table_name;
		
		$query = "UPDATE ". $table_name." SET ";
		
		$i=0;
		foreach($fields as $key => $value) {
			if($i==0) $query.= $key."='$value'";
			else $query.= ', '.$key."='$value'";
			$i++;
		}
		
		$query .= " WHERE ";
		foreach($condition) {
			
		}
		
		echo $query;
		
		//$wpdb->query($wpdb->prepare($query));
	}
	*/
	
	function sqlLoadByFields($table_name, $fields=array(), $options=array()) {
		global $wpdb;
		$table_name = $wpdb->prefix.$table_name;
		
		$query = "SELECT * FROM ". $table_name ." WHERE 1";
		if(count($fields)>0) {
			foreach($fields as $ind=>$val) {
				if($ind!='' && $val!='') $query .= " AND " .$ind. " = '" .$val. "'";
			}
		}
		
		if(count($options)>0) {
			foreach($options as $i => $v) {
				if($i=='group') $groupByCond = " GROUP BY ".$v." ";
				if($i=='order') $orderByCond = " ORDER BY ".$v." ";
				if($i=='limit') $limitCond = " limit ".$v." ";
			}
			$query .= $groupByCond.$orderByCond.$limitCond;
		}
		
		//echo $query;
		
		$results = $wpdb->get_results($query, 'ARRAY_A');
		return $results;
	}
}

?>