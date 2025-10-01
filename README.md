# Sync Roles Civi Bridge

This Drupal custom module keeps a CiviCRM custom field in sync with the roles assigned to Drupal users. It supports both real-time updates (when a user's roles change) and a manual batch process for resynchronising all users.

## Features

- Detects role changes from any source (UI edits, Drush, automation modules such as Chargebee integrations) and immediately updates the configured CiviCRM custom field.
- Resolves the custom field identifier automatically whether you provide `custom_N`, the numeric field ID, or the field machine name.
- Maps Drupal role machine names to CiviCRM option values using fuzzy matching against option labels, names, or values.
- Supports both option-based multi-select fields and free-text fields. Multi-select fields receive associative arrays keyed by option value as expected by the CiviCRM API.
- Includes an administrative batch action to resynchronise all accounts.

## Configuration

1. Enable the module and visit **Configuration → People → Sync Roles CiviCRM Bridge** (`/admin/config/people/sync-roles-bridge`).
2. Enter the identifier of the CiviCRM custom field that should store Drupal roles. You can use:
   - The column name (for example, `custom_64`)
   - The numeric field ID (e.g. `64`)
   - The field machine name
3. Click **Save configuration**.
4. Optionally run **Sync All Users** to push existing accounts to CiviCRM.

The module will automatically ignore the core `authenticated` role. If you need to exclude additional roles, adjust `sync_roles_bridge_sync_to_civicrm()` accordingly.

## Requirements

- Drupal core 8 or later
- CiviCRM for Drupal (`drupal/civicrm`)
- CiviCRM Entity module (`drupal/civicrm_entity`)

## Batch Sync

The configuration form exposes a **Sync All Users** button. This launches a Drupal batch process that loads each user entity and invokes the same synchronisation code used for real-time updates.

## Logging & Troubleshooting

All activity is written to the `sync_roles_civi_bridge` log channel. Key entries include:

- Detected role changes and the full parameter set sent to the CiviCRM API
- Contact lookup failures
- Unmapped Drupal roles when no matching CiviCRM option value is found

If CiviCRM rejects updates, review `/sites/default/files/civicrm/ConfigAndLog/*.log` for database-level errors (for example, triggers with missing definers in local environments).

## Development

```
composer install
lando drush en sync_roles_civi_bridge
```

When developing locally, ensure CiviCRM is initialised before invoking Drush commands that rely on the API.

## License

GPL-2.0-or-later. See `composer.json` for details.
