<?php
namespace Zeyuan\Cherry\mvc;
use Zeyuan\Cherry;

// 框架根目录
defined('FRAMEWORK_PATH') or define('FRAMEWORK_PATH', dirname(__DIR__));
define('D', DIRECTORY_SEPARATOR );

class Init{
    // 配置内容
    protected $config = [];
	protected $currentAppName = '';
	protected $appConfigs = [];
	
    public function __construct()
    {
        // 加载全局配置文件
		$this->config['global'] = require( APP_PATH . D . 'config' . D . 'config.php');
    }
	
	public function run(){
	
		// 载入函数库
		$this->loadFunctions();
		
        // 获取应用名称
		$this->currentAppName = $this->getApp();
		
		// 检测开发环境
        $this->setReporting();
		
		// 检测敏感字符并删除
        $this->removeMagicQuotes();
		
		 // 检测自定义全局变量并移除
        $this->unregisterGlobals();
		
		// 载入应用配置
        $this->appConfigs = $this->setAppConfig();
		
		// 配置数据库信息
        $this->setDbConfig();
		
		// 加载路由
        $this->route();
	}
	
	// 路由
	protected function route(){
		if(file_exists(APP_PATH . D . 'route' . D . $this->currentAppName .'_route.php')){
			require( APP_PATH . D . 'route' . D . $this->currentAppName .'_route.php');
		}else{
			echo '路由文件： '.APP_PATH . D . 'route' . D . $this->currentAppName .'_route.php 不存在！';
			die();
		}
	
		$httpMethod = $_SERVER['REQUEST_METHOD'];
		$uri = $_SERVER['REQUEST_URI'];

		// Strip query string (?foo=bar) and decode URI
		if (false !== $pos = strpos($uri, '?')) {
			$uri = substr($uri, 0, $pos);
		}
		$uri = rawurldecode($uri);

		$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
		switch ($routeInfo[0]) {
			case \FastRoute\Dispatcher::NOT_FOUND:
				// ... 404 Not Found
				break;
			case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
				$allowedMethods = $routeInfo[1];
				// ... 405 Method Not Allowed
				break;
			case \FastRoute\Dispatcher::FOUND:
				$handler = $routeInfo[1];
				$vars = $routeInfo[2];
				
				$actionName = 'index';
				$controllerData= explode(':',$handler);
				if(isset($controllerData[1])){
					$controllerName = $controllerData[0];
					$actionName = $controllerData[1];
				}
				
				$currentControllerName = '';
				$controllerData = explode(D,$handler);
				if(isset($controllerData['1'])){
					$currentControllerName = $controllerData['1'];
				}				
				
				$data = ['appName'=> $this->currentAppName,
						'actionName'=>$actionName,
						'controllerName'=>$currentControllerName
				];
				
				$controllerName = '\\App\\' . $controllerName; 
				if(class_exists ( $controllerName )){
					$app = new $controllerName($data);
					$app->$actionName();
				}else{
					die("类 $controllerName 不存在!");
				}
				
				// ... call $handler with $vars
				break;
		}
	}
	
	// 载入functions
	protected function loadFunctions(){
		$folder_list = array();
		$this->helper_find_files(FRAMEWORK_PATH . D . 'functions' . D, $folder_list);
		foreach($folder_list as $key=>$val){
			foreach($val as $vval){
				if(strpos($vval,'.php')){
					include($key . D . $vval);
				}
			}
		}
	}
	
	// 获取应用名称
	private function getApp(){
		$url = $_SERVER["REQUEST_URI"];
		$urlarry = explode('/', $url);
		$appname = $this->config['global']['defaultapp'];
		if(isset($urlarry[1])){
			if(in_array($urlarry[1], $this->config['global']['registerApp'])){
				$appname = $urlarry[1];
			}
		}
		
		// 判断是否手机端
		//if(\Zeyuan\Cherry\functions\isMobile() && $urlarry[1]!='app'){
			//$appname = 'mobile';
		//}
		
		// 域名前缀
		$domain = $_SERVER['HTTP_HOST'];
		$parseDomain = explode('.',$domain);
		if(isset($parseDomain[0]) && array_key_exists($parseDomain[0],$this->config['global']['domain_prefix'])){
			$appname = $this->config['global']['domain_prefix'][$parseDomain[0]];
		}
		return $appname;
	}
	
    // 检测开发环境
    public function setReporting()
    {
        if (APP_DEBUG === true) {
            error_reporting(E_ALL);
            ini_set('display_errors','On');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors','Off');
            ini_set('log_errors', 'On');
        }
    }	
	
