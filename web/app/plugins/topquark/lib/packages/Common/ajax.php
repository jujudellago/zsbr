<?php
    include_once("../../Standard.php");
    include_once("../../Smarty_Instance.class.php");
    $result = array();
    $result['result'] = 'failure';
    if (!isset($_GET['package'])  or $_GET['package']== ""){
        $result['message'] = 'You must specify a package in your call';
    }
    else{
        $Bootstrap = Bootstrap::getBootstrap();
        $Package = $Bootstrap->usePackage($_GET['package']);
        if (!is_a($Package,'Package')){
            $result['message'] = 'Unknown package '.$_GET['package'];
        }
        else{
            $Ignore = array('package');
            $parms = array();
            foreach ($_GET as $k => $v){
                if (!in_array($k,$Ignore)){
                    $parms[$k] = $v;
                }
            }
			$_result = $Package->Ajax($parms,new Smarty_Instance());
			if (is_array($_result) and isset($_result['result'])){
				$result['result'] = $_result['result'];
				unset($_result['result']);
			}
			else{
				$result['result'] = 'success';
			}
			if (is_array($_result) and isset($_result['message'])){
				$result['message'] = $_result['message'];
				unset($_result['message']);
			}
			else{
				$result['message'] = '';
			}

			if (is_array($_result) and array_key_exists('retain',$result) and is_array($_result['retain'])){
				foreach ($_result['retain'] as $k => $v){
					$result[$k] = $v;
				}
				unset($_result['retain']);
			}
			
			if (is_array($_result) and isset($_result['data'])){
				$result['data'] = $_result['data'];
				unset($_result['data']);
			}
			else{
				$result['data'] = $_result;
			}
        }
    }

    if (isset($_GET['test']) and $_GET['test'] == 'true'){
        var_dump($result);
		exit();
    }
    if (!headers_sent() )
    {
    	header('Content-type: application/json');
    }

    echo json_encode($result);
    exit();
?>