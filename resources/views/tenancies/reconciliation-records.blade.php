<div class="page-card space-y-6">
    <div>
        <h2 class="text-lg font-bold border-b pb-3">Rent Breakdown</h2>
        <div class="overflow-x-auto mt-3">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Rent</th>
                        <th>Garbage</th>
                        <th>Water</th>
                        <th>Total Due</th>
                        <th>Paid</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summary['rent']['months'] as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $row['charge']->month_year)->format('M Y') }}</td>
                            <td>KES {{ number_format((float) $row['charge']->rent_amount, 2) }}</td>
                            <td>KES {{ number_format((float) $row['charge']->garbage_fee, 2) }}</td>
                            <td>KES {{ number_format((float) $row['charge']->water_bill, 2) }}</td>
                            <td>KES {{ number_format($row['due'], 2) }}</td>
                            <td>KES {{ number_format($row['paid'], 2) }}</td>
                            <td>KES {{ number_format($row['balance'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-gray-500">
                                No rent charges were recorded through the vacate date.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="mt-3 text-sm text-gray-600">
            Outstanding rent arrears:
            <strong>KES {{ number_format($summary['rent']['arrears'], 2) }}</strong>
        </p>
    </div>

    <div>
        <h2 class="text-lg font-bold border-b pb-3">Rent Payments</h2>
        <div class="overflow-x-auto mt-3">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction Code</th>
                        <th>Method</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->paid_at->format('d M Y, h:i A') }}</td>
                            <td>{{ $payment->transaction_code ?: '—' }}</td>
                            <td>{{ $payment->payment_method ?: '—' }}</td>
                            <td>KES {{ number_format((float) $payment->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-gray-500">No approved rent payments recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <h2 class="text-lg font-bold border-b pb-3">Deposit Payments</h2>
        <div class="overflow-x-auto mt-3">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction Code</th>
                        <th>Method</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($depositPayments as $payment)
                        <tr>
                            <td>{{ $payment->paid_at->format('d M Y, h:i A') }}</td>
                            <td>{{ $payment->transaction_code ?: '—' }}</td>
                            <td>{{ $payment->payment_method ?: '—' }}</td>
                            <td>KES {{ number_format((float) $payment->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-gray-500">No deposit payments recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
