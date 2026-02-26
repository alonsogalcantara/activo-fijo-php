<?php
session_start();

// Autoloader (Simple implementation for Vanilla PHP)
spl_autoload_register(function ($class_name) {
    if (strpos($class_name, 'Models\\') === 0) {
        require_once __DIR__ . '/../src/Models/' . substr($class_name, 7) . '.php';
    } elseif (strpos($class_name, 'Controllers\\') === 0) {
        require_once __DIR__ . '/../src/Controllers/' . substr($class_name, 12) . '.php';
    }
});

// Simple Router
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']);

// Remove script path from request URI to get relative path
if (strpos($requestUri, $scriptName) === 0) {
    $requestUri = substr($requestUri, strlen($scriptName));
}

$uri = explode('/', $requestUri);

// Remove empty elements and reindex
$uri = array_values(array_filter($uri));

// Check if installed
$installLock = __DIR__ . '/../config/installed.lock';
$envFile = __DIR__ . '/../.env';

if (!file_exists($installLock) && !file_exists($envFile)) {
    // Redirect to installer
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    // Ensure no double slashes if basePath is /
    $basePath = rtrim($basePath, '/');
    header('Location: ' . $basePath . '/install/');
    exit();
}

// Basic Routing Logic (to be expanded)
// Default to Home/Login
if (empty($uri)) {
    // Check if logged in, else redirect to login
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit();
    } else {
        header('Location: /dashboard');
        exit();
    }
}

// Route dispatching
$controllerName = isset($uri[0]) ? ucfirst($uri[0]) . 'Controller' : 'AuthController';
$methodName = isset($uri[1]) ? $uri[1] : 'index';

// Auth Routes
if ($uri[0] === 'login' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once __DIR__ . '/../src/Controllers/AuthController.php';
    $controller = new \Controllers\AuthController();
    $controller->login();
    exit();
}
if ($uri[0] === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../src/Controllers/AuthController.php';
    $controller = new \Controllers\AuthController();
    $controller->authenticate();
    exit();
}
if ($uri[0] === 'logout') {
    require_once __DIR__ . '/../src/Controllers/AuthController.php';
    $controller = new \Controllers\AuthController();
    $controller->logout();
    exit();
}

// Dashboard
if ($uri[0] === 'dashboard') {
     if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit();
    }
    require_once __DIR__ . '/../src/Controllers/DashboardController.php';
    $controller = new \Controllers\DashboardController();
    $controller->index();
    exit();
}


// Assets
if ($uri[0] === 'assets') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit();
    }
    require_once __DIR__ . '/../src/Controllers/AssetsController.php';
    $controller = new \Controllers\AssetsController();
    
    if (isset($uri[1])) {
        if ($uri[1] === 'create') {
             $controller->create();
        } elseif ($uri[1] === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
             $controller->store();
        } elseif ($uri[1] === 'edit' && isset($uri[2])) {
             $controller->edit($uri[2]);
        } elseif ($uri[1] === 'update' && isset($uri[2]) && $_SERVER['REQUEST_METHOD'] === 'POST') {
             $controller->update($uri[2]);
        } elseif ($uri[1] === 'dispose' && isset($uri[2]) && $_SERVER['REQUEST_METHOD'] === 'POST') {
             $controller->dispose($uri[2]);
        } elseif ($uri[1] === 'delete' && isset($uri[2])) {
             $controller->delete($uri[2]);
        } elseif ($uri[1] === 'detail' && isset($uri[2])) {
             $controller->show($uri[2]);
        } else {
             $controller->index();
        }
    } else {
        $controller->index();
    }
    exit();
}

