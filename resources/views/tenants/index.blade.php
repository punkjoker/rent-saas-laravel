
@extends('layouts.landlord')

@section('title', 'Tenants')
@section('page-heading', 'Tenant Management')
@section('page-description', 'Register, search and manage your tenants.')

@section('content')

<style>
    .tenant-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 22px;
    }

    .tenant-stat {
        background: white;
        padding: 20px;
        border: 1px solid #e9efeb;
        border-radius: 12px;
    }

    .tenant-stat small {
        color: #78857e;
        font-size: 12px;
    }

    .tenant-stat h2 {
        font-size: 26px;
        margin-top: 8px;
    }

    .tenant-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .tenant-search {
        display: flex;
        gap: 8px;
        flex: 1;
        max-width: 500px;
    }

    .tenant-search input {
        flex: 1;
        min-width: 150px;
    }

    .tenant-name {
        font-weight: 700;
        color: #203c30;
        text-decoration: none;
    }

    .tenant-name:hover {
        color: #176b55;
    }

    .tenant-actions {
        display: flex;
        gap: 7px;
        flex-wrap: nowrap;
        white-space: nowrap;
    }

    .tenant-actions .btn {
        padding: 6px 9px;
        font-size: 12px;
    }

    .empty-state {
        text-align: center;
        padding: 55px 20px;
        color: #78857e;
    }

    @media(max-width: 600px) {
        .tenant-stats {
            grid-template-columns: 1fr;
        }

        .tenant-toolbar {
            align-items: stretch;
        }

        .tenant-search {
            max-width: none;
        }
    }
</style>

<div class="tenant-stats">
    <div class="tenant-stat">
        <small>Total Registered Tenants</small>
        <h2>{{ number_format($stats['total']) }}</h2>
    </div>

    <div class="tenant-stat">
        <small>Registered This Month</small>
        <h2>{{ number_format($stats['registered_this_month']) }}</h2>
    </div>
</div>

<div class="page-card">

    <div class="tenant-toolbar">

        <form method="GET"
              action="{{ route('tenants.index') }}"
              class="tenant-search">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                class="form-control"
                placeholder="Search name, phone, email or ID..."
            >

            <button type="submit" class="btn btn-primary">
                Search
            </button>

            @if(request('search'))
                <a href="{{ route('tenants.index') }}"
                   class="btn btn-secondary">
                    Clear
                </a>
            @endif
        </form>

        <a href="{{ route('tenants.create') }}"
           class="btn btn-primary">
            + Register Tenant
        </a>

    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tenant</th>
                    <th>Identification</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Date Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($tenants as $tenant)
                    <tr>
                        <td>
                            <a href="{{ route('tenants.show', $tenant->id) }}"
                               class="tenant-name">
                                {{ $tenant->full_name }}
                            </a>
                        </td>

                        <td>
                            {{ $tenant->identification_number ?: '—' }}
                        </td>

                        <td>{{ $tenant->phone }}</td>

                        <td>{{ $tenant->email ?: '—' }}</td>

                        <td>{{ $tenant->created_at->format('d M Y') }}</td>

                        <td>
                            <div class="tenant-actions">
                                <a href="{{ route('payments.tenant-history', $tenant->id) }}"
                                   class="btn btn-secondary">
                                    History
                                </a>

                                <a href="{{ route('tenants.show', $tenant->id) }}"
                                   class="btn btn-secondary">
                                    View
                                </a>

                                <a href="{{ route('tenants.edit', $tenant->id) }}"
                                   class="btn btn-primary">
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <h3>No tenants found</h3>

                                <p style="margin:10px 0 18px;">
                                    @if(request('search'))
                                        Try a different search term.
                                    @else
                                        You have not registered any tenants yet.
                                    @endif
                                </p>

                                @if(!request('search'))
                                    <a href="{{ route('tenants.create') }}"
                                       class="btn btn-primary">
                                        Register Your First Tenant
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $tenants->links() }}
    </div>

</div>

@endsection
