<?php 

defined('VG_ACCESS') or die('Доступ запрещен');

const TEMPLATE = 'templates/default/';
const ADMIN_TEMPLATE = 'core/admin/view/';
const UPLOAD_DIR = 'userfiles/';

const COOCIE_VERSION = '1.0.0';
const CRYPT_KEY = '+6uSnq/CoADpQXkBiecE2+wX4iYuO07mmwA0EgHP1SiFqKiid82ApYYXV9xAZm4ij6evrSH51sF+cQHed1H5pzCItqN1vJ5L+9dKFlfRddAEvj5Ympserx4sZeBLcY63';
const COOCIE_TIME = 60;
const BLOCK_TIME = 3;

const QTY = 8;
const QTY_LINKS = 3;

const ADMIN_CSS_JS = [
   'styles' => ['css/main.css'],
   'scripts' => [
       'js/frameworkfunctions.js',
       'js/scripts.js',
       'js/tinymce/tinymce.min.js',
       'js/tinymce/tinymce_init.js'
   ],
];

const USER_CSS_JS = [
   'styles' => [],
   'scripts' => [],
];

use core\base\exceptions\RouteException;

function autoloadMainClasses($class_name) {
   $class_name = str_replace('\\', '/', $class_name);

   if(!@include_once $class_name . '.php'){
      throw new RouteException('Не верное имя файла для подключения = '. $class_name);
   }

}

spl_autoload_register('autoloadMainClasses');