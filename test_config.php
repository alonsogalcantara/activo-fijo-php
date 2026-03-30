<?php
require 'config/Config.php';
file_put_contents('test_out.txt', print_r(Config::all()['paths'], true) . "\nUpload path: " . Config::uploadsPath() . "\n");
