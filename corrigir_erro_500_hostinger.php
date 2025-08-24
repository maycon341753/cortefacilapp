<?php
// Script de Correção para Erro 500 no Hostinger
// Baseado nas melhores práticas da Hostinger para resolver erros 500

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Correção de Erro 500 - Hostinger</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

$correcoes_aplicadas = [];
$erros_encontrados = [];

// 1. Backup do .htaccess atual
echo "<h2>1. 💾 Backup do .htaccess</h2>";
if (file_exists('.htaccess')) {
    $backup_name = '.htaccess_backup_' . date('Y-m-d_H-i-s');
    if (copy('.htaccess', $backup_name)) {
        echo "<p>✅ Backup criado: $backup_name</p>";
        $correcoes_aplicadas[] = "Backup do .htaccess criado";
    } else {
        echo "<p>❌ Erro ao criar backup do .htaccess</p>";
        $erros_encontrados[] = "Falha ao criar backup do .htaccess";
    }
} else {
    echo "<p>ℹ️ Arquivo .htaccess não encontrado</p>";
}
echo "<hr>";

// 2. Criar .htaccess otimizado para Hostinger
echo "<h2>2. 🔧 Criando .htaccess Otimizado</h2>";
$htaccess_content = '# .htaccess otimizado para Hostinger - CorteFacil App
# Gerado automaticamente em ' . date('Y-m-d H:i:s') . '

# Habilitar RewriteEngine
RewriteEngine On

# Definir diretório base
RewriteBase /

# Configurações de segurança
<Files ~ "^\.(htaccess|htpasswd)$">
Order allow,deny
Deny from all
</Files>

# Proteger arquivos sensíveis
<FilesMatch "\.(sql|log|md|txt)$">
Order allow,deny
Deny from all
</FilesMatch>

# Configurações PHP para Hostinger
<IfModule mod_php.c>
    php_value memory_limit 256M
    php_value max_execution_time 300
    php_value upload_max_filesize 32M
    php_value post_max_size 32M
    php_flag display_errors Off
    php_flag log_errors On
</IfModule>

# Redirecionamento para HTTPS (se necessário)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Roteamento principal
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Configurações de cache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
</IfModule>

# Compressão GZIP
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Configurações de erro personalizadas
ErrorDocument 404 /index.php
ErrorDocument 403 /index.php
ErrorDocument 500 /index.php';

if (file_put_contents('.htaccess', $htaccess_content)) {
    echo "<p>✅ Novo .htaccess criado com configurações otimizadas para Hostinger</p>";
    $correcoes_aplicadas[] = ".htaccess otimizado criado";
} else {
    echo "<p>❌ Erro ao criar novo .htaccess</p>";
    $erros_encontrados[] = "Falha ao criar .htaccess otimizado";
}
echo "<hr>";

// 3. Verificar e corrigir permissões de arquivos
echo "<h2>3. 🔐 Correção de Permissões</h2>";
$arquivos_permissoes = [
    'index.php' => 0644,
    'login.php' => 0644,
    'cadastro.php' => 0644,
    '.htaccess' => 0644
];

$diretorios_permissoes = [
    'includes' => 0755,
    'config' => 0755,
    'assets' => 0755,
    'admin' => 0755,
    'cliente' => 0755,
    'parceiro' => 0755
];

foreach ($arquivos_permissoes as $arquivo => $permissao) {
    if (file_exists($arquivo)) {
        if (chmod($arquivo, $permissao)) {
            echo "<p>✅ Permissão do arquivo $arquivo ajustada para " . decoct($permissao) . "</p>";
        } else {
            echo "<p>❌ Erro ao ajustar permissão do arquivo $arquivo</p>";
        }
    }
}

foreach ($diretorios_permissoes as $diretorio => $permissao) {
    if (is_dir($diretorio)) {
        if (chmod($diretorio, $permissao)) {
            echo "<p>✅ Permissão do diretório $diretorio ajustada para " . decoct($permissao) . "</p>";
        } else {
            echo "<p>❌ Erro ao ajustar permissão do diretório $diretorio</p>";
        }
    }
}
$correcoes_aplicadas[] = "Permissões de arquivos e diretórios ajustadas";
echo "<hr>";

// 4. Verificar e corrigir configuração do banco de dados
echo "<h2>4. 🗄️ Verificação da Configuração do Banco</h2>";
if (file_exists('config/database.php')) {
    $db_content = file_get_contents('config/database.php');
    echo "<p>✅ Arquivo de configuração do banco encontrado</p>";
    
    // Verificar se as variáveis estão definidas corretamente
    if (strpos($db_content, '$host') !== false && 
        strpos($db_content, '$dbname') !== false && 
        strpos($db_content, '$username') !== false && 
        strpos($db_content, '$password') !== false) {
        echo "<p>✅ Variáveis de configuração do banco estão definidas</p>";
        
        // Testar conexão
        try {
            require_once 'config/database.php';
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            echo "<p>✅ Conexão com banco de dados bem-sucedida</p>";
            $correcoes_aplicadas[] = "Conexão com banco de dados verificada";
        } catch (Exception $e) {
            echo "<p>❌ Erro na conexão com banco: " . $e->getMessage() . "</p>";
            $erros_encontrados[] = "Erro de conexão com banco: " . $e->getMessage();
        }
    } else {
        echo "<p>❌ Configuração do banco incompleta</p>";
        $erros_encontrados[] = "Configuração do banco incompleta";
    }
} else {
    echo "<p>❌ Arquivo config/database.php não encontrado</p>";
    $erros_encontrados[] = "Arquivo de configuração do banco não encontrado";
}
echo "<hr>";

