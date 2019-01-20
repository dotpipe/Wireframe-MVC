<?php
//namespace Swatch\Container;

//include('required.php');

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
            if (!is_dir("/$this->token"))
                mkdir("/$this->token");
            if (!is_dir("/$this->token")) {
                echo "Unable to create directories needed";
            }
            if (!is_dir("/$this->token/view/"))
                mkdir("/$this->token/view/");
            if (!is_dir("/$this->token/view")) {
                echo "Unable to create directories needed";
            }
            if (!file_exists("/$this->token/config.json")) {
                touch("/$this->token/index.php");
                touch("/$this->token/config.json");
            }
            if (!file_exists("/$this->token/config.json")) {
                echo "Unable to create files needed";
            }
            if (!file_exists("/$this->token/index.php")) {
                echo "Unable to create files needed";
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
            $fp = fopen("/$this->token/config.json", "w");
            fwrite($fp, json_encode($this));
            fclose($fp);
        }
        
        
        /*
        *
        * public function paginateModels
        * @parameters string, int, int
        *
        */
        public function paginateModels(string $view_name, int $begin = 0, int $end = 0) {
            $x = $this->mvc[$view_name]->paginateModels($this->token, $view_name, $begin, $end);
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
                touch("/$this->token/config.json");
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
            if (file_exists("/$this->token/config.json") && filesize("/$this->token/config.json") > 0)
                $fp = fopen("/$this->token/config.json", "r");
            else
                return 0;
            $json_context = fread($fp, filesize("/$this->token/config.json"));
            $json = json_decode($json_context);
            $obj = new PageControllers($json->token);
            foreach ($json->mvc as $key=>$value) {
            //index,Best
                foreach($json->mvc->$key as $ky=>$vl) {
                    if ($ky == "view") {
                        foreach($vl as $k=>$v)
                            if ($k == "copy")
                                $obj->newView($v);
                    }
                }
                foreach($json->mvc->$key as $ky=>$vl) {
                    if ($ky == "valid") {
                        foreach($vl as $k=>$v)
                            $obj->mvc[$key]->addModelValid($k,$v);
                    }
                }
                foreach($json->mvc->$key as $ky=>$vl) {
                //Second level auto 'index'
                    if ($ky == "data") {
                        $marray = [];
                        foreach($vl as $k=>$v) {
                            foreach($v as $k1=>$v1) {
                                $marray = array_merge($marray, array($k1=>$v1));
                            }
                        }
                        foreach($vl as $k=>$v) {
                            $obj->mvc[$key]->addModelData($k, $marray);
                        }
                    }
                }
                foreach($json->mvc->$key as $ky=>$vl) {
                    if ($ky == "view") {
                        foreach($vl as $k=>$v) {
                            if ($k == "partials") {
                                foreach($v as $r)
                                    $obj->mvc[$key]->view->addPartial($r);
                            }
                        }
                    }
                }
            }
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
    }

    class PageViews {

        public $path;
        public $partials = array();
        public $token;
        /*
        *
        * public function __construct
        * @parameters string, string
        *
        */
        function __construct(string $token, string $view_name) {
            $this->path = "/$token/view";
            $this->copy = $view_name;
            $this->partials = [];
            $this->token = $token;
        }
        
        /*
        *
        * public function addPartial
        * @parameters string
        *
        */
        public function addPartial(string $filename) {
            $bool = 0;
            if (!is_dir("$this->path/$this->copy/partials/"))
                mkdir("$this->path/$this->copy/partials");
            if (!is_dir("$this->path/$this->copy/partials/")) {
                echo "No permissions";
                return 0;
            }
            if (!file_exists("$this->path/$this->copy/partials/$filename")) {
                echo "Invalid Filename";
            }
            touch("$this->path/$this->copy/partials/$filename");
            foreach ($this->partials as $v) {
                if ($v == $filename)
                    $bool = 1;
            }
            if ($bool == 1)
                return 0;
            else
                $this->partials[] = "$filename";
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
            if (!is_dir("$this->path/$this->copy/partials/"))
                mkdir("$this->path/$this->copy/partials");
            if (!is_dir("$this->path/$this->copy/partials/")) {
                echo "Unable to create directories needed";
                return 0;
            }
            if (!file_exists("$this->path/$this->copy/partials/$filename")) {
                touch("$this->path/$this->copy/partials/$filename");
                if (!file_exists("$this->path/$this->copy/partials/$filename")) {
                    echo "Unable to create files needed";
                    return 0;
                }
            }
            foreach ($this->partials as $v) {
                if ($v == $filename)
                    $bool = 1;
            }
            if ($bool == 1)
                return 0;
            else
                $this->partials[] = "$filename";
            return 1;
        }
        
        /*
        *
        * public function changeTitle
        * @parameters string, string
        *
        */
        public function changeTitle(string $view_name, string $title) {
            $bool = 0;
            
            if (!$this->partials) {
                echo 'No such View';
                return 0;
            }

            $this->partials[$view_name]->title = $title;

            return 1;
        }
        
        /*
        *
        * public function writePage
        * @parameters string
        *
        */
        public function writePage(string $view_name) {
            $fp = fopen("$this->path/$this->copy/index.php", "w");
            $buf = "<?php\r";
            foreach ($this->partials as $v2) {
                $buf .= "require_once('$this->path/$this->copy/partials/" . $v2 . "');\n";
            }
            fwrite($fp, $buf);
            fclose($fp);
            return 1;
        }

        /*
        *
        * public function removePartial
        * @parameters string, string
        *
        */
        public function removePartial(string $view_name, string $partial) {
            $bool = 0;
            foreach ($this->partials[$view_name] as $v) {
                if ($v != $partial)
                    $k = array_merge($k, array($v));
                else
                    $bool = 1;
            }
            if ($bool == 1)
                return 1;
            return 0;
        }
    }

    class PageModels {
    
        public $model = array();
        public $valid = array();
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
            $this->token = $token;
            $this->copy = $view_name;
        }

        /*
        *
        * public function addModelField
        * @parameters string
        *
        */
        public function addModelField(string $fieldname) {
            if ($fieldname == null)
                return 0;
            $this->model[$fieldname] = null;
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
        * @parameters string, string, int, int
        *
        */
        public function paginateModels(string $token, string $view_name, int $begin = 0, int $end = 0) {
            $bool = 0;
            $int_cnt = 0;
            $buf = "echo '<table>";
                    $buf .= "<tr>";
                    $buf .= "<td style=\"background:opacity:0.0;border:0px;\"></td>";
                    foreach ($this->model as $kn=>$vn) {
                        if ($begin == $int_cnt || $end == 0 || $int_cnt < $end) {
                            $buf .= "<th>$kn</th>";
                        }
                        $int_cnt++;
                    }
                    $int_cnt = 0;
                    $bool = 1;
                    $buf .= "</tr>";
                    $int_dat = 0;
                    foreach ($this->data as $v1=>$va) {
                        $buf .= "<tr>";
                        $buf .= "<td>$v1</td>";
                        foreach ($va as $k2=>$v2) {

                            if ($begin == $int_cnt || $end == 0 || $int_cnt < $end) {
                                $buf .= "<td>$v2</td>";
                            }
                            $int_cnt++;
                        }
                        $int_cnt = 0;
                        $int_dat++;
                        $buf .= "</tr>";
                    }
                $buf .= "</table>';";
                //$bool
                $fp = null;
                if ($view_name == 'index')
                    $fp = fopen("$token/index.php", "a");
                else if (!file_exists("$token/view/$view_name/index.php"))
                    touch("$token/view/$view_name/index.php");
                $fp = fopen("$token/view/$view_name/index.php", "a");
                fwrite($fp, $buf);
                fclose($fp);
                return $buf;
        }
        
        /*
        *
        * public function addModelValid
        * @parameters string, string
        *
        */
        public function addModelValid(string $property, string $regex) {
            $this->addModelField($property);
            $this->valid[$property] = $regex;
            return 1;
        }

        /*
        *
        * public function checkValid
        * @parameters array, array, array &
        *
        */
        public function checkValid(array $valid, array $data, array &$wrong_ans = array()) {
            foreach ($data as $k => $v) {
                if ($v != null && !preg_match($valid[$k], $v)) {
                    $wrong_ans[$k] = null;
                }
                else
                    $wrong_ans[$k] = $v;
            }
            return 1;
        }
    }
    $y = array("Address" => "BenSt", "Duration" => "fixed");
    $z = array("Address" => "25th", "Duration" => "limited");

    $x = new PageControllers("adp");
    $x->newView("BestPHPEverNow");
    $x->mvc['index']->addModelField("Address");
    $x->mvc['index']->addModelValid("Address",'/.*/');
    $x->mvc['index']->addModelValid("Duration",'/.*/');
    $x->mvc['index']->addModelData('index', $y);
    
    $x->mvc['BestPHPEverNow']->view->addPartial("index.php");
    $x->mvc['BestPHPEverNow']->addModelValid("Address",'/1.*/');
    $x->mvc['BestPHPEverNow']->addModelValid("Duration",'/.*/');
    $x->mvc['BestPHPEverNow']->addModelData('index', $y);
    $x->mvc['BestPHPEverNow']->addModelData('friends', $z);
    $x->mvc['BestPHPEverNow']->view->writePage("BestPHPEverNow");
    $x->save();
    $x->paginateModels('BestPHPEverNow',0,2);
    echo json_encode($x);
    $x = $x->loadJSON();

    echo "<br><br><br>";
    echo json_encode($x);
    