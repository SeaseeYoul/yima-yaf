<?php

class IndexController extends \Core_ApiBase  {
    public function indexAction() {
        echo '二货';
    }
    
    public function testAction(){
        $beanstalk = new Core_Beanstalk();
        $beanstalk->connect();
        $beanstalk->watch( 'test' );
        $id= $beanstalk->put( 1024,0,3600, json_encode(array('name'=>'test','info'=>'马德制杖')));
        $beanstalk->disconnect();
        echo $id;
    }
    
    public function test_beanstalk(){
        $beanstalk = new Core_Beanstalk();
        $beanstalk->connect();
        $beanstalk->watch( 'test' );
        while (true){
            $job = $beanstalk->reserve();
            if ($job){
                // 处理job数组
                echo $job['body'];
                $beanstalk->delete($job['id']);
                echo '处理完成'.$job['id'];
                sleep(1);
            } else {
                sleep(2);
                echo '无处理数据睡眠两秒';
            }
        }
    }
    
}

?>
