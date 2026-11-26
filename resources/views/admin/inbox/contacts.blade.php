@extends('layouts.admin')

@section('title', 'Contact messages')
@section('heading', 'Contact Messages')
@section('description', 'Review messages submitted through the public homepage contact form.')

@section('content')
<section class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Received</th><th>Sender</th><th>Subject / message</th><th>Status</th><th>Update</th></tr></thead>
            <tbody>
                @forelse($messages as $message)
                    <tr>
                        <td>{{ $message->created_at->format('d M Y H:i') }}</td>
                        <td>
                            <strong>{{ $message->name }}</strong>
                            <div><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></div>
                            @if($message->phone)<div>{{ $message->phone }}</div>@endif
                        </td>
                        <td>
                            <strong>{{ $message->subject }}</strong>
                            <p style="white-space:pre-wrap;margin-top:5px">{{ $message->message }}</p>
                        </td>
                        <td><span class="admin-badge admin-badge-{{ $message->status === 'resolved' ? 'approved' : ($message->status === 'new' ? 'pending' : '') }}">{{ ucfirst($message->status) }}</span></td>
                        <td>
                            <form method="POST" action="{{ route('admin.messages.contact.update', $message) }}" class="admin-actions">
                                @csrf @method('PATCH')
                                <select class="admin-input" name="status" aria-label="Message status">
                                    @foreach(['new' => 'New', 'read' => 'Read', 'resolved' => 'Resolved'] as $value => $label)
                                        <option value="{{ $value }}" @selected($message->status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button class="admin-button" type="submit">Save</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">No contact messages yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $messages->links() }}
</section>
@endsection
