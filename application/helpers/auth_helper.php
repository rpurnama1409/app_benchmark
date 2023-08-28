<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if ( ! function_exists('authtoken'))
{
function authtoken($token){
    
        $secret_key = '9Vh02ch78'; 
        
        if ($token){
            try{  
                JWT::$leeway = 60; // $leeway in seconds
                $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
                // $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));   
                if ($decoded){
                    return ['status'=> true,'msg'=> 'token verified'];
                }
            } catch (\Exception $e) {
                return ['status'=> false, 'msg'=> $e->getMessage()];
                
            }
        }  
} 
}