<?php
namespace Swatch\Container;

include('required.php');

	class PageControllers {

        public $token;
        public $mvc = array();
        
        /*
        *
        * public function __construct
        * @parameters string, string
        *
        */
        function __construct(string $tok, string $view = 'index') {
            $this->mvc = null;
            $this->token = $tok;
            if (!is_dir("$this->token"))
                mkdir("$this->token");
            if (!is_dir("$this->token")) {
                echo "Unable to create directories needed";
            }
            if (!is_dir("$this->token/view/"))
                mkdir("$this->token/view");
            if (!is_dir("$this->token/view")) {
                echo "Unable to create directories needed";
            }
            if (!is_dir("$this->token/view/shared"))
                mkdir("$this->token/view/shared");
            if (!is_dir("$this->token/view/shared")) {
                echo "Unable to create directories needed";
            }
            if (!file_exists("$this->token/config.json"))
                touch("$this->token/config.json");
            if (!file_exists("$this->token/index.php"))
                touch("$this->token/index.php");
            if (!file_exists("$this->token/view/shared/index.php"))
                touch("$this->token/view/shared/index.php");
            if (!file_exists("$this->token/config.json")) {
                echo "Unable to create files needed";
            }
            if (!file_exists("$this->token/index.php")) {
                echo "Unable to create files needed";
            }
            if (!file_exists("$this->token/view/shared/index.php")) {
                echo "Unable to create files needed1";
            }
            $this->path = "$this->token/view";
            $this->mvc['index'] = new PageModels('index');
            $this->mvc['index']->view = new PageViews($tok, $view);
        }

        
        /*
        *
        * public function addModelData
        * @parameters string, array
        *
        */
        public function addModelData(string $view_name, array $data) {
            $this->mvc[$view_name]->addModelData($view_name, $data);
            return 1;
        }

        /*
        *
        * public function save
        * @parameters none
        *
        */
        public function save() {
            $fp = fopen("$this->token/config.json", "w");
            fwrite($fp, serialize($this));
            fclose($fp);
            return 1;
        }
        
        
        /*
        *
        * public function paginateModels
        * @parameters string, string, int, int
        *
        */
        public function paginateModels(string $view_name, string $filename, int $begin = 0, int $end = 0) {
            $x = $this->mvc[$view_name]->paginateModels($this->token, $view_name, $filename, $begin, $end);
            return $x;
           
        }
        
        /*
        *
        * private function add_view
        * @parameters string
        *
        */
        private function add_view(string $view_name) {
            if (is_dir("/$this->path/$view_name/")) {
                if (!file_exists("/$this->path/$view_name/index.php")) {
                    $fp = fopen("/$this->path/$view_name/index.php", "w");
                    fclose($fp);
                }
                
            }
            else {
                mkdir("/$this->path/$view_name");
                if (!is_dir("/$this->path/$view_name")) {
                    echo "Permissions Error: Unable to create Directory";
                    return 0;
                }
                
                touch("/$this->path/$view_name/index.php");
                touch("$this->token/config.json");
            }
            $this->mvc[$view_name] = new PageModels($view_name);
            $this->mvc[$view_name]->view = new PageViews($this->token, $view_name);
            return 1;
        }
        
        /*
        *
        * public function newView
        * @parameters string
        *
        */
        public function newView(string $view_name) {
            $this->add_view($view_name);
        }
        
        
        /*
        *
        * public function loadJSON
        * @parameters none
        *
        */
        public function loadJSON() {
            if (file_exists("$this->token/config.json") && filesize("$this->token/config.json") > 0)
                $fp = fopen("$this->token/config.json", "r");
            else
                return 0;
            $json_context = fread($fp, filesize("$this->token/config.json"));
            
            $obj = unserialize($json_context);
            return $obj;
        }
        
        /*
        *
        * public function addPartial
        * @parameters string
        *
        */
        public function addPartial(string $filename) {
            return $this->view->addPartial($filename);
        }
        
        /*
        *
        * public function addShared
        * @parameters string
        *
        */
        public function addShared(string $filename) {
            return $this->view->addShared($filename);
        }
        
        /*
        *
        * public function addAction
        * @parameters string, string, string
        *
        */
        public function addAction(string $token, string $view_name, string $action_name) {
            return $this->view->actions[$action_name] = new Actions($this->token, $view_name, $action_name);
        }
    }

    class Actions {

        public $token;
        public $path;
        public $copy;
        /*
        *
        * public function __construct
        * @parameters string, string, string
        *
        */
        function __construct(string $token, string $view_name, string $action_name) {
            $this->copy = $view_name;
            if (!is_dir("$token/view/$view_name") && !mkdir("$token/view/$view_name"))
                echo "Unable to create needed directories";
            $this->path = "$this->token/view/$view_name";
            $this->token = $token;
            $this->createAction($action_name);
        }
        
        /*
        *
        * public function createAction
        * @parameters string
        *
        */
        public function createAction(string $action_name) {
            $this->actions[$action_name] = new PageViews($this->token, $this->copy);
            echo $action_name;
            $this->actions[$action_name]->addPartial("index.php", $this->copy, $action_name);
            return 0;
        }
    }

    class PageViews {

        public $path;
        public $partials = array();
        public $token;
        public $injections = array();
        public $selector;

        /*
        *
        * public function __construct
        * @parameters string, string, string
        *
        */
        function __construct(string $token, string $view_name) {
            $this->token = $token;
            $this->path = "$this->token/view";

            if (!is_dir("$this->path/$view_name") && !mkdir("$this->path/$view_name"))
                echo "Unable to create needed directories";
            $this->copy = $view_name;
            $this->injections = [];
        }
        
        /*
        *
        * public function addPartial
        * @parameters string, string, string
        *
        */
        public function addPartial(string $filename, string $view_name = "FALSE", string $res_dir = "FALSE") {
            if ($view_name == "FALSE")
                $view_name = $this->copy;
            if ($res_dir == "FALSE")
                $res_dir = "partials";
            $bool = 0;
            
            $exp_dir = explode('/', $res_dir);
            $kmd = "";
            foreach ($exp_dir as $k) {
                $kmd .= $k . '/';
                if (!is_dir("$this->path/$view_name/$kmd") && !mkdir("$this->path/$view_name/$kmd"))
                    echo "Unable to create directories";
            }
            if (!is_dir("$this->path/$view_name/$res_dir") && !mkdir("$this->path/$view_name/$res_dir")) {
                echo "Unable to create directories needed";
                return 0;
            }
            if (!file_exists("$this->path/$view_name/$res_dir/$filename") && !touch("$this->path/$view_name/$res_dir/$filename")) {
                echo "Unable to create files needed";
                return 0;
            }
            foreach ($this->injections as $k=>$v) {
                if ($v[0] == $res_dir && $v[1] == $filename)
                    $bool = 1;
            }
            if ($bool == 1)
                return 0;
            else
                $this->injections[] = array($res_dir,"$filename");
            return 1;
        }
        
        /*
        *
        * public function addShared
        * @parameters string
        *
        */
        public function addShared(string $filename) {
            $bool = 0;
            if (!is_dir("$this->path/shared"))
                mkdir("$this->path/shared");
            if (!is_dir("$this->path/shared")) {
                echo "Unable to create directories needed";
                return 0;
            }
            if (!file_exists("$this->path/shared/$filename")) {
                touch("$this->path/shared/$filename");
                if (!file_exists("$this->path/shared/$filename")) {
                    echo "Unable to create files needed";
                    return 0;
                }
            }
            foreach ($this->injections as $k=>$v) {
                if ($v[0] == "shared" && $v[1] == $filename)
                    $bool = 1; 
            }
            if ($bool == 1)
                return 0;
            else
                $this->injections[] = array("shared","$filename");
            return 1;
        }


        public function writeIndex() {
            $buff = "<?php";
            $fp = fopen("$this->token/index.php", "a");
            foreach ($this->injections as $k) {
                $vk = $k[0];
                $vv = $k[1];
                if ($vk == "shared") {
                    $buff .= "\r\n\trequire_once(\"view/shared/$vv\");";
                }
                else {
                    $buff .= "\r\nrequire_once(\"view/index/$vk/$vv\");";
                }
            }
            $buff .= "\r\n?>\r\n";
            fwrite($fp, $buff);
            fclose($fp);
        }
        /*
        *
        * public function writePage
        * @parameters string
        *
        */
        public function writePage(string $view_name) {
            $fp = null;
            if ($view_name == "index") {
                touch("$this->token/index.php");
                $this->writeIndex();
                return 1;
            }
            else if (!file_exists("$this->path/$view_name/index.php") && !touch("$this->path/$view_name/index.php"))
                echo "Unable to create files needed";
            if ($view_name != "index")
                $fp = fopen("$this->path/$view_name/index.php", "a");
            $buff = "<?php\r\n";
            foreach ($this->injections as $k) {
                $vk = $k[0];
                $vv = $k[1];
                if ($vk == "shared") {
                    $buff .= "require_once(\"../shared/$vv\");\r\n";
                }
                else if ($vk == "partials")
                    $buff .= "require_once(\"../$view_name/$vk/$vv\");\r\n";
                else
                    $buff .= "require_once(\"../$view_name/$vk/$vv\");\r\n";
                    

            }
            $buff .= "?>\r\n";
            fwrite($fp, $buff);
            fclose($fp);
            return 1;
        }

        /*
        *
        * public function removeDependency
        * @parameters string, string
        *
        */
        public function removeDependency(string $folder, string $partial) {
            $bool = 0;
            $k = [];
            foreach ($this->injections as $v) {
                if ($v != array($folder,$partial))
                    $k = array_merge($k, array($v));
                else $bool = 1;
            }
            if ($bool == 1) {
                $this->injections = $k;
                return 1;
            }
            return 0;
        }
    }

    class PageModels {
    
        public $model = array();
        public $valid = array();
        public $errormsgs = array();
        public $data = array();
        public $copy;
        public $token;

        
        /*
        *
        * public function __construct
        * @parameters string, string
        *
        */
        function ___construct(string $token, string $view_name) {
            $this->valid = [];
            $this->model = [];
            $this->errormsgs = [];
            $this->token = $token;
            $this->copy = $view_name;
        }

        /*
        *
        * public function addModelField
        * @parameters string
        *
        */
        public function addModelField(string $fieldname, string $regex = "/.*/", string $errmsg = "Please reenter data") {
            if ($fieldname == null)
                return 0;
            $this->model[$fieldname] = null;
            $this->model[$fieldname]['regex'] = $regex;
            $this->model[$fieldname]['errmsg'] = $errmsg;
            return 1;
        }
        
        
        /*
        *
        * public function editModelData
        * @parameters string, array
        *
        */
        public function editModelData(string $view_name, array $data) {
            $wrong_ans = [];
            $this->checkValid($this->valid, $data, $wrong_ans);
            if (sizeof($this->model) == 0) {
                $this->model = $data;
                return 1;
            }
            foreach ($data as $k=>$v) {
                if ($wrong_ans[$k] == null)
                    $this->data[$view_name]->$k = null;
                else
                    $this->data[$view_name]->$k = $v;
            }
            return 1;
        }

        /*
        *
        * public function addModelData
        * @parameters string, array
        *
        */
        public function addModelData(string $view_name, array $data) {
            $wrong_ans = [];
            $this->checkValid($this->valid, $data, $wrong_ans);
            
            if (sizeof($this->model) == 0) {
                $this->model = $data;
                return 1;
            }
            $cnt = 0;
            foreach ($this->model as $k=>$v) {
                if ($cnt == 0)
                    $cnt = sizeof($this->valid);
                if (sizeof($data) != $cnt) {
                    echo "Size of entry has " . sizeof($data) . " columns and should be $cnt";
                    return 0;
                }
            }
            foreach ($data as $k=>$v) {
                if ($wrong_ans[$k] == null)
                    $this->data[$view_name][$k] = null;
                else
                    $this->data[$view_name][$k] = $v;
            }
            return 1;
        }
        
        /*
        *
        * public function paginateModels
        * @parameters string, string, string, int, int
        *
        */
        public function paginateModels(string $token, string $view_name, string $filename, int $begin = 0, int $end = 0) {
            $bool = 0;
            $int_cnt = 0;
            $buf = "<?php\r\n\techo '<table>\r\n";
            $buf .= "\t\t<tr>\r\n";
            if ($begin == 0)
                $buf .= "\t\t\t<th style=\"background:opacity:0.0;border:0px;\"></th>\r\n";
            foreach ($this->model as $kn=>$vn) {
                if ($begin == $int_cnt || $end == 0 || $int_cnt < $end) {
                    $buf .= "\t\t\t<th>$kn</th>\r\n";
                }
                $int_cnt++;
            }
            $int_cnt = 0;
            $bool = 1;
            $buf .= "\t\t</tr>\r\n";
            $int_dat = 0;
            foreach ($this->data as $v1=>$va) {
                $buf .= "\t\t<tr>\r\n";
                if ($begin == 0)
                    $buf .= "\t\t\t<td>$v1</td>\r\n";
                foreach ($va as $k2=>$v2) {
                    if ($begin == $int_cnt || $end == 0 || $int_cnt < $end) {
                        $buf .= "\t\t\t<td>$v2</td>\r\n";
                    }
                    $int_cnt++;
                }
                $int_cnt = 0;
                $int_dat++;
                $buf .= "\t\t</tr>\r\n";
            }
            $buf .= "\t</table>';\r\n ?>\r\n";
            //$bool
            $view = null;
            if ($view_name == 'index')
                $view = "$token/$filename";
            else if (!file_exists("$token/view/$view_name/$filename"))
                touch("$token/view/$view_name/$filename");
            if (!file_exists("$token/$filename") && !file_exists("$token/view/$view_name/$filename")) {
                echo "Unable to make files needed";
                return 0;
            }
            else if ($view_name != 'index')
                $view = "$token/view/$view_name/$filename";
            $fp = fopen($view, "a");
            fwrite($fp, $buf);
            fclose($fp);
            return $buf;
        }
        
        /*
        *
        * public function addModelValid
        * @parameters string, string, string
        *
        */
        public function addModelValid(string $property, string $regex = "/.*/", string $errmsg = "Please check your entry") {
            
            $this->addModelField($property, $regex, $errmsg);
            $this->valid[$property]['regex'] = $regex;
            $this->valid[$property]['errmsg'] = $errmsg;
            return 1;
        }
        
        /*
        *
        * public function checkValid
        * @parameters string, array &
        *
        */
        private function errorReturn(string $key, array &$errormsgs = array()) {
            $errormsgs[$key] = $this->valid[$key]['errmsg'];
            return 1;
        }

        /*
        *
        * public function checkValid
        * @parameters array, array, array &
        *
        */
        public function checkValid(array $valid, array $data, array &$wrong_ans = array()) {
            $this->errormsgs = [];
            foreach ($data as $k => $v) {
                if ($v != null && !preg_match($valid[$k]['regex'], $v)) {
                    $wrong_ans[$k] = null;
                    $this->errorReturn($k, $this->errormsgs);
                }
                else
                    $wrong_ans[$k] = $v;
            }
            return 1;
        }
    }
