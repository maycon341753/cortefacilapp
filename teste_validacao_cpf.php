<?php
/**
 * Teste das funções de validação de CPF
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/functions.php';

echo "<h2>Teste de Validação de CPF</h2>";

// Lista de CPFs para testar
$cpfs_teste = [
    '111.444.777-35', // Válido
    '123.456.789-01', // Inválido
    '000.000.000-00', // Inválido (todos zeros)
    '111.111.111-11', // Inválido (todos iguais)
    '12345678901',    // Válido sem formatação
    '123.456.789-10', // Inválido
    '11144477735',    // Válido sem formatação
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>CPF</th><th>Resultado</th><th>Status</th></tr>";

foreach ($cpfs_teste as $cpf) {
    $resultado = validarCPF($cpf);
    $status = $resultado ? '✅ Válido' : '❌ Inválido';
    $cor = $resultado ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td>$cpf</td>";
    echo "<td>" . ($resultado ? 'true' : 'false') . "</td>";
    echo "<td style='color: $cor;'>$status</td>";
    echo "</tr>";
}

echo "</table>";

// Gerar alguns CPFs válidos para teste
echo "<h3>CPFs Válidos Gerados para Teste:</h3>";

function gerarCPFValidoTeste() {
    $cpf = '';
    for ($i = 0; $i < 9; $i++) {
        $cpf .= rand(0, 9);
    }
    
    // Calcular dígitos verificadores
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += intval($cpf[$i]) * (10 - $i);
    }
    $resto = $soma % 11;
    $dv1 = ($resto < 2) ? 0 : 11 - $resto;
    
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += intval($cpf[$i]) * (11 - $i);
    }
    $soma += $dv1 * 2;
    $resto = $soma % 11;
    $dv2 = ($resto < 2) ? 0 : 11 - $resto;
    
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . $dv1 . $dv2;
}

echo "<ul>";
for ($i = 0; $i < 5; $i++) {
    $cpf_gerado = gerarCPFValidoTeste();
    $validacao = validarCPF($cpf_gerado) ? '✅' : '❌';
    echo "<li>$cpf_gerado $validacao</li>";
}
echo "</ul>";

echo "<hr><p><a href='cadastro.php?tipo=parceiro'>← Voltar para cadastro</a></p>";
?>