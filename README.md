# OJS 3.3 Role Synchronizer Plugin

This OJS 3.3 plugin allows journal managers to synchronize user roles between different journals (contexts) on the same installation. It provides a UI to copy selected roles from a source journal to the current one, simplifying user management in multi-journal setups. **This is particularly useful for publishers managing multiple journals, as it provides a solution for the common need to assign reviewers and other users to the same roles across several journals.** This version includes fixes for multilingual support and enhanced security against common web vulnerabilities.

## Features

-   Copy user roles from one journal to another.
-   Preserve existing roles; only add missing roles to the target journal.
-   **Enhanced security measures:**
    -   CSRF (Cross-Site Request Forgery) protection.
    -   Secure query structure to prevent SQL Injection attacks.
-   Multilingual support (Turkish and English) with a fallback translation mechanism for missing keys.

## Installation

### Method 1: Via the Plugin Gallery (Recommended)

1.  Structure the plugin files in the following directory:

    ```
    roleSynchronizer/
    ├── RoleSynchronizerPlugin.php
    ├── RoleSynchronizerHandler.inc.php
    ├── index.php
    ├── version.xml
    └── locale/
        ├── tr_TR/
        │   └── locale.po
        └── en_US/
            └── locale.po
    ```

2.  Compress the folder in `tar.gz` format:
    ```bash
    tar -czf roleSynchronizer.tar.gz roleSynchronizer/
    ```

3.  Log in to the OJS administration panel.
4.  Navigate to **Settings > Website > Plugins > Upload a New Plugin**.
5.  Upload the `roleSynchronizer.tar.gz` file.
6.  Enable the plugin.

### Method 2: Manual Installation

1.  Copy the plugin files to the `/plugins/generic/roleSynchronizer/` directory.
2.  Check file permissions (755 for folders, 644 for files).
3.  Enable the plugin from the administration panel.

## Usage

1.  Log in to the target journal where roles will be copied.
2.  Navigate to **Settings > Website > Plugins > Generic Plugins**.
3.  Find the **Role Synchronizer** plugin.
4.  Click the **Settings** button.
5.  In the pop-up window, select the source journal and the roles you want to synchronize.
6.  Click the **Synchronize** button.

## Important Notes

-   ⚠️ **The operation is irreversible!** Always back up your database before running the synchronization.
-   Existing roles are not modified; only missing roles are added to users in the target journal.
-   A single user can have multiple roles.
-   Only Journal Manager and Site Admin roles can use this plugin.

## Security Enhancements

-   **CSRF Protection:** Synchronization requests are validated using OJS's built-in CSRF token mechanism, preventing unauthorized requests.
-   **SQL Injection Protection:** The database queries use a parameterized query structure, preventing user input from being directly embedded into the query.

## Technical Details

-   **Supported OJS Version**: 3.3.x
-   **Database Changes**: None
-   **Language Support**: English, Turkish

## Troubleshooting

### Plugin is not loading
-   Check file permissions.
-   Check the OJS log files.
-   Verify that the plugin folder structure is correct.

### Synchronization is not working
-   Check if the same roles exist in both the source and target journals.
-   Check the PHP error logs.
-   Verify that the user has sufficient permissions.

### CSRF error
-   Refresh the page and try again.
-   Clear your browser's cache.

## Support

For issues related to the plugin:
-   Check OJS log files.
-   Review PHP error logs.
-   Check the database connection.

## License

This plugin is distributed under the GPL v3 license.
