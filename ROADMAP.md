# Pango product roadmap

## Delivered in this release

- [x] Show rent-charge status and agreed due dates, including overdue balances.
- [x] Track deposit refunds through reconciliation, show pending/refunded status, and include completed refunds in reports.
- [x] Let landlords advertise properties with photos; publish only listings approved by an administrator.
- [x] Add a protected administrator portal for subscriptions, subscription extensions, landlord approvals, property-listing moderation, portfolio counts, and analytics.
- [x] Add a public, place-filterable vacancy marketplace with standalone admin posts and grouped landlord unit vacancies.
- [x] Automatically hide and unpublish landlord vacancy posts when a unit becomes occupied or otherwise unavailable; allow manual unpublishing.

## Next priorities

1. Gather landlord and renter feedback on the vacancy listing flow.
2. Prioritize follow-up improvements based on marketplace usage and feedback.

## Operational notes

- Run migrations with `php artisan migrate`.
- Run `php artisan storage:link` to serve approved listing photos from local public storage.
- An administrator account is required to approve landlord accounts and property listings.
- Create or promote an administrator with `php artisan admin:create admin@example.com`.
- Keep this roadmap current as future product work is prioritized.
