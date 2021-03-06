<?php
if (!function_exists ('add_action')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}
class Qode_Import {

    public $message = "";
    public $attachments = false;
    function Qode_Import() {
        add_action('admin_menu', array(&$this, 'qode_admin_import'));
        add_action('admin_init', array(&$this, 'register_qode_theme_settings'));

    }
    function register_qode_theme_settings() {
        register_setting( 'qode_options_import_page', 'qode_options_import');
    }

    function init_qode_import() {
        if(isset($_REQUEST['import_option'])) {
            $import_option = $_REQUEST['import_option'];
            if($import_option == 'content'){
                $this->import_content('proya_content.xml');
            }elseif($import_option == 'custom_sidebars') {
                $this->import_custom_sidebars('custom_sidebars.txt');
            } elseif($import_option == 'widgets') {
                $this->import_widgets('widgets.txt','custom_sidebars.txt');
            } elseif($import_option == 'options'){
                $this->import_options('options.txt');
            }elseif($import_option == 'menus'){
                $this->import_menus('menus.txt');
            }elseif($import_option == 'settingpages'){
                $this->import_settings_pages('settingpages.txt');
            }elseif($import_option == 'complete_content'){
                $this->import_content('proya_content.xml');
                $this->import_options('options.txt');
                $this->import_widgets('widgets.txt','custom_sidebars.txt');
                $this->import_menus('menus.txt');
                $this->import_settings_pages('settingpages.txt');
                $this->message = __("Content imported successfully", "qode");
            }
        }
    }

    public function import_content($file){
        if (!class_exists('WP_Importer')) {
            ob_start();
            $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
            require_once($class_wp_importer);
            require_once(get_template_directory() . '/includes/import/class.wordpress-importer.php');
            $qode_import = new WP_Import();
            set_time_limit(0);
            $path = get_template_directory() . '/includes/import/files/' . $file;

            $qode_import->fetch_attachments = $this->attachments;
            $returned_value = $qode_import->import($file);
            if(is_wp_error($returned_value)){
                $this->message = __("An Error Occurred During Import", "qode");
            }
            else {
                $this->message = __("Content imported successfully", "qode");
            }
            ob_get_clean();
        } else {
            $this->message = __("Error loading files", "qode");
        }
    }

    public function import_widgets($file, $file2){
        $this->import_custom_sidebars($file2);
        $options = $this->file_options($file);
        foreach ((array) $options['widgets'] as $qode_widget_id => $qode_widget_data) {
            update_option( 'widget_' . $qode_widget_id, $qode_widget_data );
        }
        $this->import_sidebars_widgets($file);
        $this->message = __("Widgets imported successfully", "qode");
    }

    public function import_sidebars_widgets($file){
        $qode_sidebars = get_option("sidebars_widgets");
        unset($qode_sidebars['array_version']);
        $data = $this->file_options($file);
        if ( is_array($data['sidebars']) ) {
            $qode_sidebars = array_merge( (array) $qode_sidebars, (array) $data['sidebars'] );
            unset($qode_sidebars['wp_inactive_widgets']);
            $qode_sidebars = array_merge(array('wp_inactive_widgets' => array()), $qode_sidebars);
            $qode_sidebars['array_version'] = 2;
            wp_set_sidebars_widgets($qode_sidebars);
        }
    }

    public function import_custom_sidebars($file){
        $options = $this->file_options($file);
        update_option( 'qode_sidebars', $options);
        $this->message = __("Custom sidebars imported successfully", "qode");
    }

    public function import_options($file){
        $options = $this->file_options($file);
        update_option( 'qode_options_proya', $options);
        $this->message = __("Options imported successfully", "qode");
    }

    public function import_menus($file){
        global $wpdb;
        $qode_terms_table = $wpdb->prefix . "terms";
        $this->menus_data = $this->file_options($file);
        $menu_array = array();
        foreach ($this->menus_data as $registered_menu => $menu_slug) {
            $term_rows = $wpdb->get_results("SELECT * FROM $qode_terms_table where slug='{$menu_slug}'", ARRAY_A);
            if(isset($term_rows[0]['term_id'])) {
                $term_id_by_slug = $term_rows[0]['term_id'];
            } else {
                $term_id_by_slug = null;
            }
            $menu_array[$registered_menu] = $term_id_by_slug;
        }
        set_theme_mod('nav_menu_locations', array_map('absint', $menu_array ) );

    }
    public function import_settings_pages($file){
        $pages = $this->file_options($file);

        foreach($pages as $qode_page_option => $qode_page_id){
            update_option( $qode_page_option, $qode_page_id);
        }
    }
    public function file_options($file){
        $file_content = "";
        $file_for_import = get_template_directory() . '/includes/import/files/' . $file;
        /*if ( file_exists($file_for_import) ) {
            $file_content = $this->qode_file_contents($file_for_import);
        } else {
            $this->message = __("File doesn't exist", "qode");
        }*/
        $file_content = $this->qode_file_contents($file);
        if ($file_content) {
            $unserialized_content = unserialize(base64_decode($file_content));
            if ($unserialized_content) {
                return $unserialized_content;
            }
        }
        return false;
    }

