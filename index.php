<?php
include_once("global.php");
include_once("ipbase.php");
include_once("config.local.inc.php");
include_once("common.inc.php");

$body = print_quotes();
print_whole_page($body);

function print_quotes() {
   global $CONSTANTS, $qs_ip;
   $quotes_count = 0;
   $body = load_quotes($quotes_count);
   $title = transparent_text($CONSTANTS["site_name"], "#000000", "#aaaaaa");
   $last_update = print_last_update();
   $page_time = timeTrack();
   $rec = array(
      "title"        => $title,
      "quotes"       => $body,
      "last_update"  => $last_update,
      "quotes_count" => $quotes_count,
      "page_time"    => $page_time,
      "admin_email"  => $CONSTANTS["admin_email"],
   );
   $body = get_include_contents("home.php", $rec);
   return $body;
}

function load_quotes(&$quotes_count) {
   global $CONSTANTS, $qs_ip;
   cache_init();
   if ($body = cache_get(get_quotes_cache_name())) {
   } else {
      $rec = array(
         "site_name" => $CONSTANTS["site_name"],
      );
      $body = get_include_contents("quotes.php", $rec);
      $rows = explode("\r\n\r\n\r\n\r\n", $body);
      if (is_array($rows)) {
         $page = ($page > 0) ? $page : 1;
         $per_page = 3000;
         $pages_num = 1;
         $quotes_count = count($rows);
         $display_rows = array_slice($rows, count($rows) - $per_page * $page, $per_page);
         $display_rows = quotes_filter($display_rows, $qs_ip);
         $display_rows = array_reverse($display_rows);
         $body = implode("\r\n\r\n\r\n", $display_rows);
         $body = str_replace("\r\n\r\n", "<br />", str_replace("\r\n\r\n\r\n", "<br /><br /><br />", $body));
         cache_set(get_quotes_cache_name(), $body);
      }
   }
   cache_close();
   return $body;
}

function print_disclaimer() {
   global $CONSTANTS;
   $title = transparent_text($CONSTANTS["site_name"], "#000000", "#aaaaaa");
   $rec = array(
      "title"       => $title,
      "site_name"   => $CONSTANTS["site_name"],
      "admin_email" => $CONSTANTS["admin_email"],
   );
   $body = get_include_contents("disclaimer.php", $rec);
   return $body;
}

function print_news() {
   global $CONSTANTS;
   $title = transparent_text($CONSTANTS["site_name"], "#000000", "#aaaaaa");
   $rec = array(
      "title"       => $title,
      "site_name"   => $CONSTANTS["site_name"],
      "admin_email" => $CONSTANTS["admin_email"],
   );
   $body = get_include_contents("news.php", $rec);
   $body = str_replace("\r\n\r\n", "<br />", str_replace("\r\n\r\n\r\n", "<br /><br /><br />", $body));
   return $body;
}

