<?php
session_start();

// Security: Check if already installed
$installLockFile = __DIR__ . '/install.lock';
if (file_exists($installLockFile)) {
    die('
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Instalación Completada</title>
        <link rel="stylesheet" href="install.css">
    </head>
    <body>
        <div class="container">
            <h1 style="color: #059669;">✓ Instalación Completada</h1>
            <div class="alert alert-success">
                <p><strong>El sistema ya está instalado.</strong></p>
                <p style="margin-top: 1rem;">Por seguridad, el instalador está bloqueado.</p>
                <p style="margin-top: 1rem; font-size: 0.9rem;">
                    Para reinstalar, elimine el archivo <code>public/install/install.lock</code> y <code>.env</code>
                </p>
            </div>
            <a href="../" style="display: inline-block; margin-top: 1rem; padding: 0.75rem 1.5rem; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
                Ir al Sistema
            </a>
        </div>
    </body>
    </html>
    ');
}

// Helper function to check if a step is completed
function isStepCompleted($step) {
    return isset($_SESSION['install_progress']) && $_SESSION['install_progress'] >= $step;
}

// Current step
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// Path to project root
$rootDir = __DIR__ . '/../../';

// Step 1: Requirements Check
if ($step == 1) {
    $requirements = [
        'PHP Version >= 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'Config Directory Writable' => is_writable($rootDir) || is_writable($rootDir . '.env'),
    ];

    $allMet = !in_array(false, $requirements);

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $allMet) {
        $_SESSION['install_progress'] = 1;
        header('Location: ?step=2');
        exit;
    }
}

