<?php
// Excluir arquivo temporário de teste
$temp_file = __DIR__ . '/parceiro/teste_sessao_temp.php';
if (file_exists($temp_file)) {
    unlink($temp_file);
    echo 'Arquivo temporário excluído';
} else {
    echo 'Arquivo temporário não encontrado';
}
?>
