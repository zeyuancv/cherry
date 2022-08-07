<?php
namespace Zeyuan\Cherry\mvc;

class Controller{
	
	protected $_appName  = '';
	protected $_actionName = '';
	protected $_controllerName = '';
	protected $_theme;
	protected $_view;
	
	function __construct($data){

	    foreach($data as $val){
	        if(isset($val['theme'])){
                $this->_theme = $val['theme'];
            }
        }

		$this->_appName = $data['appName'];
		$this->_actionName = $data['actionName'];
		$this->_controllerName = $data['controllerName'];
		$this->_view = new \Zeyuan\Cherry\mvc\View($this->_appName, $this->_theme,$this->_controllerName, $this->_actionName);
	}
	
	// 分配变量
    public function assign($name, $value)
    {
        $this->_view->assign($name, $value);
    }	
	
    // 渲染视图
    public function render($viewName, $expire = 0, $flush=false)
    {
        $this->_view->render($viewName, $expire, $flush);
    }

    // 渲染视图
    public function fetch($viewName)
    {
        return $this->_view->fetch($viewName);
    }

    // 缓存
    public function cache($cacheKey, $cacheVal,$expire=0, $flush=false)
    {
        $varDir = APP_PATH . D . 'var' . D . 'cache' . D . $this->_appName . D . $this->_theme . D . $this->_controllerName . D;

        // 查找缓存文件
        if (!is_dir($varDir)) {
            mkdir($varDir, 775, true);
        }

        $cacheType = '';
        $cacheSwitch = 0;
        foreach (CACHE as $key => $val) {
            if ($key == 'phpCache' ) {
                $cacheType = $key;
                $cacheSwitch = $val['switch'];
            }
        }

        switch ($cacheType) {
            case 'phpCache':
                if ($cacheSwitch == 1) {
                    if($cacheKey){
                        $varDataPath = $varDir . $cacheKey. '_'.$expire.'.php';
                    }else{
                        echo '缓存文件：' . $varDir . $cacheKey. '_'.$expire . '.php' .' 不存在!';
                        return [];
                    }

                    if(file_exists($varDataPath)){
                        // 是否失效
                        $endexpire = substr($varDataPath,strrpos($varDataPath,'_')+1);
                        $endexpire = explode('.', $endexpire);

                        if(isset($endexpire['0'])){
                            $endexpire = $endexpire['0'];
                        }else{
                            $endexpire = 0;
                        }

                        $fileCreateTime = filectime($varDataPath);
                        if((($endexpire==0 || ($fileCreateTime + $endexpire) > time() )) && $flush==false){
                            // 缓存文件有效
                            return include ( $varDataPath );
                        }else{
                            unlink($varDataPath);
                            file_put_contents( $varDataPath, "<?php \n return ".'$'."$cacheKey=" . var_export($cacheVal, true).';');
                            return $cacheVal;
                        }
                    }else{
                        file_put_contents( $varDataPath, "<?php \n return ".'$'."$cacheKey=" . var_export($cacheVal, true).';');
                        return $cacheVal;
                    }
                }
        }
    }
}