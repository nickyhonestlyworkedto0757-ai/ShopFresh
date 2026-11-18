<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:255',
            'accept_terms' => 'accepted',
        ], ['accept_terms.accepted' => 'You must read and agree to the Legal Terms and Privacy Policy before subscribing.']);

        $created = NewsletterSubscriber::firstOrCreate(['email' => strtolower(trim($data['email']))]);
        return redirect()->back(fallback: route('home'))->with('success', $created->wasRecentlyCreated
            ? 'Welcome to our newsletter! You will receive our latest offers.'
            : 'You are already subscribed. Thank you!');
    }
}