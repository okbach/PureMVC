<?php
function smartInclude($filePath) {

    $basePath = dirname(__DIR__);

    $fullPath = $basePath . '/' . $filePath;
  

    if (file_exists($fullPath)) {

        //require $fullPath;
        

        return require_once  $fullPath; //true;
        
    } else {
        echo "basePath: " . realpath($basePath) . "<br>";

        echo "Failed to include the file. Requested path: " . realpath($fullPath) . "<br>";
        echo "File Path: " . $filePath . "<br>";

        echo "File name: " . basename($fullPath) . "<br>";


        return false;
    }
}

?>