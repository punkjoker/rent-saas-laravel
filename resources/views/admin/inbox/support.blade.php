@extends('layouts.admin')

@section('title', 'Technical support')
@section('heading', 'Technical Support Inbox')
@section('description', 'Reply to landlord support conversations.')

@section('content')
<section class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Landlord</th><th>Latest message</th><th>Updated</th><th>Unread</th><th></th></tr></thead>
            <tbody>
                @forelse($conversations as $conversation)
                    @php($latestMessage = $conversation->messages->first())
                    <tr>
                        <td>
                            <strong>{{ $conversation->landlord->business_name }}</strong>
                            <div class="admin-muted">{{ $conversation->landlord->user->email }}</div>
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($latestMessage?->message ?? 'No messages yet', 120) }}</td>
                        <td>{{ $conversation->updated_at->format('d M Y H:i') }}</td>
                        <td>{{ $conversation->unread_count }}</td>
                        <td><a class="admin-button" href="{{ route('admin.messages.support.show', $conversation) }}">Open conversation</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5">No support conversations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $conversations->links() }}
</section>
@endsection
