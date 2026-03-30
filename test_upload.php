<?php
require_once 'config/Config.php';
require_once 'src/Models/Document.php';

$mock_file = [
    'name' => 'test_document.txt',
    'type' => 'text/plain',
    'tmp_name' => __DIR__ . '/test_document.txt',
    'error' => UPLOAD_ERR_OK,
    'size' => 100
];

// Create dummy temporary file
file_put_contents(__DIR__ . '/test_document.txt', "Hello World");

// Fake $_SERVER and $_SESSION to avoid warnings
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SESSION['user_id'] = 1;

$model = new \Models\Document();
$result = $model->uploadFile($mock_file, 'asset', 1, 1);
var_dump($result);

if (file_exists(__DIR__ . '/test_document.txt')) {
    unlink(__DIR__ . '/test_document.txt');
}
