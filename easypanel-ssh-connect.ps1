# Script para conectar via SSH e corrigir backend no EasyPanel
# Uso: .\easypanel-ssh-connect.ps1 -Host [servidor] -User [usuario] [-AutoFix]

param(
    [string]$HostName,
    [string]$User,
    [string]$Password,
    [switch]$TestConnection,
    [switch]$AutoFix,
    [switch]$Help
)

function Show-Help {
    Write-Host "=== EASYPANEL SSH CONNECTOR ===" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Uso:" -ForegroundColor Yellow
    Write-Host "  .\easypanel-ssh-connect.ps1 -HostName [host] -User [usuario]" -ForegroundColor Yellow
    Write-Host "  .\easypanel-ssh-connect.ps1 -TestConnection -HostName [host] -User [usuario]" -ForegroundColor Yellow
    Write-Host "  .\easypanel-ssh-connect.ps1 -AutoFix -HostName [host] -User [usuario]" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Parametros:" -ForegroundColor Green
    Write-Host "  -HostName  : Endereco do servidor EasyPanel"
    Write-Host "  -User      : Usuario SSH"
    Write-Host "  -Password  : Senha SSH (opcional)"
    Write-Host "  -TestConnection : Apenas testa a conexao SSH"
    Write-Host "  -AutoFix   : Executa correcoes automaticas"
    Write-Host "  -Help      : Mostra esta ajuda"
    Write-Host ""
    Write-Host "Exemplos:" -ForegroundColor Green
    Write-Host "  .\easypanel-ssh-connect.ps1 -HostName cortefacil.easypanel.host -User root -AutoFix"
    Write-Host "  .\easypanel-ssh-connect.ps1 -TestConnection -HostName servidor.com -User admin"
    Write-Host ""
}

function Test-SSHConnection {
    param([string]$HostName, [string]$UserName)
    
    Write-Host "Testando conexao SSH..." -ForegroundColor Yellow
    
    try {
        $result = ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no "$UserName@$HostName" "echo 'OK'"
        
        if ($result -eq "OK") {
            Write-Host "Conexao SSH estabelecida com sucesso!" -ForegroundColor Green
            return $true
        } else {
            Write-Host "Falha na conexao SSH" -ForegroundColor Red
            return $false
        }
    } catch {
        Write-Host "Erro ao conectar via SSH: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

function Invoke-AutoFix {
    param([string]$HostName, [string]$UserName)
    
    Write-Host "Executando correcoes automaticas..." -ForegroundColor Yellow
    Write-Host ""
    
    # Criar script de correção temporário
    $fixScript = @'
#!/bin/bash
echo "Verificando containers..."
docker ps -a
echo ""
echo "Procurando container backend..."
BACKEND_ID=$(docker ps -q -f "name=backend" | head -1)
if [ -z "$BACKEND_ID" ]; then
    echo "Container backend nao encontrado"
else
    echo "Backend encontrado: $BACKEND_ID"
fi
echo ""
echo "Iniciando/reiniciando backend..."
if [ ! -z "$BACKEND_ID" ]; then
    docker restart $BACKEND_ID
else
    echo "Criando novo container..."
fi
sleep 5
echo ""
echo "Testando conectividade..."
curl -s --max-time 5 http://localhost:3001/health && echo "Backend OK" || echo "Backend com problema"
curl -s --max-time 5 http://localhost/api/health && echo "Proxy OK" || echo "Proxy com problema"
echo ""
echo "Status final:"
docker ps | grep backend
echo ""
echo "Logs do backend (ultimas 10 linhas):"
if [ ! -z "$BACKEND_ID" ]; then
    docker logs --tail 10 $BACKEND_ID 2>/dev/null || echo "Nao foi possivel obter logs"
fi
'@
    
    # Salvar script temporário
    $tempFile = "fix-backend-temp.sh"
    $fixScript | Out-File -FilePath $tempFile -Encoding UTF8
    
    try {
        Write-Host "Enviando script para servidor..." -ForegroundColor Yellow
        scp -o StrictHostKeyChecking=no $tempFile "$UserName@$HostName":~/fix-backend.sh
        
        Write-Host "Executando correcoes no servidor..." -ForegroundColor Yellow
        ssh -o StrictHostKeyChecking=no "$UserName@$HostName" "chmod +x ~/fix-backend.sh && ~/fix-backend.sh"
        
        Write-Host "" 
        Write-Host "Correcoes executadas!" -ForegroundColor Green
        Write-Host ""
        Write-Host "Proximos passos:" -ForegroundColor Yellow
        Write-Host "1. Verifique se o backend esta rodando"
        Write-Host "2. Teste a API: curl http://localhost:3001/health"
        Write-Host "3. Configure o proxy /api se necessario"
        Write-Host "4. Teste o frontend: https://cortefacil.app/api/health"
        
    } catch {
        Write-Host "Erro ao executar correcoes: $($_.Exception.Message)" -ForegroundColor Red
    } finally {
        # Limpar arquivo temporário
        if (Test-Path $tempFile) {
            Remove-Item $tempFile -Force
        }
    }
}

# Main script logic
if ($Help) {
    Show-Help
    exit 0
}

if (-not $HostName -or -not $User) {
    Write-Host "Erro: HostName e User sao obrigatorios" -ForegroundColor Red
    Write-Host "Use -Help para ver a ajuda" -ForegroundColor Yellow
    exit 1
}

Write-Host "=== EASYPANEL SSH CONNECTOR ===" -ForegroundColor Cyan
Write-Host "Host: $HostName" -ForegroundColor White
Write-Host "User: $User" -ForegroundColor White
Write-Host ""

if ($TestConnection) {
    Test-SSHConnection -HostName $HostName -UserName $User
} elseif ($AutoFix) {
    if (Test-SSHConnection -HostName $HostName -UserName $User) {
        Invoke-AutoFix -HostName $HostName -UserName $User
    } else {
        Write-Host "Nao foi possivel conectar via SSH. Verifique as credenciais." -ForegroundColor Red
    }
} else {
    Write-Host "Conectando via SSH..." -ForegroundColor Yellow
    ssh "$User@$HostName"
}

Write-Host "Script finalizado." -ForegroundColor Green