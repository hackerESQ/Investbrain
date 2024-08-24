<?php

use App\Models\Portfolio;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;

new class extends Component {

    // props
    public Portfolio $portfolio;

    public array $sortBy = ['column' => 'symbol', 'direction' => 'asc'];

    public array $headers;

    public function mount()
    {
    
        $this->headers = [
            ['key' => 'symbol', 'label' => __('Symbol'), 'class' => ''],
            ['key' => 'market_data_name', 'label' => __('Name'), 'sortable' => true],
            ['key' => 'quantity', 'label' => __('Quantity')],
            ['key' => 'average_cost_basis', 'label' => __('Average Cost Basis')],
            ['key' => 'total_cost_basis', 'label' => __('Total Cost Basis')],
            ['key' => 'market_data_market_value', 'label' => __('Market Value')],
            ['key' => 'total_market_value', 'label' => __('Total Market Value')],
            ['key' => 'market_gain_dollars', 'label' => __('Market Gain/Loss')],
            ['key' => 'market_gain_percent', 'label' => __('Market Gain/Loss')],
            ['key' => 'realized_gain_dollars', 'label' => __('Realized Gain/Loss')],
            ['key' => 'dividends_earned', 'label' => __('Dividends Earned')],
            ['key' => 'market_data_fifty_two_week_low', 'label' => __('52 week low')],
            ['key' => 'market_data_fifty_two_week_high', 'label' => __('52 week high')],
            ['key' => 'num_transactions', 'label' => __('Number of Transactions')],
            ['key' => 'market_data_updated_at', 'label' => __('Market Data Age')],
        ];
    }

    public function holdings(): Collection
    {
        return $this->portfolio
                    ->holdings()
                    ->withCount(['transactions as num_transactions' => function ($query) {
                        $query->portfolio($this->portfolio->id);
                    }])
                    ->withAggregate('market_data', 'name')
                    ->withAggregate('market_data', 'market_value')
                    ->withAggregate('market_data', 'fifty_two_week_low')
                    ->withAggregate('market_data', 'fifty_two_week_high')
                    ->withAggregate('market_data', 'updated_at')
                    ->selectRaw('(market_data.market_value * holdings.quantity) AS total_market_value')
                    ->selectRaw('((market_data.market_value - holdings.average_cost_basis) * holdings.quantity) AS market_gain_dollars')
                    ->selectRaw('(((market_data.market_value - holdings.average_cost_basis) / holdings.average_cost_basis) * 100) AS market_gain_percent')
                    ->join('market_data', 'holdings.symbol', 'market_data.symbol')
                    ->orderBy(...array_values($this->sortBy))
                    ->where('quantity', '>', 0)
                    ->get();
    }

}; ?>

<div class="">

    <x-table wire:loading.remove :headers="$headers" :rows="$this->holdings()" :sort-by="$sortBy">
        @scope('cell_average_cost_basis', $row)
            {{ Number::currency($row->average_cost_basis ?? 0) }}
        @endscope
        @scope('cell_total_cost_basis', $row)
            {{ Number::currency($row->total_cost_basis ?? 0) }}
        @endscope
        @scope('cell_realized_gain_dollars', $row)
            {{ Number::currency($row->realized_gain_dollars ?? 0) }}
        @endscope
        @scope('cell_market_gain_dollars', $row)
            {{ Number::currency($row->market_gain_dollars ?? 0) }}
        @endscope
        @scope('cell_market_gain_percent', $row)
            {{ Number::percentage($row->market_gain_percent ?? 0) }}
        @endscope
        @scope('cell_market_data_market_value', $row)
            {{ Number::currency($row->market_data_market_value ?? 0) }}
        @endscope
        @scope('cell_market_data_fifty_two_week_low', $row)
            {{ Number::currency($row->market_data_fifty_two_week_low ?? 0) }}
        @endscope
        @scope('cell_market_data_fifty_two_week_high', $row)
            {{ Number::currency($row->market_data_fifty_two_week_high ?? 0) }}
        @endscope
        @scope('cell_total_market_value', $row)
            {{ Number::currency($row->total_market_value ?? 0) }}
        @endscope
        @scope('cell_dividends_earned', $row)
            {{ Number::currency($row->dividends_earned ?? 0) }}
        @endscope
        @scope('cell_dividends_earned', $row)
            {{ Number::currency($row->dividends_earned ?? 0) }}
        @endscope
        @scope('cell_market_data_updated_at', $row)
            {{ \Carbon\Carbon::parse($row->market_data_updated_at)->diffForHumans() }}
        @endscope
    </x-table>
</div>