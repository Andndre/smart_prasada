---
name: laravel-i18n
description: |
    Manage Laravel internationalization (i18n) translations for pages or features. Creates new translation files in `resources/lang/{locale}/`, updates blade views to use the new namespace, and removes duplicate keys from `app.php`. Use when asked to add translations for a page, create language files, or manage i18n for a specific feature. Keywords: i18n, translations, lang, locale, language, bilingual, Indonesian, English.
---

# When to use

Use this skill when:

- User asks to create translations for a specific page or feature
- User asks to add language files for a page in `resources/lang/`
- User asks to manage translations using the i18n MCP tool
- User asks to move existing translations from `app.php` to a separate file

# When NOT to use

- For general Laravel i18n setup or configuration
- For creating new locales (supported locales already exist: en, id)
- When user explicitly wants all translations in `app.php` instead of separate files

# Inputs required

- Page or feature name to create translations for
- List of translation keys needed (or blade files to analyze)
- Target locales (default: en and id)

# Workflow

1. **Analyze blade files**
    - Read all blade files in the target page folder
    - Identify all `__("app.key")` translation calls
    - Extract unique translation keys

2. **Check existing translations**
    - Read `resources/lang/en/app.php` and `resources/lang/id/app.php`
    - Identify if keys already exist in `app.php`
    - List keys that need to be created vs moved

3. **Create translation file (if keys don't exist yet)**
    - Create `resources/lang/{locale}/{page-name}.php`
    - Add all translation keys with values for each locale
    - Use descriptive key names matching the feature/page context

4. **Update blade views**
    - Replace `__("app.key")` with `__("{page-name}.key")` in all blade files
    - Use sed or direct file editing for reliability

5. **Remove duplicates from app.php**
    - If keys were moved from `app.php`, remove them from both locale files
    - Clean up any empty lines left behind

6. **Verify**
    - Confirm blade files use the new namespace exclusively
    - Confirm `app.php` no longer contains the moved keys
    - Confirm translation files exist and are valid PHP arrays

# Files

- Translation files: `resources/lang/{locale}/{page-name}.php`
- Blade views: `resources/views/{category}/{page}/**/*.blade.php`
- Main translations: `resources/lang/{locale}/app.php`

# MCP i18n Tool Notes

The `the-i18n-mcp` server may report errors if:

- Locale directories don't exist yet (use `list_locale_dirs` to check)
- Keys already exist (use `add_translations` instead of creating new files)
- Translations are in the wrong location

**Workaround**: When MCP tools fail or behave unexpectedly:

1. Use `mcp--filesystem` tools directly for file operations
2. Create translation files manually via `write_file`
3. Use CLI commands (`sed`) for bulk replacements in blade files

# Example

**Task**: Add translations for the "laporan-peninggalan" page

**Steps**:

1. Read blade files in `resources/views/guest/laporan-peninggalan/`
2. Extract 23 translation keys from `__("app.*")` calls
3. Create `resources/lang/en/laporan-peninggalan.php` and `resources/lang/id/laporan-peninggalan.php`
4. Update blade files: `sed -i "s/__(\"app\./__(\"laporan-peninggalan./g" *.blade.php`
5. Remove duplicate keys from `app.php`
6. Verify with grep that no `__("app.` calls remain in the page folder
