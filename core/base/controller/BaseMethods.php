<?php 







namespace core\base\controller;


trait BaseMethods
{

    protected function clearStr($str){

        if(is_array($str)){
            foreach ($str as $key => $item) $str[$key] = $this->clearStr($item);
            return $str;
        }else{
            return trim(strip_tags($str));
        }
    }

    protected function clearNum($num){
        return (!empty($num) && preg_match('/\d/', $num)) ?
            preg_replace('/[^\d.]/','', $num) * 1 : 0;
    }

    protected function isPost(){
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    protected function isAjax(){
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    protected function redirect($http = false, $code = false){

        if($code){
            $codes = ['301' => 'HTTP/1.1 301 Move Permanently'];

            if($codes[$code]) header($codes[$code]);         
        }

        if($http) $redirect = $http;
        else $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : PATH;

        header("Location: $redirect");

        exit;
    }

    protected function getStyles(){

        if($this->styles){
            foreach($this->styles as $style) echo '<link rel="stylesheet" href="' . $style . '">';
        }
    }

    protected function getScripts(){

        if($this->scripts){
            foreach($this->scripts as $script) echo '<script src="' . $script . '"></script>';
        }
    }

    protected function writeLog($messege, $file = 'log.txt', $event = 'Fault'){

        $dataTime = new \DateTime();

        $str = $event . ': ' . $dataTime->format('d-m-Y G-i-s') . ' - ' . $messege . "\r\n";

        file_put_contents('log/' . $file, $str, FILE_APPEND);

    }
}