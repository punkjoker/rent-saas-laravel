
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pango | Smart Property Management</title>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #176b55;
            --primary-dark: #105340;
            --accent: #d9f1e6;
            --dark: #14231f;
            --muted: #75827d;
            --light: #f7faf8;
            --white: #ffffff;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            color: var(--dark);
            background: white;
            line-height: 1.6;
        }

        html {
            scroll-behavior: smooth;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        button, input {
            font: inherit;
        }

        .container {
            width: 90%;
            max-width: 1180px;
            margin: auto;
        }

        /* NAVBAR */

        header {
            background: rgba(255,255,255,.96);
            border-bottom: 1px solid #edf1ee;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar {
            height: 78px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'Manrope', sans-serif;
            font-size: 27px;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -1.5px;
        }

        .logo span {
            color: #d2a85c;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 34px;
            font-size: 14px;
            font-weight: 600;
            color: #53615b;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn {
            padding: 12px 21px;
            border-radius: 9px;
            font-weight: 700;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: .2s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-outline {
            border: 1px solid #dce5df;
            background: white;
            color: var(--dark);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* HERO */

        .hero {
            background:
                radial-gradient(circle at 85% 20%, #dff3e8 0, transparent 34%),
                linear-gradient(135deg, #f7faf8, #edf7f1);
            padding: 100px 0 95px;
            overflow: hidden;
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            align-items: center;
            gap: 60px;
        }

        .eyebrow {
            display: inline-block;
            padding: 8px 14px;
            background: #e1f1e8;
            color: var(--primary);
            border-radius: 30px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .7px;
            text-transform: uppercase;
            margin-bottom: 23px;
        }

        .hero h1 {
            font-family: 'Manrope', sans-serif;
            font-size: clamp(40px, 5vw, 65px);
            line-height: 1.12;
            letter-spacing: -2.5px;
            margin-bottom: 23px;
        }

        .hero h1 span {
            color: var(--primary);
        }

        .hero p {
            color: #68766f;
            font-size: 17px;
            max-width: 560px;
            margin-bottom: 32px;
        }

        .hero-buttons {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .hero-note {
            margin-top: 24px;
            font-size: 13px;
            color: var(--muted);
        }

        .hero-visual {
            background: white;
            padding: 22px;
            border-radius: 22px;
            box-shadow: 0 25px 65px rgba(28, 74, 53, .12);
            border: 1px solid #e8f0eb;
        }

        .visual-top {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            align-items: center;
        }

        .visual-title {
            font-weight: 800;
            font-size: 16px;
        }

        .live {
            color: var(--primary);
            background: #e5f5eb;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .stat {
            padding: 19px;
            background: #f7faf8;
            border-radius: 13px;
        }

        .stat small {
            color: var(--muted);
            font-size: 12px;
        }

        .stat h3 {
            margin-top: 7px;
            font-size: 23px;
            font-weight: 800;
        }

        .visual-bottom {
            margin-top: 17px;
            padding: 18px;
            background: #eaf5ed;
            border-radius: 12px;
            color: var(--primary);
        }

        .visual-bottom strong {
            display: block;
            font-size: 14px;
        }

        .visual-bottom small {
            font-size: 12px;
        }

        /* FEATURES */

        .section {
            padding: 90px 0;
        }

        .section-heading {
            text-align: center;
            max-width: 650px;
            margin: 0 auto 48px;
        }

        .section-heading h2 {
            font-family: 'Manrope', sans-serif;
            font-size: 36px;
            letter-spacing: -1.3px;
            margin-bottom: 12px;
        }

        .section-heading p {
            color: var(--muted);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

        .feature {
            padding: 30px;
            border: 1px solid #e9efeb;
            border-radius: 16px;
            transition: .2s;
        }

        .feature:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,.05);
        }

        .feature-icon {
            width: 49px;
            height: 49px;
            display: grid;
            place-items: center;
            background: #e6f4eb;
            color: var(--primary);
            border-radius: 13px;
            font-size: 22px;
            margin-bottom: 20px;
        }

        .feature h3 {
            font-size: 17px;
            margin-bottom: 10px;
        }

        .feature p {
            color: var(--muted);
            font-size: 14px;
        }

        /* AUTH SECTION */

        .auth-section {
            background: #f6faf7;
            padding: 95px 0;
        }

        .auth-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
            max-width: 1000px;
            margin: auto;
            align-items: start;
        }

        .auth-card {
            background: white;
            padding: 34px;
            border: 1px solid #e7eee9;
            border-radius: 17px;
            box-shadow: 0 10px 35px rgba(0,0,0,.035);
        }

        .auth-card h2 {
            font-family: 'Manrope', sans-serif;
            font-size: 25px;
            margin-bottom: 7px;
        }

        .auth-card .subheading {
            color: var(--muted);
            font-size: 13px;
            margin-bottom: 25px;
        }

        .trial-label {
            display: inline-block;
            background: #e3f4e9;
            color: var(--primary);
            font-size: 11px;
            font-weight: 800;
            padding: 6px 10px;
            border-radius: 20px;
            margin-bottom: 16px;
        }

        .form-group {
            margin-bottom: 17px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 7px;
        }

        .form-control {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #dfe7e1;
            border-radius: 8px;
            outline: none;
            background: #fff;
            font-size: 14px;
            transition: .2s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(23,107,85,.09);
        }

        .form-control.is-invalid {
            border-color: #dc5555;
        }

        .form-error {
            color: #dc4545;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }

        .form-submit {
            width: 100%;
            margin-top: 8px;
            padding: 14px;
        }


        /* PRICING SECTION */
        .pricing-section { padding: 90px 0; background: #f7faf8; }
        .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; align-items: stretch; }
        .pricing-card { position: relative; background: #fff; border: 1px solid #e2ebe5; border-radius: 16px; padding: 27px 23px; display: flex; flex-direction: column; box-shadow: 0 8px 25px rgba(20,35,31,.035); }
        .pricing-card.popular { border: 2px solid var(--primary); box-shadow: 0 15px 35px rgba(23,107,85,.10); }
        .popular-tag { position: absolute; top: -13px; right: 18px; background: var(--primary); color: #fff; border-radius: 20px; padding: 5px 12px; font-size: 10px; font-weight: 800; letter-spacing: .4px; }
        .pricing-card h3 { font-size: 19px; margin-bottom: 8px; }
        .pricing-price { color: var(--primary); font-size: 29px; font-weight: 800; letter-spacing: -1px; margin: 9px 0 2px; }
        .pricing-price small { color: var(--muted); font-size: 12px; font-weight: 500; letter-spacing: 0; }
        .pricing-limit { color: var(--muted); font-size: 13px; margin-bottom: 20px; }
        .pricing-features { list-style: none; margin: 0 0 24px; padding: 0; flex: 1; }
        .pricing-features li { font-size: 13px; color: #53615b; padding: 7px 0; border-bottom: 1px solid #f0f3f1; }
        .pricing-features li::before { content: '✓'; color: var(--primary); font-weight: 800; margin-right: 8px; }
        .pricing-card .btn { width: 100%; }
        .pricing-footnote { text-align: center; color: var(--muted); font-size: 13px; margin-top: 25px; }
        .selected-plan-summary { background: #edf7f1; color: var(--primary-dark); padding: 12px 14px; border-radius: 9px; font-size: 13px; margin: 0 0 20px; }
        @media(max-width: 650px) { .pricing-section { padding: 65px 0; } }

        .auth-note {
            font-size: 12px;
            text-align: center;
            color: var(--muted);
            margin-top: 17px;
        }

        .remember-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            margin-bottom: 15px;
        }

        .remember-row label {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        /* ALERTS */

        .alert {
            max-width: 1000px;
            margin: 0 auto 25px;
            padding: 14px 18px;
            border-radius: 9px;
            font-size: 14px;
        }

        .alert-success {
            background: #e5f5eb;
            color: #176b55;
        }

        .alert-error {
            background: #fff0f0;
            color: #bd3434;
        }

        /* FOOTER */

        footer {
            background: #14231f;
            color: white;
            padding: 40px 0;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        footer p {
            color: #a6b5ac;
            font-size: 13px;
        }

        footer .logo {
            color: white;
        }

        /* RESPONSIVE */

        @media(max-width: 850px) {
            .hero-content {
                grid-template-columns: 1fr;
                gap: 45px;
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .nav-links {
                display: none;
            }
        }

        @media(max-width: 650px) {
            .auth-grid {
                grid-template-columns: 1fr;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .hero {
                padding: 65px 0;
            }

            .section, .auth-section {
                padding: 65px 0;
            }

            .section-heading h2 {
                font-size: 29px;
            }

            .auth-card {
                padding: 25px 20px;
            }

            .nav-actions .btn {
                padding: 10px 12px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>

<header>
    <div class="container navbar">

        <a href="{{ route('home') }}" class="logo">
            PANGO<span>.</span>
        </a>

        <nav class="nav-links">
            <a href="#features">Features</a>
            <a href="#pricing">Pricing</a>
            <a href="#about">About</a>
            <a href="#register">Get Started</a>
        </nav>

        <div class="nav-actions">
            <a href="#login" class="btn btn-outline">Sign In</a>
            <a href="#register" class="btn btn-primary">Start Free Trial</a>
        </div>

    </div>
</header>


<!-- HERO SECTION -->

<section class="hero" id="about">

    <div class="container hero-content">

        <div>

            <span class="eyebrow">Property management made simple</span>

            <h1>
                Manage your properties.
                <span>Grow your income.</span>
            </h1>

            <p>
                Take control of your rental business with one smart platform.
                Manage tenants, track rent, monitor deposits, handle monthly
                bills and generate reports without the paperwork.
            </p>

            <div class="hero-buttons">
                <a href="#register" class="btn btn-primary">
                    Start Your 14-Day Free Trial →
                </a>

                <a href="#features" class="btn btn-outline">
                    Explore Features
                </a>
            </div>

            <div class="hero-note">
                ✓ No setup fees &nbsp; ✓ Multiple properties &nbsp;
                ✓ Designed for landlords
            </div>

        </div>


        <div class="hero-visual">

            <div class="visual-top">
                <div class="visual-title">Your Rental Overview</div>
                <span class="live">● Dashboard Preview</span>
            </div>

            <div class="stat-grid">

                <div class="stat">
                    <small>Total Properties</small>
                    <h3>Multiple</h3>
                </div>

                <div class="stat">
                    <small>Tenant Records</small>
                    <h3>Organized</h3>
                </div>

                <div class="stat">
                    <small>Rent Tracking</small>
                    <h3>Monthly</h3>
                </div>

                <div class="stat">
                    <small>Reports</small>
                    <h3>Ready</h3>
                </div>

            </div>

            <div class="visual-bottom">
                <strong>Everything in one place.</strong>
                <small>Stay informed about your rental business.</small>
            </div>

        </div>

    </div>

</section>


<!-- FEATURES SECTION -->

<section class="section" id="features">

    <div class="container">

        <div class="section-heading">

            <span class="eyebrow">Powerful tools</span>

            <h2>Everything your rental business needs</h2>

            <p>
                Spend less time on administration and more time growing
                your property investments.
            </p>

        </div>


        <div class="features-grid">

            <div class="feature">
                <div class="feature-icon">⌂</div>
                <h3>Property Management</h3>
                <p>Manage multiple apartments, buildings and rental units from one account.</p>
            </div>

            <div class="feature">
                <div class="feature-icon">♙</div>
                <h3>Tenant Management</h3>
                <p>Maintain tenant profiles, occupancy records, tenancy details and history.</p>
            </div>

            <div class="feature">
                <div class="feature-icon">▣</div>
                <h3>Rent Collection</h3>
                <p>Record full and partial rent payments, monitor arrears and track balances.</p>
            </div>

            <div class="feature">
                <div class="feature-icon">◈</div>
                <h3>Deposit Tracking</h3>
                <p>Track security deposits, deductions, reconciliations and refunds.</p>
            </div>

            <div class="feature">
                <div class="feature-icon">⚡</div>
                <h3>Monthly Bills</h3>
                <p>Manage water, electricity, garbage and other property charges.</p>
            </div>

            <div class="feature">
                <div class="feature-icon">↗</div>
                <h3>Reports & Receipts</h3>
                <p>Generate financial reports and share tenant receipts securely through links.</p>
            </div>

        </div>

    </div>

</section>


<!-- PRICING SECTION -->
<section class="pricing-section" id="pricing">
    <div class="container">
        <div class="section-heading">
            <span class="eyebrow">Simple, transparent pricing</span>
            <h2>Choose a plan that grows with you</h2>
            <p>Every plan includes unlimited properties. Choose based on the number of active tenants you manage.</p>
        </div>

        <div class="pricing-grid">
            @forelse($plans as $plan)
                <article class="pricing-card {{ $plan->slug === 'professional' ? 'popular' : '' }}">
                    @if($plan->slug === 'professional')
                        <span class="popular-tag">MOST POPULAR</span>
                    @endif
                    <h3>{{ $plan->name }}</h3>
                    <div class="pricing-price">KES {{ number_format((float) $plan->price) }}<small> / month</small></div>
                    <p class="pricing-limit">
                        {{ $plan->tenant_limit === null ? 'Unlimited active tenants' : number_format($plan->tenant_limit) . ' active tenants' }}
                    </p>
                    <ul class="pricing-features">
                        <li>Unlimited properties</li>
                        <li>Tenant and occupancy management</li>
                        <li>Rent and payment tracking</li>
                        <li>Deposits and monthly charges</li>
                        <li>Reports and receipts</li>
                    </ul>
                    <a href="#register"
                       class="btn {{ $plan->slug === 'professional' ? 'btn-primary' : 'btn-outline' }} choose-plan"
                       data-plan-id="{{ $plan->id }}"
                       data-plan-name="{{ $plan->name }}">
                        Choose {{ $plan->name }}
                    </a>
                </article>
            @empty
                <p class="alert alert-error">Pricing plans are currently unavailable. Please try again later.</p>
            @endforelse
        </div>
        <p class="pricing-footnote">Start with a 14-day free trial for up to 20 active tenants. No payment is required during registration.</p>
    </div>
</section>


<!-- AUTHENTICATION SECTION -->

<section class="auth-section" id="register">

    <div class="container">

        <div class="section-heading">

            <span class="eyebrow">Join Pango</span>

            <h2>Take control of your rental business</h2>

            <p>
                Create your account or sign in to manage your properties.
            </p>

        </div>


        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                Please check the form and correct the highlighted errors.
            </div>
        @endif


        <div class="auth-grid">

            <!-- REGISTRATION FORM -->

            <div class="auth-card">

                <span class="trial-label">14 DAYS FREE TRIAL</span>

                <h2>Create an Account</h2>

                <p class="subheading">
                    Register as a landlord and start managing your properties.
                </p>

                <div id="selectedPlanSummary" class="selected-plan-summary">
                    Choose a package from the Pricing section. Your account will first start on the 14-day free trial.
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <input type="hidden" name="selected_plan_id" id="selected_plan_id" value="{{ old('selected_plan_id', optional($plans->first())->id) }}">
                    @error('selected_plan_id')
                        <span class="form-error">{{ $message }}</span>
                    @enderror

                    <div class="form-group">
                        <label>Full Name</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Enter your full name"
                            value="{{ old('name') }}"
                            required
                            autocomplete="name"
                        >
                        @error('name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Business / Property Name</label>
                        <input
                            type="text"
                            name="business_name"
                            class="form-control @error('business_name') is-invalid @enderror"
                            placeholder="e.g. Greenview Apartments"
                            value="{{ old('business_name') }}"
                            required
                        >
                        @error('business_name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input
                            type="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="you@example.com"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                        >
                        @error('email')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input
                            type="tel"
                            name="phone"
                            class="form-control @error('phone') is-invalid @enderror"
                            placeholder="e.g. 0712345678"
                            value="{{ old('phone') }}"
                            required
                            autocomplete="tel"
                        >
                        @error('phone')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input
                            type="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Minimum 8 characters"
                            required
                            autocomplete="new-password"
                        >
                        @error('password')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            class="form-control"
                            placeholder="Confirm your password"
                            required
                            autocomplete="new-password"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary form-submit">
                        Create My Account →
                    </button>

                    <p class="auth-note">
                        By registering, you agree to our terms and privacy policy.
                    </p>

                </form>

            </div>


            <!-- LOGIN FORM -->

            <div class="auth-card" id="login">

                <h2>Welcome Back</h2>

                <p class="subheading">
                    Sign in to access your property management dashboard.
                </p>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label>Email Address</label>
                        <input
                            type="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="Enter your registered email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="username"
                        >
                        @error('email')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                    </div>

                    <div class="remember-row">
                        <label>
                            <input type="checkbox" name="remember" value="1">
                            Remember me
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary form-submit">
                        Sign In →
                    </button>

                    <p class="auth-note">
                        Secure access to your rental management account.
                    </p>

                </form>

            </div>

        </div>

    </div>

</section>


<!-- FOOTER -->

<footer>

    <div class="container footer-content">

        <div>
            <a href="{{ route('home') }}" class="logo">
                PANGO<span>.</span>
            </a>

            <p>Smart property management, made simple.</p>
        </div>

        <p>© {{ date('Y') }} Pango. All rights reserved.</p>

    </div>

</footer>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const planInput = document.getElementById('selected_plan_id');
    const summary = document.getElementById('selectedPlanSummary');
    const planButtons = document.querySelectorAll('.choose-plan');

    function selectPlan(id, name) {
        if (planInput) planInput.value = id;
        if (summary) {
            summary.textContent = name + ' selected. Your account starts with a 14-day free trial for up to 20 active tenants. No payment is taken now.';
        }
    }

    planButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            selectPlan(this.dataset.planId, this.dataset.planName);
        });
    });

    const selectedButton = Array.from(planButtons).find(function (button) {
        return button.dataset.planId === (planInput ? planInput.value : '');
    });
    if (selectedButton) {
        selectPlan(selectedButton.dataset.planId, selectedButton.dataset.planName);
    }
});
</script>

</body>
</html>
