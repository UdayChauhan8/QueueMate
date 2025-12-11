<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\QueueUpdated;
use App\Http\Controllers\Controller;
use App\Jobs\SendJoinNotification;
use App\Models\Clinic;
use App\Models\Service as ClinicService;
use App\Models\Token;
use App\Services\WaitTimeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class JoinController extends Controller
{
    public function join(Request $request, string $clinic_slug)
    {
        $validated = $request->validate([
            'service_id' => ['nullable', 'integer'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'notify' => ['nullable', 'array'],
            'notify.*' => [Rule::in(['whatsapp'])],
        ]);

        $clinic = Clinic::where('slug', $clinic_slug)->firstOrFail();
        $service = null;
        if (!empty($validated['service_id'])) {
            $service = ClinicService::where('clinic_id', $clinic->id)->findOrFail($validated['service_id']);
        }

        // Dedupe by phone (normalized) within 15 minutes for active tokens using JSONB meta phone_hash
        $normalizedPhone = preg_replace('/\D+/', '', $validated['customer_phone']);
        $phoneHash = hash('sha256', $normalizedPhone);
        $existing = Token::where('clinic_id', $clinic->id)
            ->where('status', 'waiting')
            ->where('created_at', '>=', now()->subMinutes(15))
            ->whereRaw("meta->>'phone_hash' = ?", [$phoneHash])
            ->first();
        if ($existing) {
            $statusUrl = URL::to("/api/v1/clinics/{$clinic->slug}/status/{$existing->id}");
            return response()->json([
                'token_id' => $existing->id,
                'token_number' => $existing->token_number,
                'position' => $this->position($clinic, $service, $existing),
                'estimated_wait_minutes' => $existing->estimated_wait,
                'status_url' => $statusUrl,
            ], 201);
        }

        $token = DB::transaction(function () use ($clinic, $service, $validated) {
            // Assign next token number atomically per clinic per day
            $todayStart = now()->startOfDay();
            $last = Token::where('clinic_id', $clinic->id)
                ->where('created_at', '>=', $todayStart)
                ->lockForUpdate()
                ->orderByDesc('token_number')
                ->first();
            $nextNumber = ($last?->token_number ?? 0) + 1;

            $token = new Token();
            $token->clinic_id = $clinic->id;
            $token->service_id = $service?->id;
            $token->token_number = $nextNumber;
            $token->customer_name = $validated['customer_name'];
            $token->customer_phone_encrypted = encrypt($validated['customer_phone']);
            $token->status = 'waiting';
            $normalizedPhone = preg_replace('/\D+/', '', $validated['customer_phone']);
            $token->meta = ['phone_hash' => hash('sha256', $normalizedPhone)];
            $token->estimated_wait = 0; // temp
            $token->save();

            return $token;
        }, 3);

        $eta = WaitTimeService::estimate($clinic, $service);
        $token->estimated_wait = $eta;
        $token->save();

        // Broadcast queue update
        QueueUpdated::dispatch($clinic->id);

        // Dispatch notification job (stub)
        dispatch(new SendJoinNotification($token, $validated['notify'] ?? []));

        $statusUrl = URL::to("/api/v1/clinics/{$clinic->slug}/status/{$token->id}");

        return response()->json([
            'token_id' => $token->id,
            'token_number' => $token->token_number,
            'position' => $this->position($clinic, $service, $token),
            'estimated_wait_minutes' => $eta,
            'status_url' => $statusUrl,
        ], 201);
    }

    public function status(Request $request, string $clinic_slug, int $token_id)
    {
        $clinic = Clinic::where('slug', $clinic_slug)->firstOrFail();
        $token = Token::where('clinic_id', $clinic->id)->findOrFail($token_id);

        $service = $token->service_id ? ClinicService::find($token->service_id) : null;

        return [
            'token_id' => $token->id,
            'token_number' => $token->token_number,
            'status' => $token->status,
            'position' => $this->position($clinic, $service, $token),
            'estimated_wait_minutes' => $token->estimated_wait,
            'called_at' => $token->called_at,
            'served_at' => $token->served_at,
        ];
    }

    private function position(Clinic $clinic, ?ClinicService $service, Token $token): int
    {
        // Count waiting tokens created before this token plus 1
        $query = Token::where('clinic_id', $clinic->id)
            ->when($service?->id, fn($q) => $q->where('service_id', $service->id))
            ->where('status', 'waiting')
            ->where('id', '<', $token->id);
        return $query->count() + 1;
    }
}
