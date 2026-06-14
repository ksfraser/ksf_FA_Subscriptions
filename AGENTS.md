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

## Development Workflow

All development is done in the **devel tree** (`~/Documents/ksf_FA_Subscriptions`). Do **not** edit files in the UAT bind point directly.

### Workflow Steps
1. **Develop** in this repo (feature branches preferred)
2. **Test**: run repo-appropriate tests
3. **Lint**: `php -l` on modified PHP files (no syntax errors)
4. **Commit** and **Push** branch to GitHub
5. **Merge** to `master` when ready
6. **Push** `master` to GitHub
7. **Deploy** to UAT by pulling in the Infrastructure bind point:

   ```
   cd ~/ksf_Infrastructure/fa_modules/ksf_FA_Subscriptions
   git stash -u
   git pull origin master
   git stash pop
   ```

### UAT Bind Point
| Path | Purpose |
|------|---------|
| `~/Documents/ksf_FA_Subscriptions` | Devel tree — all development, testing, commits |
| `~/ksf_Infrastructure/fa_modules/ksf_FA_Subscriptions` | UAT bind point — deployment target, integration testing (if mirrored) |

