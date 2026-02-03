<?php

namespace App\Http\Controllers;

use App\Models\EmailAttachment;
use App\Models\EmailAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmailAttachmentController extends Controller
{
    public function download(Request $request, EmailAttachment $attachment)
    {
        $user = $request->user();

        // Allow if account is visible to this user (uses scopeForUser)
        $accountId = $attachment->message->email_account_id;

        $query = EmailAccount::query()->forUser($user)->where('id', $accountId);
        if (! $query->exists()) {
            abort(403);
        }

        // Try private (storage/app) path first, then public storage
        $possible = [
            storage_path('app/' . $attachment->file_path),
            storage_path('app/public/' . $attachment->file_path),
        ];

        foreach ($possible as $path) {
            if (file_exists($path)) {
                return response()->download($path, $attachment->file_name ?? basename($path));
            }
        }

        abort(404);
    }
}