// Users
if ($uri[0] === 'users') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit();
    }
    require_once __DIR__ . '/../src/Controllers/UsersController.php';
    $controller = new \Controllers\UsersController();
    
    if (isset($uri[1])) {
        if ($uri[1] === 'create') {
             $controller->create();
        } elseif ($uri[1] === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
             $controller->store();
        } elseif ($uri[1] === 'edit' && isset($uri[2])) {
             $controller->edit($uri[2]);
        } elseif ($uri[1] === 'update' && isset($uri[2]) && $_SERVER['REQUEST_METHOD'] === 'POST') {
             $controller->update($uri[2]);
        } elseif ($uri[1] === 'delete' && isset($uri[2])) {
             $controller->delete($uri[2]);
        } elseif ($uri[1] === 'detail' && isset($uri[2])) {
             $controller->show($uri[2]);
        } else {
             $controller->index();
        }
    } else {
        $controller->index();
    }
    exit();
}

// Accounts
if ($uri[0] === 'accounts') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit();
    }
    require_once __DIR__ . '/../src/Controllers/AccountsController.php';
    $controller = new \Controllers\AccountsController();
    
    if (isset($uri[1])) {
        if ($uri[1] === 'create') {
             $controller->create();
        } elseif ($uri[1] === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
             $controller->store();
        } elseif ($uri[1] === 'edit' && isset($uri[2])) {
             $controller->edit($uri[2]);
        } elseif ($uri[1] === 'update' && isset($uri[2]) && $_SERVER['REQUEST_METHOD'] === 'POST') {
             $controller->update($uri[2]);
        } elseif ($uri[1] === 'delete' && isset($uri[2])) {
             $controller->delete($uri[2]);
        } elseif ($uri[1] === 'detail' && isset($uri[2])) {
             $controller->show($uri[2]);
        } else {
             $controller->index();
        }
    } else {
        $controller->index();
    }
    exit();
}

// Accounting
if ($uri[0] === 'accounting') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit();
    }
    require_once __DIR__ . '/../src/Controllers/AccountingController.php';
    $controller = new \Controllers\AccountingController();
    
    if (isset($uri[1])) {
        if ($uri[1] === 'update_ajax') {
             $controller->update_ajax();
        } elseif ($uri[1] === 'forecast_ajax') {
             $controller->forecast_ajax();
        } else {
             $controller->index();
        }
    } else {
        $controller->index();
    }
    exit();
}

// Documents
if ($uri[0] === 'documents') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit();
    }
    require_once __DIR__ . '/../src/Controllers/DocumentsController.php';
    $controller = new \Controllers\DocumentsController();
    
    if (isset($uri[1])) {
        if ($uri[1] === 'delete' && isset($uri[2])) {
             $controller->delete($uri[2]);
        }
    }
    exit();
}

// Reports
if ($uri[0] === 'reports') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit();
    }
    require_once __DIR__ . '/../src/Controllers/ReportsController.php';
    $controller = new \Controllers\ReportsController();
    
    if (isset($uri[1])) {
        if ($uri[1] === 'accounting_pdf') {
            $controller->accounting_pdf();
        } elseif ($uri[1] === 'accounting_excel') {
            $controller->accounting_excel();
        } elseif ($uri[1] === 'responsive_letter' && isset($uri[2])) {
            $controller->responsive_letter_pdf($uri[2]);
        } elseif ($uri[1] === 'kardex' && isset($uri[2])) {
            $controller->kardex_pdf($uri[2]);
        } elseif ($uri[1] === 'history' && isset($uri[2])) {
            $controller->history_pdf($uri[2]);
        }
    }
    exit();
}

// Admin Routes
if ($uri[0] === 'admin' && isset($uri[1]) && $uri[1] === 'users') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit();
    }
    require_once __DIR__ . '/../src/Controllers/UsersController.php';
    $controller = new \Controllers\UsersController();
    
    if (isset($uri[2])) {
        if ($uri[2] === 'grant' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->grantAccess();
        } elseif ($uri[2] === 'revoke' && isset($uri[3])) {
            $controller->revokeAccess($uri[3]);
        }
    } else {
        $controller->admin();
    }
    exit();
}

// Audit Log Routes
if ($uri[0] === 'audit') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit();
    }
    require_once __DIR__ . '/../src/Controllers/AuditController.php';
    $controller = new \Controllers\AuditController();
    $controller->index();
    exit();
}

// 404
http_response_code(404);
require_once __DIR__ . '/../src/Views/404.php';
