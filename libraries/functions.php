<?php 


function myPrint($Mydata){
    echo '<pre>';
    print_r($Mydata);
    echo '</pre>';
}

if (!function_exists('mb_str_replace')) {

	function mb_str_replace($search, $replace, $subject){
        return implode($replace, explode($search, $subject));
    }
}