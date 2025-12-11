import React, { useState } from 'react'
import { useParams } from 'react-router-dom'
import { joinClinic } from '../services/api'

export default function Join() {
  const { clinic_slug } = useParams()
  const [form, setForm] = useState({ customer_name: '', customer_phone: '', service_id: '' })
  const [loading, setLoading] = useState(false)
  const [result, setResult] = useState(null)
  const [error, setError] = useState(null)

  const onChange = (e) => setForm({ ...form, [e.target.name]: e.target.value })

  const submit = async (e) => {
    e.preventDefault()
    setError(null)
    setLoading(true)
    try {
      const payload = {
        clinicSlug: clinic_slug,
        service_id: form.service_id ? Number(form.service_id) : undefined,
        customer_name: form.customer_name,
        customer_phone: form.customer_phone,
        notify: ['whatsapp']
      }
      const data = await joinClinic(payload)
      setResult(data)
    } catch (err) {
      setError(err.response?.data?.message || err.message)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="container py-4" style={{ maxWidth: 560 }}>
      <h2 className="mb-3">Join Queue — {clinic_slug}</h2>
      {!result && (
        <form onSubmit={submit}>
          <div className="mb-3">
            <label className="form-label">Name</label>
            <input className="form-control" name="customer_name" value={form.customer_name} onChange={onChange} required />
          </div>
          <div className="mb-3">
            <label className="form-label">Phone</label>
            <input className="form-control" name="customer_phone" value={form.customer_phone} onChange={onChange} required />
          </div>
          <button className="btn btn-primary" disabled={loading}>
            {loading ? 'Joining...' : 'Join Queue'}
          </button>
          {error && <div className="text-danger mt-2">{error}</div>}
        </form>
      )}

      {result && (
        <div className="alert alert-success">
          <h4 className="alert-heading">Token Confirmed</h4>
          <p className="mb-1">Token Number: <strong>#{result.token_number}</strong></p>
          <p className="mb-1">Estimated Wait: <strong>{result.estimated_wait_minutes} mins</strong></p>
          <p className="mb-0">Track status: <a href={result.status_url} target="_blank" rel="noreferrer">{result.status_url}</a></p>
        </div>
      )}
    </div>
  )
}
