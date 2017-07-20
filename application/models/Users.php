<?php 

use Illuminate\Database\Eloquent\Model as Mymodel; 

class UsersModel extends Mymodel{ 	
    protected $table = 'c_user'; 
    protected $primaryKey = 'uid';
    public $timestamps = false;
} ?>