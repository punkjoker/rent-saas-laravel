@extends('layouts.landlord')

@section('title', 'Reports')
@section('page-heading', 'Portfolio Reports')
@section('page-description', 'Review rental income, collections, expenses, deposits, occupancy and tenancy activity.')

@push('styles')
<style>
    .report-filter-form {
        display: grid;
        grid-template-columns: minmax(180px, 1fr) minmax(140px, .7fr) auto auto;
        align-items: end;
        gap: 12px;
    }

    .report-page {
        display: grid;
        gap: 18px;
    }

    .report-page > .page-card,
    .report-kpi,
    .report-chart-card {
        margin-bottom: 0;
    }

    .report-filter-form label {
        display: grid;
        gap: 7px;
        color: #53675c;
        font-size: 12px;
        font-weight: 700;
    }

    .report-kpi-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .report-kpi {
        min-width: 0;
        padding: 18px 20px;
    }

    .report-kpi-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        color: #65766d;
        font-size: 12px;
        font-weight: 700;
    }

    .report-kpi-icon {
        display: grid;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        place-items: center;
        border-radius: 11px;
        color: #176b55;
        background: rgba(57, 153, 111, .12);
    }

    .report-kpi-value {
        display: block;
        margin-top: 12px;
        color: #20392e;
        font-size: clamp(20px, 2vw, 26px);
        font-weight: 800;
        letter-spacing: -.04em;
        overflow-wrap: anywhere;
    }

    .report-kpi-note {
        margin-top: 4px;
        color: #7a8a81;
        font-size: 11px;
    }

    .report-chart-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .report-chart-card {
        min-width: 0;
        margin: 0;
    }

    .report-chart-card-wide {
        grid-column: 1 / -1;
    }

    .report-chart-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 18px;
    }

    .report-chart-heading h2 {
        color: #20392e;
        font-size: 16px;
        font-weight: 800;
    }

    .report-chart-heading p {
        margin-top: 5px;
        color: #718078;
        font-size: 12px;
    }

    .report-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 18px;
        margin-top: 16px;
        color: #5e7066;
        font-size: 11px;
    }

    .report-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .report-legend-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
    }

    .report-month-chart {
        display: grid;
        grid-template-columns: repeat(12, minmax(38px, 1fr));
        gap: 8px;
        overflow-x: auto;
        padding: 6px 2px 0;
    }

    .report-month-chart.single-month {
        grid-template-columns: minmax(90px, 180px);
        justify-content: center;
    }

    .report-month-column {
        display: grid;
        min-width: 0;
        height: 196px;
        grid-template-rows: 1fr auto;
        gap: 9px;
        text-align: center;
    }

    .report-month-bars {
        display: flex;
        align-items: flex-end;
        justify-content: center;
        gap: 4px;
        min-height: 0;
        border-bottom: 1px solid rgba(89, 119, 99, .2);
    }

    .report-month-bar {
        width: min(18px, 22%);
        min-height: 0;
        border-radius: 6px 6px 0 0;
        transition: height .2s ease;
    }

    .report-month-label {
        color: #65766d;
        font-size: 10px;
        font-weight: 700;
    }

    .report-bar-billed {
        background: linear-gradient(180deg, #73c7a0, #238060);
    }

    .report-bar-collected {
        background: linear-gradient(180deg, #8cb4f4, #477dd1);
    }

    .report-bar-expenses {
        background: linear-gradient(180deg, #c0a9ef, #8461c6);
    }

    .report-bar-deposits {
        background: linear-gradient(180deg, #f1c978, #d49532);
    }

    .report-bar-refunds {
        background: linear-gradient(180deg, #f3a5a5, #cf5555);
    }

    .report-horizontal-list {
        display: grid;
        gap: 15px;
        max-height: 390px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .report-horizontal-item {
        display: grid;
        gap: 7px;
    }

    .report-horizontal-heading {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 12px;
        color: #30483b;
        font-size: 12px;
        font-weight: 700;
    }

    .report-horizontal-heading span:last-child {
        color: #718078;
        font-size: 10px;
        font-weight: 600;
        text-align: right;
    }

    .report-meter-row {
        display: grid;
        grid-template-columns: 58px minmax(40px, 1fr) auto;
        align-items: center;
        gap: 8px;
        color: #718078;
        font-size: 10px;
    }

    .report-meter-track {
        height: 8px;
        overflow: hidden;
        border-radius: 999px;
        background: rgba(90, 120, 101, .12);
    }

    .report-meter-fill {
        height: 100%;
        min-width: 0;
        border-radius: inherit;
        background: linear-gradient(90deg, #71c59d, #238060);
    }

    .report-meter-fill.blue {
        background: linear-gradient(90deg, #9bbdf0, #477dd1);
    }

    .report-meter-fill.purple {
        background: linear-gradient(90deg, #c7b5ee, #8461c6);
    }

    .report-meter-fill.amber {
        background: linear-gradient(90deg, #f1d08a, #d49532);
    }

    .report-meter-row strong {
        color: #4a6053;
        font-size: 10px;
        text-align: right;
    }

    .report-occupancy {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 28px;
        min-height: 230px;
    }

    .report-occupancy svg {
        width: 170px;
        height: 170px;
        flex: 0 0 170px;
    }

    .report-occupancy-legend {
        display: grid;
        gap: 12px;
        min-width: 130px;
        color: #5e7066;
        font-size: 12px;
    }

    .report-occupancy-legend div {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .report-occupancy-legend strong {
        margin-left: auto;
        color: #253d31;
    }

    .report-empty {
        display: grid;
        min-height: 150px;
        place-items: center;
        color: #74847b;
        font-size: 13px;
        text-align: center;
    }

    .report-activity-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .report-activity {
        padding: 15px;
        border: 1px solid rgba(255, 255, 255, .78);
        border-radius: 14px;
        background: rgba(255, 255, 255, .42);
    }

    .report-activity span {
        display: block;
        color: #65766d;
        font-size: 11px;
        font-weight: 700;
    }

    .report-activity strong {
        display: block;
        margin-top: 8px;
        color: #20392e;
        font-size: 24px;
    }

    @media (max-width: 900px) {
        .report-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .report-chart-grid {
            grid-template-columns: 1fr;
        }

        .report-chart-card-wide {
            grid-column: auto;
        }
    }

    @media (max-width: 600px) {
        .report-filter-form {
            grid-template-columns: 1fr 1fr;
        }

        .report-filter-form .btn {
            width: 100%;
        }

        .report-occupancy {
            flex-direction: column;
            gap: 12px;
            padding: 12px 0;
        }

        .report-occupancy-legend {
            width: 100%;
            max-width: 280px;
        }

        .report-activity-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $cashflowMax = max(
        1,
        (float) collect($monthlySeries)->max('rent_billed'),
        (float) collect($monthlySeries)->max('rent_collected'),
        (float) collect($monthlySeries)->max('expenses'),
        (float) collect($monthlySeries)->max('deposits'),
        (float) collect($monthlySeries)->max('deposit_refunds')
    );
    $propertyScale = max(
        1,
        (float) $propertyMetrics->max('rent_billed'),
        (float) $propertyMetrics->max('rent_collected')
    );
    $expenseScale = max(1, (float) $expenseBreakdown->max());
    $depositScale = max(
        1,
        (float) $propertyMetrics->max('deposit_agreed'),
        (float) $propertyMetrics->max('deposit_collected')
    );
    $circleLength = 2 * pi() * 52;
    $occupiedLength = $totalUnits > 0
        ? $circleLength * ($occupiedUnits / $totalUnits)
        : 0;
    $occupancyRate = $totalUnits > 0
        ? round(($occupiedUnits / $totalUnits) * 100, 1)
        : 0;
@endphp

<div class="report-page">
    <section class="page-card">
        <form method="GET" action="{{ route('reports.index') }}" class="report-filter-form">
            <label>
                Month
                <select name="month" class="form-control">
                    <option value="">All months</option>
                    @foreach($monthsForFilter as $number => $label)
                        <option value="{{ $number }}" @selected($month === $number)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label>
                Year
                <select name="year" class="form-control">
                    @foreach($years as $optionYear)
                        <option value="{{ $optionYear }}" @selected($year === $optionYear)>
                            {{ $optionYear }}
                        </option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Reset</a>
        </form>
    </section>

    <section class="report-kpi-grid" aria-label="Summary for {{ $periodLabel }}">
        <article class="page-card report-kpi">
            <div class="report-kpi-heading">
                Rent billed
                <span class="report-kpi-icon"><i class="fas fa-file-invoice-dollar" aria-hidden="true"></i></span>
            </div>
            <strong class="report-kpi-value">KES {{ number_format($totals['rent_billed'], 2) }}</strong>
            <p class="report-kpi-note">{{ $periodLabel }}</p>
        </article>
        <article class="page-card report-kpi">
            <div class="report-kpi-heading">
                Rent collected
                <span class="report-kpi-icon"><i class="fas fa-money-bill-wave" aria-hidden="true"></i></span>
            </div>
            <strong class="report-kpi-value">KES {{ number_format($totals['rent_collected'], 2) }}</strong>
            <p class="report-kpi-note">Approved payments in period</p>
        </article>
        <article class="page-card report-kpi">
            <div class="report-kpi-heading">
                Rent arrears
                <span class="report-kpi-icon"><i class="fas fa-hourglass-half" aria-hidden="true"></i></span>
            </div>
            <strong class="report-kpi-value">KES {{ number_format($rentArrears, 2) }}</strong>
            <p class="report-kpi-note">Outstanding at period end</p>
        </article>
        <article class="page-card report-kpi">
            <div class="report-kpi-heading">
                Operating expenses
                <span class="report-kpi-icon"><i class="fas fa-receipt" aria-hidden="true"></i></span>
            </div>
            <strong class="report-kpi-value">KES {{ number_format($totals['expenses'], 2) }}</strong>
            <p class="report-kpi-note">{{ $periodLabel }}</p>
        </article>
        <article class="page-card report-kpi">
            <div class="report-kpi-heading">
                Deposit receipts
                <span class="report-kpi-icon"><i class="fas fa-piggy-bank" aria-hidden="true"></i></span>
            </div>
            <strong class="report-kpi-value">KES {{ number_format($totals['deposits'], 2) }}</strong>
            <p class="report-kpi-note">Received in period</p>
        </article>
        <article class="page-card report-kpi">
            <div class="report-kpi-heading">
                Deposit refunds
                <span class="report-kpi-icon"><i class="fas fa-arrow-rotate-left" aria-hidden="true"></i></span>
            </div>
            <strong class="report-kpi-value">KES {{ number_format($totals['deposit_refunds'], 2) }}</strong>
            <p class="report-kpi-note">Paid in period</p>
        </article>
        <article class="page-card report-kpi">
            <div class="report-kpi-heading">
                Active tenants
                <span class="report-kpi-icon"><i class="fas fa-users" aria-hidden="true"></i></span>
            </div>
            <strong class="report-kpi-value">{{ number_format($activeTenantCount) }}</strong>
            <p class="report-kpi-note">At period end</p>
        </article>
    </section>

    <section class="report-chart-grid">
        <article class="page-card report-chart-card report-chart-card-wide">
            <div class="report-chart-heading">
                <div>
                    <h2>Monthly cash activity</h2>
                    <p>Rent billed, rent collected, expenses and deposit receipts · {{ $periodLabel }}</p>
                </div>
            </div>

            @if(collect($monthlySeries)->sum('rent_billed') +
                collect($monthlySeries)->sum('rent_collected') +
                collect($monthlySeries)->sum('expenses') +
                collect($monthlySeries)->sum('deposits') +
                collect($monthlySeries)->sum('deposit_refunds') > 0)
                <div class="report-month-chart {{ count($monthlySeries) === 1 ? 'single-month' : '' }}">
                    @foreach($monthlySeries as $monthData)
                        <div class="report-month-column">
                            <div class="report-month-bars"
                                 role="img"
                                 aria-label="{{ $monthData['label'] }}: rent billed KES {{ number_format($monthData['rent_billed'], 2) }}, rent collected KES {{ number_format($monthData['rent_collected'], 2) }}, expenses KES {{ number_format($monthData['expenses'], 2) }}, deposit receipts KES {{ number_format($monthData['deposits'], 2) }}, deposit refunds KES {{ number_format($monthData['deposit_refunds'], 2) }}">
                                <span class="report-month-bar report-bar-billed"
                                      title="Rent billed: KES {{ number_format($monthData['rent_billed'], 2) }}"
                                      style="height:{{ $monthData['rent_billed'] > 0 ? max(($monthData['rent_billed'] / $cashflowMax) * 100, 2) : 0 }}%"></span>
                                <span class="report-month-bar report-bar-collected"
                                      title="Rent collected: KES {{ number_format($monthData['rent_collected'], 2) }}"
                                      style="height:{{ $monthData['rent_collected'] > 0 ? max(($monthData['rent_collected'] / $cashflowMax) * 100, 2) : 0 }}%"></span>
                                <span class="report-month-bar report-bar-expenses"
                                      title="Expenses: KES {{ number_format($monthData['expenses'], 2) }}"
                                      style="height:{{ $monthData['expenses'] > 0 ? max(($monthData['expenses'] / $cashflowMax) * 100, 2) : 0 }}%"></span>
                                <span class="report-month-bar report-bar-deposits"
                                      title="Deposit receipts: KES {{ number_format($monthData['deposits'], 2) }}"
                                      style="height:{{ $monthData['deposits'] > 0 ? max(($monthData['deposits'] / $cashflowMax) * 100, 2) : 0 }}%"></span>
                                <span class="report-month-bar report-bar-refunds"
                                      title="Deposit refunds: KES {{ number_format($monthData['deposit_refunds'], 2) }}"
                                      style="height:{{ $monthData['deposit_refunds'] > 0 ? max(($monthData['deposit_refunds'] / $cashflowMax) * 100, 2) : 0 }}%"></span>
                            </div>
                            <span class="report-month-label">{{ $monthData['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="report-legend">
                    <span class="report-legend-item"><i class="report-legend-dot report-bar-billed"></i>Rent billed</span>
                    <span class="report-legend-item"><i class="report-legend-dot report-bar-collected"></i>Rent collected</span>
                    <span class="report-legend-item"><i class="report-legend-dot report-bar-expenses"></i>Expenses</span>
                    <span class="report-legend-item"><i class="report-legend-dot report-bar-deposits"></i>Deposit receipts</span>
                    <span class="report-legend-item"><i class="report-legend-dot report-bar-refunds"></i>Deposit refunds</span>
                </div>
            @else
                <div class="report-empty">No cash activity was recorded for {{ $periodLabel }}.</div>
            @endif
        </article>

        <article class="page-card report-chart-card">
            <div class="report-chart-heading">
                <div>
                    <h2>Rent performance by property</h2>
                    <p>Rent charged and received during {{ $periodLabel }}.</p>
                </div>
            </div>

            @if($propertyMetrics->isNotEmpty())
                <div class="report-horizontal-list">
                    @foreach($propertyMetrics as $property)
                        @php
                            $billedWidth = min(100, ($property['rent_billed'] / $propertyScale) * 100);
                            $collectedWidth = min(100, ($property['rent_collected'] / $propertyScale) * 100);
                        @endphp
                        <div class="report-horizontal-item">
                            <div class="report-horizontal-heading">
                                <span>{{ $property['name'] }}</span>
                                <span>Arrears KES {{ number_format($property['arrears'], 2) }}</span>
                            </div>
                            <div class="report-meter-row">
                                <span>Rent billed</span>
                                <div class="report-meter-track"><div class="report-meter-fill" style="width:{{ $billedWidth }}%"></div></div>
                                <strong>KES {{ number_format($property['rent_billed'], 0) }}</strong>
                            </div>
                            <div class="report-meter-row">
                                <span>Collected</span>
                                <div class="report-meter-track"><div class="report-meter-fill blue" style="width:{{ $collectedWidth }}%"></div></div>
                                <strong>KES {{ number_format($property['rent_collected'], 0) }}</strong>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="report-empty">Add a property to see its rent performance.</div>
            @endif
        </article>

        <article class="page-card report-chart-card">
            <div class="report-chart-heading">
                <div>
                    <h2>Expenses by category</h2>
                    <p>Operating costs recorded during {{ $periodLabel }}.</p>
                </div>
            </div>

            @if($expenseBreakdown->isNotEmpty())
                <div class="report-horizontal-list">
                    @foreach($expenseBreakdown as $category => $amount)
                        <div class="report-horizontal-item">
                            <div class="report-horizontal-heading">
                                <span>{{ $category }}</span>
                                <span>KES {{ number_format($amount, 2) }}</span>
                            </div>
                            <div class="report-meter-track">
                                <div class="report-meter-fill purple"
                                     style="width:{{ min(100, ($amount / $expenseScale) * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="report-empty">No expenses were recorded for {{ $periodLabel }}.</div>
            @endif
        </article>

        <article class="page-card report-chart-card">
            <div class="report-chart-heading">
                <div>
                    <h2>Deposit position by property</h2>
                    <p>Agreed deposit compared with receipts recorded by period end.</p>
                </div>
            </div>

            @if($propertyMetrics->sum('deposit_agreed') > 0)
                <div class="report-horizontal-list">
                    @foreach($propertyMetrics->filter(fn ($property) => $property['deposit_agreed'] > 0) as $property)
                        <div class="report-horizontal-item">
                            <div class="report-horizontal-heading">
                                <span>{{ $property['name'] }}</span>
                                <span>Uncollected KES {{ number_format($property['deposit_balance'], 2) }}</span>
                            </div>
                            <div class="report-meter-row">
                                <span>Agreed</span>
                                <div class="report-meter-track"><div class="report-meter-fill amber" style="width:{{ min(100, ($property['deposit_agreed'] / $depositScale) * 100) }}%"></div></div>
                                <strong>KES {{ number_format($property['deposit_agreed'], 0) }}</strong>
                            </div>
                            <div class="report-meter-row">
                                <span>Collected</span>
                                <div class="report-meter-track"><div class="report-meter-fill" style="width:{{ min(100, ($property['deposit_collected'] / $depositScale) * 100) }}%"></div></div>
                                <strong>KES {{ number_format($property['deposit_collected'], 0) }}</strong>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="report-empty">No deposit agreements were recorded by period end.</div>
            @endif
        </article>

        <article class="page-card report-chart-card">
            <div class="report-chart-heading">
                <div>
                    <h2>Portfolio occupancy</h2>
                    <p>Tenancy status at the end of {{ $periodLabel }}.</p>
                </div>
            </div>

            <div class="report-occupancy">
                <svg viewBox="0 0 160 160" role="img"
                     aria-label="{{ $occupiedUnits }} of {{ $totalUnits }} units occupied, {{ $availableUnits }} without a tenancy">
                    <circle cx="80" cy="80" r="52" fill="none" stroke="rgba(90, 120, 101, .15)" stroke-width="18"></circle>
                    @if($occupiedUnits > 0)
                        <circle cx="80" cy="80" r="52" fill="none" stroke="#238060" stroke-width="18"
                                stroke-dasharray="{{ $occupiedLength }} {{ $circleLength - $occupiedLength }}"
                                stroke-linecap="round" transform="rotate(-90 80 80)"></circle>
                    @endif
                    <text x="80" y="77" text-anchor="middle" fill="#20392e" font-size="25" font-weight="700">
                        {{ $occupancyRate }}%
                    </text>
                    <text x="80" y="98" text-anchor="middle" fill="#718078" font-size="10">occupied</text>
                </svg>
                <div class="report-occupancy-legend">
                    <div><i class="report-legend-dot report-bar-billed"></i><span>Occupied units</span><strong>{{ $occupiedUnits }}</strong></div>
                    <div><i class="report-legend-dot" style="background:rgba(90, 120, 101, .2)"></i><span>No tenancy</span><strong>{{ $availableUnits }}</strong></div>
                    <div><span>Total units</span><strong>{{ $totalUnits }}</strong></div>
                </div>
            </div>
        </article>

        <article class="page-card report-chart-card">
            <div class="report-chart-heading">
                <div>
                    <h2>Tenancy activity</h2>
                    <p>Portfolio status at or during {{ $periodLabel }}.</p>
                </div>
            </div>
            <div class="report-activity-grid">
                <div class="report-activity">
                    <span>Active at period end</span>
                    <strong>{{ $activeAtPeriodEnd->count() }}</strong>
                </div>
                <div class="report-activity">
                    <span>Upcoming after period end</span>
                    <strong>{{ $upcomingTenancies }}</strong>
                </div>
                <div class="report-activity">
                    <span>Vacated during period</span>
                    <strong>{{ $vacatedDuringPeriod }}</strong>
                </div>
            </div>
            <p class="report-kpi-note" style="margin-top: 14px;">
                Deposit balances at period end: agreed KES {{ number_format($depositAgreed, 2) }},
                received KES {{ number_format($depositCollectedToDate, 2) }},
                uncollected KES {{ number_format($depositBalance, 2) }}.
            </p>
        </article>
    </section>
</div>
@endsection
