<?php
	if (!defined('ADMIN_PAGE_PARM')){
		// Commented out as part of Wordpress Integration....
	    //define('ADMIN_PAGE_PARM','page');
	}
	class Bootstrap{
		
		var $RegisteredPackages;
		var $primeAdminIsPrimed;
	    
	    function Bootstrap(){
	        $this->internalCache = array();
	    }
	    
	    function & getMessageList(){
	        // Singleton Pattern
	        global $MessageList;
	        if (!is_a($MessageList,'MessageList')){
	            $MessageList = new MessageList();
	        }
	        return $MessageList;
	    }
	    
	    function & getBootstrap(){
	        // Singleton Pattern
	        global $Bootstrap;
	        if (!is_a($Bootstrap,'Bootstrap')){
	            $Bootstrap = new Bootstrap();
	        }
	        return $Bootstrap;
	    }
	    
	    function & getCurrentArticle($DefaultBlog = ""){
	        if ($this->packageExists('Article')){
	            if (is_a($this->current_article,'Article')){
	                return $this->current_article;
	            }
	            $this->usePackage('Article');
    	        if($_SERVER['SCRIPT_FILENAME'] != DOC_BASE.$this->getIndexURL(false)){
    	            // We're not working within the article framework, we're on another page
    	            return null;
    	        }
    	        if ($_GET['preview'] == 'true'){
    	            // Preview
    	            $Article = $this->createPreviewArticle();
    	        }
    	        else{
                    $BlogContainer = new BlogContainer();
                    $ArticleContainer = new ArticleContainer();
    	            if($_GET[INDEX_PAGE_PARM] == ""){
    	                if ($DefaultBlog == ""){
    	                    // The show parm is blank, default to the first article in the first blog
    	                    $AllBlogs = $BlogContainer->expandBlogs();
    	                    if (is_array($AllBlogs) and count($AllBlogs)){
    	                        foreach ($AllBlogs as $Blog){
    	                            if (is_a($Article = $ArticleContainer->getFirstArticleInBlog($Blog->getParameter('BlogURLKey')),'Article')){
    	                                break;
    	                            }
    	                        }
    	                    }
    	                }
    	                else{
    	                    $Article = $ArticleContainer->getFirstArticleInBlog($DefaultBlog);
    	                }
    	                if (is_a($Article,'Article')){
    	                    $Article->setParameter('ArticleIsDefault',true);
    	                }
                    }
                    elseif (!is_numeric($_GET[INDEX_PAGE_PARM])){
    	                $AllBlogs = $BlogContainer->expandBlogs();
    	                $Blog = current($AllBlogs);
    	                while (is_a($Blog,'Blog')){
    	                    if ($Blog->getParameter('BlogURLKey') == $_GET[INDEX_PAGE_PARM]){
    	                        $StartingDepth = $Blog->getParameter('BlogDepth');
    	                        while (is_a($Blog,'Blog')){
                                    $Article = $ArticleContainer->getFirstArticleInBlog($Blog->getParameter('BlogURLKey'));
                                    if (is_a($Article,'Article')){
                                        unset($Blog);
                                    }
                                    else{
                                        $Blog = next($AllBlogs);
                                        if (is_a($Blog,'Blog') and $Blog->getParameter('BlogDepth') <= $StartingDepth){
                                            unset($Blog);
                                        }
                                    }
    	                        }
    	                        break;
    	                    }
    	                    $Blog = next($AllBlogs);
    	                }
                    }
                    else{
                        $Article = $ArticleContainer->getArticle($_GET[INDEX_PAGE_PARM]);
                        if (!is_a($Article,'Article') or $Article->getParameter('ArticleDoNotPublish')){
                            unset($Article);
                        }
                    }
    	        }
    	        if (!is_a($Article,'Article')){
    	            $Article = new Article();
    	            $Article->setParameter('ArticleID','not_found');
    	            if (!is_numeric($_GET[INDEX_PAGE_PARM])){
    	                $Article->setParameter('ArticleBlog',$_GET[INDEX_PAGE_PARM]);
    	            }
    	        }
    	        $this->current_article = $Article;
    	    }
    	    return $Article;
	    }
	    
	    function createPreviewArticle(){
            $Article = new Article();
            $this->addStartFunction('removePopupCSS();');
            $this->addHeadExtra("
                <script language='javascript' src='".RELATIVE_BASE_URL."lib/js/tinymce/jscripts/tiny_mce/tiny_mce_popup.js'></script>
                <script language='javascript' type='text/javascript'>
                <!--
                    // Remove the Popup CSS file
                    function removePopupCSS(){
                        var allLinks = document.getElementsByTagName('link');
                        for (var i = 0; i < allLinks.length; i++) {
                            if (allLinks[i].href && allLinks[i].href.match(/\/editor_popup.css?$/)){
                                allLinks[i].parentNode.removeChild(allLinks[i]);
                            }
                        }
                    }
                -->
                </script>
                <script type='text/javascript' src='".RELATIVE_BASE_URL."lib/js/tinymce/jscripts/tiny_mce/plugins/preview/jscripts/embed.js'></script>
            ");
            if ($_POST['preview_ready'] == ""){
                $Article->setParameter('ArticleText',"
                    <p>Please wait while preview is loaded</p>
                    <form action='index.php?preview=true' method='post' name='ContentForm' id='ContentForm'>
                    <input type='hidden' name='content' value=''>
                    <input type='hidden' name='preview_ready' value='true'>
                    <input type='hidden' name='blog' value=''>
                    <input type='hidden' name='title' value=''>
                    <input type='button' value='Go now!' onclick='javascript:submitContentForm();'>
                    </form>
                    <div style='display:none' id='ContentDiv'>{\$content}</div>
                ");
                $this->addStartFunction('submitContentForm();');
                $this->addHeadExtra("
                    <script type='text/javascript' language='javascript'>
                        function submitContentForm(){
                            document.ContentForm.title.value = window.opener.getValue('enArticleTitle');
                            document.ContentForm.blog.value = window.opener.getValue('ArticleBlog');
                            document.ContentForm.content.value = window.opener.tinyMCE.getContent();
                            document.ContentForm.submit();
                        }
                    </script>
                ");
            }
            else{
                $Article->setParameter('ArticleText',stripslashes($_POST['content'])."<p>(<a href='javascript:window.close()'>close preview window</a>)</p>");
        		$Article->setParameter('ArticleText',preg_replace("/<!-- textarea/i","<textarea",$Article->getParameter('ArticleText')));
        		$Article->setParameter('ArticleText',preg_replace("/<\/textarea -->/i","</textarea>",$Article->getParameter('ArticleText')));
    		    $Article->smartyDecodeParameter('ArticleText');
                if ($_POST['title'] != ""){
                    $Article->setParameter('ArticleTitle',stripslashes($_POST['title']));
                }
                else{
                    $Article->setParameter('ArticleHideTitle',true);
                }
                $Article->setParameter('ArticleBlog',$_POST['blog']);
            }
            
            return $Article;
        }
	    
	    function addStartFunction($StartFunction){
	        if (!is_array($this->start_functions)){
	            $this->start_functions = array();
	        }
	        $this->start_functions[] = $StartFunction;
	    }
	    
	    function getStartFunctions(){
	        if (!is_array($this->start_functions)){
	            $this->start_functions = array();
	        }
	        return $this->start_functions;
	    }
	    
	    function addHeadExtra($HeadExtra){
	        if (!is_array($this->head_extras)){
	            $this->head_extras = array();
	        }
	        $this->head_extras[] = $HeadExtra;
	    }
	    
	    function getHeadExtras(){
	        if (!is_array($this->head_extras)){
	            $this->head_extras = array();
	        }
	        return $this->head_extras;
	    }
	    
        function primeAdminPage(){
            // Set some variables that will help since this is an admin page
            
            if (!$this->primeAdminIsPrimed){
                if (defined('CMS_ADMIN_SCRIPT')){
                    $this->admin_url = CMS_ADMIN_SCRIPT;
                }
                else{
                    $this->admin_url = 'admin.php';
                }
    	        $this->addURLToAdminBreadcrumb($this->admin_url,'Main Menu','AdminLeftNav');
    	        switch (CMS_PLATFORM){
    	        case 'Joomla':
    	        case 'WordPress':
    	            break;
    	        default:
    	            $this->addURLToAdminBreadcrumb('index.php?action=logout','Logout','AdminRightNav');
    	            $this->addURLToAdminBreadcrumb('index.php?action=resetpass','Reset Password','AdminRightNav');
    	        }
    	        $this->primeAdminIsPrimed = true;
    	    }
        }
        
        function getIndexURL($IncludePageParm = true){
            if (defined('CMS_USER_SCRIPT')){
                if ($IncludePageParm){
                    if (strpos(CMS_USER_SCRIPT,"?") !== false){
                        $sep = "&";
                    }
                    else{
                        $sep = "?";
                    }
                    return CMS_USER_SCRIPT.$sep.INDEX_PAGE_PARM.'=';
                }
                else{
                    return CMS_USER_SCRIPT;
                }
            }
            else{
                if ($IncludePageParm){
                    if (strpos(INDEX_URL,"?")){
                        $sep = "&";
                    }
                    else{
                        $sep = "?";
                    }
                    return INDEX_URL.$sep.INDEX_PAGE_PARM.'=';
                }
                else{
                    return INDEX_URL;
                }
            }
        }
        
        function getSiteThemeDirectory(){
            return 'themes/'.SITE_THEME.'/';
        }
        
	    function usePackage($Package){
			if (is_array($this->RegisteredPackages) and in_array($Package,array_keys($this->RegisteredPackages))){
				return $this->useRegisteredPackage($Package);
			}
            if (!file_exists(PACKAGE_DIRECTORY.$Package)){
                return PEAR::raiseError("Package '$Package' is unknown");
            }
            $Directory = PACKAGE_DIRECTORY.$Package."/";
            if (file_exists($Directory."conf.php")){
                include_once($Directory."conf.php");
                $ClassName=$Package."Package";
                if (class_exists($ClassName)){
                    return new $ClassName();
                }
            }
            return PEAR::raiseError("You must have a file called conf.php that includes a class called {$Package}Package");
	    }
		
		function useRegisteredPackage($Package){
            $Directory = PACKAGE_DIRECTORY.$this->RegisteredPackages[$Package];
            if (file_exists($Directory."conf.php")){
                include_once($Directory."conf.php");
                $ClassName=$Package."Package";
                if (class_exists($ClassName)){
                    $_package = new $ClassName();
					if (is_a($_package,'Package')){
						$_package->package_directory = $this->RegisteredPackages[$Package];
					}
					
					// Need to reload it, now that the directory is set
					$_package->loadUserConf();
					return $_package;
                }
            }
            return PEAR::raiseError("You must have a file called conf.php that includes a class called {$Package}Package");
		}
	
		function registerPackage($PackageName,$PackageDirectory){
			if (!is_array($this->RegisteredPackages)){
				$this->RegisteredPackages = array();
			}
			if (!in_array($PackageName,array_keys($this->RegisteredPackages))){
				$this->RegisteredPackages[$PackageName] = $PackageDirectory;
			}
		}
		
		function getAllRegisteredPackages(){
			if (!is_array($this->RegisteredPackages)){
				$this->RegisteredPackages = array();
			}
			$return = array();
			foreach (array_keys($this->RegisteredPackages) as $rp){
				$return[$rp] = $this->usePackage($rp);
			}
			return $return;
		}
	    
	    function getAllPackages(){
			$Packages = array();
	        $IgnoreDirectories = apply_filters('get_all_packages_ignore',array(".","..","Common"));
        	if ($dh = opendir(PACKAGE_DIRECTORY)) {
                while (($PackageDirectory = readdir($dh)) !== false) {
                    if (is_dir(PACKAGE_DIRECTORY.$PackageDirectory) and !in_array($PackageDirectory,$IgnoreDirectories)){
        	            $Package = $this->usePackage($PackageDirectory);
        	            if (!PEAR::isError($Package) and $Package->is_active){
        	                $Packages[$Package->package_name] = $Package;
        	            }
                    }
                }
            }
        	closedir($dh);

			// get Registered Packages now
			$Packages = array_merge($Packages,$this->getAllRegisteredPackages());
        	
        	// Sort the packages by title (so they appear pretty in the main menu)
			if (!function_exists('sortPackagesByTitle')){
	        	function sortPackagesByTitle($a,$b){
	        	    if ($a->package_title == $b->package_title){
	        	        return 0;
	        	    }
	        	    elseif ($a->package_title < $b->package_title){
	        	        return -1;
	        	    }
	        	    else{
	        	        return 1;
	        	    }
	        	}
			}
        	uasort($Packages,'sortPackagesByTitle');
        	
	        return $Packages;
	    }
	    
	    function packageExists($PackageName){			
			if (is_array($this->RegisteredPackages) and in_array($PackageName,array_keys($this->RegisteredPackages))){
				return true;
			}
            elseif (file_exists(PACKAGE_DIRECTORY.$PackageName)){
                return true;
            }
			else{
				return false;
			}
	    }
	    
	    function getAuthorizedAdminMenuPackages(){
	        if ($_SESSION['auth_name'] == ""){
	            return array();
	        }
	        $this->usePackage('Users');
	        $UserPackageContainer = new UserPackageContainer();
	        $AuthorizedPackages = $UserPackageContainer->getAllUserPackages($_SESSION['auth_name']);

	        $AllPackages = $this->getAllPackages();
	        $AuthorizedAdminMenuPackages = array();
	        foreach ($AllPackages as $PackageName => $Package){
	            if (in_array($PackageName,$AuthorizedPackages)
	            or ($Package->auth_level > USER_AUTH_EVERYONE and
	                $_SESSION['auth_level'] >= $Package->auth_level)){
	                    $AuthorizedAdminMenuPackages[$PackageName] = $Package;
	            }
	        }
	        return $AuthorizedAdminMenuPackages;
	    }
	    
	    function getAdminURL(){
	        return $this->makeAdminURL($_GET['package'],$_GET[ADMIN_PAGE_PARM]);
	    }
	    
	    function addPackagePageToAdminBreadcrumb($Package,$Page,$WhichQueue = "AdminLeftNav"){
	        if (!isset($this->$WhichQueue)){
	            $this->$WhichQueue = array();
	            $this->breadcrumb_queues[] = $WhichQueue;
	        }
	        $this->{$WhichQueue}[] = array('url' => $this->makeAdminURL($Package->package_name,$Page), 'title' => $Package->admin_pages[$Page]['title']);
	    }
	    
	    function addURLToAdminBreadcrumb($Url,$Title,$WhichQueue = "AdminLeftNav"){
	        if (!isset($this->$WhichQueue)){
	            $this->$WhichQueue = array();
	            $this->breadcrumb_queues[] = $WhichQueue;
	        }
	        $this->{$WhichQueue}[] = array('url' => $Url, 'title' => $Title);
	    }
	    
	    function clearBreadcrumbs($WhichQueue = ""){
	        if ($WhichQueue != ""){
	            unset ($this->$WhichQueue);
	            if (($index = array_search($WhichQueue,$this->breadcrumb_queues)) !== false){
	                unset ($this->breadcrumb_queues[$index]);
	            }
	        }
	        else{
	            foreach ($this->breadcrumb_queues as $index => $Queue){
	                unset ($this->$Queue);
	                unset ($this->breadcrumb_queues[$index]);
	            }
	        }
	    }
	    
	    function getAdminBreadcrumb($WhichQueue = "AdminLeftNav",$Reverse = false){
	        if (!isset($this->$WhichQueue)){
	            $this->$WhichQueue = array();
	        }
	        if ($Reverse){
	            return array_reverse($this->$WhichQueue);
	        }
	        else{
	            return $this->$WhichQueue;
	        }
	    }
	    
	    function makeAdminURL($Package,$Page){
	        if (is_a($Package,'Package')){
	            $PackageName = $Package->package_name;
	        }
	        else{
	            $PackageName = $Package;
	        }
	        $url = $this->admin_url;
	        if (strpos($url,'?') !== false){
	            $connector = "&";
	        }
	        else{
	            $connector = "?";
	        }
	        if ($PackageName != ""){
	            $url.= $connector."package=".$PackageName;
	            if ($Page != ""){
	                $url.= "&".ADMIN_PAGE_PARM."=".$Page;
	            }
	        }
	        return $url;
	    }
	    
	    function getAdminTitle($Package,$Page){
	        if (is_a($Package,'Package')){
	            $PackageName = $Package->package_name;
	        }
	        else{
	            $PackageName = $Package;
	        }
	        $tmpPackage = $this->usePackage($PackageName);
	        return $tmpPackage->admin_pages[$Page]['title'];
	    }
	    
	    function Paint($parms,&$smarty){
			if ($parms['subject'] == 'dump_timers'){
				return $this->dump_timers();
			}
			elseif($parms['subject'] == 'dump_debug'){
				return $this->dump_debug();
			}
	        if ($parms['package'] == ""){
	            return "You must define a variable called 'package' referencing a valid package on the system";
	        }
	        $Package = $this->usePackage($parms['package']);
	        if (!is_a($Package,'Package')){
	            return "Could not find the package called '".$parms['package']."'.";
	        }
	        
	        if (!method_exists($Package,'Paint')){
	            return "The package '".$parms['package']."' does not contain a method called Paint().";
	        }
	        else{
	            $return = $Package->Paint($parms,$smarty);
	            return $return;
	        }
	    }
	    
	    function Retrieve($parms,&$smarty){
	        if ($parms['package'] == ""){
	            return "You must define a variable called 'package' referencing a valid package on the system";
	        }
	        $Package = $this->usePackage($parms['package']);
	        if (!is_a($Package,'Package')){
	            return "Could not find the package called '".$parms['package']."'.";
	        }
	        
	        if (!method_exists($Package,'Retrieve')){
	            return "The package '".$parms['package']."' does not contain a method called Retrieve().";
	        }
	        else{
                if ($parms['var'] == ""){
                    return "You must pass a 'var' parm when calling Retrieve.  This is the variable that will get assigned";
                }
                else{
	                return $Package->Retrieve($parms,$smarty);
	            }
	        }
	    }
	    
	    function getCachedPage($RequestURI){
	        if (is_array($_POST) and count($_POST)){ // don't return when info might have been posted
	            return false;
	        }
	        $filename = $this->getCachedFilename($RequestURI);
	        if (file_exists($filename)){ // and filemtime($filename) < $LastLoggedInTime ??
	            return file_get_contents($filename);
	        }
	    }
	    
	    function saveCachedPage($RequestURI,$FileContents){ 
	        $filename = $this->getCachedFilename($RequestURI);
	        if ($h = fopen($filename,'w')){
	            fwrite($h,$FileContents);
	            fwrite($h,"\n<!-- $RequestURI -->");
	            fclose($h);
	        }
	        else{
	            return false;
	        }
	        return true;
	    }
	    
	    function getCachedFilename($RequestURI){
	        return PAGE_CACHE_DIR.md5($RequestURI);
	    }
	    
	    function addTimestamp($desc = ""){
	        if (ENABLE_TIMER !== true or $_GET['timer'] != 'true'){ return; }
	        if (!is_array($this->timestamps)){
	            $this->timestamps = array();
	            $this->addTimestamp('Start');
	        }
            list($usec, $sec) = explode(" ", microtime());
            if (function_exists('memory_get_usage')){
                $mem = memory_get_usage(true);
            }
            else{
                $mem = "N/A";
            }
    
	        $this->timestamps[] = array('desc' => $desc, 'time' => ((float)$usec + (float)$sec), 'mem' => $mem);
	    }
	    
	    function dumpTimestamps(){
	        if (ENABLE_TIMER !== true or $_GET['timer'] != 'true'){ return; }
	        ob_start();
	        $this->addTimestamp('Finish');
	        $start = null;
	        echo "<table cellpadding='5' border='1'><tr><th>Desc</th><th>Elapsed</th><th>Interval</th><th>Memory</th></tr>\n";
	        foreach ($this->timestamps as $timestamp){
	            if ($start === null){
	                $start = $timestamp['time'];
	                $previous = $start;
	            }
	            $since_start = (float)$timestamp['time'] - (float)$start;
	            echo "<tr>";
	            echo "<td>".$timestamp['desc']."</td>";
	            echo "<td>".round(floatval($timestamp['time'] - $start),3)."</td>";
	            echo "<td>".round(floatval($timestamp['time'] - $previous),3)."</td>";
	            echo "<td>".number_format($timestamp['mem']/(1024*1024),1)."</td>";
	            echo "</tr>\n";
	            //echo $timestamp['desc'] . " - " . ((float)$timestamp['time'] - (float)$previous) . " (" . floatval($timestamp['time'] - $start) . " since start)<br />";
	            $previous = $timestamp['time'];
	        }
	        echo "</table>";
	        $table = ob_get_clean();
	        echo $table;
	    }
	
	    function start_timer($desc){
	        if ((!defined('ENABLE_TIMER') or ENABLE_TIMER !== true) and (!isset($_GET['timer']) or $_GET['timer'] != 'true')){ return; }
	        if (!is_array($this->timers)){
	            $this->timers = array();
	        }
            if (function_exists('memory_get_usage')){
                $mem = memory_get_usage(true);
            }
            else{
                $mem = "N/A";
            }
    
	        if (!is_array($this->timers[$desc])){
				$this->timers[$desc] = array('last' => 0, 'elapsed' => 0);
			}
			$this->timers[$desc]['last'] = microtime();
			
			//$this->snapshot_memory('Start: '.$desc);
		}
	    	    
	    function stop_timer($desc){
	        if ((!defined('ENABLE_TIMER') or ENABLE_TIMER !== true) and (!isset($_GET['timer']) or $_GET['timer'] != 'true')){ return; }
	        if (!is_array($this->timers) or !is_array($this->timers[$desc])){
				return false;
	        }
			list($usec, $sec) = explode(" ", microtime());			
			list($last_usec, $last_sec) = explode(" ", $this->timers[$desc]['last']);
			
			$this->timers[$desc]['elapsed']+= $sec - $last_sec + $usec - $last_usec;

			//$this->snapshot_memory('Stop: '.$desc);
		}
		
		function snapshot_memory($desc){
	        if ((!defined('ENABLE_TIMER') or ENABLE_TIMER !== true) and (!isset($_GET['timer']) or $_GET['timer'] != 'true')){ return; }
	
            if (!function_exists('memory_get_usage')){
				return;
            }
            else{
                $mem = memory_get_usage();
            }

	        if (!is_array($this->memory_snapshots)){
	            $this->memory_snapshots = array();
	        }

			//$this->memory_snapshots[] = array('desc' => $desc, 'mem' => $mem);
			$this->memory_snapshots[$desc] = array('mem' => $mem);
			if (function_exists('memory_get_peak_usage')){
				$this->memory_snapshots[$desc]['peak'] = memory_get_peak_usage();
			}
    
			
		}
		
		function dump_timers(){
	        if ((!defined('ENABLE_TIMER') or ENABLE_TIMER !== true) and (!isset($_GET['timer']) or $_GET['timer'] != 'true')){ return; }
	        if (!is_array($this->timers)){
	            $this->timers = array();
	        }
	        if (!is_array($this->memory_snapshots)){
	            $this->memory_snapshots = array();
	        }
	
			$ret = "";
			foreach ($this->timers as $key => $timer){
				$ret.= "<p>$key => ".$timer['elapsed']."</p>\n";
			}
			
			foreach ($this->memory_snapshots as $key => $memory_snapshot){
				$ret.= "<p>".$key." => ".number_format($memory_snapshot['mem']/(1024*1024),4)." (Peak: ".number_format($memory_snapshot['peak']/(1024*1024),4).")</p>\n";
			}
			
			return $ret;
		}
		
		function add_debug($var){
			if (!is_array($_SESSION)){
				session_start();
			}
			if (!isset($_SESSION['debug_vars'])){
				session_register('debug_vars');
				$_SESSION['debug_vars'] = array();
			}
			$_SESSION['debug_vars'][] = $var;
		}
		
		function clear_debug(){
			if (!isset($_SESSION['debug_vars'])){
				session_register('debug_vars');
			}
			$_SESSION['debug_vars'] = array();
		}
		
		function dump_debug(){
			ob_start();
			if (count($_SESSION['debug_vars'])){
				for($i = count($_SESSION['debug_vars']) - 1; $i >= 0; $i--){
					echo "<p>$i: ";
					var_dump($_SESSION['debug_vars'][$i]);
					echo "</p>";
				}
			}
			return ob_get_clean();
		}
	}
?>