<?php

function timeTrack() {}
function transparent_text($text, $color1, $color2) { return $text; }
function get_include_contents($file, $rec = []) {
    extract($rec);
    ob_start();
    include $file;;
    $string = ob_get_clean();
    return $string;
}
function statistics() {}
