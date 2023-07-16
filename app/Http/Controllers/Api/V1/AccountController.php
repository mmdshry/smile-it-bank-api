<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AccountRequest;
use App\Models\Account;
use Exception;
use Illuminate\Http\JsonResponse;

class AccountController extends Controller
{
    /**
     * Creates a new account.
     *
     * @param AccountRequest $request The request data.
     * @return JsonResponse The JSON response.
     */
    public function store(AccountRequest $request): JsonResponse
    {
        try {
            $account = Account::create($request->validated());

            return response()->json(compact('account'), 201);
        } catch (Exception) {
            return response()->json(['error' => 'An error occurred during creating the account'], 500);
        }
    }

    /**
     * Gets the balance of an account.
     *
     * @param Account $account The account.
     * @return JsonResponse The JSON response.
     */
    public function getBalance(Account $account): JsonResponse
    {
        return response()->json(['balance' => $account->balance]);
    }
}
