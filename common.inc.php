<?

function print_whole_page($body) {
   global $CONSTANTS;
   header("Content-type: text/html; charset=".$CONSTANTS["php_charset"]);
   $google_code = get_include_contents("google_code.php");
   $CONSTANTS["body"] = $body;
   $CONSTANTS["google_code"] = $google_code;
   $page = get_include_contents("page.php", $CONSTANTS);
   echo $page;
   die();
}

function print_last_update() {
   $res = date("D, d F Y H:i", filemtime("quotes.php"));
   $res = "Last update: " . $res;
   return $res;
}

function get_cookie_name() {
   $numbers = explode(".", $_SERVER["REMOTE_ADDR"]);
   $add = "";
   if (is_array($numbers)) {
      foreach ($numbers as $number) {
         $add .= sprintf("%02s", dechex($number));
      }
   }
   $res = "qsquotesv2".$add;
   return $res;
}

function accept_statistics() {
   $ip = $_SERVER["REMOTE_ADDR"];
   $host = @gethostbyaddr($ip);
   $l = $ip . " - " . $host . " - " . date("Y-m-d H:i:s") . " - " . $_SERVER["HTTP_REFERER"] . "\n";
   $f = fopen("accept.txt", "a+");
   if ($f) {
      fwrite($f, $l);
      fclose($f);
   }
   return true;
}

function decline_statistics() {
   $ip = $_SERVER["REMOTE_ADDR"];
   $host = @gethostbyaddr($ip);
   $l = $ip . " - " . $host . " - " . date("Y-m-d H:i:s") . " - " . $_SERVER["HTTP_REFERER"] . "\n";
   $f = fopen("decline.txt", "a+");
   if ($f) {
      fwrite($f, $l);
      fclose($f);
   }
   return true;
}

function print_decline_quote_code($rec) {
   global $CONSTANTS;
   $rec["admin_email"] = $CONSTANTS["admin_email"];
   $res = get_include_contents("decline_quote_code.php", $rec);
   return $res;
}

function get_quote_id($quote) {
   if (preg_match("/<a.*?href=\"(.*?)\">.*?<\/a>/is", $quote, $matches)) {
      $id = $matches[1];
   }
   return $id;
}

function get_quote_link($quote) {
   global $CONSTANTS;
   $res = $CONSTANTS["http_path"] . "/" . get_quote_id($quote);
   return $res;
}

function quotes_filter($rows, $ip) {
   $delimiters = ",;\s";
   if (is_array($rows) && is_array($ip)) {
      foreach ($rows as $key => $row) {
         if (preg_match("/<!--deny (.*?)-->/is", $row, $matches)) {
            $str = $matches[1];
            $items = preg_split("/[$delimiters]+/", $str, -1, PREG_SPLIT_NO_EMPTY);
            if (is_array($items)) {
               foreach ($items as $item) {
                  if (array_search($_SERVER["REMOTE_ADDR"], $ip) == $item) {
                     unset($rows[$key]);
                     break;
                  }
               }
            }
         }
         if ($rows[$key]) {
            if (preg_match("/<!--allow (.*?)-->/is", $row, $matches)) {
               $str = $matches[1];
               $items = preg_split("/[$delimiters]+/", $str, -1, PREG_SPLIT_NO_EMPTY);
               if (is_array($items)) {
                  $keep = false;
                  foreach ($items as $item) {
                     if (array_search($_SERVER["REMOTE_ADDR"], $ip) == $item) {
                        $keep = true;
                        break;
                     }
                  }
                  if (!$keep) {
                     unset($rows[$key]);
                  }
               }

            }
         }
         if ($rows[$key]) {
/*
            $rows[$key] .= print_decline_quote_code(array(
               "quote_link" => get_quote_link($rows[$key]),
            ));
*/
            $rows[$key] = preg_replace("/<!--.*?-->/is", "", $rows[$key]);
         }
      }
   }
   return $rows;
}

function get_quotes_cache_name() {
   return "qsquotes";
}

function cache_init() {
   global $memcache_obj, $CONSTANTS;
   if (!$CONSTANTS["enable_cache"]) {
      return false;
   }
   if (class_exists("Memcache")) {
      $memcache_obj = new Memcache();
      $res = $memcache_obj->connect($CONSTANTS["memcache_host"], $CONSTANTS["memcache_port"]);
      if (!$res) {
         cache_close();
         $memcache_obj = $_res;
      }
   } elseif (function_exists("memcache_connect")) {
      $memcache_obj = memcache_connect($host, $port);
   } else {
      $memcache_obj = false;
   }
   return $memcache_obj;
}

function cache_set($name, $value) {
   global $memcache_obj, $CONSTANTS;
   if (class_exists("Memcache") && $memcache_obj) {
      $res = $memcache_obj->set($name, $value, $CONSTANTS["memcache_pack"] ? MEMCACHE_COMPRESSED : 0, $CONSTANTS["memcache_time"]);
   } elseif (function_exists("memcache_set" && $memcache_obj)) {
      $res = memcache_set($memcache_obj, $name, $value, $CONSTANTS["memcache_pack"] ? MEMCACHE_COMPRESSED : 0, $CONSTANTS["memcache_time"]);
   } else {
      $res = false;
   }
   return $res;
}

function cache_get($name) {
   global $memcache_obj, $CONSTANTS;
   if (class_exists("Memcache") && $memcache_obj) {
      $res = $memcache_obj->get($name);
   } elseif (function_exists("memcache_get" && $memcache_obj)) {
      $res = memcache_get($name);
   } else {
      $res = false;
   }
   return $res;
}

function cache_close() {
   global $memcache_obj, $CONSTANTS;
   if (class_exists("Memcache") && $memcache_obj) {
      $res = $memcache_obj->close();
   } elseif (function_exists("memcache_close") && $memcache_obj) {
      $res = memcache_close($memcache_obj);
   } else {
      $res = false;
   }
   return $res;
}

