<?php

namespace App\Livewire;

use App\Models\Email;
use Carbon\Carbon;
use Livewire\WithPagination;
use Livewire\Component;

class BitcionPurchase extends Component
{
    use WithPagination;
    public $search = '';
    public $BtcPurchased = 0, $from = '', $to = '';
    public function render()
    {

        $query = Email::where('status', 'Completed')->where('payment_note', 'Market Purchase Order');
        if ($this->from) {
            $from = Carbon::parse($this->from)->toDateString();
            // dd($from);
            $query->whereDate('created_at', '>=', $from);
        }
        if ($this->to) {

            $to = Carbon::parse($this->to)->toDateString();
            $query->whereDate('created_at', '<=', $to);
        }


        if ($this->search) {

            $query->where(function ($subquery) {
                $subquery->where('amount', 'like', '%' . $this->search . '%')
                    ->orWhere('subject', 'like', '%' . $this->search . '%')
                    ->orWhere('payment_note', 'like', '%' . $this->search . '%');
            });

            // dd($query->get());
        }
        $data = $query->paginate(10);
        return view('livewire.bitcion-purchase', compact('data'))->extends('layouts/master')->section('content');
    }
    public function mount()
    {
        $bitcionPurchased = Email::where('status', 'Completed')
            ->where('payment_note', 'Market Purchase Order')
            ->select('amount')
            ->get();
        foreach ($bitcionPurchased as $total) {
            $amount = $this->number($total->amount);
            $this->BtcPurchased += (int) $amount;
        }
    }

    public function number($data)
    {
        return floatval(preg_replace('/[^0-9.]/', '', $data));
    }
}
