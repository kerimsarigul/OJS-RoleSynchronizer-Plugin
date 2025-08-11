[Türkçe README için buraya tıklayın.](README.tr.md)

# OJS 3.3 Role Synchronizer Plugin

This OJS 3.3 plugin allows journal managers to synchronize user roles between different journals (contexts) on the same installation. It provides a UI to copy selected roles from a source journal to the current one, simplifying user management in multi-journal setups. **This is particularly useful for publishers managing multiple journals, as it provides a solution for the common need to assign reviewers and other users to the same roles across several journals.** This version includes fixes for multilingual support and enhanced security against common web vulnerabilities.

## Features

-   Copy user roles from one journal to another.
-   Preserve existing roles; only add missing roles to the target journal.
-   **Enhanced security measures:**
    -   CSRF (Cross-Site Request Forgery) protection.
    -   Secure query structure to prevent SQL Injection attacks.
-   Multilingual support (Turkish and English) with a fallback translation mechanism for missing keys.

<img width="960" height="641" alt="rolesynchronizerplugin" src="https://github.com/user-attachments/assets/a18f0034-a6f0-4128-b5c5-5b135ee59476" />

## Installation

### Method 1: Via the Plugin Gallery (Recommended)

1.  Download the latest `roleSynchronizer.tar.gz` file from the **[Releases](https://github.com/kerimsarigul/OJS-RoleSynchronizer-Plugin/releases)** page of this repository.

2.  Log in to the OJS administration panel.
3.  Navigate to **Settings > Website > Plugins > Upload a New Plugin**.
4.  Upload the `roleSynchronizer.tar.gz` file.
5.  Enable the plugin.

### Method 2: Manual Installation (From Source Code)

1.  Clone or download the source code from this repository.
2.  Place the `roleSynchronizer/` folder inside the `/plugins/generic/` directory of your OJS installation.
3.  Check file permissions (755 for folders, 644 for files).
4.  Enable the plugin from the administration panel.

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

For issues, bug reports, or feature requests related to the plugin, please contact the maintainer below:

-   **Maintainer:** Kerim SARIGÜL
-   **Email:** kerim@kerimsarigul.com
-   **GitHub:** [OJS-RoleSynchronizer-Plugin](https://github.com/kerimsarigul/OJS-RoleSynchronizer-Plugin/)

## License

This plugin is distributed under the GPL v3 license.
