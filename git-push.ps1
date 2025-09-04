# Script PowerShell para fazer push das alterações
# Compatível com PowerShell 5.x

Write-Host "Iniciando processo de Git Push..." -ForegroundColor Green

# Adicionar todos os arquivos
Write-Host "Adicionando arquivos..." -ForegroundColor Yellow
try {
    git add .
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Arquivos adicionados com sucesso" -ForegroundColor Green
    } else {
        Write-Host "Erro ao adicionar arquivos" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "Erro ao executar git add: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Fazer commit
Write-Host "Fazendo commit..." -ForegroundColor Yellow
try {
    git commit -m "feat: Adicionar solucoes para problema MySQL Docker IP 172.18.0.6"
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Commit realizado com sucesso" -ForegroundColor Green
    } else {
        Write-Host "Nenhuma alteracao para commit ou erro no commit" -ForegroundColor Yellow
    }
} catch {
    Write-Host "Erro ao executar git commit: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Fazer push
Write-Host "Fazendo push para repositorio remoto..." -ForegroundColor Yellow
try {
    git push
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Push realizado com sucesso!" -ForegroundColor Green
        Write-Host "Verificando status final..." -ForegroundColor Cyan
        git status --porcelain
        if ($LASTEXITCODE -eq 0) {
            Write-Host "Repositorio sincronizado" -ForegroundColor Green
        }
    } else {
        Write-Host "Erro ao fazer push" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "Erro ao executar git push: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

Write-Host "Processo concluido com sucesso!" -ForegroundColor Green