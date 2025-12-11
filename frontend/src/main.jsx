import React from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import 'bootstrap/dist/css/bootstrap.min.css'
import JoinPage from './pages/Join.jsx'

function App() {
  const defaultClinic = import.meta.env.VITE_CLINIC_SLUG || 'greenlab'
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/join/:clinic_slug" element={<JoinPage />} />
        <Route path="*" element={<Navigate to={`/join/${defaultClinic}`} replace />} />
      </Routes>
    </BrowserRouter>
  )
}

createRoot(document.getElementById('root')).render(<App />)
