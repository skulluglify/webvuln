<?php
include "../skfw/autoload.php";
//var_dump($_SERVER["QUERY_STRING"]);
//var_dump($_SERVER["REQUEST_METHOD"]);
//var_dump($_SERVER["REQUEST_URI"]);
//var_dump($_SERVER["REQUEST_TIME"]);


//$contentType = $_FILES["picture"]["type"];
//header("content-type: $contentType");
//$data = file_get_contents($_FILES["picture"]["tmp_name"]);
//echo $data;

try {
    $collector = new \Skfw\Cabbage\HttpHeaderCollector();
    foreach ($collector->headers() as $header)
    {
        foreach ($header->getValues() as $value)
        {
            echo $header->getName() . ": " . $value . "<br/>";
        }
    }

//    $collector = new \Skfw\Cabbage\HttpFileCollector();
//    var_dump($collector->files());

//    $collector = new \Skfw\Cabbage\HttpParamCollector();
//    var_dump($collector->params());

    $content_type = str($collector->header("content-type"));
    $json_unpack = $content_type === "application/json";

    $body_content = new \Skfw\Cabbage\HttpBodyContent(json_unpack: $json_unpack);
    var_dump($body_content->body());

} catch (\Exception $e) {
    echo $e->getMessage();

}