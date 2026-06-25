import React from 'react'
import { useForm } from '@inertiajs/react'

interface TwoFactorSetupProps {
  secret: string
  otpAuthUri: string
}

interface ConfirmForm {
  code: string
}

export default function TwoFactorSetup(props: TwoFactorSetupProps): React.JSX.Element {
  const { data, setData, post, processing, errors } = useForm<ConfirmForm>({
    code: '',
  })

  const submit = (e: React.FormEvent) => {
    e.preventDefault()
    post('/two-factor/confirm')
  }

  return (
    <div style={{ maxWidth: 720, margin: '40px auto', fontFamily: 'system-ui' }}>
      <h1>Configurar 2FA</h1>
      <p>Use o segredo abaixo no seu app (Google Authenticator, 1Password, etc.).</p>

      <div style={{ marginTop: 12 }}>
        <div style={{ fontWeight: 600 }}>Secret</div>
        <div style={{ fontFamily: 'monospace', padding: 12, background: '#f6f6f6' }}>{props.secret}</div>
      </div>

      <div style={{ marginTop: 12 }}>
        <div style={{ fontWeight: 600 }}>otpauth URI</div>
        <div style={{ fontFamily: 'monospace', padding: 12, background: '#f6f6f6', wordBreak: 'break-all' }}>
          {props.otpAuthUri}
        </div>
      </div>

      <form onSubmit={submit} style={{ marginTop: 16 }}>
        <div>
          <label>Código para confirmar</label>
          <input
            value={data.code}
            onChange={(e) => setData('code', e.target.value)}
            inputMode="numeric"
            autoComplete="one-time-code"
            style={{ width: '100%', padding: 8 }}
          />
          {errors.code ? <div style={{ color: 'crimson' }}>{errors.code}</div> : null}
        </div>

        <button type="submit" disabled={processing} style={{ marginTop: 16, padding: '8px 12px' }}>
          Confirmar 2FA
        </button>
      </form>
    </div>
  )
}

