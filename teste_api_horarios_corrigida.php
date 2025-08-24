<?php
// Simular parâmetros GET
$_GET['salao_id'] = 10;
$_GET['profissional_id'] = 20;
$_GET['data'] = '2025-08-24';

echo "<h2>Teste da API de Horários Corrigida</h2>";
echo "<p>Parâmetros: salao_id=10, profissional_id=20, data=2025-08-24</p>";
echo "<hr>";

// Incluir a API
include 'api/horarios.php';
?>