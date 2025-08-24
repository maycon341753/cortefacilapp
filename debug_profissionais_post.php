<?php
// Debug para verificar dados POST/GET sendo enviados para profissionais.php

echo "<h2>Debug - Dados recebidos</h2>";

echo "<h3>Método da requisição:</h3>";
echo "<p>" . $_SERVER['REQUEST_METHOD'] . "</p>";

echo "<h3>Dados POST:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h3>Dados GET:</h3>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

echo "<h3>Headers:</h3>";
echo "<pre>";
print_r(getallheaders());
echo "</pre>";

echo "<h3>URL completa:</h3>";
echo "<p>" . $_SERVER['REQUEST_URI'] . "</p>";

echo "<h3>Referer:</h3>";
echo "<p>" . ($_SERVER['HTTP_REFERER'] ?? 'Nenhum') . "</p>";

echo "<hr>";
echo "<p><a href='parceiro/profissionais.php'>Ir para página de profissionais</a></p>";
?>