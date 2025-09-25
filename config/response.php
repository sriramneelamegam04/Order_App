<?php
// config/response.php

function sendSuccess($msg, $data = []) {
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "msg" => $msg,
        "data" => $data
    ]);
    exit;
}

function sendError($msg, $code = 400) {
    http_response_code($code);
    echo json_encode([
        "success" => false,
        "msg" => $msg
    ]);
    exit;
}
