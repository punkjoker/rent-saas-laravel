# Pango product roadmap

## Delivered in this release

- [x] Show rent-charge status and agreed due dates, including overdue balances.
- [x] Track deposit refunds through reconciliation, show pending/refunded status, and include completed refunds in reports.
- [x] Let landlords advertise properties with photos; publish only listings approved by an administrator.
- [x] Add a protected administrator portal for subscriptions, subscription extensions, landlord approvals, property-listing moderation, portfolio counts, and analytics.

## Ongoing delivery order

1. Rent due-date status and arrears visibility.
2. Deposit refund recording and reporting.
3. Landlord property advertisements and public approved listings.
4. Administrator authentication, account/listing moderation, subscription extensions, and analytics.

## Operational notes

- Run migrations with `php artisan migrate`.
- Run `php artisan storage:link` to serve approved listing photos from local public storage.
- An administrator account is required to approve landlord accounts and property listings.
- Create or promote an administrator with `php artisan admin:create admin@example.com`.
- Keep this roadmap current as future product work is prioritized.
