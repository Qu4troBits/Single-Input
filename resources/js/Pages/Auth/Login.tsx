import React from 'react'
import { useForm } from '@inertiajs/react'

interface LoginForm {
  email: string
  password: string
}

export default function Login(): React.JSX.Element {
  const { data, setData, post, processing, errors } = useForm<LoginForm>({
    email: '',
    password: '',
  })

  const submit = (e: React.FormEvent) => {
    e.preventDefault()
    post('/login')
  }

  return (
    <div style={{ maxWidth: 420, margin: '40px auto', fontFamily: 'system-ui' }}>
      <h1>Login</h1>
      <form onSubmit={submit}>
        <div style={{ marginTop: 12 }}>
          <label>Email</label>
          <input
            value={data.email}
            onChange={(e) => setData('email', e.target.value)}
            type="email"
            autoComplete="email"
            style={{ width: '100%', padding: 8 }}
          />
          {errors.email ? <div style={{ color: 'crimson' }}>{errors.email}</div> : null}
        </div>

        <div style={{ marginTop: 12 }}>
          <label>Senha</label>
          <input
            value={data.password}
            onChange={(e) => setData('password', e.target.value)}
            type="password"
            autoComplete="current-password"
            style={{ width: '100%', padding: 8 }}
          />
          {errors.password ? <div style={{ color: 'crimson' }}>{errors.password}</div> : null}
        </div>

        <button type="submit" disabled={processing} style={{ marginTop: 16, padding: '8px 12px' }}>
          Entrar
        </button>
      </form>
    </div>
  )
}

