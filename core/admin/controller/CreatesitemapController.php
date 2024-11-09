<?php 


namespace core\admin\controller;

use core\base\controller\BaseMethods;

class CreatesitemapController  extends BaseAdmin
{

   use BaseMethods;

   protected $all_links = [];
   protected $temp_links = [];
   protected $bad_links = [];

   protected $maxLinks = 5000;

   protected $parsingLogFile = 'parsing_log.txt';
   protected $fileArr = ['jpg', 'png', 'jpeg', 'gif', 'xls', 'xlsx', 'pdf', 'mp4', 'mpeg', 'mp3'];

   protected $filterArr = [
      'url' => [],
      'get' => []
   ];

   public function inputData($links_counter = 1, $redirect = true){

      $links_counter = $this->clearNum($links_counter);

      if(!function_exists('curl_init')){

         $this->cancel(0, 'Library CURL is absent. Sitemap creation impossible', '', true);

         $this->writelog('Отсутствует библиотека CURL');
         $_SESSION['res']['answer'] = '<div class="error">Library CURL is absent. Sitemap creation impossible</div>';
         $this->redirect();
      }

      if(!$this->userId) $this->execBase();

      if(!$this->checkParsingTable()){

         $this->cancel(0, 'You have a problem with database table parsing_data', '', true);

      }

      set_time_limit(0);

      $reserve = $this->model->get('parsing_data')[0];

      $table_rows = [];

      foreach($reserve as $name => $item){

         $table_rows[$name] = '';

         if($item) $this->$name = json_decode($item);
            elseif($name === 'all_links' || $name === 'temp_links') $this->$name = [SITE_URL];

      }

      $this->maxLinks = (int)$links_counter > 1 ? ceil($this->maxLinks / $links_counter) : $this->maxLinks;

      while($this->temp_links){

         $temp_links_count = count($this->temp_links);

         $links = $this->temp_links;

         $this->temp_links = [];

         if($temp_links_count > $this->maxLinks){

            $links = array_chunk($links, ceil($temp_links_count / $this->maxLinks));

            $count_chunks = count($links);

            for($i = 0; $i < $count_chunks; $i++){
               

               $this->parsing($links[$i]);

               unset( $links[$i] );

               if($links){

                  foreach($table_rows as $name => $item){

                     if($name === 'temp_links')  $table_rows[$name] = json_encode(array_merge(...$links));
                     else $table_rows[$name] = json_encode($this->$name);

                  }

                  $this->model->edit('parsing_data', [
                        'fields' => $table_rows
                     ]);

               }

            }

         }else{

            $this->parsing($links);

         }

         foreach($table_rows as $name => $item){

            $table_rows[$name] = json_encode($this->$name);

         }

         $this->model->edit('parsing_data', [
            'fields' => $table_rows
         ]);

      }

      foreach($table_rows as $name => $item){

         $table_rows[$name] = '';

      }

      $this->model->edit('parsing_data', [
         'fields' => $table_rows
      ]);

      if($this->all_links){

         foreach($this->all_links as $key => $link){

            if(!$this->filter($link) || in_array($link, $this->bad_links)) unset($this->all_links[$key]);  // Выпуск №70 16:32

         }

      }


      $this->createSitemap();
      
      if($redirect){
         
         !$_SESSION['res']['answer'] && $_SESSION['res']['answer'] = '<div class="success">Site map created</div>';
   
         $this->redirect();

      }else{

         $this->cancel(1, 'Sitemap is created! ' . count($this->all_links) . ' links', '', true);

      }

   }

