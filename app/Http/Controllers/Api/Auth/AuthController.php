<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;

/**
 * Контролер автентифікації для API з підтримкою багатотенантності.
 *  - login()  ― видача токена, привʼязаного до конкретного тенанта (ability tid:{id}).
 *  - logout() ― відкликання поточного токена.
 */
class AuthController extends Controller
{
    /**
     * Видає Sanctum‑токен, привʼязаний до tenant‑ID через ability «tid:{id}»
     * й записує tenant_id у personal_access_tokens.
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'tenant_uuid' => ['nullable', 'uuid'],
        ]);

        /** @var User|null $user */
        $user = User::whereEmail($data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => trans('auth.failed')]);
        }

        // ─────────────────────── Визначаємо тенанта
        $tenant = $data['tenant_uuid']
            ? Tenant::whereUuid($data['tenant_uuid'])->first()
            : $user->tenants()->first();

        if (! $tenant || ! $user->tenants->contains($tenant)) {
            return response()->json(['message' => 'Invalid tenant'], 403);
        }

        // ─────────────────────── Ротація старих токенів (уникаємо сміття)
        $user->tokens()->where('name', "api:{$tenant->id}")->delete();

        /** @var NewAccessToken $token */
        $token = $user->createToken("api:{$tenant->id}", ["tid:{$tenant->id}"]);

        // Записуємо tenant_id прямо до таблиці tokens (для швидкого пошуку)
        $token->accessToken->forceFill(['tenant_id' => $tenant->id])->save();

        return response()->json([
            'token' => $token->plainTextToken,
            'tenant_id' => $tenant->id,
            'tenant_uuid' => $tenant->uuid,
            'user' => $user,
        ]);
    }

    /**
     * Вихід з API – видаляє поточний токен користувача.
     */
    public function logout(Request $request): JsonResponse
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        /** @var \Laravel\Sanctum\PersonalAccessToken|null $token */
        $token = $request->user()->currentAccessToken();
        $token?->delete();        // відкликаємо, якщо існує

        return response()->json(['message' => 'Logged out']);
    }
}
