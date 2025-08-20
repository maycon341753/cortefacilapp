<?php
/**
 * Funções auxiliares do sistema
 * Utilitários gerais para o sistema
 */

/**
 * Formata data para exibição
 * @param string $data
 * @param string $formato
 * @return string
 */
function formatarData($data, $formato = 'd/m/Y') {
    if (empty($data)) return '';
    
    $timestamp = strtotime($data);
    return date($formato, $timestamp);
}

/**
 * Formata data e hora para exibição
 * @param string $datetime
 * @return string
 */
function formatarDataHora($datetime) {
    if (empty($datetime)) return '';
    
    $timestamp = strtotime($datetime);
    return date('d/m/Y H:i', $timestamp);
}

/**
 * Formata hora para exibição
 * @param string $hora
 * @return string
 */
function formatarHora($hora) {
    if (empty($hora)) return '';
    
    return date('H:i', strtotime($hora));
}

/**
 * Converte data do formato brasileiro para MySQL
 * @param string $data_br
 * @return string
 */
function dataBrParaMysql($data_br) {
    if (empty($data_br)) return '';
    
    $partes = explode('/', $data_br);
    if (count($partes) == 3) {
        return $partes[2] . '-' . $partes[1] . '-' . $partes[0];
    }
    
    return $data_br;
}

/**
 * Gera opções de select HTML
 * @param array $opcoes
 * @param mixed $selecionado
 * @param string $value_key
 * @param string $text_key
 * @return string
 */
function gerarOpcoes($opcoes, $selecionado = null, $value_key = 'id', $text_key = 'nome') {
    $html = '';
    
    foreach ($opcoes as $opcao) {
        $value = is_array($opcao) ? $opcao[$value_key] : $opcao;
        $text = is_array($opcao) ? $opcao[$text_key] : $opcao;
        $selected = ($value == $selecionado) ? 'selected' : '';
        
        $html .= "<option value='{$value}' {$selected}>{$text}</option>";
    }
    
    return $html;
}

/**
 * Gera badge de status
 * @param string $status
 * @return string
 */
function gerarBadgeStatus($status) {
    $badges = [
        'pendente' => '<span class="badge badge-warning">Pendente</span>',
        'confirmado' => '<span class="badge badge-success">Confirmado</span>',
        'cancelado' => '<span class="badge badge-danger">Cancelado</span>',
        'concluido' => '<span class="badge badge-info">Concluído</span>'
    ];
    
    return isset($badges[$status]) ? $badges[$status] : '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
}

/**
 * Calcula idade
 * @param string $data_nascimento
 * @return int
 */
function calcularIdade($data_nascimento) {
    if (empty($data_nascimento)) return 0;
    
    $nascimento = new DateTime($data_nascimento);
    $hoje = new DateTime();
    
    return $hoje->diff($nascimento)->y;
}

/**
 * Gera senha aleatória
 * @param int $tamanho
 * @return string
 */
function gerarSenhaAleatoria($tamanho = 8) {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $senha = '';
    
    for ($i = 0; $i < $tamanho; $i++) {
        $senha .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    
    return $senha;
}

/**
 * Valida data
 * @param string $data
 * @param string $formato
 * @return bool
 */
function validarData($data, $formato = 'Y-m-d') {
    $d = DateTime::createFromFormat($formato, $data);
    return $d && $d->format($formato) === $data;
}

/**
 * Valida hora
 * @param string $hora
 * @return bool
 */
function validarHora($hora) {
    return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora);
}

/**
 * Limita texto
 * @param string $texto
 * @param int $limite
 * @param string $sufixo
 * @return string
 */
function limitarTexto($texto, $limite = 100, $sufixo = '...') {
    if (strlen($texto) <= $limite) {
        return $texto;
    }
    
    return substr($texto, 0, $limite) . $sufixo;
}

/**
 * Formata valor monetário
 * @param float $valor
 * @return string
 */
