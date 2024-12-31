<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Mail\PasswordResetMail; // Mail personnalisé pour l'email de réinitialisation
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Création de l'utilisateur
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Génération d'un token JWT pour l'utilisateur
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }


    public function login(Request $request)
    {
        // Validation des données
        $credentials = $request->only('email', 'password');

        try {
            // Authentification et génération du token
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid email or password'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
        ]);
    }


    public function forgotPassword(Request $request)
    {
        // Validation de l'email
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Récupérer l'utilisateur par email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Générer un token unique de réinitialisation
        $token = Str::random(60); // Crée un token de 60 caractères aléatoires

        // Enregistrer le token dans la table password_resets
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now(),
        ]);

        // Créer un lien de réinitialisation
        $resetUrl = url("/api/password-reset?token={$token}");

        // Envoyer un email à l'utilisateur avec le lien de réinitialisation
        Mail::to($user->email)->send(new PasswordResetMail($resetUrl));

        // Réponse de succès
        return response()->json([
            'message' => 'Password reset link sent to your email address',
            'reset_link' => $resetUrl, // Simulé pour le moment
        ]);
    }
    public function logout()
    {
        // try {
        //     JWTAuth::invalidate(JWTAuth::getToken());
        //     return response()->json(['message' => 'Successfully logged out']);
        // } catch (JWTException $exception) {
        //     return response()->json(['error' => 'Failed to logout, please try again'], 500);
        // }
    }

    public function getUser()
    {
        $user = Auth::user();
        return response()->json($user);
    }

    public function resetPassword(Request $request)
    {
        // Validation du token et du mot de passe
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|exists:password_resets,token',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Récupérer le token et l'utilisateur
        $passwordReset = DB::table('password_resets')->where('token', $request->token)->first();

        if (!$passwordReset) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
        
        // dd($passwordReset);

        // Trouver l'utilisateur
        $user = User::where('email', $passwordReset['email'])->first();

        // Mettre à jour le mot de passe
        $user->password = Hash::make($request->password); // Hasher le mot de passe
        $user->save();

        // Supprimer le token de réinitialisation de la base de données
        DB::table('password_resets')->where('token', $request->token)->delete();

        return response()->json(['message' => 'Password reset successfully']);
    }

}
