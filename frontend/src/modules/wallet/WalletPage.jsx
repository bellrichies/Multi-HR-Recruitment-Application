import { useState } from 'react';
import { fundWallet, wallet, walletTransactions } from '../../api/wallet.api';
import { Toast } from '../../components/feedback/Toast';
import { ErrorState } from '../../components/feedback/ErrorState';
import { LoadingState } from '../../components/feedback/LoadingState';
import { Breadcrumbs } from '../../components/navigation/Breadcrumbs';
import { DataTable } from '../../components/tables/DataTable';
import { Button } from '../../components/ui/Button';
import { Input } from '../../components/ui/Input';
import { StatCard } from '../../components/ui/StatCard';
import { useApi } from '../../hooks/useApi';
import { formatCurrency } from '../../utils/formatCurrency';

export function WalletPage() {
  const [amount, setAmount] = useState('');
  const [toast, setToast] = useState('');
  const walletState = useApi(wallet, []);
  const transactionState = useApi(() => walletTransactions({ per_page: 20 }), []);

  async function submit(event) {
    event.preventDefault();
    const response = await fundWallet({ amount: Number(amount), provider: 'paystack', purpose: 'wallet_funding' });
    setToast(response.message || 'Wallet funding initialized.');
    setAmount('');
  }

  if (walletState.loading || transactionState.loading) return <LoadingState label="Loading wallet..." />;
  if (walletState.error) return <ErrorState error={walletState.error} onRetry={walletState.refresh} />;

  return (
    <section className="space-y-6">
      <Toast message={toast} onClose={() => setToast('')} />
      <div>
        <Breadcrumbs items={['Wallet']} />
        <h1 className="mt-2 text-2xl font-semibold text-ink">Wallet</h1>
      </div>
      <div className="grid gap-4 lg:grid-cols-[1fr_360px]">
        <StatCard label="Available balance" value={formatCurrency(walletState.data?.available_balance, walletState.data?.currency)} />
        <form className="rounded-md border border-line bg-white p-4" onSubmit={submit}>
          <Input label="Funding amount" min="100" type="number" value={amount} onChange={(event) => setAmount(event.target.value)} required />
          <Button className="mt-4 w-full" type="submit">Fund wallet</Button>
        </form>
      </div>
      <DataTable
        columns={[
          { key: 'reference', label: 'Reference' },
          { key: 'transaction_type', label: 'Type' },
          { key: 'direction', label: 'Direction' },
          { key: 'amount', label: 'Amount', render: (row) => formatCurrency(row.amount) },
          { key: 'status', label: 'Status' },
        ]}
        emptyTitle="No wallet transactions"
        rows={transactionState.data || []}
      />
    </section>
  );
}