function formatarMoeda($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/**
 * Remove acentos
 * @param string $texto
 * @return string
 */
function removerAcentos($texto) {
    $acentos = [
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
        'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
        'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
        'Ç' => 'C', 'ç' => 'c',
        'Ñ' => 'N', 'ñ' => 'n'
    ];
    
    return strtr($texto, $acentos);
}

/**
 * Gera slug amigável
 * @param string $texto
 * @return string
 */
function gerarSlug($texto) {
    $texto = removerAcentos($texto);
    $texto = strtolower($texto);
    $texto = preg_replace('/[^a-z0-9\s]/', '', $texto);
    $texto = preg_replace('/\s+/', '-', $texto);
    $texto = trim($texto, '-');
    
    return $texto;
}

/**
 * Verifica se é data futura
 * @param string $data
 * @return bool
 */
function isDataFutura($data) {
    return strtotime($data) > time();
}

/**
 * Verifica se é data passada
 * @param string $data
 * @return bool
 */
function isDataPassada($data) {
    return strtotime($data) < strtotime(date('Y-m-d'));
}

/**
 * Verifica se é hoje
 * @param string $data
 * @return bool
 */
function isHoje($data) {
    return date('Y-m-d', strtotime($data)) === date('Y-m-d');
}

/**
 * Obtém dias da semana
 * @return array
 */
function getDiasSemana() {
    return [
        0 => 'Domingo',
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado'
    ];
}

/**
 * Obtém meses do ano
 * @return array
 */
function getMeses() {
    return [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
    ];
}

/**
 * Log de atividades
 * @param string $mensagem
 * @param string $nivel
 */
function logAtividade($mensagem, $nivel = 'INFO') {
    $data = date('Y-m-d H:i:s');
    $usuario = isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : 'Sistema';
    
    $log = "[{$data}] [{$nivel}] [{$usuario}] {$mensagem}" . PHP_EOL;
    
    file_put_contents('../logs/atividades.log', $log, FILE_APPEND | LOCK_EX);
}

/**
 * Valida CPF
 * @param string $cpf
 * @return bool
 */
function validarCPF($cpf) {
    // Remove formatação
    $cpf = preg_replace('/\D/', '', $cpf);
    
    // Verifica se tem 11 dígitos
    if (strlen($cpf) !== 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
    
    // Calcula primeiro dígito verificador
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += intval($cpf[$i]) * (10 - $i);
    }
    $resto = $soma % 11;
    $dv1 = $resto < 2 ? 0 : 11 - $resto;
    
    // Calcula segundo dígito verificador
    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += intval($cpf[$i]) * (11 - $i);
    }
    $resto = $soma % 11;
    $dv2 = $resto < 2 ? 0 : 11 - $resto;
    
    // Verifica se os dígitos calculados conferem
    return ($dv1 == intval($cpf[9]) && $dv2 == intval($cpf[10]));
}

/**
 * Valida CNPJ
 * @param string $cnpj
 * @return bool
 */
function validarCNPJ($cnpj) {
    // Remove formatação
    $cnpj = preg_replace('/\D/', '', $cnpj);
    
    // Verifica se tem 14 dígitos
    if (strlen($cnpj) !== 14) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
        return false;
    }
    
    // Calcula primeiro dígito verificador
    $soma = 0;
    $peso = 5;
    for ($i = 0; $i < 12; $i++) {
        $soma += intval($cnpj[$i]) * $peso;
        $peso = $peso == 2 ? 9 : $peso - 1;
    }
    $resto = $soma % 11;
    $dv1 = $resto < 2 ? 0 : 11 - $resto;
    
    // Calcula segundo dígito verificador
    $soma = 0;
    $peso = 6;
    for ($i = 0; $i < 13; $i++) {
        $soma += intval($cnpj[$i]) * $peso;
        $peso = $peso == 2 ? 9 : $peso - 1;
    }
    $resto = $soma % 11;
    $dv2 = $resto < 2 ? 0 : 11 - $resto;
    
    // Verifica se os dígitos calculados conferem
    return ($dv1 == intval($cnpj[12]) && $dv2 == intval($cnpj[13]));
}

/**
 * Formata CPF
 * @param string $cpf
 * @return string
 */
function formatarCPF($cpf) {
    $cpf = preg_replace('/\D/', '', $cpf);
    if (strlen($cpf) === 11) {
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }
    return $cpf;
}

/**
 * Formata CNPJ
 * @param string $cnpj
 * @return string
 */
function formatarCNPJ($cnpj) {
    $cnpj = preg_replace('/\D/', '', $cnpj);
    if (strlen($cnpj) === 14) {
        return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
    }
    return $cnpj;
}
?>