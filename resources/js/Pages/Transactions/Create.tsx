import React from 'react'
import { Link, useForm } from '@inertiajs/react'

interface Option {
  id: number
  name: string
}

interface Props {
  bankAccounts: Option[]
  categories: Option[]
}

interface FormData {
  bank_account_id: string
  category_id: string
  description: string
  amount: string
  direction: 'in' | 'out'
  status: 'pending' | 'paid' | 'cancelled'
  competence_month: string
  payment_date: string
}

export default function TransactionsCreate(props: Props): React.JSX.Element {
  const today = new Date().toISOString().slice(0, 10)
  const firstDay = today.slice(0, 7) + '-01'

  const { data, setData, post, processing, errors } = useForm<FormData>({
    bank_account_id: props.bankAccounts[0]?.id ? String(props.bankAccounts[0].id) : '',
    category_id: props.categories[0]?.id ? String(props.categories[0].id) : '',
    description: '',
    amount: '0.00',
    direction: 'out',
    status: 'pending',
    competence_month: firstDay,
    payment_date: '',
  })

  const submit = (e: React.FormEvent) => {
    e.preventDefault()
    post('/transactions')
  }

  return (
    <div style={{ maxWidth: 720, margin: '40px auto', fontFamily: 'system-ui' }}>
      <h1>Novo lançamento</h1>
      <div style={{ marginTop: 12 }}>
        <Link href="/transactions">Voltar</Link>
      </div>

      <form onSubmit={submit} style={{ marginTop: 16 }}>
        <div style={{ marginTop: 12 }}>
          <label>Conta</label>
          <select
            value={data.bank_account_id}
            onChange={(e) => setData('bank_account_id', e.target.value)}
            style={{ width: '100%', padding: 8 }}
          >
            {props.bankAccounts.map((a) => (
              <option key={a.id} value={String(a.id)}>
                {a.name}
              </option>
            ))}
          </select>
          {errors.bank_account_id ? <div style={{ color: 'crimson' }}>{errors.bank_account_id}</div> : null}
        </div>

        <div style={{ marginTop: 12 }}>
          <label>Categoria</label>
          <select
            value={data.category_id}
            onChange={(e) => setData('category_id', e.target.value)}
            style={{ width: '100%', padding: 8 }}
          >
            {props.categories.map((c) => (
              <option key={c.id} value={String(c.id)}>
                {c.name}
              </option>
            ))}
          </select>
          {errors.category_id ? <div style={{ color: 'crimson' }}>{errors.category_id}</div> : null}
        </div>

        <div style={{ marginTop: 12 }}>
          <label>Descrição</label>
          <input
            value={data.description}
            onChange={(e) => setData('description', e.target.value)}
            style={{ width: '100%', padding: 8 }}
          />
          {errors.description ? <div style={{ color: 'crimson' }}>{errors.description}</div> : null}
        </div>

        <div style={{ marginTop: 12 }}>
          <label>Valor (ex: 123.45)</label>
          <input value={data.amount} onChange={(e) => setData('amount', e.target.value)} style={{ width: '100%', padding: 8 }} />
          {errors.amount ? <div style={{ color: 'crimson' }}>{errors.amount}</div> : null}
        </div>

        <div style={{ marginTop: 12, display: 'flex', gap: 12 }}>
          <div style={{ flex: 1 }}>
            <label>Direção</label>
            <select
              value={data.direction}
              onChange={(e) => setData('direction', e.target.value as FormData['direction'])}
              style={{ width: '100%', padding: 8 }}
            >
              <option value="in">in</option>
              <option value="out">out</option>
            </select>
            {errors.direction ? <div style={{ color: 'crimson' }}>{errors.direction}</div> : null}
          </div>
          <div style={{ flex: 1 }}>
            <label>Status</label>
            <select
              value={data.status}
              onChange={(e) => setData('status', e.target.value as FormData['status'])}
              style={{ width: '100%', padding: 8 }}
            >
              <option value="pending">pending</option>
              <option value="paid">paid</option>
              <option value="cancelled">cancelled</option>
            </select>
            {errors.status ? <div style={{ color: 'crimson' }}>{errors.status}</div> : null}
          </div>
        </div>

        <div style={{ marginTop: 12, display: 'flex', gap: 12 }}>
          <div style={{ flex: 1 }}>
            <label>Competência (YYYY-MM-01)</label>
            <input
              value={data.competence_month}
              onChange={(e) => setData('competence_month', e.target.value)}
              style={{ width: '100%', padding: 8 }}
            />
            {errors.competence_month ? <div style={{ color: 'crimson' }}>{errors.competence_month}</div> : null}
          </div>
          <div style={{ flex: 1 }}>
            <label>Pagamento (opcional)</label>
            <input
              value={data.payment_date}
              onChange={(e) => setData('payment_date', e.target.value)}
              placeholder="YYYY-MM-DD"
              style={{ width: '100%', padding: 8 }}
            />
            {errors.payment_date ? <div style={{ color: 'crimson' }}>{errors.payment_date}</div> : null}
          </div>
        </div>

        <button type="submit" disabled={processing} style={{ marginTop: 16, padding: '8px 12px' }}>
          Salvar
        </button>
      </form>
    </div>
  )
}

