# Pango

Pango is a property-management and rental marketplace application built with Laravel. It helps landlords manage their properties and tenancies, track rent and expenses, and publish available properties and accommodation. Administrators manage subscriptions, approvals, and marketplace content.

## Features

### Landlord workspace

- Manage properties, units, tenants, and tenancies.
- Track rent charges, payments, agreed due dates, and overdue balances.
- Reconcile vacated tenancies with arrears and itemized damage costs, then track deposit refunds.
- Record expenses by property and review financial reports and dashboard charts.
- Advertise properties and vacancies with photos; listings can be filtered by place, price, or other available filters.
- Use technical support messaging with the Pango admin team.

### Accommodation marketplace

- Publish accommodation with multiple photos, nightly pricing, and guest details.
- Let visitors search listings and submit date-based booking requests.
- Review booking details and confirm or decline requests from the landlord workspace.
- View accommodation booking and revenue analytics and projections.
- Accommodation features require an eligible plan or active add-on.

### Public marketplace

- Browse approved property advertisements, available rental vacancies, and published accommodation.
- Filter public listings by location and available price filters.
- Contact the team through the public contact form.

### Administrator portal

- Review landlord accounts and approve or reject property listings.
- Manage subscriptions and extend landlord access.
- Manage standalone vacancy listings and review contact messages and landlord support conversations.
- View subscription, marketplace, and accommodation analytics.

## Requirements

- PHP and Composer versions supported by the project dependencies.
- A database supported by Laravel (the application is commonly used with MySQL).
- Node.js and npm if you need to build or modify frontend assets.

## Installation

From the project directory:

1. Install PHP dependencies:

   ```sh
   composer install
   ```

2. Create the local environment file and configure the database connection:

   ```sh
   cp .env.example .env
   ```

   On Windows PowerShell, use `Copy-Item .env.example .env`.

3. Generate an application key, run migrations, and link public uploads:

   ```sh
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   ```

4. Start the development server:

   ```sh
   php artisan serve
   ```

## Administrator account

Create or promote an administrator from the project directory:

```sh
php artisan admin:create admin@example.com
```

The command prompts for the administrator's name and password. The administrator portal is available at `/manage/login`.

## M-Pesa configuration

Daraja STK Push payments require the following environment settings:

```dotenv
MPESA_ENVIRONMENT=sandbox
MPESA_CONSUMER_KEY=
MPESA_CONSUMER_SECRET=
MPESA_SHORTCODE=
MPESA_PASSKEY=
MPESA_CALLBACK_URL=
MPESA_CALLBACK_TOKEN=
```

Use the Safaricom-issued credentials for the selected environment. The callback URL must be publicly reachable over HTTPS, and the callback token should be a strong random secret. Do not commit credentials to source control. Payment renewals are initiated by the landlord; automatic recurring charges are not used.

## Tests

Run the application test suite with:

```sh
php artisan test
```

