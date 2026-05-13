# AGENTS.md - ksf_FA_Subscriptions#

## Architecture Overview#

**FA Module** for Subscription Management - recurring billing, renewals, and customer portal.

### Core Principles#
- **SOLID**, **DRY**, **TDD**, **DI**, **SRP**#

## Repository Structure#

```
ksf_FA_Subscriptions/
├── sql/#
│   ├── fa_subscriptions.sql#
│   ├── fa_subscription_renewals.sql#
│   └── fa_subscription_payments.sql#
├── includes/#
│   ├── subscriptions_db.inc#
│   ├── renewals_db.inc#
│   └── payments_db.inc#
├── pages/#
├── hooks.php#
├── composer.json#
└── ProjectDocs/#
```

## Dependencies#

- **ksf_FA_Subscriptions_Core** (business logic)#
- **ksf_FA_CRM** (link to customers)#
- **ksf_FA_Wallet** (subscription payments)#
- **FrontAccounting 2.4+**#
