<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->repository->all());
    }

    public function store(UserRequest $request): JsonResponse
    {
        $user = $this->repository->create($request->validated());
        return response()->json($user, 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user->load(['orders', 'subscriptions']));
    }

    public function update(UserRequest $request, User $user): JsonResponse
    {
        $user = $this->repository->update($user, $request->validated());
        return response()->json($user);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->repository->delete($user);
        return response()->json(['message' => 'Usuário removido com sucesso.']);
    }

    public function findByRole(string $role): JsonResponse
    {
        return response()->json($this->repository->findByRole($role));
    }

    public function findByEmail(string $email): JsonResponse
    {
        $user = $this->repository->findByEmail($email);
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }
        return response()->json($user);
    }
}
