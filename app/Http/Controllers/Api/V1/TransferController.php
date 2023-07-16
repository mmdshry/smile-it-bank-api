<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TransferRequest;
use App\Models\Account;
use App\Models\Transfer;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class TransferController extends Controller
{
    /**
     * Display a listing of the incoming and outgoing transfers for the given account.
     *
     * @param Account $account The account to retrieve transfers for.
     * @return JsonResponse
     */
    public function index(Account $account)
    {
        return response()->json([
            'transfers' => [
                'incoming_transfers' => $account->incomingTransfers,
                'outgoing_transfers' => $account->outgoingTransfers
            ]
        ]);
    }

    /**
     * Store a newly created transfer in storage.
     *
     * @param TransferRequest $request The transfer request.
     * @return JsonResponse
     */
    public function store(TransferRequest $request)
    {
        $amount = $request->amount;
        $source = Account::findOrFail($request->source_account_id);
        $destination = Account::findOrFail($request->destination_account_id);

        if ($source->balance < $amount) {
            return response()->json(['error' => 'Insufficient balance'], 422);
        }

        $transfer = $this->transfer($source, $destination, $amount);

        return response()->json(['message' => 'Amount transferred successfully', 'data' => $transfer], 201);
    }

    /**
     * Perform the transfer of funds between two accounts.
     *
     * @param Account $source The source account.
     * @param Account $destination The destination account.
     * @param float $amount The amount to transfer.
     * @return Transfer
     * @throws RuntimeException
     */
    private function transfer(Account $source, Account $destination, float $amount): Transfer
    {
        try {
            DB::transaction(function () use ($source, $amount, $destination) {
                $source->update(['balance' => $source->balance - $amount]);
                $destination->update(['balance' => $destination->balance + $amount]);
            });
        } catch (Exception) {
            throw new RuntimeException('An error occurred during the transfer');
        }

        return $this->createTransaction($source, $destination, $amount);
    }

    /**
     * Record the transaction history for the given accounts.
     *
     * @param Account $source The source account.
     * @param Account $destination The destination account.
     * @param float $amount The amount transferred.
     * @return Transfer
     */
    private function createTransaction(Account $source, Account $destination, float $amount): Transfer
    {
        return Transfer::create([
            'source_account_id' => $source->id,
            'destination_account_id' => $destination->id,
            'amount' => $amount
        ]);
    }
}
