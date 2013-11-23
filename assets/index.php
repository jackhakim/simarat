<?php
$expires = 60*60*24*14; // Cache lifetime 14 days
$comext  = array('js','css','json','xml');
$req = array('file'=>'', 'params'=>array());


$path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
if (trim($path, '/') != '' && $path != "/" . __FILE__)
{
	$req['file'] = fileinfo($path);
}

if(@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $req['file']['modified'] && @trim($_SERVER['HTTP_IF_NONE_MATCH']) == $req['file']['etag']) {
	header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
	exit;
}else{
	ob_start("ob_gzhandler");	
	header("Age: ".$expires);
	header("Etag: ".$req['file']['etag']);
	header("Content-type: ".$req['file']['mime']);
	header("Pragma: public; maxage=".$expires);
	header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires). ' GMT');
	header("Last-Modified: ".gmdate("D, d M Y H:i:s", $req['file']['modified']).' GMT');
	readfile($req['file']['file']);
	exit;
}

function fileinfo($file){
	$file = trim($file);
	if($file == '') {
		return false;
	}
	$return = array('file'=>$file,'path'=>'');
	foreach (explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $return['file'])) as $val) {
		$val = trim($val);
		if ($val != '') { 
			$seg[] = ($val);
		}
	}
	for($i=0;$i<count($seg)-1;$i++){
		$return['path'] = sprintf('%s%s/', $return['path'], $seg[$i]);
	}
	$return['filename'] = $seg[count($seg)-1];
	$return['ext']      = explode('.', $return['filename']);
	$return['ext']      = $return['ext'][count($return['ext'])-1];
	$return['file']     = sprintf('%s%s', $return['path'], $return['filename']);
	
	if(file_exists($return['file'])) {
		$return['etag']     = md5_file($return['file']);
		$return['modified'] = @filemtime($return['file']);
		$return['mime']     = get_mime($return['ext']);
		$return['test']     = gmdate("D, d M Y H:i:s",time()) . ' GMT';
	}
	return $return;
}

function get_mime($ext){ 
    switch(strtolower($ext)){ 
        case "js"  : return "application/javascript; charset=utf-8"; 
        case "json": return "application/json; charset=utf-8"; 
        case "jpg" : case "jpeg": case "jpe": return "image/jpg"; 
        case "png" : case "gif": case "bmp": return "image/".strtolower($ext); 
		case "ico" : return "image/x-icon";
        case "css" : return "text/css; charset=utf-8"; 
        case "xml" : return "application/xml"; 
        case "html": case "htm": case "php": return "text/html; charset=utf-8"; 
        default: return false; 
    } 
}

?>