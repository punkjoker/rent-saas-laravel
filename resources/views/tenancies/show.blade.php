
@extends('layouts.landlord')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('tenancies.index') }}"
               class="text-green-700 hover:underline text-sm">
                ← Back to Tenancies
            </a>

            <h1 class="text-2xl font-bold text-gray-800 mt-3">
                Tenancy Details
            </h1>

            <p class="text-gray-500 mt-1">
                Agreement reference #{{ str_pad($tenancy->id, 5, '0', STR_PAD_LEFT) }}
            </p>
        </div>

        @if($tenancy->status === 'active' || $tenancy->status === 'upcoming')
            <form method="POST"
                  action="{{ route('tenancies.end', $tenancy->id) }}"
                  onsubmit="return confirm('Are you sure you want to end this tenancy? The unit will become vacant if no other tenancy is scheduled.');"
                  class="flex flex-col sm:flex-row gap-2">
                @csrf
                @method('PATCH')

                <input type="date"
                       name="ended_at"
                       max="{{ now()->toDateString() }}"
                       value="{{ now()->toDateString() }}"
                       required
                       class="form-control">

                <button type="submit"
                        class="btn bg-red-600 hover:bg-red-700">
                    End Tenancy
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="page-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="text-sm text-gray-500">Tenancy Status</p>

                @if($tenancy->status === 'active')
                    <span class="inline-block mt-2 px-3 py-1 rounded-full text-sm bg-green-100 text-green-700">
                        Active
                    </span>
                @elseif($tenancy->status === 'upcoming')
                    <span class="inline-block mt-2 px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-700">
                        Upcoming
                    </span>
                @else
                    <span class="inline-block mt-2 px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-600">
                        Ended
                    </span>
                @endif
            </div>

            <div class="text-sm text-gray-500">
                Created {{ $tenancy->created_at->format('d M Y, h:i A') }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="page-card space-y-4">
            <h2 class="font-bold text-lg border-b pb-3">Tenant Information</h2>

            <div>
                <p class="text-sm text-gray-500">Full Name</p>
                <p class="font-semibold">
                    {{ $tenancy->tenant->first_name }}
                    {{ $tenancy->tenant->middle_name }}
                    {{ $tenancy->tenant->last_name }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Phone Number</p>
                <p class="font-semibold">{{ $tenancy->tenant->phone }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Email</p>
                <p class="font-semibold">{{ $tenancy->tenant->email ?: 'Not provided' }}</p>
            </div>

            <a href="{{ route('tenants.show', $tenancy->tenant_id) }}"
               class="inline-block text-green-700 font-semibold hover:underline">
                View Tenant Profile →
            </a>
        </div>

        <div class="page-card space-y-4">
            <h2 class="font-bold text-lg border-b pb-3">Property and Unit</h2>

            <div>
                <p class="text-sm text-gray-500">Property</p>
                <p class="font-semibold">{{ $tenancy->property->property_name }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Unit Number</p>
                <p class="font-semibold">{{ $tenancy->unit->unit_number }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Property Location</p>
                <p class="font-semibold">
                    {{ $tenancy->property->address ?: 'Not provided' }}
                    {{ $tenancy->property->town ? ', '.$tenancy->property->town : '' }}
                </p>
            </div>
        </div>

        <div class="page-card space-y-4">
            <h2 class="font-bold text-lg border-b pb-3">Agreement Period</h2>

            <div>
                <p class="text-sm text-gray-500">Start Date</p>
                <p class="font-semibold">{{ $tenancy->start_date->format('d M Y') }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Lease End Date</p>
                <p class="font-semibold">
                    {{ $tenancy->lease_end_date?->format('d M Y') ?? 'Open-ended' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Rent Due</p>
                <p class="font-semibold">
                    {{ $tenancy->rent_due_day }}{{ $tenancy->rent_due_day === 1 ? 'st' : ($tenancy->rent_due_day === 2 ? 'nd' : ($tenancy->rent_due_day === 3 ? 'rd' : 'th')) }}
                    of every month
                </p>
            </div>

            @if($tenancy->ended_at)
                <div>
                    <p class="text-sm text-gray-500">Actual End Date</p>
                    <p class="font-semibold">{{ $tenancy->ended_at->format('d M Y') }}</p>
                </div>
            @endif
        </div>

        <div class="page-card space-y-4">
            <h2 class="font-bold text-lg border-b pb-3">Agreed Charges</h2>

            <div class="flex justify-between gap-3">
                <span class="text-gray-500">Monthly Rent</span>
                <strong>KES {{ number_format((float) $tenancy->monthly_rent, 2) }}</strong>
            </div>

            <div class="flex justify-between gap-3">
                <span class="text-gray-500">Deposit</span>
                <strong>KES {{ number_format((float) $tenancy->deposit_amount, 2) }}</strong>
            </div>

            <div class="flex justify-between gap-3">
                <span class="text-gray-500">Garbage Fee</span>
                <strong>KES {{ number_format((float) $tenancy->garbage_fee, 2) }}</strong>
            </div>

            <div class="border-t pt-3 flex justify-between gap-3">
                <span class="font-semibold">Monthly Rent + Garbage</span>
                <strong class="text-green-700">
                    KES {{ number_format((float) $tenancy->monthly_rent + (float) $tenancy->garbage_fee, 2) }}
                </strong>
            </div>
        </div>
    </div>

    @if($tenancy->notes)
        <div class="page-card">
            <h2 class="font-bold text-lg mb-3">Additional Notes</h2>
            <p class="text-gray-700 whitespace-pre-line">{{ $tenancy->notes }}</p>
        </div>
    @endif

</div>
@endsection
