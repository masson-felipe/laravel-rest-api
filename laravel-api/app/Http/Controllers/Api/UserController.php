<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function store(StoreUserRequest $request)
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'document' => $request->document,
                'password' => Hash::make($request->password),
                'type' => $request->type,
            ]);

            $user->wallet()->create([
                'balance' => 100.00,
            ]);

            return $user;
        });

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'document' => $user->document,
            'type' => $user->type,
        ], 201);
    }
}
