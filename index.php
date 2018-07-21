<?php

// (rus.) Стартовые настройки

header('Content-type: text/html; charset=utf-8');
date_default_timezone_set('Asia/Yekaterinburg');
setlocale(LC_ALL, 'ru_RU.UTF-8');
mb_internal_encoding("utf-8");

error_reporting(E_ALL);
ini_set('display_errors', '1');

// (rus.) Перехват ошибок

set_exception_handler(
    function (\Throwable $throw) {
        
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        
        echo $throw->getMessage();
        exit(1);
        
    }
);

/**
 * @param object $xmlIterator
 * @param integer $level
 */
function echoNodeList($xmlnode, $level)
{
    if ($level > 6) {
        $headerLevel = 6;
    } else {
        $headerLevel = $level;
    }
    
    echo '<h'.$headerLevel.'>'.$xmlnode['name'].'</h'.$headerLevel.'>';
    
    echo '<ul>';
    
    try {
        foreach ($xmlnode->recordtable->record as $record) {
            
            echo '<li><a href="base/'.$record['id'].'/'.$record['file'].'">'.$record['name'].'</a></li>';
            
        }
    } catch (\Exception $e) {
    }
    
    try {
        foreach ($xmlnode->node as $node) {
            
            echoNodeList($node, $level+1);
            
        }
    } catch (\Exception $e) {
    }
    
    echo '</ul>';
}

echo '<a href="https://velonski.ru">velonski.ru</a>';
echo '<h1>Персональная база данных MyTetra</h1>';

$xml = simplexml_load_file('mytetra.xml');
foreach ($xml as $node) {
    
    echoNodeList($node, 2);
    
}

exit(0);