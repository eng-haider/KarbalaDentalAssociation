<?php

namespace App\Http\Controllers;

use App\Models\OsifBroadcast;
use App\Models\OsifBroadcastMessage;
use App\Support\OsifInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

/**
 * «إرسال رسالة للجميع» in the registrations list: the dialog starts a
 * broadcast (uploading its image), then sends it number by number here.
 */
class OsifBroadcastController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $this->authorizeSender($request);

        // Answered here: this app turns validation errors into JSON for api/* routes only.
        $validator = Validator::make($request->all(), [
            // WhatsApp takes JPEG or PNG images up to 5 MB.
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ], [
            'image.file' => 'تعذّر رفع الصورة. اختر الصورة مرة أخرى.',
            'image.mimes' => 'الصورة يجب أن تكون JPG أو PNG.',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز 5 ميغابايت.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            $broadcast = OsifBroadcast::start($request->user(), $request->file('image'));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'queue' => $broadcast->pending()->orderBy('id')->get()->map->queueItem()->all(),
        ]);
    }

    public function send(Request $request, OsifBroadcastMessage $message): JsonResponse
    {
        $this->authorizeSender($request);

        try {
            $sent = $message->broadcast->send($message);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        // Already sent, or another tab is sending it now.
        return response()->json(['sent' => $sent], $sent ? 200 : 409);
    }

    /** Messages cost money and reach everyone: the WhatsApp sending role alone, as for invitations. */
    private function authorizeSender(Request $request): void
    {
        abort_unless($request->user(), 401);

        if ($reasons = OsifInvitation::apiUnavailableReasons($request->user())) {
            abort(response()->json(['message' => implode(' ', $reasons)], 403));
        }
    }
}
