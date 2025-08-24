<?php
// Teste da API de horários no ambiente online
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste API Online - CorteFácil</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Teste da API de Horários - Ambiente Online</h1>
    
    <div class="test-section info">
        <h3>Informações do Ambiente</h3>
        <p><strong>URL Base:</strong> <?php echo $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']); ?></p>
        <p><strong>Servidor:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></p>
        <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
    </div>

    <div class="test-section">
        <h3>Teste 1: Verificar se a API responde</h3>
        <button onclick="testarAPI()">Testar API de Horários</button>
        <div id="resultado-api"></div>
    </div>

    <div class="test-section">
        <h3>Teste 2: Verificar dados no banco</h3>
        <button onclick="verificarDados()">Verificar Dados</button>
        <div id="resultado-dados"></div>
    </div>

    <div class="test-section">
        <h3>Teste 3: Simular chamada do frontend</h3>
        <button onclick="simularFrontend()">Simular Frontend</button>
        <div id="resultado-frontend"></div>
    </div>

    <script>
        function testarAPI() {
            const resultado = document.getElementById('resultado-api');
            resultado.innerHTML = '<p>Testando API...</p>';
            
            // Testar com dados fixos
            const url = '../api/horarios.php?profissional=1&data=2024-01-20';
            
            fetch(url)
                .then(response => {
                    console.log('Status da resposta:', response.status);
                    console.log('Headers:', response.headers);
                    return response.text();
                })
                .then(data => {
                    console.log('Resposta bruta:', data);
                    resultado.innerHTML = `
                        <div class="success">
                            <h4>Resposta da API:</h4>
                            <pre>${data}</pre>
                        </div>
                    `;
                    
                    // Tentar fazer parse JSON
                    try {
                        const json = JSON.parse(data);
                        console.log('JSON parseado:', json);
                    } catch (e) {
                        console.error('Erro ao fazer parse JSON:', e);
                        resultado.innerHTML += `
                            <div class="error">
                                <p><strong>Erro:</strong> Resposta não é um JSON válido</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Erro na requisição:', error);
                    resultado.innerHTML = `
                        <div class="error">
                            <h4>Erro na requisição:</h4>
                            <pre>${error.message}</pre>
                        </div>
                    `;
                });
        }
        
        function verificarDados() {
            const resultado = document.getElementById('resultado-dados');
            resultado.innerHTML = '<p>Verificando dados...</p>';
            
            fetch('verificar_dados_basicos.php')
                .then(response => response.text())
                .then(data => {
                    resultado.innerHTML = `
                        <div class="info">
                            <h4>Dados do Banco:</h4>
                            <pre>${data}</pre>
                        </div>
                    `;
                })
                .catch(error => {
                    resultado.innerHTML = `
                        <div class="error">
                            <h4>Erro ao verificar dados:</h4>
                            <pre>${error.message}</pre>
                        </div>
                    `;
                });
        }
        
        function simularFrontend() {
            const resultado = document.getElementById('resultado-frontend');
            resultado.innerHTML = '<p>Simulando chamada do frontend...</p>';
            
            // Simular exatamente como o frontend faz
            const CorteFacilTest = {
                ajax: {
                    get: function(url, callback) {
                        fetch(url)
                            .then(response => response.json())
                            .then(data => {
                                if (typeof callback === 'function') {
                                    callback(null, data);
                                }
                            })
                            .catch(error => {
                                if (typeof callback === 'function') {
                                    callback(error, null);
                                }
                            });
                    }
                }
            };
            
            CorteFacilTest.ajax.get('../api/horarios.php?profissional=1&data=2024-01-20', function(error, horarios) {
                if (error) {
                    console.error('Erro ao carregar horários:', error);
                    resultado.innerHTML = `
                        <div class="error">
                            <h4>Erro na simulação do frontend:</h4>
                            <pre>${error.message}</pre>
                        </div>
                    `;
                    return;
                }
                
                console.log('Horários recebidos:', horarios);
                resultado.innerHTML = `
                    <div class="success">
                        <h4>Simulação do Frontend - Sucesso:</h4>
                        <pre>${JSON.stringify(horarios, null, 2)}</pre>
                        <p><strong>Quantidade de horários:</strong> ${horarios ? horarios.length : 0}</p>
                    </div>
                `;
            });
        }
        
        // Executar teste automático ao carregar
        window.onload = function() {
            console.log('Página carregada, executando teste automático...');
            setTimeout(testarAPI, 1000);
        };
    </script>
</body>
</html>