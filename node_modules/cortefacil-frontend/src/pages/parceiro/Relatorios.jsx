import React, { useState, useEffect } from 'react'
import { useAuth } from '../../contexts/AuthContext'

const Relatorios = () => {
  const { user } = useAuth()
  const [loading, setLoading] = useState(true)
  const [dateRange, setDateRange] = useState({
    inicio: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
    fim: new Date().toISOString().split('T')[0]
  })
  const [stats, setStats] = useState({
    faturamento: {
      total: 0,
      meta: 5000,
      crescimento: 0
    },
    agendamentos: {
      total: 0,
      confirmados: 0,
      cancelados: 0,
      concluidos: 0
    },
    clientes: {
      novos: 0,
      recorrentes: 0,
      total: 0
    },
    servicos: {
      mais_vendidos: [],
      total_servicos: 0
    }
  })
  const [chartData, setChartData] = useState({
    faturamento_diario: [],
    agendamentos_por_dia: [],
    servicos_populares: []
  })

  // Mock data para demonstração
  useEffect(() => {
    const mockStats = {
      faturamento: {
        total: 3250.00,
        meta: 5000,
        crescimento: 15.2
      },
      agendamentos: {
        total: 85,
        confirmados: 32,
        cancelados: 8,
        concluidos: 45
      },
      clientes: {
        novos: 12,
        recorrentes: 28,
        total: 40
      },
      servicos: {
        mais_vendidos: [
          { nome: 'Corte Masculino', quantidade: 25, faturamento: 625.00 },
          { nome: 'Corte Feminino', quantidade: 18, faturamento: 630.00 },
          { nome: 'Coloração', quantidade: 8, faturamento: 640.00 },
          { nome: 'Hidratação', quantidade: 15, faturamento: 600.00 }
        ],
        total_servicos: 66
      }
    }

    const mockChartData = {
      faturamento_diario: [
        { dia: '01', valor: 120 },
        { dia: '02', valor: 180 },
        { dia: '03', valor: 95 },
        { dia: '04', valor: 220 },
        { dia: '05', valor: 160 },
        { dia: '06', valor: 280 },
        { dia: '07', valor: 190 }
      ],
      agendamentos_por_dia: [
        { dia: 'Seg', agendamentos: 8 },
        { dia: 'Ter', agendamentos: 12 },
        { dia: 'Qua', agendamentos: 6 },
        { dia: 'Qui', agendamentos: 15 },
        { dia: 'Sex', agendamentos: 18 },
        { dia: 'Sáb', agendamentos: 22 },
        { dia: 'Dom', agendamentos: 4 }
      ],
      servicos_populares: [
        { nome: 'Corte Masculino', porcentagem: 38 },
        { nome: 'Corte Feminino', porcentagem: 27 },
        { nome: 'Hidratação', porcentagem: 23 },
        { nome: 'Coloração', porcentagem: 12 }
      ]
    }
    
    setTimeout(() => {
      setStats(mockStats)
      setChartData(mockChartData)
      setLoading(false)
    }, 1000)
  }, [dateRange])

  const handleDateChange = (field, value) => {
    setDateRange(prev => ({
      ...prev,
      [field]: value
    }))
  }

  const exportarRelatorio = () => {
    // Simular exportação
    const csvContent = `Relatório do Salão - ${dateRange.inicio} a ${dateRange.fim}\n\n` +
      `Faturamento Total: R$ ${stats.faturamento.total.toFixed(2)}\n` +
      `Total de Agendamentos: ${stats.agendamentos.total}\n` +
      `Novos Clientes: ${stats.clientes.novos}\n\n` +
      `Serviços Mais Vendidos:\n` +
      stats.servicos.mais_vendidos.map(s => `${s.nome}: ${s.quantidade} vendas - R$ ${s.faturamento.toFixed(2)}`).join('\n')
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    link.setAttribute('href', url)
    link.setAttribute('download', `relatorio_${dateRange.inicio}_${dateRange.fim}.csv`)
    link.style.visibility = 'hidden'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  }

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Relatórios</h1>
          <p className="text-gray-600">Acompanhe o desempenho do seu salão</p>
        </div>
        <button
          onClick={exportarRelatorio}
          className="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors"
        >
          📊 Exportar Relatório
        </button>
      </div>

      {/* Filtros de Data */}
      <div className="bg-white p-4 rounded-lg shadow-sm border">
        <div className="flex flex-col md:flex-row gap-4 items-end">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
            <input
              type="date"
              value={dateRange.inicio}
              onChange={(e) => handleDateChange('inicio', e.target.value)}
              className="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
            <input
              type="date"
              value={dateRange.fim}
              onChange={(e) => handleDateChange('fim', e.target.value)}
              className="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <button
            onClick={() => setLoading(true)}
            className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors"
          >
            Atualizar
          </button>
        </div>
      </div>

      {/* Cards de Estatísticas */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {/* Faturamento */}
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Faturamento</p>
              <p className="text-2xl font-bold text-gray-900">R$ {stats.faturamento.total.toFixed(2)}</p>
            </div>
            <div className="p-3 bg-green-100 rounded-full">
              <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
              </svg>
            </div>
          </div>
          <div className="mt-4">
            <div className="flex items-center text-sm">
              <span className={`font-medium ${
                stats.faturamento.crescimento >= 0 ? 'text-green-600' : 'text-red-600'
              }`}>
                {stats.faturamento.crescimento >= 0 ? '+' : ''}{stats.faturamento.crescimento}%
              </span>
              <span className="text-gray-600 ml-1">vs mês anterior</span>
            </div>
            <div className="mt-2">
              <div className="bg-gray-200 rounded-full h-2">
                <div 
                  className="bg-green-600 h-2 rounded-full" 
                  style={{ width: `${(stats.faturamento.total / stats.faturamento.meta) * 100}%` }}
                ></div>
              </div>
              <p className="text-xs text-gray-600 mt-1">
                Meta: R$ {stats.faturamento.meta.toFixed(2)}
              </p>
            </div>
          </div>
        </div>

        {/* Agendamentos */}
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Agendamentos</p>
              <p className="text-2xl font-bold text-gray-900">{stats.agendamentos.total}</p>
            </div>
            <div className="p-3 bg-blue-100 rounded-full">
              <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          </div>
          <div className="mt-4 space-y-1">
            <div className="flex justify-between text-sm">
              <span className="text-gray-600">Concluídos</span>
              <span className="font-medium text-green-600">{stats.agendamentos.concluidos}</span>
            </div>
            <div className="flex justify-between text-sm">
              <span className="text-gray-600">Confirmados</span>
              <span className="font-medium text-blue-600">{stats.agendamentos.confirmados}</span>
            </div>
            <div className="flex justify-between text-sm">
              <span className="text-gray-600">Cancelados</span>
              <span className="font-medium text-red-600">{stats.agendamentos.cancelados}</span>
            </div>
          </div>
        </div>

        {/* Clientes */}
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Clientes</p>
              <p className="text-2xl font-bold text-gray-900">{stats.clientes.total}</p>
            </div>
            <div className="p-3 bg-purple-100 rounded-full">
              <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
              </svg>
            </div>
          </div>
          <div className="mt-4 space-y-1">
            <div className="flex justify-between text-sm">
              <span className="text-gray-600">Novos</span>
              <span className="font-medium text-green-600">{stats.clientes.novos}</span>
            </div>
            <div className="flex justify-between text-sm">
              <span className="text-gray-600">Recorrentes</span>
              <span className="font-medium text-blue-600">{stats.clientes.recorrentes}</span>
            </div>
          </div>
        </div>

        {/* Serviços */}
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Serviços Realizados</p>
              <p className="text-2xl font-bold text-gray-900">{stats.servicos.total_servicos}</p>
            </div>
            <div className="p-3 bg-orange-100 rounded-full">
              <svg className="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
          </div>
          <div className="mt-4">
            <p className="text-xs text-gray-600">Serviços mais populares</p>
            <div className="mt-2 space-y-1">
              {stats.servicos.mais_vendidos.slice(0, 2).map((servico, index) => (
                <div key={index} className="flex justify-between text-sm">
                  <span className="text-gray-600 truncate">{servico.nome}</span>
                  <span className="font-medium">{servico.quantidade}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* Gráficos */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Faturamento Diário */}
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <h3 className="text-lg font-medium text-gray-900 mb-4">Faturamento Diário</h3>
          <div className="h-64 flex items-end justify-between space-x-2">
            {chartData.faturamento_diario.map((item, index) => (
              <div key={index} className="flex flex-col items-center flex-1">
                <div 
                  className="bg-blue-500 rounded-t w-full transition-all duration-300 hover:bg-blue-600"
                  style={{ height: `${(item.valor / 300) * 200}px` }}
                  title={`Dia ${item.dia}: R$ ${item.valor}`}
                ></div>
                <span className="text-xs text-gray-600 mt-2">{item.dia}</span>
              </div>
            ))}
          </div>
        </div>

        {/* Agendamentos por Dia da Semana */}
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <h3 className="text-lg font-medium text-gray-900 mb-4">Agendamentos por Dia</h3>
          <div className="h-64 flex items-end justify-between space-x-2">
            {chartData.agendamentos_por_dia.map((item, index) => (
              <div key={index} className="flex flex-col items-center flex-1">
                <div 
                  className="bg-green-500 rounded-t w-full transition-all duration-300 hover:bg-green-600"
                  style={{ height: `${(item.agendamentos / 25) * 200}px` }}
                  title={`${item.dia}: ${item.agendamentos} agendamentos`}
                ></div>
                <span className="text-xs text-gray-600 mt-2">{item.dia}</span>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Serviços Mais Vendidos */}
      <div className="bg-white p-6 rounded-lg shadow-sm border">
        <h3 className="text-lg font-medium text-gray-900 mb-4">Serviços Mais Vendidos</h3>
        <div className="overflow-x-auto">
          <table className="min-w-full">
            <thead>
              <tr className="border-b border-gray-200">
                <th className="text-left py-2 text-sm font-medium text-gray-600">Serviço</th>
                <th className="text-center py-2 text-sm font-medium text-gray-600">Quantidade</th>
                <th className="text-right py-2 text-sm font-medium text-gray-600">Faturamento</th>
                <th className="text-right py-2 text-sm font-medium text-gray-600">% do Total</th>
              </tr>
            </thead>
            <tbody>
              {stats.servicos.mais_vendidos.map((servico, index) => {
                const porcentagem = (servico.quantidade / stats.servicos.total_servicos) * 100
                return (
                  <tr key={index} className="border-b border-gray-100">
                    <td className="py-3 text-sm text-gray-900">{servico.nome}</td>
                    <td className="py-3 text-sm text-gray-900 text-center">{servico.quantidade}</td>
                    <td className="py-3 text-sm text-gray-900 text-right">R$ {servico.faturamento.toFixed(2)}</td>
                    <td className="py-3 text-sm text-gray-900 text-right">{porcentagem.toFixed(1)}%</td>
                  </tr>
                )
              })}
            </tbody>
          </table>
        </div>
      </div>

      {/* Resumo do Período */}
      <div className="bg-white p-6 rounded-lg shadow-sm border">
        <h3 className="text-lg font-medium text-gray-900 mb-4">Resumo do Período</h3>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="text-center">
            <div className="text-2xl font-bold text-blue-600">
              {((stats.agendamentos.concluidos / stats.agendamentos.total) * 100).toFixed(1)}%
            </div>
            <div className="text-sm text-gray-600">Taxa de Conclusão</div>
          </div>
          <div className="text-center">
            <div className="text-2xl font-bold text-green-600">
              R$ {(stats.faturamento.total / stats.agendamentos.concluidos).toFixed(2)}
            </div>
            <div className="text-sm text-gray-600">Ticket Médio</div>
          </div>
          <div className="text-center">
            <div className="text-2xl font-bold text-purple-600">
              {((stats.clientes.recorrentes / stats.clientes.total) * 100).toFixed(1)}%
            </div>
            <div className="text-sm text-gray-600">Taxa de Retenção</div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default Relatorios