    function qode_file_contents( $path ) {
		$url      = "http://export.qodethemes.com/".$path;
		$response = wp_remote_get($url);
		$body     = wp_remote_retrieve_body($response);
		return $body;
    }

    function qode_admin_import() {
        if(isset($_REQUEST['import'])){
            //$this->init_qode_import();
        }

        //$this->pagehook = add_submenu_page('qode_options_proya_page', 'Qode Theme', esc_html__('Qode Import', 'qode'), 'manage_options', 'qode_options_import_page', array(&$this, 'qode_generate_import_page'));
        $this->pagehook = add_menu_page('Qode Theme', esc_html__('Qode Import', 'qode'), 'manage_options', 'qode_options_import_page', array(&$this, 'qode_generate_import_page'),'dashicons-download');

    }

    function qode_generate_import_page() {
        wp_enqueue_style('qodef-bootstrap');
        wp_enqueue_style('qodef-page-admin');
        wp_enqueue_style('qodef-options-admin');
        wp_enqueue_style('qodef-ui-admin');
        wp_enqueue_style('qodef-forms-admin');

        ?>
        <div id="qode-metaboxes-general" class="wrap qodef-page qodef-page-info">
            <h2 class="qodef-page-title"><?php _e('Bridge — One-Click Import', 'qode') ?></h2>
            <form method="post" action="" id="importContentForm">
                <div class="qodef-page-form">
                    <div class="qodef-page-form-section-holder clearfix">
                        <h3 class="qodef-page-section-title">Import Demo Content</h3>
                        <div class="qodef-page-form-section">
                            <div class="qodef-field-desc">
                                <h4><?php esc_html_e('Demo Site', 'qode'); ?></h4>
                                <p>Choose demo site you want to import</p>
                            </div>
                            <div class="qodef-section-content">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <select name="import_example" id="import_example" class="form-control qodef-form-element">
												<option value="bridge">Original</option>
												<option value="bridge3">Business</option>
												<option value="bridge4">Agency</option>
												<option value="bridge5">Estate</option>
												<option value="bridge6">Light</option>
												<option value="bridge7">Urban</option>
												<option value="bridge8">Fashion</option>
												<option value="bridge9">Cafe</option>
												<option value="bridge11">Modern</option>
												<option value="bridge12">University</option>
												<option value="bridge10">One Page</option>
												<option value="bridge14">Restaurant</option>
												<option value="bridge15">Construct</option>
												<option value="bridge13">Winery</option>
												<option value="bridge16">Portfolio Masonry</option>
												<option value="bridge17">Vintage</option>
												<option value="bridge18">Creative Business</option>
												<option value="bridge19">Catalog</option>
												<option value="bridge20">Portfolio</option>
												<option value="bridge21">Minimalist</option>
												<option value="bridge22">Dark Parallax</option>
												<option value="bridge23">Split Screen</option>
												<option value="bridge24">Avenue</option>
												<option value="bridge25">Portfolio Pinterest</option>
												<option value="bridge26">Health</option>
												<option value="bridge27">Flat</option>
												<option value="bridge28">Wireframey</option>
												<option value="bridge29">Denim</option>
												<option value="bridge30">Mist</option>
												<option value="bridge31">Architecture</option>
												<option value="bridge32">Small Brand</option>
												<option value="bridge33">Creative</option>
												<option value="bridge34">Parallax</option>
												<option value="bridge35">Minimal</option>
												<option value="bridge36">Simple Blog</option>
												<option value="bridge37">Pinterest Blog</option>
												<option value="bridge38">Studio</option>
												<option value="bridge39">Contemporary Art</option>
												<option value="bridge40">Chocolaterie</option>
												<option value="bridge41">Branding</option>
												<option value="bridge42">Collection</option>
												<option value="bridge43">Creative Vintage</option>
												<option value="bridge44">Coming Soon Simple</option>
												<option value="bridge45">Coming Soon Creative</option>
												<option value="bridge46">Lawyer</option>
												<option value="bridge47">Health Blog</option>
												<option value="bridge48">Photography Split Screen</option>
												<option value="bridge49">Agency One Page</option>
												<option value="bridge50">Fashion Shop</option>
												<option value="bridge51">Company</option>
												<option value="bridge52">Wellness</option>
												<option value="bridge53">Case Study</option>
												<option value="bridge54">Design Studio</option>
												<option value="bridge55">Digital Agency</option>
												<option value="bridge56">Organic</option>
												<option value="bridge57">Jazz</option>
												<option value="bridge58">Wedding</option>
												<option value="bridge59">Jeans</option>
												<option value="bridge60">Innovation</option>
												<option value="bridge61">Travel Blog</option>
												<option value="bridge62">Passepartout</option>
												<option value="bridge63">Graphic Studio</option>
												<option value="bridge64">Cupcake</option>
												<option value="bridge65">Sunglasses Shop</option>
												<option value="bridge66">Kids</option>
												<option value="bridge67">Animals</option>
												<option value="bridge68">Photo Studio</option>
												<option value="bridge69">Urban Fashion</option>
												<option value="bridge70">Marine</option>
												<option value="bridge71">Interior Design</option>
												<option value="bridge72">Bar & Grill</option>
												<option value="bridge73">Brewery</option>
												<option value="bridge74">Corporate</option>
												<option value="bridge75">Office</option>
												<option value="bridge76">Paper</option>
												<option value="bridge77">Simple Photography</option>
												<option value="bridge78">Furniture</option>
												<option value="bridge79">Skin Care</option>
												<option value="bridge80">Rustic</option>
												<option value="bridge81">Cargo</option>
												<option value="bridge82">Creative Photography</option>
												<option value="bridge83">Construction</option>
												<option value="bridge84">Campaign</option>
												<option value="bridge85">Dim Sum</option>
												<option value="bridge86">Flat Company</option>
												<option value="bridge87">Photography Portfolio</option>
												<option value="bridge88">Charity</option>
												<option value="bridge89">Handmade</option>
												<option value="bridge90">Telecom</option>
												<option value="bridge91">Black-And-White</option>
												<option value="bridge92">Pets</option>
												<option value="bridge93">Designer Personal</option>
												<option value="bridge94">Modern Business</option>
												<option value="bridge95">Contemporary Company</option>
												<option value="bridge96">Bridge Communication Demo </option>
									            <option value="bridge97">Bridge Blog Slider Demo </option>
									            <option value="bridge98">Bridge Fashion Photography Demo </option>
									            <option value="bridge99">Bridge Urban Shop Demo</option>
									            <option value="bridge100">Bridge CV Demo</option>
									            <option value="bridge101">Standard</option>
									            <option value="bridge102">Split Screen</option>
									            <option value="bridge103">Left Menu Initially Hidden</option>
									            <option value="bridge104">Left Menu With Background Image</option>
									            <option value="bridge105">Left Menu</option>
									            <option value="bridge106">Blog with Slider</option>
									            <option value="bridge107">Masonry Gallery</option>
									            <option value="bridge108">Short Slider</option>
									            <option value="bridge109">Angled Sections</option>
									            <option value="bridge110">Grid</option>
									            <option value="bridge111">Elegant Slider</option>
									            <option value="bridge112">Full Screen Sections</option>
									            <option value="bridge113">Shop Grid</option>
									            <option value="bridge114">Shop Wide</option>
									            <option value="bridge115">One Page Site</option>
									            <option value="bridge116">Dark Border</option>
									            <option value="bridge117">Portfolio with Left Menu</option>
									            <option value="bridge118">Portfolio Pinterest Style</option>
									            <option value="bridge119">Shop with Left Menu</option>
									            <option value="bridge120">Photo Slider</option>
									            <option value="bridge121">Blog in Grid</option>
									            <option value="bridge122">Blog Pinterest Style</option>
									            <option value="bridge123">Video Slider</option>
									            <option value="bridge124">Blog Loop</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row next-row">
                                        <div class="col-lg-3">
                                        	<img id="demo_site_img" src="<?php echo get_template_directory_uri() . '/css/admin/images/demos/bridge.jpg' ?>" alt="demo site" />
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="qodef-page-form-section" >
                            <div class="qodef-field-desc">
                                <h4><?php esc_html_e('Import Type', 'qode'); ?></h4>
                                <p>Choose if you would like to import all or specific content</p>
                            </div>

                            <div class="qodef-section-content">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <select name="import_option" id="import_option" class="form-control qodef-form-element">
                                                <option value="">Please Select</option>
                                                <option value="complete_content">All</option>
                                                <option value="content">Content</option>
                                                <option value="widgets">Widgets</option>
                                                <option value="options">Options</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="qodef-page-form-section" >
                            <div class="qodef-field-desc">
                                <h4><?php esc_html_e('Import attachments', 'qode'); ?></h4>
                                <p>Do you want to import media files?</p>
                            </div>

                            <div class="qodef-section-content">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <input type="checkbox" value="1" class="qodef-form-element" name="import_attachments" id="import_attachments" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-button-section clearfix">
                                    <input type="submit" class="btn btn-primary btn-sm " value="Import" name="import" id="import_demo_data" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-3"></div>

                        </div>

                        <div class="import_load"><span><?php _e('The import process may take some time. Please be patient.', 'qode') ?> </span><br />
                            <div class="qode-progress-bar-wrapper html5-progress-bar">
                                <div class="progress-bar-wrapper">
                                    <progress id="progressbar" value="0" max="100"></progress>
                                </div>
                                <div class="progress-value">0%</div>
                                <div class="progress-bar-message">
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-warning">
                            <strong><?php _e('Important notes:', 'qode') ?></strong>
                            <ul>
                                <li><?php _e('Please note that import process will take time needed to download all attachments from demo web site.', 'qode'); ?></li>
                                <li> <?php _e('If you plan to use shop, please install WooCommerce before you run import.', 'qode')?></li>
                            </ul>
                        </div>


                        <!--								<div class="success_msg alert" id="success_msg" >--><?php //echo $this->message; ?><!--</div>-->




                    </div>

                </div>
            </form>
        </div>
        <script type="text/javascript">
            $j(document).ready(function() {
				$j('#import_example').on('change', function (e) {
					var optionSelected = $j("option:selected", this).val();
					$j('#demo_site_img').attr('src', '<?php echo get_template_directory_uri() . '/css/admin/images/demos/' ?>' + optionSelected + '.jpg' );
				});
                $j(document).on('click', '#import_demo_data', function(e) {
                    e.preventDefault();
                    if ($j( "#import_option" ).val() == "") {
                    	alert('Please select Import Type.');
                    	return false;
                    }
                    if (confirm('Are you sure, you want to import Demo Data now?')) {
                        $j('.import_load').css('display','block');
                        var progressbar = $j('#progressbar')
                        var import_opt = $j( "#import_option" ).val();
                        var import_expl = $j( "#import_example" ).val();
                        var p = 0;
                        if(import_opt == 'content'){
                            for(var i=1;i<10;i++){
                                var str;
                                if (i < 10) str = 'bridge_content_0'+i+'.xml';
                                else str = 'bridge_content_'+i+'.xml';
                                jQuery.ajax({
                                    type: 'POST',
                                    url: ajaxurl,
                                    data: {
                                        action: 'qode_dataImport',
                                        xml: str,
                                        example: import_expl,
                                        import_attachments: ($j("#import_attachments").is(':checked') ? 1 : 0)
                                    },
                                    success: function(data, textStatus, XMLHttpRequest){
                                        p+= 10;
                                        $j('.progress-value').html((p) + '%');
                                        progressbar.val(p);
                                        if (p == 90) {
                                            str = 'bridge_content_10.xml';
                                            jQuery.ajax({
                                                type: 'POST',
                                                url: ajaxurl,
                                                data: {
                                                    action: 'qode_dataImport',
                                                    xml: str,
                                                    example: import_expl,
                                                    import_attachments: ($j("#import_attachments").is(':checked') ? 1 : 0)
                                                },
                                                success: function(data, textStatus, XMLHttpRequest){
                                                    p+= 10;
                                                    $j('.progress-value').html((p) + '%');
                                                    progressbar.val(p);
                                                    $j('.progress-bar-message').html('<div class="alert alert-success"><strong>Import is completed</strong></div>');
                                                },
                                                error: function(MLHttpRequest, textStatus, errorThrown){
                                                }
                                            });
                                        }
                                    },
                                    error: function(MLHttpRequest, textStatus, errorThrown){
                                    }
                                });
                            }
                        } else if(import_opt == 'widgets') {
                            jQuery.ajax({
                                type: 'POST',
                                url: ajaxurl,
                                data: {
                                    action: 'qode_widgetsImport',
                                    example: import_expl
                                },
                                success: function(data, textStatus, XMLHttpRequest){
                                    $j('.progress-value').html((100) + '%');
                                    progressbar.val(100);
                                },
                                error: function(MLHttpRequest, textStatus, errorThrown){
                                }
                            });
                            $j('.progress-bar-message').html('<div class="alert alert-success"><strong>Import is completed</strong></div>');
                        } else if(import_opt == 'options'){
                            jQuery.ajax({
                                type: 'POST',
                                url: ajaxurl,
                                data: {
                                    action: 'qode_optionsImport',
                                    example: import_expl
                                },
                                success: function(data, textStatus, XMLHttpRequest){
                                    $j('.progress-value').html((100) + '%');
                                    progressbar.val(100);
                                },
                                error: function(MLHttpRequest, textStatus, errorThrown){
                                }
                            });
                            $j('.progress-bar-message').html('<div class="alert alert-success"><strong>Import is completed</strong></div>');
                        }else if(import_opt == 'complete_content'){
                            for(var i=1;i<10;i++){
                                var str;
                                if (i < 10) str = 'bridge_content_0'+i+'.xml';
                                else str = 'bridge_content_'+i+'.xml';
                                jQuery.ajax({
                                    type: 'POST',
                                    url: ajaxurl,
                                    data: {
                                        action: 'qode_dataImport',
                                        xml: str,
                                        example: import_expl,
                                        import_attachments: ($j("#import_attachments").is(':checked') ? 1 : 0)
                                    },
                                    success: function(data, textStatus, XMLHttpRequest){
                                        p+= 10;
                                        $j('.progress-value').html((p) + '%');
                                        progressbar.val(p);
                                        if (p == 90) {
                                            str = 'bridge_content_10.xml';
                                            jQuery.ajax({
                                                type: 'POST',
                                                url: ajaxurl,
                                                data: {
                                                    action: 'qode_dataImport',
                                                    xml: str,
                                                    example: import_expl,
                                                    import_attachments: ($j("#import_attachments").is(':checked') ? 1 : 0)
                                                },
                                                success: function(data, textStatus, XMLHttpRequest){
                                                    jQuery.ajax({
                                                        type: 'POST',
                                                        url: ajaxurl,
                                                        data: {
                                                            action: 'qode_otherImport',
                                                            example: import_expl
                                                        },
                                                        success: function(data, textStatus, XMLHttpRequest){
                                                            $j('.progress-value').html((100) + '%');
                                                            progressbar.val(100);
                                                            $j('.progress-bar-message').html('<div class="alert alert-success">Import is completed.</div>');
                                                        },
                                                        error: function(MLHttpRequest, textStatus, errorThrown){
                                                        }
                                                    });
                                                },
                                                error: function(MLHttpRequest, textStatus, errorThrown){
                                                }
                                            });
                                        }
                                    },
                                    error: function(MLHttpRequest, textStatus, errorThrown){
                                    }
                                });
                            }
                        }
                    }
                    return false;
                });
            });
        </script>


    <?php	}

}
global $my_Qode_Import;
$my_Qode_Import = new Qode_Import();



