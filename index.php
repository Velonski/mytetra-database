<?php

// (rus.) Стартовые настройки

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
 * (rus.) С помощью этой функции выводим на веб-странице "дерево" каталога
 * всех записей в базе данных.
 * @param resource $xmlnode
 * @param integer $level
 * @return void
 */
function echoNodeList($xmlnode, int $level = 1)
{
    if ($level > 6) {
        $headerLevel = 6;
    } else {
        $headerLevel = $level;
    }
    
    echo '<h'.$headerLevel.'>'.$xmlnode['name'].'</h'.$headerLevel.'>';
    
    echo '<ul>';
    
    if (isset($xmlnode->recordtable->record)) {
        foreach ($xmlnode->recordtable->record as $record) {
            
            echo '<li><a href="base/'.$record['dir'].'/'.$record['file'].'">'.$record['name'].'</a></li>';
            
        }
    }
    
    if (isset($xmlnode->node)) {
        foreach ($xmlnode->node as $node) {
            
            echoNodeList($node, $level+1);
            
        }
    }
    
    echo '</ul>';
}

/**
 * (rus.) Ищем в базе данных запись с конкретным идентификатором.
 * Когда запись обнаруживается, то возвращаем массив мета-данных записи.
 * @param resource $xmlnode
 * @param string $recordId
 * @return mixed[]
 */
function searchRecordMetaData($xmlnode, string $recordId): array
{
    if (isset($xmlnode->recordtable->record)) {
        foreach ($xmlnode->recordtable->record as $record) {
            
            if ($record['id'] === $recordId) return $record;
            
        }
    }
    
    if (isset($xmlnode->node)) {
        foreach ($xmlnode->node as $node) {
            
            $data = searchRecordMetaData($node, $recordId);
            if (!empty($data)) return $data;
            
        }
    }
    
    return [];
}

/**
 * @return void
 */
function echoPageDataTree()
{
    header('Content-type: text/html; charset=utf-8');
    http_response_code(200);
    
    echo '<a href="https://velonski.ru">velonski.ru</a>';
    echo '<h1>Персональная база данных MyTetra</h1>';

    $xml = simplexml_load_file('mytetra.xml');
    foreach ($xml as $node) {
        
        echoNodeList($node, 2);
        
    }
}

/**
 * @param string $recordId
 * @return void
 */
function echoPageRecord($recordId)
{
    $xml = simplexml_load_file('mytetra.xml');
    foreach ($xml as $node) {
        
        $data = searchRecordMetaData($node, $recordId);
        if (!empty($data)) break;
        
    }
    
    if (empty($data)) {
        
        echoPage404('Не найдена запись с указанным идентификатором.');
        
    }
    
    $recordFilePath = './base/'.$record['dir'].'/'.$record['file'];
    if (!file_exists($recordFilePath)) {
        throw new \Exception('Файл с данными не существует.');
    }
    
    header('Content-type: text/html; charset=utf-8');
    http_response_code(200);
    
    echo '<a href="https://velonski.ru">velonski.ru</a>';
    echo '<a href="/">Каталог записей</a>';
    
    echo '<h1>'.$data['name'].'</h1>';
    
    echo '<ul>';
    echo '<li><b>автор:</b> '.$data['author'].'</li>';
    echo '<li><b>ссылка:</b> '.$data['url'].'</li>';
    echo '<li><b>теги:</b> '.$data['tags'].'</li>';
    echo '</ul>';
    
    foreach (getPageRecordBody($recordFilePath) as $echoString) {
        
        echo $echoString;
        
    }
}

/**
 * Generator.
 * @param string $filePath
 * @yield string
 */
function getPageRecordBody(string $filePath)
{
    $file = fopen($filePath);
    
    $bodyFound = false;
    
    $charCount = 0;
    $chars = '';
    
    while (feof($file) !== true) {
        
        $chars .= fgetc($file);
        $charCount++;
        
        if ($bodyFound === false) {
            if ($charCount )
        }
        
    }
    
    fclose($file);
}

/**
 * @param string $message
 * @return void
 */
function echoPage404($message)
{
    header('Content-type: text/plain; charset=utf-8');
    http_response_code(404);
    
    echo $message;
}

/**
 * (rus.) Обработка GET-параметров запроса к веб-странице.
 * 
 * Если в запросе есть GET-параметр $recordId, то пытаемся найти в базе данных конкретную запись
 * и показать её содержимое на веб-странице.
 * 
 * В противном случае показываем "дерево" каталога записей.
 */

if (isset($_GET['recordId'])) {
    
    echoPageRecord($_GET['recordId']);
    
} else {
    
    echoPageDataTree();
    
}

exit(0);