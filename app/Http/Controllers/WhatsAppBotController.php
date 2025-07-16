<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Feedback;

class WhatsAppBotController extends Controller
{
    public function receive(Request $request)
    {
        Log::info('Webhook payload:', $request->all());

        $message = $request->input('entry.0.changes.0.value.messages.0');

        if (!$message) return response()->json(['status' => 'no message']);

        $from = $message['from'];
        $text = strtolower($message['text']['body']);

        $patient = Patient::firstOrCreate(
            ['phone' => $from],
            ['name' => 'Unknown', 'age' => null, 'gender' => null]
        );

        if (str_contains($text, 'appointment')) {
            $this->sendMessage($from, "📝 Please send your Name, Age, and Department for appointment booking.\nFormat:\n`Name | Age | Department`");
        } elseif (preg_match('/\|/', $text)) {
            [$name, $age, $department] = array_map('trim', explode('|', $text));
            $patient->update(['name' => $name, 'age' => $age]);

            Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => null, // Optional
                'appointment_date' => now()->addDay(),
                'time_slot' => '10:00 AM - 11:00 AM',
                'status' => 'pending',
            ]);

            $this->sendMessage($from, "✅ Appointment booked for *$name* tomorrow at 10:00 AM.");
        } elseif (str_contains($text, 'report')) {
            $this->sendMessage($from, "📄 Please enter your patient ID to fetch reports.");
        } elseif (str_contains($text, 'feedback')) {
            $this->sendMessage($from, "🙏 Please send your feedback message. We value your thoughts.");
        } else {
            $this->sendMessage($from, "🏥 *Welcome to LifeLine Hospital!*\n\nSend:\n- `Appointment`\n- `Report`\n- `Feedback`\n\nPowered by Nexottel Health Bot 🤖");
        }

        return response()->json(['status' => 'message handled']);
    }
public function verify(Request $request)
{
    $verify_token = 'lifeline_hospital_token';

    if (
        $request->has('hub_mode') &&
        $request->hub_mode === 'subscribe' &&
        $request->hub_verify_token === $verify_token
    ) {
        return response($request->hub_challenge, 200);
    }

    return response('Token mismatch', 403);
}

    private function sendMessage($to, $message)
    {
        $token = env('WHATSAPP_TOKEN');
        $phone_id = env('WHATSAPP_PHONE_ID');

        $url = "https://graph.facebook.com/v19.0/{$phone_id}/messages";

        Http::withToken($token)->post($url, [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $message],
        ]);
    }
    public function verify(Request $request)
{
    $verify_token = 'lifeline_hospital_token'; // Must match Meta's token

    if ($request->hub_verify_token === $verify_token) {
        return response($request->hub_challenge);
    }

    return response('Invalid verification token', 403);
}

}
