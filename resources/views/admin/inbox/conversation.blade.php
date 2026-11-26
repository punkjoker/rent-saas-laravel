@extends('layouts.admin')

@section('title', 'Support conversation')
@section('heading', 'Support Conversation')
@section('description', $conversation->landlord->business_name.' · '.$conversation->landlord->user->email)

@section('content')
<section class="admin-card">
    <div style="display:grid;gap:12px;margin-bottom:20px">
        @forelse($messages as $message)
            <article style="max-width:80%;justify-self:{{ $message->sender_type === 'admin' ? 'end' : 'start' }};padding:13px 16px;border-radius:14px;background:{{ $message->sender_type === 'admin' ? '#e5f4eb' : '#f1f4f2' }}">
                <strong>{{ $message->sender_type === 'admin' ? 'You' : $message->sender->name }}</strong>
                <p style="margin-top:6px;white-space:pre-wrap">{{ $message->message }}</p>
                <small class="admin-muted">{{ $message->created_at->format('d M Y H:i') }}</small>
            </article>
        @empty
            <p>No messages in this conversation yet.</p>
        @endforelse
    </div>
    <form method="POST" action="{{ route('admin.messages.support.reply', $conversation) }}">
        @csrf
        <label class="admin-form">Reply
            <textarea class="admin-input" name="message" rows="4" required maxlength="5000"></textarea>
        </label>
        <button class="admin-button" type="submit" style="margin-top:10px">Send reply</button>
        <a class="admin-button admin-button-neutral" href="{{ route('admin.messages.support') }}">Back to inbox</a>
    </form>
</section>
@endsection
