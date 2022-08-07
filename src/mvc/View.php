<?php

namespace Zeyuan\Cherry\mvc;

/**
 * 视图基类
 */
class View
{
    protected $variables = array();
    protected $_controller;
    protected $_action;
    protected $_theme;
    protected $_app;
    protected $_out_put;

    // 构造函数
    function __construct($app, $theme, $controller, $action)
    {
        $this->_controller = strtolower($controller);
        $this->_action = strtolower($action);
        $this->_theme = $theme;
        $this->_app = strtolower($app);
    }

    // 分配变量
    public function assign($name, $value)
    {
        $this->variables[$name] = $value;
    }

    // 渲染显示
    public function render($viewName, $expire = 0, $flush=false)
    {

        $cacheType = '';
        $cacheSwitch = 0;
        foreach (CACHE as $key=> $val){
            if($key=='fullPageCache'){
                $cacheType = $key;
                $cacheSwitch = $val['switch'];
            }
        }

        switch ($cacheType){
            case 'fullPageCache':
                if($cacheSwitch==1){
                    $viewDir = APP_PATH . D .'var' . D . 'template' . D . $this->_app . D . $this->_theme . D . $this->_controller . D;
                    // 查找缓存文件
                    if(!is_dir($viewDir)){
                        mkdir($viewDir,775,true);
                    }

                    if($viewName){
                        $varTemplatePath = $viewDir . md5($viewName). '_'.$expire.'.php';
                    }else{
                        $varTemplatePath = $viewDir. md5($this->_action . '.php').'_'.$expire.'.php';
                    }

                    if(file_exists($varTemplatePath)){
                        // 是否失效
                        $endexpire = substr($varTemplatePath,strpos($varTemplatePath,'_')+1);
                        $endexpire = explode('.', $endexpire);

                        if(isset($endexpire['0'])){
                            $endexpire = $endexpire['0'];
                        }else{
                            $endexpire = 0;
                        }

                        $fileCreateTime = filectime($varTemplatePath);
                        if(($endexpire==0 || ($fileCreateTime + $endexpire) > time() ) && $flush==false){
                            // 缓存文件有效
                            include ( $varTemplatePath );
                        }else{
                            unlink($varTemplatePath);
                            $contents =  $this->fetch($viewName);
                            echo $contents;
                            file_put_contents( $varTemplatePath, $contents);
                        }
                    }else{
                        unlink($varTemplatePath);
                        $contents =  $this->fetch($viewName);
                        echo $contents;
                        file_put_contents( $varTemplatePath, $contents);
                    }
                }else{
                    extract($this->variables);
                    if($viewName){
                        $controllerLayout = APP_PATH . 'template/' . D . $this->_app . D . $this->_theme . D . $this->_controller . '/' . $viewName;
                    }else{
                        $controllerLayout = APP_PATH . 'template/' . D . $this->_app . D . $this->_theme . D . $this->_controller . '/' . $this->_action . '.php';
                    }

                    //判断视图文件是否存在
                    if (file_exists($controllerLayout)) {
                        include($controllerLayout);
                    } else {
                        echo '<h1>无法找到视图文件:'.$controllerLayout.'</h1>';
                    }
                }
                break;
        }
    }

    public function fetch($viewName){
        extract($this->variables);
        if($viewName){
            $controllerLayout = APP_PATH . 'template/' . D . $this->_app . D . $this->_theme . D . $this->_controller . '/' . $viewName;
        }else{
            $controllerLayout = APP_PATH . 'template/' . D . $this->_app . D . $this->_theme . D . $this->_controller . '/' . $this->_action . '.php';
        }

        //判断视图文件是否存在
        if (file_exists($controllerLayout)) {
            ob_start();
            include($controllerLayout);
            $content = ob_get_contents();
            ob_end_clean();
            return $this->_out_put =  $content;
        } else {
            echo '<h1>无法找到视图文件</h1>';
        }
    }
}