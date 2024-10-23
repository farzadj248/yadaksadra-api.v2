<?php

namespace App\Helper;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Date;
use App\Models\LogActivity;
use Request;

class EventLogs
{
    public static function getAll(){
        $files=File::files('./logs/');
        
        $paths=array();
        
        foreach($files as $path) { 
            $file = pathinfo($path);
            array_push($paths,$file['filename']);
        } 
    
        return $paths;
    }
    
    public static function get($file_name){
        if(!file_exists('./logs/'.$file_name.'.json')) {
            return null;
        }
        
        $json = \File::get('./logs/'.$file_name.'.json');
        $json_data = json_decode($json, true);
        
        
        return $json_data;
    }
    
    public static function save($item){
        $new_data = $item;
        
        $file_name=Date::now()->format('Y-m-d').'.json';
        
        if(!file_exists('./logs/'.$file_name)) {
            File::put('./logs/'.$file_name,"");
        } 
        
        $json = \File::get('./logs/'.$file_name);
        
        if($json){
            $json_data = json_decode($json, true);
            
            array_push($json_data,$new_data);
            
            File::put('./logs/'.$file_name,json_encode($json_data));
        }else{
            $data=array();
            array_push($data,$new_data);
            
            File::put('./logs/'.$file_name,json_encode($data));
        }
    }
    
    public static function remove($file_name){
        if(File::exists('./logs/'.$file_name.'.json')) {
            File::delete('./logs/'.$file_name.'.json');
        }
    }
    
    
   public static function addToLog($log) {
    	LogActivity::create([
    	    'subject' => $log['subject'],
    	    'body' => $log['body'],
    	    'user_id' => $log['user_id'],
    	    'user_name'=> $log['user_name'],
    	    'method' => Request::method(),
    	    'ip' => Request::ip()
    	]);

    }

    public static function logActivityLists($q) {
       $logs=LogActivity::orderBy("id","DESC");
        
        if($q){
            $logs->whereRaw('concat(log_activities.subject,log_activities.user_name,log_activities.created_at) like ?', "%{$q}%");
        }
        
        return $logs->paginate(10);
       
    }
    
    public static function deleteLog($id) {
    	LogActivity::find($id)->delete();
    }
    
}
