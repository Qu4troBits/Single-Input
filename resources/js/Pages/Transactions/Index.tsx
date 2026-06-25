import React from 'react'
import { Link } from '@inertiajs/react'

interface TransactionRow {
  id: number
  description: string
  amount: string
  direction: string
  status: string
  competence_month: string
  payment_date: string | null
}

interface Props {
  transactions: TransactionRow[]
}

export default function TransactionsIndex(props: Props): React.JSX.Element {
  return (
    <div style={{ maxWidth: 960, margin: '40px auto', fontFamily: 'system-ui' }}>
      <h1>Transactions</h1>
      <div style={{ marginTop: 12 }}>
        <Link href="/transactions/create">Novo lançamento</Link>
      </div>

      <table style={{ width: '100%', marginTop: 16, borderCollapse: 'collapse' }}>
        <thead>
          <tr>
            <th style={{ textAlign: 'left', borderBottom: '1px solid #ddd', padding: 8 }}>ID</th>
            <th style={{ textAlign: 'left', borderBottom: '1px solid #ddd', padding: 8 }}>Descrição</th>
            <th style={{ textAlign: 'left', borderBottom: '1px solid #ddd', padding: 8 }}>Valor</th>
            <th style={{ textAlign: 'left', borderBottom: '1px solid #ddd', padding: 8 }}>Direção</th>
            <th style={{ textAlign: 'left', borderBottom: '1px solid #ddd', padding: 8 }}>Status</th>
            <th style={{ textAlign: 'left', borderBottom: '1px solid #ddd', padding: 8 }}>Competência</th>
            <th style={{ textAlign: 'left', borderBottom: '1px solid #ddd', padding: 8 }}>Pagamento</th>
          </tr>
        </thead>
        <tbody>
          {props.transactions.map((t) => (
            <tr key={t.id}>
              <td style={{ borderBottom: '1px solid #f0f0f0', padding: 8 }}>{t.id}</td>
              <td style={{ borderBottom: '1px solid #f0f0f0', padding: 8 }}>{t.description}</td>
              <td style={{ borderBottom: '1px solid #f0f0f0', padding: 8 }}>{t.amount}</td>
              <td style={{ borderBottom: '1px solid #f0f0f0', padding: 8 }}>{t.direction}</td>
              <td style={{ borderBottom: '1px solid #f0f0f0', padding: 8 }}>{t.status}</td>
              <td style={{ borderBottom: '1px solid #f0f0f0', padding: 8 }}>{t.competence_month}</td>
              <td style={{ borderBottom: '1px solid #f0f0f0', padding: 8 }}>{t.payment_date ?? '-'}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}

