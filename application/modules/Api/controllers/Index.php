<?php

class IndexController extends \Core_ApiBase  {
    public function indexAction() {
        $mod = new UsersModel(); 
        $data = $mod::find(5);
        var_dump($data->uid);die;
//         $this->getView()->display('index.html'); 
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
