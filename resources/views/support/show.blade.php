@extends('layouts.landlord')

@section('title', 'Technical support')
@section('page-heading', 'Technical Support')
@section('page-description', 'Send messages to the Pango admin team and view their replies.')

@section('content')
<section class="page-card">
    <div style="display:grid;gap:12px;margin-bottom:20px">
        @forelse($messages as $message)
            <article style="max-width:80%;justify-self:{{ $message->sender_type === 'landlord' ? 'end' : 'start' }};padding:13px 16px;border-radius:14px;background:{{ $message->sender_type === 'landlord' ? '#e5f4eb' : '#f1f4f2' }}">
                <strong>{{ $message->sender_type === 'admin' ? 'Pango Support' : 'You' }}</strong>
                <p style="margin-top:6px;white-space:pre-wrap">{{ $message->message }}</p>
                <small>{{ $message->created_at->format('d M Y, H:i') }}</small>
            </article>
        @empty
            <p>No messages yet. Send your question below to start a support conversation.</p>
        @endforelse
    </div>
    <form method="POST" action="{{ route('support.send') }}">
        @csrf
        <label class="form-group">Message
            <textarea class="form-control" name="message" rows="4" maxlength="5000" required></textarea>
        </label>
        <button class="btn btn-primary" type="submit">Send to support</button>
    </form>
</section>
@endsection