   protected function parsing($urls){

      if(!$urls) return;
      
               $curlMulty = curl_multi_init();
      
               $curl = [];

      foreach($urls as $i => $url){

         $curl[$i] = curl_init();
         curl_setopt($curl[$i], CURLOPT_URL, $url);
         curl_setopt($curl[$i], CURLOPT_RETURNTRANSFER, true);
         curl_setopt($curl[$i], CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36");
         curl_setopt($curl[$i], CURLOPT_HEADER, true);
         curl_setopt($curl[$i], CURLOPT_FOLLOWLOCATION, 1);
         curl_setopt($curl[$i], CURLOPT_TIMEOUT, 120);
         curl_setopt($curl[$i], CURLOPT_ENCODING, 'gzip,deflate');

         curl_multi_add_handle($curlMulty, $curl[$i]);

      }

      do{

         $status = curl_multi_exec($curlMulty, $active);
         $info = curl_multi_info_read($curlMulty);

         if(false !== $info){

            if($info['result' !== 0]){

               $i = array_search($info['handle'], $curl);

               $error = curl_errno($curl[$i]);
               $messege = curl_error($curl[$i]);
               $header = curl_getinfo($curl[$i]);

               if($error !== 0){

                  $this->cancel(0, 'Error loading' . $header['url'] .
                                       ' http code ' . $header['http code'] .
                                       'error: ' . $error . ' messege ' . $messege
                  
                                 );

               }

            }
            
         }

         if($status > 0){

            $this->cancel(0, curl_multi_strerror($status));

         }


      /* }while ($status === CURLM_CALL_MULTI_PERFOM || $active); */  // Выпуск 64 20:00
      }while ($active > 0); 


      $result = [];

      foreach($urls as $i => $url){

         $result[$i] = curl_multi_getcontent($curl[$i]);
         curl_multi_remove_handle($curlMulty, $curl[$i]);
         curl_close($curl[$i]);

         if(!preg_match('/Content-Type:\s+text\/html/ui', $result[$i])){

            $this->bad_links[] = $url;
   
            $this->cancel(0, 'Incorrect content type' . $url);

           continue;
   
         }

         if(!preg_match('/HTTP\/\d\.?\d?\s+20\d/ui', $result[$i])){

            $this->bad_links[] = $url;
   
            $this->cancel(0, 'Incorrect server code' . $url);

            continue;
   
         }

         $this->createLinks($result[$i]);

      }

      curl_multi_close($curlMulty);
      
   }

   protected function createLinks($content){

      if($content){
         echo('CURL SUCCESS');

         preg_match_all('/<a\s*?[^>]*?href\s*?=\s*?(["\'])(.+?)\1[^>]*?>/ui', $content, $links); 
                  

         if($links[2]){

            foreach($links[2] as $link){

              if($link === '/' || $link === SITE_URL . '/') continue;

               foreach($this->fileArr as $ext){

                  if($ext){

                     $ext = addslashes($ext);
                     $ext = str_replace('.', '\.', $ext);

                     if(preg_match('/' . $ext . '(\s*$|\?[^\/]*$)/ui', $link)){

                        continue 2;

                     }
                  }
               }

               if(strpos($link, '/') === 0){
                  $link = SITE_URL . $link;
               }

               $site_url = mb_str_replace('.', '\.', mb_str_replace('/', '\/', SITE_URL));

               if(!in_array($link, $this->bad_links) && 
                  !preg_match('/^(' . $site_url . ')?\/?#[^\/]*?$/ui', $link) && 
                  strpos($link, SITE_URL) === 0 &&
                  !in_array($link, $this->all_links)){

                     $this->temp_links[] = $link;   // Выпуск №65 11-31
                     $this->all_links[] = $link;

               }

            }

         }

      }
   
   }

   protected function filter($link){

      if($this->filterArr){

         foreach($this->filterArr as $type => $values){

            if($values){

               foreach($values as $item){

                  $item = str_replace('/','\/', addslashes($item));

                  if($type === 'url'){

                     if(preg_match('/^[^\?]*'. $item . '/ui', $link)){
                     return false;

                     }
                  }

                  if($type === 'get'){

                     if(preg_match('/(\?|&amp;|=|&)'. $item . '(=|&amp;|&|$)/ui', $link, $matches)) 
                     return false;
                     

                  }

               }

            }

         }
      }

      return true;
   }

   protected function checkParsingTable(){

      $tables = $this->model->showTables();

      if(!in_array('parsing_data', $tables)){

         $query = 'CREATE TABLE parsing_data (all_links longtext, temp_links longtext, bad_links longtext)';

         if(!$this->model->query($query, 'c') || 
         !$this->model->add('parsing_data', ['fields' => ['all_links' => '', 'temp_links' => '', 'bad_links' => '']])
         ){ return false;  }

      }

      return true;
   }

   protected function cancel($cuccess = 0, $messege = '', $log_messege = '', $exit = false){

      $exitArr = [];

      $exitArr['cuccess'] = $cuccess;
      $exitArr['messege'] = $messege ? $messege : 'ERROR PARSING';
      $log_messege = $log_messege ? $log_messege : $exitArr['messege'];

      $class = 'cuccess';

      if(!$exitArr['cuccess']){

         $class = 'error';

         $this->writeLog($log_messege,'parsing_log.txt');

      }

      if($exit){

         $exitArr['messege'] = '<div class="' . $class . '">' . $exitArr['messege'] . '</div>';
         exit(json_encode($exitArr));

      }

   }

   protected function createSitemap(){

      $dom = new \DOMDocument('1.0', 'utf-8');
      $dom->formatOutput = true;

      $root = $dom->createElement('urlset');
      $root->setAttribute('xmlns','http://www.sitemaps.org/schemas/sitemap/0.9');
      $root->setAttribute('xmlns:xls','http://w3.org/2001/XMLSchema-instanse');
      $root->setAttribute('xsi:schemaLocation','http://www.sitemaps.org/schemas/sitemap/0.9 http://http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
      
      $dom->appendChild($root);

      $sxe = simplexml_import_dom($dom);

      if($this->all_links){
         
         $date = new \DateTime();
         $lastMod = $date->format('Y-m-d') . 'T' . $date->format('H:i:s+01:00');

         foreach($this->all_links as $item){

            $elem = trim(mb_substr($item, mb_strlen(SITE_URL)), '/');
            $elem = explode('/', $elem);

            $count = '0.' . (count($elem) - 1);
            $priority = 1 - (float)$count;

            if($priority == 1) $priority = '1.0';

            $urlMain = $sxe->addChild('url');

            $urlMain->addChild('loc', htmlspecialchars($item));

            $urlMain->addChild('lastmod', $lastMod);
            $urlMain->addChild('chengefreg', 'weekly');
            $urlMain->addChild('priority', $priority);
            

         }

      }

      $dom->save($_SERVER['DOCUMENT_ROOT'] . PATH . 'sitemaps.xml');

   }

}