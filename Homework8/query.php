<?php
//ini_set('default_socket_timeout', 3);
$url2 = "http://congress.api.sunlightfoundation.com/";
$url1 = "http://104.198.0.197:8080/";
$request_url = "";
$params = "";
$varCount = 0;
if (!empty($_GET)) {
    foreach($_GET as $key => $value) {
        if ($key == "type") {
            $request_type = $value;
        } else if ($key=="active_bill") {
            $params = $params . rawurlencode("history.active") . "=" . $value . "&";
            $params = $params . "last_version.urls.pdf__exists=true&";
        } else {
            $params = $params . rawurlencode(htmlspecialchars($key)) . "=" . rawurlencode(htmlspecialchars($value)) . "&";
        }
    }
}
$request_url = $url1 . $request_type . "?" . trim($params, "&");
$response = file_get_contents($request_url);
if (!$response) {
    $request_url = $url2 . $request_type . "?" . trim($params, "&");
    $response = file_get_contents($request_url);
    echo $response;
} else {
    echo $response;
}
?>