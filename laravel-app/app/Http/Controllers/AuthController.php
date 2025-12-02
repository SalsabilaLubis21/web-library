<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Halaman login
    public function loginPage() {
        return view('auth.login');
    }

    // Halaman register
    public function registerPage() {
        return view('auth.register');
    }

    // -----------------------------
    // REGISTER FACE
    // -----------------------------
    public function registerFace(Request $request) {
        $request->validate([
            'name'      => 'required',
            'email'     => 'required|email|unique:users',
            'embedding' => 'required' // array float
        ]);

        // Convert array float ke BLOB
        $embeddingArray = json_decode($request->embedding, true); // array float
        $blob = pack('f*', ...$embeddingArray); // encode ke binary

        User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => bcrypt('defaultpassword'), // bisa diganti form input
            'face_embedding'=> $blob
        ]);

        return response()->json(['status' => 'success']);
    }

    // -----------------------------
    // LOGIN FACE
    // -----------------------------
    public function loginFace(Request $request) {
        $request->validate([
            'embedding' => 'required'
        ]);

        $newVector = json_decode($request->embedding, true);

        $users = User::all();

        foreach ($users as $user) {
            // Ambil BLOB dari DB dan convert ke array float
            $dbVector = array_values(unpack('f*', $user->face_embedding));

            $similarity = $this->cosineSimilarity($dbVector, $newVector);

            if ($similarity >= 0.75) {
                // Login user otomatis
                Auth::login($user);

                return response()->json([
                    'status' => 'success',
                    'user' => $user->name
                ]);
            }
        }

        return response()->json(['status' => 'fail']);
    }

    // -----------------------------
    // COSINE SIMILARITY FUNCTION
    // -----------------------------
    private function cosineSimilarity($a, $b) {
        $dot = 0; $magA = 0; $magB = 0;

        for ($i = 0; $i < count($a); $i++) {
            $dot += $a[$i] * $b[$i];
            $magA += $a[$i] * $a[$i];
            $magB += $b[$i] * $b[$i];
        }

        return $dot / (sqrt($magA) * sqrt($magB));
    }
}
 