<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\ContractAmendment;

class DocumentController extends Controller
{
    public function demandNotice(Contract $contract)
    {
        $contractController = new ContractController();
        $penalties = $contractController->calculatePenalties($contract);

        $data = [
            'contract' => $contract->load(['subject', 'object.district']),
            'penalties' => $penalties['penalties'],
            'total_penalty' => $penalties['total_penalty'],
            'total_debt' => $penalties['total_debt'],
            'generated_date' => now()->format('d.m.Y'),
            'deadline_days' => 3
        ];

        return view('documents.demand_notice', $data);
    }

    public function amendment(Contract $contract, ContractAmendment $amendment)
    {
        $data = [
            'contract' => $contract->load(['subject', 'object.district', 'baseAmount']),
            'amendment' => $amendment->load(['newBaseAmount']),
            'generated_date' => now()->format('d.m.Y')
        ];

        return view('documents.amendment', $data);
    }

    public function cancellation(Contract $contract)
    {
        $cancellation = $contract->cancellation;

        $data = [
            'contract' => $contract->load(['subject', 'object.district']),
            'cancellation' => $cancellation->load(['reason']),
            'generated_date' => now()->format('d.m.Y')
        ];

        return view('documents.cancellation', $data);
    }
}