// Step 2: Database Configuration
if ($step == 2) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $host = $_POST['host'] ?? 'localhost';
        $dbName = $_POST['db_name'] ?? 'asset_manager';
        $user = $_POST['db_user'] ?? 'root';
        $pass = $_POST['db_pass'] ?? '';
        
        // New user creation options
        $createNewUser = isset($_POST['create_new_user']) && $_POST['create_new_user'] === '1';
        $newDbUser = $_POST['new_db_user'] ?? '';
        $newDbPass = $_POST['new_db_pass'] ?? '';

        try {
            // Attempt connection without DB name first to create it if needed
            $pdo = new PDO("mysql:host=$host", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create database if not exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
            
            // Create new database user if requested
            if ($createNewUser) {
                if (empty($newDbUser) || empty($newDbPass)) {
                    throw new Exception("Nuevo usuario y contraseña son requeridos.");
                }
                
                // Create user
                $pdo->exec("CREATE USER IF NOT EXISTS '$newDbUser'@'localhost' IDENTIFIED BY '$newDbPass';");
                
                // Grant privileges on the database
                $pdo->exec("GRANT ALL PRIVILEGES ON `$dbName`.* TO '$newDbUser'@'localhost';");
                $pdo->exec("FLUSH PRIVILEGES;");
                
                // Use the new user for future connections
                $user = $newDbUser;
                $pass = $newDbPass;
                
                $success = "Base de datos y usuario creados exitosamente.";
            }
            
            // Save to session
            $_SESSION['db_config'] = [
                'host' => $host,
                'name' => $dbName,
                'user' => $user,
                'pass' => $pass
            ];
            
            $_SESSION['install_progress'] = 2;
            header('Location: ?step=3');
            exit;

        } catch (PDOException $e) {
            $error = "Database connection failed: " . $e->getMessage();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Step 3: Import Schema
if ($step == 3) {
    if (!isset($_SESSION['db_config'])) {
        header('Location: ?step=2');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            $config = $_SESSION['db_config'];
            $pdo = new PDO("mysql:host={$config['host']};dbname={$config['name']}", $config['user'], $config['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Read SCHEMA.sql from install directory
            $schemaPath = __DIR__ . '/SCHEMA.sql';
            if (!file_exists($schemaPath)) {
                throw new Exception("SCHEMA.sql not found at $schemaPath");
            }

            $sql = file_get_contents($schemaPath);
            
            // Execute schema
            $pdo->exec($sql);

            $_SESSION['install_progress'] = 3;
            header('Location: ?step=4');
            exit;

        } catch (Exception $e) {
            $error = "Error importing schema: " . $e->getMessage();
        }
    }
}

// Step 4: Admin User Creation
if ($step == 4) {
    if (!isset($_SESSION['db_config'])) {
        header('Location: ?step=2');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        if (empty($name) || empty($email) || empty($password)) {
            $error = "All fields are required.";
        } else {
            try {
                $config = $_SESSION['db_config'];
                $pdo = new PDO("mysql:host={$config['host']};dbname={$config['name']}", $config['user'], $config['pass']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
                // Hash password
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
                // Insert into users table
                // Table structure: id, name, first_name..., email, ..., role, ..., password_hash, system_role
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, system_role, status) VALUES (?, ?, ?, 'Admin', 'admin', 'Activo')");
                $stmt->execute([$name, $email, $passwordHash]);
    
                $_SESSION['install_progress'] = 4;
                header('Location: ?step=5');
                exit;
            } catch (Exception $e) {
                $error = "Error creating admin user: " . $e->getMessage();
            }
        }
    }
}

// Step 5: Finalize (Write Config)
if ($step == 5) {
     if (!isset($_SESSION['db_config'])) {
        header('Location: ?step=2');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            $config = $_SESSION['db_config'];
            
            // Create .env file content
            $envContent = "DB_HOST='{$config['host']}'\n";
            $envContent .= "DB_NAME='{$config['name']}'\n";
            $envContent .= "DB_USER='{$config['user']}'\n";
            $envContent .= "DB_PASS='{$config['pass']}'\n";
            
            // Write to .env
            if (file_put_contents($rootDir . '.env', $envContent) === false) {
                 throw new Exception("Could not write .env file.");
            }

            // Create lock file in config directory
            file_put_contents($rootDir . 'config/installed.lock', 'Installed at: ' . date('Y-m-d H:i:s'));
            
            // Create lock file in install directory (security)
            file_put_contents(__DIR__ . '/install.lock', 'Installed at: ' . date('Y-m-d H:i:s'));
            
            // Destroy session install data
            session_destroy();

            header('Location: /activoFijo/public');
            exit;

        } catch (Exception $e) {
            $error = "Error finalizing installation: " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación Activo Fijo</title>
    <link rel="stylesheet" href="install.css">
</head>
<body>
    <div class="container">
        <h1>Instalación</h1>
        <div class="step-indicator">Paso <?php echo $step; ?> de 5</div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($step == 1): ?>
            <form method="POST">
                <h2>Requisitos del Sistema</h2>
                <ul class="requirements-list">
                    <?php foreach ($requirements as $req => $met): ?>
                        <li>
                            <?php echo $req; ?>
                            <span class="status-check <?php echo $met ? 'status-ok' : 'status-fail'; ?>">
                                <?php echo $met ? '✔ OK' : '✘ FALLÓ'; ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="form-group">
                    <?php if ($allMet): ?>
                        <button type="submit">Siguiente</button>
                    <?php else: ?>
                        <button type="button" disabled>Corrige los errores para continuar</button>
                    <?php endif; ?>
                </div>
            </form>
        <?php elseif ($step == 2): ?>
            <form method="POST" id="dbConfigForm">
                <h2>Configuración de Base de Datos</h2>
                
                <div class="form-group">
                    <label>Servidor (Host)</label>
                    <input type="text" name="host" value="localhost" required>
                </div>
                <div class="form-group">
                    <label>Nombre de la Base de Datos</label>
                    <input type="text" name="db_name" value="asset_manager" required>
                </div>
                
                <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #eee;">
                
                <div class="form-group">
                    <label>Usuario Admin MySQL (para crear BD y usuario)</label>
                    <input type="text" name="db_user" value="root" required>
                </div>
                <div class="form-group">
                    <label>Contraseña Admin MySQL</label>
                    <input type="password" name="db_pass">
                </div>
                
                <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #eee;">
                
                <div class="form-group" style="background: #f9fafb; padding: 1rem; border-radius: 8px; border: 1px solid #e5e7eb;">
                    <label style="display: flex; align-items: center; cursor: pointer; margin-bottom: 0;">
                        <input type="checkbox" name="create_new_user" value="1" id="createNewUserCheck" 
                               style="width: auto; margin-right: 0.5rem;" onchange="toggleNewUserFields()">
                        <span style="font-weight: 600; color: #059669;">Crear nuevo usuario MySQL para la aplicación</span>
                    </label>
                    <p style="font-size: 0.75rem; color: #6b7280; margin: 0.5rem 0 0 1.5rem;">
                        Recomendado: crea un usuario dedicado con permisos solo para esta base de datos.
                    </p>
                </div>
                
                <div id="newUserFields" style="display: none;">
                    <div class="form-group">
                        <label>Nuevo Usuario MySQL</label>
                        <input type="text" name="new_db_user" placeholder="activofijo_user" id="newUserInput">
                    </div>
                    <div class="form-group">
                        <label>Contraseña para Nuevo Usuario</label>
                        <input type="password" name="new_db_pass" placeholder="Contraseña segura" id="newPassInput">
                    </div>
                </div>
                
                <button type="submit">Crear BD y Continuar</button>
            </form>
            
            <script>
                function toggleNewUserFields() {
                    const checkbox = document.getElementById('createNewUserCheck');
                    const fields = document.getElementById('newUserFields');
                    const userInput = document.getElementById('newUserInput');
                    const passInput = document.getElementById('newPassInput');
                    
                    if (checkbox.checked) {
                        fields.style.display = 'block';
                        userInput.required = true;
                        passInput.required = true;
                    } else {
                        fields.style.display = 'none';
                        userInput.required = false;
                        passInput.required = false;
                    }
                }
            </script>
        <?php elseif ($step == 3): ?>
            <form method="POST">
                <h2>Importar Base de Datos</h2>
                <p>La conexión fue exitosa. Ahora se importará el esquema de la base de datos (tablas y datos iniciales).</p>
                <div class="alert alert-success">Conectado a la BD: <strong><?php echo $_SESSION['db_config']['name']; ?></strong></div>
                <button type="submit">Importar Esquema</button>
            </form>
        <?php elseif ($step == 4): ?>
             <form method="POST">
                <h2>Crear Usuario Administrador</h2>
                <p>Crea la cuenta del superusuario para acceder al sistema.</p>
                <div class="form-group">
                    <label>Nombre Completo</label>
                    <input type="text" name="name" placeholder="Administrador" required>
                </div>
                <div class="form-group">
                    <label>Correo Electrónico (Email)</label>
                    <input type="email" name="email" placeholder="admin@example.com" required>
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit">Crear Administrador</button>
            </form>
        <?php elseif ($step == 5): ?>
             <form method="POST">
                <h2>Finalizar Instalación</h2>
                <p>Todo está listo. Haz clic en finalizar para escribir el archivo de configuración y acceder al sistema.</p>
                <button type="submit">Finalizar e Ir al Inicio</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
