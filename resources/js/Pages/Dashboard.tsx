import React from 'react'
import { Link, useForm, usePage } from '@inertiajs/react'

interface PageProps {
  auth: {
    user: { id: number; email: string; name: string } | null
  }
}

export default function Dashboard(): React.JSX.Element {
  const { auth } = usePage<PageProps>().props
  const { post, processing } = useForm()

  return (
    <div style={{ maxWidth: 720, margin: '40px auto', fontFamily: 'system-ui' }}>
      <h1>Dashboard</h1>
      <div style={{ marginTop: 12 }}>
        {auth.user ? (
          <div>
            Logado como <strong>{auth.user.email}</strong>
          </div>
        ) : (
          <div>Não autenticado</div>
        )}
      </div>

      <div style={{ display: 'flex', gap: 12, marginTop: 16 }}>
        <Link href="/transactions">Transactions</Link>
        <Link href="/two-factor/setup">Configurar 2FA</Link>
      </div>

      <button
        type="button"
        disabled={processing}
        onClick={() => post('/logout')}
        style={{ marginTop: 16, padding: '8px 12px' }}
      >
        Sair
      </button>
    </div>
  )
}

