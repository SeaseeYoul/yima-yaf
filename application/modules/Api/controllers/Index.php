<?php

class IndexController extends \Yaf_Controller_Abstract  {

    public function indexAction() {
        $model = new Log();
        var_dump($model::record(2,'WARNING'));
        FirePHP_ChromePhp::log(array('info'=>'MDZZ文雪','content'=>'是的确实是这样'));
        echo $model::save();die;
    }
    
}

?>
