<?php
/**
 * Debug para investigar múltiplas conexões e instâncias
 */

require_once 'config/database.php';
require_once 'models/agendamento.php';

echo "<h2>🔍 Debug Múltiplas Conexões</h2>";

// 1. Verificar singleton do Database
echo "<h3>1. Testando Singleton Database</h3>";
$db1 = Database::getInstance();
$db2 = Database::getInstance();

echo "<p class='info'>Instância 1 ID: " . spl_object_id($db1) . "</p>";
echo "<p class='info'>Instância 2 ID: " . spl_object_id($db2) . "</p>";
echo "<p class='" . ($db1 === $db2 ? 'success' : 'error') . "'>" . ($db1 === $db2 ? '✅' : '❌') . " Singleton funcionando: " . ($db1 === $db2 ? 'SIM' : 'NÃO') . "</p>";

// 2. Verificar conexões PDO
echo "<h3>2. Testando Conexões PDO</h3>";
$conn1 = $db1->connect();
$conn2 = $db2->connect();

echo "<p class='info'>Conexão 1 ID: " . spl_object_id($conn1) . "</p>";
echo "<p class='info'>Conexão 2 ID: " . spl_object_id($conn2) . "</p>";
echo "<p class='" . ($conn1 === $conn2 ? 'success' : 'error') . "'>" . ($conn1 === $conn2 ? '✅' : '❌') . " Mesma conexão: " . ($conn1 === $conn2 ? 'SIM' : 'NÃO') . "</p>";

// 3. Verificar banco conectado em cada conexão
echo "<h3>3. Verificando Bancos Conectados</h3>";
$stmt1 = $conn1->query("SELECT DATABASE() as db_name, CONNECTION_ID() as conn_id");
$result1 = $stmt1->fetch();

$stmt2 = $conn2->query("SELECT DATABASE() as db_name, CONNECTION_ID() as conn_id");
$result2 = $stmt2->fetch();

echo "<p class='info'>Conexão 1 - Banco: {$result1['db_name']}, ID: {$result1['conn_id']}</p>";
echo "<p class='info'>Conexão 2 - Banco: {$result2['db_name']}, ID: {$result2['conn_id']}</p>";

// 4. Testar múltiplas instâncias de Agendamento
echo "<h3>4. Testando Múltiplas Instâncias Agendamento</h3>";
$agendamento1 = new Agendamento();
$agendamento2 = new Agendamento();

echo "<p class='info'>Agendamento 1 ID: " . spl_object_id($agendamento1) . "</p>";
echo "<p class='info'>Agendamento 2 ID: " . spl_object_id($agendamento2) . "</p>";

// Usar reflexão para acessar propriedades privadas
$reflection1 = new ReflectionClass($agendamento1);
$reflection2 = new ReflectionClass($agendamento2);

// Verificar propriedade db/conn
if ($reflection1->hasProperty('db')) {
    $dbProp1 = $reflection1->getProperty('db');
    $dbProp1->setAccessible(true);
    $dbValue1 = $dbProp1->getValue($agendamento1);
    echo "<p class='info'>Agendamento 1 - DB ID: " . spl_object_id($dbValue1) . "</p>";
}

if ($reflection1->hasProperty('conn')) {
    $connProp1 = $reflection1->getProperty('conn');
    $connProp1->setAccessible(true);
    $connValue1 = $connProp1->getValue($agendamento1);
    echo "<p class='info'>Agendamento 1 - Conn ID: " . spl_object_id($connValue1) . "</p>";
}

if ($reflection2->hasProperty('db')) {
    $dbProp2 = $reflection2->getProperty('db');
    $dbProp2->setAccessible(true);
    $dbValue2 = $dbProp2->getValue($agendamento2);
    echo "<p class='info'>Agendamento 2 - DB ID: " . spl_object_id($dbValue2) . "</p>";
}

if ($reflection2->hasProperty('conn')) {
    $connProp2 = $reflection2->getProperty('conn');
    $connProp2->setAccessible(true);
    $connValue2 = $connProp2->getValue($agendamento2);
    echo "<p class='info'>Agendamento 2 - Conn ID: " . spl_object_id($connValue2) . "</p>";
}

// 5. Testar se há diferenças na estrutura da tabela
echo "<h3>5. Verificando Estrutura da Tabela em Diferentes Conexões</h3>";

// Conexão via Database::getInstance()
$stmt_desc1 = $conn1->query("DESCRIBE agendamentos");
$columns1 = $stmt_desc1->fetchAll();
$statusCol1 = null;
foreach ($columns1 as $col) {
    if ($col['Field'] === 'status') {
        $statusCol1 = $col;
        break;
    }
}

echo "<p class='" . ($statusCol1 ? 'success' : 'error') . "'>Conexão 1 - Status: " . ($statusCol1 ? $statusCol1['Type'] : 'NÃO ENCONTRADA') . "</p>";

// 6. Testar query direta com diferentes conexões
echo "<h3>6. Testando Query com Status em Diferentes Conexões</h3>";

try {
    $testSql = "SELECT COUNT(*) as total FROM agendamentos WHERE status != 'cancelado'";
    $stmt_test1 = $conn1->prepare($testSql);
    $stmt_test1->execute();
    $result_test1 = $stmt_test1->fetch();
    echo "<p class='success'>✅ Conexão 1 - Query com status funcionou: {$result_test1['total']} registros</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Conexão 1 - Erro na query com status: {$e->getMessage()}</p>";
}

// 7. Verificar se há cache ou pool de conexões
echo "<h3>7. Verificando Cache de Conexões</h3>";

// Forçar nova conexão
$newDb = new Database();
$newConn = $newDb->connect();

echo "<p class='info'>Nova conexão ID: " . spl_object_id($newConn) . "</p>";
echo "<p class='" . ($newConn === $conn1 ? 'error' : 'success') . "'>" . ($newConn === $conn1 ? '❌' : '✅') . " Nova conexão é diferente: " . ($newConn === $conn1 ? 'NÃO' : 'SIM') . "</p>";

$stmt_new = $newConn->query("SELECT DATABASE() as db_name");
$result_new = $stmt_new->fetch();
echo "<p class='info'>Nova conexão - Banco: {$result_new['db_name']}</p>";

echo "<hr>";
echo "<p><strong>Debug de múltiplas conexões concluído!</strong></p>";

?>

<style>
.success { color: green; }
.error { color: red; }
.info { color: blue; }
</style>