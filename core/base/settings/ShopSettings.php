<?php

namespace core\base\settings;

use core\base\controller\Singleton;
use core\base\settings\Settings;

class ShopSettings
{

   use BaseSettings;

   private $routes = [
      'plugins' => [
         'dir' => false,
         'routes' => [
            
         ]
      ],
   ];
   
   private $templateArr = [
      'text' => ['price', 'short', 'name'],
      'textarea' => ['goods_content']
   ];


}
