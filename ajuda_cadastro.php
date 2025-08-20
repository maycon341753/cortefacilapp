<?php
/**
 * Página de ajuda para cadastro de parceiros
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajuda - Cadastro de Parceiros | CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3><i class="fas fa-question-circle me-2"></i>Ajuda - Cadastro de Parceiros</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle me-2"></i>Como cadastrar um parceiro</h5>
                            <p>Para se cadastrar como parceiro no CorteFácil, você precisa fornecer informações válidas. Aqui estão algumas dicas importantes:</p>
                        </div>

                        <h5><i class="fas fa-id-card me-2"></i>Validação de CPF/CNPJ</h5>
                        <div class="alert alert-warning">
                            <p><strong>Importante:</strong> O sistema valida se o CPF ou CNPJ informado é matematicamente válido. Certifique-se de digitar um documento real e válido.</p>
                        </div>

                        <h6>Exemplos de CPFs válidos para teste:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-group list-group-flush">
                                    <?php
                                    // Gerar CPFs válidos para exemplo
                                    function gerarCPFValido() {
                                        $cpf = '';
                                        for ($i = 0; $i < 9; $i++) {
                                            $cpf .= rand(0, 9);
                                        }
                                        
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
                                    
                                    for ($i = 0; $i < 5; $i++) {
                                        echo '<li class="list-group-item"><code>' . gerarCPFValido() . '</code></li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-light">
                                    <small><i class="fas fa-lightbulb me-1"></i><strong>Dica:</strong> Você pode copiar qualquer um desses CPFs para testar o cadastro. Eles são matematicamente válidos.</small>
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-4"><i class="fas fa-exclamation-triangle me-2"></i>Erros Comuns</h5>
                        <div class="accordion" id="accordionErros">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                        "CPF inválido"
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionErros">
                                    <div class="accordion-body">
                                        <strong>Causa:</strong> O CPF digitado não passa na validação matemática.<br>
                                        <strong>Solução:</strong> Verifique se digitou corretamente ou use um dos CPFs de exemplo acima.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                        "Este documento já está cadastrado"
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionErros">
                                    <div class="accordion-body">
                                        <strong>Causa:</strong> Já existe um parceiro cadastrado com este CPF/CNPJ.<br>
                                        <strong>Solução:</strong> Use um documento diferente ou faça login se já possui conta.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                        "Este email já está cadastrado"
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionErros">
                                    <div class="accordion-body">
                                        <strong>Causa:</strong> Já existe uma conta com este email.<br>
                                        <strong>Solução:</strong> Use um email diferente ou faça login se já possui conta.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-success mt-4">
                            <h6><i class="fas fa-check-circle me-2"></i>Sistema Funcionando</h6>
                            <p class="mb-0">O sistema de cadastro está funcionando normalmente. Se você seguir as orientações acima, o cadastro será realizado com sucesso!</p>
                        </div>

                        <div class="text-center mt-4">
                            <a href="cadastro.php?tipo=parceiro" class="btn btn-primary me-2">
                                <i class="fas fa-user-plus me-2"></i>Tentar Cadastro Novamente
                            </a>
                            <a href="login.php" class="btn btn-outline-secondary">
                                <i class="fas fa-sign-in-alt me-2"></i>Já tenho conta
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>