// 5. Verificar arquivos PHP críticos
echo "<h2>5. 📁 Verificação de Arquivos Críticos</h2>";
$arquivos_criticos = [
    'index.php',
    'includes/auth.php',
    'includes/functions.php',
    'config/database.php'
];

foreach ($arquivos_criticos as $arquivo) {
    if (file_exists($arquivo)) {
        // Verificar sintaxe PHP
        $output = [];
        $return_var = 0;
        exec("php -l $arquivo 2>&1", $output, $return_var);
        
        if ($return_var === 0) {
            echo "<p>✅ $arquivo - Sintaxe PHP válida</p>";
        } else {
            echo "<p>❌ $arquivo - Erro de sintaxe: " . implode(' ', $output) . "</p>";
            $erros_encontrados[] = "Erro de sintaxe em $arquivo";
        }
    } else {
        echo "<p>❌ $arquivo - Arquivo não encontrado</p>";
        $erros_encontrados[] = "Arquivo $arquivo não encontrado";
    }
}
echo "<hr>";

// 6. Criar arquivo de teste simples
echo "<h2>6. 🧪 Criando Arquivo de Teste</h2>";
$teste_content = '<?php
// Arquivo de teste para verificar funcionamento básico
echo "<h1>✅ PHP funcionando corretamente!</h1>";
echo "<p>Versão PHP: " . phpversion() . "</p>";
echo "<p>Data/Hora: " . date("Y-m-d H:i:s") . "</p>";
echo "<p>Servidor: " . $_SERVER["SERVER_SOFTWARE"] . "</p>";
?>';

if (file_put_contents('teste_php.php', $teste_content)) {
    echo "<p>✅ Arquivo de teste criado: <a href='teste_php.php' target='_blank'>teste_php.php</a></p>";
    $correcoes_aplicadas[] = "Arquivo de teste PHP criado";
} else {
    echo "<p>❌ Erro ao criar arquivo de teste</p>";
    $erros_encontrados[] = "Falha ao criar arquivo de teste";
}
echo "<hr>";

// 7. Verificar configurações de sessão
echo "<h2>7. 🔐 Verificação de Sessões</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<p>✅ Sessão iniciada com sucesso</p>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Save Path:</strong> " . session_save_path() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";
$correcoes_aplicadas[] = "Configurações de sessão verificadas";
echo "<hr>";

// 8. Resumo das correções
echo "<h2>8. 📊 Resumo das Correções</h2>";

if (!empty($correcoes_aplicadas)) {
    echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin-bottom: 15px;'>";
    echo "<h3>✅ Correções Aplicadas:</h3>";
    echo "<ul>";
    foreach ($correcoes_aplicadas as $correcao) {
        echo "<li>$correcao</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if (!empty($erros_encontrados)) {
    echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin-bottom: 15px;'>";
    echo "<h3>❌ Erros Encontrados:</h3>";
    echo "<ul>";
    foreach ($erros_encontrados as $erro) {
        echo "<li>$erro</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "<div style='background: #cce5ff; padding: 15px; border-left: 4px solid #007cba;'>";
echo "<h3>🔍 Próximos Passos:</h3>";
echo "<ol>";
echo "<li><strong>Teste o site:</strong> Acesse <a href='/' target='_blank'>página principal</a> e <a href='teste_php.php' target='_blank'>arquivo de teste</a></li>";
echo "<li><strong>Verifique logs:</strong> Monitore os logs de erro do servidor</li>";
echo "<li><strong>Upload para produção:</strong> Se funcionando localmente, faça upload dos arquivos corrigidos</li>";
echo "<li><strong>Configuração Hostinger:</strong> Verifique as configurações PHP no painel da Hostinger</li>";
echo "<li><strong>Suporte:</strong> Se o erro persistir, contate o suporte da Hostinger</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><em>Correções aplicadas em " . date('Y-m-d H:i:s') . "</em></p>";

// 9. Instruções específicas para Hostinger
echo "<h2>9. 🌐 Instruções Específicas para Hostinger</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;'>";
echo "<h3>📋 Checklist para Hostinger:</h3>";
echo "<ul>";
echo "<li>✅ Verificar versão PHP no hPanel (recomendado: PHP 8.0 ou superior)</li>";
echo "<li>✅ Habilitar 'display_errors' temporariamente para debug</li>";
echo "<li>✅ Verificar limites de recursos (memory_limit, max_execution_time)</li>";
echo "<li>✅ Confirmar que todos os arquivos foram enviados via FTP/File Manager</li>";
echo "<li>✅ Verificar se o domínio está apontando para o diretório correto</li>";
echo "<li>✅ Testar com .htaccess renomeado temporariamente</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><strong>🎯 Script de correção concluído!</strong></p>";
?>