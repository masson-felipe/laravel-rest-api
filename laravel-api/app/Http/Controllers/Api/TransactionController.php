<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
USE App\Models\{User, Transaction};
USE App\Services\{AuthorizationService, NotificationService};
USE Illuminate\Support\Facades\DB;
USE App\Http\Requests\StoreTransactionRequest;
use App\Models\Notification;

class TransactionController extends Controller
{
    public function store(
        StoreTransactionRequest $request,
        AuthorizationService $authorizationService,
        NotificationService $notificationService
    ) {
        $payer = $request->user();
        $payee = User::findOrFail($request->payee_id);

        if ($payer->type == 'MERCHANT') {
            return response()->json([
                'message' => 'Lojista não podem enviar dinheiro'
            ], 403);
        }

        if ($payer->id === $payee->id) {
            return response()->json([
                'message' => 'Não é possível transferir para si mesmo'
            ], 422);
        }

        if ($payer->wallet->balance < $request->value) {
            return response()->json([
                'message' => 'Saldo insuficiente'
            ], 422);
        }

        if (!$authorizationService->authorize()) {
            return response()->json([
                'message' => 'Transação não autorizada'
            ], 403);
        }

        DB::transaction(function () use ($payer, $payee, $request, &$transaction) {

            $payer->wallet->decrement('balance', $request->value);
            $payee->wallet->increment('balance', $request->value);

            $transaction = Transaction::create([
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'value' => $request->value,
                'status' => 'APPROVED',
            ]);
        });

        $notification = Notification::create([
            'transaction_id' => $transaction->id,
            'email' => $payee->email,
            'message' => 'Você recebeu uma transferência.',
            'status' => 'PENDING',
        ]);

        try {
            $notificationService->send(
                $notification->email,
                $notification->message
            );

            $notification->update([
                'status' => 'SENT',
            ]);

        } catch (\Throwable $e) {
            $notification->update([
                'status' => 'FAILED',
                'error_message' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'id' => $transaction->id,
            'value' => $transaction->value,
            'payer_id' => $transaction->payer_id,
            'payee_id' => $transaction->payee_id,
            'status' => $transaction->status,
        ], 201);
    }
}
