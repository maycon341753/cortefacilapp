import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)
const rootDir = path.resolve(__dirname, '..')

console.log('🔨 Iniciando build do servidor...')

// Criar diretório de build se não existir
const buildDir = path.join(rootDir, 'dist')
if (!fs.existsSync(buildDir)) {
  fs.mkdirSync(buildDir, { recursive: true })
}

// Copiar arquivos do servidor
const serverSrcDir = path.join(rootDir, 'backend', 'server')
const serverBuildDir = path.join(buildDir, 'server')

function copyDirectory(src, dest) {
  if (!fs.existsSync(dest)) {
    fs.mkdirSync(dest, { recursive: true })
  }

  const entries = fs.readdirSync(src, { withFileTypes: true })

  for (const entry of entries) {
    const srcPath = path.join(src, entry.name)
    const destPath = path.join(dest, entry.name)

    if (entry.isDirectory()) {
      // Pular node_modules
      if (entry.name === 'node_modules') continue
      copyDirectory(srcPath, destPath)
    } else {
      // Copiar apenas arquivos necessários
      if (entry.name.endsWith('.js') || 
          entry.name.endsWith('.json') || 
          entry.name === '.env.production') {
        fs.copyFileSync(srcPath, destPath)
        console.log(`📄 Copiado: ${entry.name}`)
      }
    }
  }
}

try {
  copyDirectory(serverSrcDir, serverBuildDir)
  
  // Criar package.json otimizado para produção
  const serverPackageJson = {
    "name": "cortefacil-server",
    "version": "1.0.0",
    "type": "module",
    "main": "server.js",
    "scripts": {
      "start": "node server.js"
    },
    "dependencies": {
      "express": "^4.18.2",
      "cors": "^2.8.5",
      "helmet": "^7.0.0",
      "morgan": "^1.10.0",
      "dotenv": "^16.3.1",
      "mysql2": "^3.6.0",
      "bcryptjs": "^2.4.3",
      "jsonwebtoken": "^9.0.2",
      "joi": "^17.9.2",
      "express-rate-limit": "^6.10.0"
    },
    "engines": {
      "node": ">=16.0.0",
      "npm": ">=8.0.0"
    }
  }

  fs.writeFileSync(
    path.join(serverBuildDir, 'package.json'),
    JSON.stringify(serverPackageJson, null, 2)
  )

  console.log('✅ Build do servidor concluído!')
  console.log(`📁 Arquivos gerados em: ${serverBuildDir}`)

} catch (error) {
  console.error('❌ Erro durante o build do servidor:', error)
  process.exit(1)
}