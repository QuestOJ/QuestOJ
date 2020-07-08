<?php

    /**
     * 执行库 : HTML 输出 (/lib/lib_html.php)
     * 这个执行库会记录 HTML 的调用顺序并输出
     */

    
    if(!defined("load")){
        header("Location:/403");
        exit;
    }

    class HTML{
        var $sources = array();
        var $pageTitle = "首页";

        var $appendFlag = 1;
        var $nonRoute = 1;

        public static function header() {
            global $pageTitle;

            if(!include(PATH."/lib/template/header.php")){
                log::writeLog(7, 1, 112, "HTML Print Failed (header) - Include Failed");
            }
            
            if (isUserLogin()) {
                if(!include(PATH."/lib/template/nav.php")){
                    log::writeLog(7, 1, 112, "HTML Print Failed (nav) - Include Failed");
                }                
            } else {
                if(!include(PATH."/lib/template/nav_guest.php")){
                    log::writeLog(7, 1, 112, "HTML Print Failed (nav_guest) - Include Failed");
                }                            
            }
        }

        public static function footer() {
            if(!include(PATH."/lib/template/footer.php")){
                log::writeLog(7, 1, 112, "HTML Print Failed (footer) - Include Failed");
            }           
        }

        /**
         * 渲染 HTML
         * 
         * 将会输出通过 AppendHTML 调用的所有 HTML 文件（/lib/template/$templateName.php）
         * 如果调用失败将会写入系统日志（文件 + 数据库），并抛出异常
         * 
         * @param NULL
         * @return NULL
         */

        public function printHTML(){
            global $pageTitle;
            
            $pageTitle = $this->pageTitle;

            foreach($this->sources as $name){
                if(!include(PATH."/lib/template/{$name}.php")){
                    log::writeLog(7, 1, 112, "HTML Print Failed ({$name}) - Include Failed");
                }
            }
        }

        /**
         * 扩展 HTML 文件载入列表
         * 
         * 将文件加入渲染列表中等待渲染（/lib/template/$templateName.php）
         * 若文件不存在将会写入系统日志（文件 + 数据库），并抛出异常
         * 
         * @param string $templateName
         * @return null
         *
         */

        public function appendHTML($templateName){
            array_push($this->sources, $templateName);

            if($templateName == "403"){
                frame::HTTPCode(403);
            }else if($templateName == "404"){
                frame::HTTPCode(404);
            }

            if(!file_exists(PATH."/lib/template/{$templateName}.php")){
                log::writeLog(7, 1, 111, "HTML Append Failed ({$templateName}) - No such file");
            }
        }

        /**
         * 修改站点标题
         * 
         * @param string $pageTitle
         * @return null
         */

        public function pageTitle($pageTitle){
            $this->pageTitle = $pageTitle;
        }

        /**
         * 转发请求
         * 
         * @param string $routePath
         * @param string $routeFile
         * @param string $pageTitle
         * @param int $cases
         * 
         * $routePath -- 匹配的 URL
         * $routeFile -- 转发文件
         * $pageTitle -- 站点标题
         * $cases -- (0 HTML文件) / (1 控制器文件)
         * 
         * @return bool
         */

        public function route($routePath, $routeFile, $pageTitle, $cases){
            $routePath = str_replace("/","\\/",$routePath);

            $rstr = '';
            $tmparr=parse_url($_SERVER["REQUEST_URI"]);
            $rstr = $tmparr['path'];

            if(preg_match("/^".$routePath."$/", $rstr)){
                $this->nonRoute = 0;

                if($cases == 1){
                    if(!include_once(PATH."/lib/controller/{$routeFile}.php")){
                        log::writeLog(7, 1, 110, "Route Failed ({$routeFile}) - No such file");
                    }
                }else{
                    $this->appendHTML($routeFile);
                }

                $this->pageTitle($pageTitle);
                return true;
            }

            return false;
        }

        /**
         * 检查转发状态
         * 
         * 如果没有转发成功，返回 404
         * 
         * @param NULL
         * @return NULL
         */
        
        public function checkRoute(){
            if($this->nonRoute == 1){
                header("Location:/404");
            }
        }
    }
?>