if(!function_exists('qode_dataImport'))
{
    function qode_dataImport()
    {
        global $my_Qode_Import;

        if ($_POST['import_attachments'] == 1)
            $my_Qode_Import->attachments = true;
        else
            $my_Qode_Import->attachments = false;

        $folder = "bridge/";
        if (!empty($_POST['example']))
            $folder = $_POST['example']."/";

        $my_Qode_Import->import_content($folder.$_POST['xml']);

        die();
    }

    add_action('wp_ajax_qode_dataImport', 'qode_dataImport');
}

if(!function_exists('qode_widgetsImport'))
{
    function qode_widgetsImport()
    {
        global $my_Qode_Import;

        $folder = "bridge/";
        if (!empty($_POST['example']))
            $folder = $_POST['example']."/";

        $my_Qode_Import->import_widgets($folder.'widgets.txt',$folder.'custom_sidebars.txt');

        die();
    }

    add_action('wp_ajax_qode_widgetsImport', 'qode_widgetsImport');
}

if(!function_exists('qode_optionsImport'))
{
    function qode_optionsImport()
    {
        global $my_Qode_Import;

        $folder = "bridge/";
        if (!empty($_POST['example']))
            $folder = $_POST['example']."/";

        $my_Qode_Import->import_options($folder.'options.txt');

        die();
    }

    add_action('wp_ajax_qode_optionsImport', 'qode_optionsImport');
}

if(!function_exists('qode_otherImport'))
{
    function qode_otherImport()
    {
        global $my_Qode_Import;

        $folder = "bridge/";
        if (!empty($_POST['example']))
            $folder = $_POST['example']."/";

        $my_Qode_Import->import_options($folder.'options.txt');
        $my_Qode_Import->import_widgets($folder.'widgets.txt',$folder.'custom_sidebars.txt');
        $my_Qode_Import->import_menus($folder.'menus.txt');
        $my_Qode_Import->import_settings_pages($folder.'settingpages.txt');

        die();
    }

    add_action('wp_ajax_qode_otherImport', 'qode_otherImport');
}