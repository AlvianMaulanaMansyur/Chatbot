<?php

namespace App\Http\Controllers\chatbot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MainController extends Controller
{
    public function index()
    {
        return view('chatbot.index');
    }

    public function handleWebhook(Request $request)
    {
        $body = $request->getContent(); // ambil raw body
        $signature = $request->header('X-Tawk-Signature');
        $secret = env('TAWKTO_SECRET'); // secret key dari .env

        // Hitung HMAC SHA1
        $digest = hash_hmac('sha1', $body, $secret);

        // Verifikasi signature
        if (!hash_equals($digest, $signature)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Signature valid → lanjut proses
        $payload = $request->all();
        $message = $payload['message'] ?? null;

        // Kirim ke n8n
        $response = Http::post('https://n8n-domain.com/webhook/39e12b0f-98bd-4e30-8417-a03eaaafd5bc', [
            'message' => $message,
            'user' => $payload['visitor'] ?? null,
        ]);

        return response()->json(['status' => 'ok']);
    }
}
