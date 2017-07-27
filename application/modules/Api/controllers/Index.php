<?php

class IndexController extends \Core_ApiBase  {
    public function indexAction() {
        $mod = new UsersModel(); 
        for ($i=1;$i<1000;$i++){
            $data = $mod::find(5);
        }
        
        var_dump($data->uid);
// $xhprof_data = xhprof_disable();
// var_dump($xhprof_data);
// die;
        
        $view = $this->getView();
        $view->assign('foo',array(1,2,3,4));
        $view->display('index/index.phtml');
    }
    
    public function testAction(){
        $beanstalk = new Core_Beanstalk();
        $beanstalk->connect();
        $beanstalk->useTube( 'test' );
        $id= $beanstalk->put( 1024,0,3600, json_encode(array('name'=>'test','info'=>'马德制杖')));
        $beanstalk->disconnect();
        echo $id;
    }
    
    
    public function upAction(){
        if ($this->getRequest()->isPost()){
            $up = Files_ImageUpload::getInstance('Image');
            
            if(!$up->upload()){
                $imgError = $up->getUpError();
                $this->api_success($imgError);
            }
            // 获取上传文件后的地址
            $fileinfo = $up->getFiles('image');
            
            // 保存文件
            $up->save();
            $pas = $up->getTypeFile($fileinfo[0]['pic'],'S');
            $this->api_success($pas);
        }
        
    }
    
}

?>
