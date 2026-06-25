import React from 'react'
import { useForm } from '@inertiajs/react'

interface ChallengeForm {
  code: string
}

export default function TwoFactorChallenge(): React.JSX.Element {
  const { data, setData, post, processing, errors } = useForm<ChallengeForm>({
    code: '',
  })

  const submit = (e: React.FormEvent) => {
    e.preventDefault()
    post('/two-factor/challenge')
  }

  return (
    <div style={{ maxWidth: 420, margin: '40px auto', fontFamily: 'system-ui' }}>
      <h1>2FA</h1>
      <form onSubmit={submit}>
        <div style={{ marginTop: 12 }}>
          <label>Código do autenticador</label>
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
          Verificar
        </button>
      </form>
    </div>
  )
}

