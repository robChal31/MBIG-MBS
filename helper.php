<?php
// helper.php

$removeLocationOnText = ['(Luar Jabodetabek)', 'Luar Jabodetabek', '(Jabodetabek)', 'Jabodetabek'];

function sanitizeText($text, $removeList = array()) {
    if (is_null($text) || !is_string($text)) {
        return '';
    }
    
    $text = trim($text);
    
    if (!empty($removeList) && is_array($removeList)) {
        foreach ($removeList as $removeString) {
            $text = str_ireplace($removeString, '', $text);
        }
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
    }
    
    return $text;
}

// 🔥 RETURN ARRAY YANG BENAR
return [
    'removeLocationOnText' => $removeLocationOnText,
    'sanitizeText' => 'sanitizeText' // atau function
];