<?php
// Teste para verificar se o cadastro de parceiros com horários está funcionando
require_once 'config/database.php';
require_once 'models/usuario.php';
require_once 'includes/functions.php';

echo "<h2>Teste de Cadastro de Parceiro com Horários</h2>";

// Dados de teste
$dadosUsuario = [
    'nome' => 'Salão Teste',
    'email' => 'teste@salao.com',
    'telefone' => '11999999999',
    'senha' => '123456',
    'tipo_usuario' => 'parceiro'
];

$horarios = [
    ['dia_semana' => 1, 'hora_abertura' => '08:00', 'hora_fechamento' => '18:00'], // Segunda
    ['dia_semana' => 2, 'hora_abertura' => '08:00', 'hora_fechamento' => '18:00'], // Terça
    ['dia_semana' => 3, 'hora_abertura' => '08:00', 'hora_fechamento' => '18:00'], // Quarta
    ['dia_semana' => 4, 'hora_abertura' => '08:00', 'hora_fechamento' => '18:00'], // Quinta
    ['dia_semana' => 5, 'hora_abertura' => '08:00', 'hora_fechamento' => '18:00'], // Sexta
    ['dia_semana' => 6, 'hora_abertura' => '08:00', 'hora_fechamento' => '16:00'], // Sábado
];

$dadosSalao = [
    'nome' => 'Salão Teste',
    'endereco' => 'Rua Teste, 123',
    'bairro' => 'Centro',
    'cidade' => 'São Paulo',
    'cep' => '01000-000',
    'telefone' => '11999999999',
    'documento' => '12345678901234',
    'tipo_documento' => 'cnpj',
    'razao_social' => 'Salão Teste LTDA',
    'inscricao_estadual' => '123456789',
    'descricao' => 'Salão de teste',
    'horarios' => $horarios
];

try {
    $usuario = new Usuario();
    
    // Verificar se email já existe
    if ($usuario->emailExiste($dadosUsuario['email'])) {
        echo "<p style='color: orange;'>Email já existe, pulando teste...</p>";
    } else {
        echo "<p>Tentando cadastrar parceiro com horários...</p>";
        
        if ($usuario->cadastrarParceiro($dadosUsuario, $dadosSalao)) {
            echo "<p style='color: green;'>✅ Parceiro cadastrado com sucesso!</p>";
            
            // Verificar se os horários foram salvos
            $db = new Database();
            $conn = $db->connect();
            
            // Buscar o salão recém-criado
            $stmt = $conn->prepare("SELECT id FROM saloes WHERE documento = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute(['12345678901234']);
            $salao = $stmt->fetch();
            
            if ($salao) {
                $salao_id = $salao['id'];
                echo "<p>Salão ID: {$salao_id}</p>";
                
                // Verificar horários
                $stmt = $conn->prepare("SELECT * FROM horarios_funcionamento WHERE id_salao = ? ORDER BY dia_semana");
                $stmt->execute([$salao_id]);
                $horarios_salvos = $stmt->fetchAll();
                
                if ($horarios_salvos) {
                    echo "<p style='color: green;'>✅ Horários salvos com sucesso!</p>";
                    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
                    echo "<tr><th>Dia</th><th>Abertura</th><th>Fechamento</th></tr>";
                    
                    $dias = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                    
                    foreach ($horarios_salvos as $horario) {
                        $dia_nome = $dias[$horario['dia_semana']];
                        echo "<tr>";
                        echo "<td>{$dia_nome}</td>";
                        echo "<td>{$horario['hora_abertura']}</td>";
                        echo "<td>{$horario['hora_fechamento']}</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p style='color: red;'>❌ Nenhum horário foi salvo!</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ Salão não encontrado!</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Erro ao cadastrar parceiro!</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}

echo "<br><a href='cadastro.php?tipo=parceiro'>← Voltar para o cadastro</a>";
?>