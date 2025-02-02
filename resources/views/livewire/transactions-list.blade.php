<?php

use App\Models\Portfolio;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {

    use Toast; 

    // props
    public Collection $transactions;
    public ?Portfolio $portfolio;
    public ?Transaction $editingTransaction;
    public Bool $shouldGoToHolding = true;

    protected $listeners = [
        'transaction-updated' => '$refresh',
        'transaction-saved' => '$refresh'
    ];

    // methods
    public function showTransactionDialog($transactionId)
    {
        if (!auth()->user()->can('fullAccess', $this->portfolio)) {
            $this->error(__('You do not have permission to manage transactions for this portfolio'));
            return;
        }

        $this->editingTransaction = Transaction::findOrFail($transactionId);
        $this->dispatch('toggle-manage-transaction');
    }

    public function goToHolding($holding)
    {
        return $this->redirect(route('holding.show', ['portfolio' => $holding['portfolio_id'], 'symbol' => $holding['symbol']]));
    }

}; ?>

<div class="">

    @foreach($transactions->sortByDesc('date')->take(10) as $transaction)

        <x-list-item 
            no-separator 
            :item="$transaction" 
            class="cursor-pointer"
            x-data="{ loading: false, timeout: null }"
            @click="
                if ($wire.shouldGoToHolding) {

                    $wire.goToHolding({{ $transaction }})
                    
                    return;
                }
                timeout = setTimeout(() => { loading = true }, 200);
                $wire.showTransactionDialog('{{ $transaction->id }}').then(() => {
                    clearTimeout(timeout);
                    loading = false;
                })
            "
        >
            <x-slot:value class="flex items-center">
                <x-badge 
                    :value="$transaction->split
                        ? 'SPLIT'
                        : ($transaction->reinvested_dividend
                            ? 'REINVEST' 
                            : $transaction->transaction_type)" 
                    class="{{ $transaction->transaction_type == 'BUY' 
                        ? 'badge-success' 
                        : 'badge-error' }} badge-sm mr-3" 
                />
                {{ $transaction->symbol }} 
                ({{ $transaction->quantity }} 
                @ {{ $transaction->transaction_type == 'BUY' 
                    ? Number::currency($transaction->cost_basis)
                    : Number::currency($transaction->sale_price) }})

                <x-loading x-show="loading" x-cloak class="text-gray-400 ml-2" />
            </x-slot:value>
            <x-slot:sub-value>
                <span title="{{ __('Transaction Date') }}">{{ $transaction->date->format('F j, Y') }} </span>
            </x-slot:sub-value>
        </x-list-item>

    @endforeach

    <x-ib-alpine-modal 
        key="manage-transaction"
        title="{{ __('Manage Transaction') }}"
    >
        @livewire('manage-transaction-form', [
            'portfolio' => $portfolio, 
            'transaction' => $editingTransaction, 
        ], key($editingTransaction->id ?? 'new'))

    </x-ib-alpine-modal>
</div>