    // 检测敏感字符并转义
    public function removeMagicQuotes()
    {
		$_GET = isset($_GET) ? $this->stripSlashesDeep($_GET ) : '';
		$_POST = isset($_POST) ? $this->stripSlashesDeep($_POST ) : '';
		$_COOKIE = isset($_COOKIE) ? $this->stripSlashesDeep($_COOKIE) : '';
		$_SESSION = isset($_SESSION) ? $this->stripSlashesDeep($_SESSION) : '';
        
    }	
	
    // 转义敏感字符
    public function stripSlashesDeep($value)
    {
        $value = is_array($value) ? array_map(array($this, 'stripSlashesDeep'), $value) : addslashes($value);
        return $value;
    }	
	
    // 检测自定义全局变量并移除。因为 register_globals 已经弃用，如果
    // 已经弃用的 register_globals 指令被设置为 on，那么局部变量也将
    // 在脚本的全局作用域中可用。 例如， $_POST['foo'] 也将以 $foo 的
    // 形式存在，这样写是不好的实现，会影响代码中的其他变量。 相关信息，
    // 参考: http://php.net/manual/zh/faq.using.php#faq.register-globals
    public function unregisterGlobals()
    {
        if (ini_get('register_globals')) {
            $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
            foreach ($array as $value) {
                foreach ($GLOBALS[$value] as $key => $var) {
                    if ($var === $GLOBALS[$key]) {
                        unset($GLOBALS[$key]);
                    }
                }
            }
        }
    }	
	
    // 配置数据库信息
    public function setDbConfig()
    {
		// 预设值
		if(isset($this->config['global']['db']['host'])){
			define('DB_HOST', $this->config['global']['db']['host']);
		}
		if(isset($this->config['global']['db']['dbname'])){
			define('DB_NAME', $this->config['global']['db']['dbname']);
		}
		if(isset($this->config['global']['db']['username'])){
			define('DB_USER', $this->config['global']['db']['username']);
		}
		if(isset($this->config['global']['db']['password'])){
			define('DB_PASS', $this->config['global']['db']['password']);
		}
		if(isset($this->config['global']['db']['tbPrefix'])){
			define('TB_PREFIX', $this->config['global']['db']['tbPrefix']);
		}
		if(isset($this->config['global']['db']['port'])){
			define('TB_PREFIX', $this->config['global']['db']['port']);
		}
		
		if(count($this->appConfigs)>0){
			if(isset($this->appConfigs['config']['dbDrive'])){
			$dbDrive = $this->appConfigs['config']['dbDrive'];
				define('DB_HOST', $this->appConfigs['db'][$dbDrive]['host']);
				define('DB_NAME', $this->appConfigs['db'][$dbDrive]['dbname']);
				define('DB_USER', $this->appConfigs['db'][$dbDrive]['username']);
				define('DB_PASS', $this->appConfigs['db'][$dbDrive]['password']);
				define('TB_PREFIX', $this->appConfigs['db'][$dbDrive]['tbPrefix']);		
				define('DB_PORT', $this->appConfigs['db'][$dbDrive]['port']);		
			}
		}
    }		
	
	// 配置应用信息
	public function setAppConfig(){
		if(is_dir(APP_PATH . D . 'config' . D . $this->currentAppName . D)){
			$this->helper_find_files(APP_PATH . D . 'config' . D . $this->currentAppName . D, $folder_list);
			foreach($folder_list as $key=>$val){
				foreach($val as $vval){
					if(strpos($vval,'.php')){
						$keyName = substr($vval,0,strpos($vval,'.'));
						$appConfigs[$keyName] = include($key . D . $vval);
					}
				}
			}
		}
		
		return $appConfigs;
	}
	
	// PHP获取目录及子目录下所有文件名
	function helper_find_files($dir, &$dir_array)
	{
		// 读取当前目录下的所有文件和目录（不包含子目录下文件）
		$files = scandir($dir);
	 
		if (is_array($files)) {
			foreach ($files as $val) {
				// 跳过. 和 ..
				if ($val == '.' || $val == '..')
					continue;
	 
				// 判断是否是目录
				if (is_dir($dir . '/' . $val)) {
					// 将当前目录添加进数组
					$dir_array[$dir][] = $val;
					// 递归继续往下寻找
					$this->helper_find_files($dir . '/' . $val, $dir_array);
				} else {
					// 不是目录也需要将当前文件添加进数组
					$dir_array[$dir][] = $val;
				}
			}
		}
	}	
	
}