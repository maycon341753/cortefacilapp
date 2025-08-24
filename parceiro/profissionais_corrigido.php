<?php
/**
 * Página de Gerenciamento de Profissionais - VERSÃO CORRIGIDA
 * Permite ao parceiro cadastrar, editar e gerenciar profissionais do seu salão
 */

require_once "../includes/auth.php";
require_once "../includes/functions.php";
require_once "../models/salao.php";
require_once "../models/profissional.php";

// Verificar se é parceiro
requireParceiro();

$usuario = getLoggedUser();
$salao = new Salao();
$profissional = new Profissional();

$erro = "";
$sucesso = "";

// Verificar se tem salão cadastrado
$meu_salao = $salao->buscarPorDono($usuario["id"]);
if (!$meu_salao) {
    header("Location: salao.php");
    exit;
}

// Processar ações
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Validar CSRF
        if (!verifyCsrfToken($_POST["csrf_token"] ?? "")) {
            throw new Exception("Token de segurança inválido.");
        }
        
        $acao = $_POST["acao"] ?? "";
        
        if ($acao === "cadastrar" || $acao === "editar") {
            // Validar dados
            $nome = trim($_POST["nome"] ?? "");
            $especialidade = trim($_POST["especialidade"] ?? "");
            $telefone = trim($_POST["telefone"] ?? "");
            $email = trim($_POST["email"] ?? "");
            $ativo = isset($_POST["ativo"]);
            
            if (empty($nome)) {
                throw new Exception("Nome do profissional é obrigatório.");
            }
            
            if (strlen($nome) < 3) {
                throw new Exception("Nome deve ter pelo menos 3 caracteres.");
            }
            
            if (empty($especialidade)) {
                throw new Exception("Especialidade é obrigatória.");
            }
            
            // Preparar dados
            $dados = [
                "nome" => $nome,
                "especialidade" => $especialidade,
                "telefone" => $telefone,
                "email" => $email,
                "ativo" => $ativo ? 1 : 0
            ];
            
            if ($acao === "cadastrar") {
                // Cadastrar novo profissional
                $dados["id_salao"] = $meu_salao["id"];
                $resultado = $profissional->cadastrar($dados);
                $mensagem = "Profissional cadastrado com sucesso!";
                $log_acao = "profissional_cadastrado";
            } else {
                // Editar profissional existente
                $id_profissional = (int)($_POST["id_profissional"] ?? 0);
                if (!$id_profissional) {
                    throw new Exception("ID do profissional inválido.");
                }
                
                // Verificar se o profissional pertence ao salão do usuário
                $prof_existente = $profissional->buscarPorId($id_profissional);
                if (!$prof_existente || $prof_existente["id_salao"] != $meu_salao["id"]) {
                    throw new Exception("Profissional não encontrado.");
                }
                
                $resultado = $profissional->atualizar($id_profissional, $dados);
                $mensagem = "Profissional atualizado com sucesso!";
                $log_acao = "profissional_atualizado";
            }
            
            if ($resultado) {
                $sucesso = $mensagem;
                logActivity($usuario["id"], $log_acao, "Profissional: {$nome}");
            } else {
                throw new Exception("Erro ao salvar dados do profissional.");
            }
            
        } elseif ($acao === "excluir") {
            $id_profissional = (int)($_POST["id_profissional"] ?? 0);
            if (!$id_profissional) {
                throw new Exception("ID do profissional inválido.");
            }
            
            // Verificar se o profissional pertence ao salão do usuário
            $prof_existente = $profissional->buscarPorId($id_profissional);
            if (!$prof_existente || $prof_existente["id_salao"] != $meu_salao["id"]) {
                throw new Exception("Profissional não encontrado.");
            }
            
            $resultado = $profissional->excluir($id_profissional);
            if ($resultado) {
                $sucesso = "Profissional excluído com sucesso!";
                logActivity($usuario["id"], "profissional_excluido", "Profissional: {$prof_existente["nome"]}");
            } else {
                throw new Exception("Erro ao excluir profissional.");
            }
        }
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Buscar profissionais do salão (em tempo real)
$profissionais = $profissional->listarPorSalao($meu_salao["id"]);
?>