<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PublicPageController extends Controller
{
    public function home()
    {
        return view('public.home');
    }

    public function about()
    {
        return view('public.about');
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function contactSubmit(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'subject' => 'nullable|string|max:200',
            'message' => 'required|string|max:2000',
        ]);

        $message = ContactMessage::create($data);
        if ($adminEmail = config('mail.admin_address')) {
            try {
                Mail::raw(
                    "{$data['message']}\n\nDari: {$data['name']} <{$data['email']}>",
                    fn ($mail) => $mail->to($adminEmail)
                        ->subject('[Kontak Web] '.($data['subject'] ?: 'Pesan baru'))
                );
            } catch (\Throwable $exception) {
                Log::error('Notifikasi pesan kontak gagal.', [
                    'contact_message_id' => $message->id,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        return back()->with('contact_success', true);
    }

    public function contactMessages()
    {
        $messages = ContactMessage::latest()->paginate(50);

        return view('contact-messages.index', compact('messages'));
    }

    public function privacy()
    {
        return view('public.privacy');
    }

    public function terms()
    {
        return view('public.terms');
    }

    public function refund()
    {
        return view('public.refund');
    }
}
