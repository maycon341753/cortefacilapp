import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App.jsx'

// Bootstrap CSS via CDN será carregado no index.html

// React Toastify CSS
import 'react-toastify/dist/ReactToastify.css'

// FontAwesome CSS
import '@fortawesome/fontawesome-free/css/all.min.css'

// CSS personalizado com Tailwind (carregado por último para ter prioridade)
import './assets/css/index.css'

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>,
)