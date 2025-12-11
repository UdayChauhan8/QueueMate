import axios from 'axios'

const client = axios.create({
  baseURL: import.meta.env.VITE_BACKEND_URL || 'http://localhost:8000',
  headers: { 'Content-Type': 'application/json' }
})

export async function joinClinic({ clinicSlug, service_id, customer_name, customer_phone, notify = ['whatsapp'] }) {
  const { data } = await client.post(`/api/v1/clinics/${clinicSlug}/join`, {
    service_id, customer_name, customer_phone, notify
  })
  return data
}
