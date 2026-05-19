---
name: laravel-i18n
description: |
    Manage Laravel internationalization (i18n) translations for pages or features. Creates new translation files in `resources/lang/{locale}/`, updates blade views to use the new namespace, and removes duplicate keys from `app.php`. Use when asked to add translations for a page, create language files, or manage i18n for a specific feature. Keywords: i18n, translations, lang, locale, language, bilingual, Indonesian, English, the-i18n-mcp, MCP server.
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
    - Use sed via CLI for reliability

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

# The i18n MCP Server Tools

This project uses `the-i18n-mcp` server for translation management.

## Available Tools

| Tool                | Purpose                            | Notes                         |
| ------------------- | ---------------------------------- | ----------------------------- |
| `list_locale_dirs`  | Lists available locale directories | May fail if directories empty |
| `get_translations`  | Retrieves translations by keys     | Needs existing PHP files      |
| `add_translations`  | Adds translations to files         | May skip keys if existing     |
| `sync_translations` | Syncs translations between locales | Useful for bulk updates       |

## Common Issues & Solutions

### Issue: "No locale subdirectories found"

**Cause**: Tool expects `lang/{locale}/` structure, but Laravel uses `resources/lang/{locale}/`
**Solution**: Use `mcp--filesystem` tools directly instead of i18n MCP

### Issue: `add_translations` skips all keys

**Cause**: Tool may treat keys as existing even in new files
**Solution**: Create files manually with `mcp--filesystem--write_file`, then use `add_translations` only for incremental additions

### Issue: `get_translations` fails on empty directories

**Cause**: Directory exists but has no PHP files
**Solution**: Check with `directory_tree` first, then create files before attempting get

### Issue: `list_locale_dirs` returns empty despite files existing

**Cause**: MCP server has caching issues or path resolution problems
**Solution**: Use `mcp--filesystem--directory_tree` for reliable structure checking

## Workaround Strategy (Primary Method)

When MCP i18n tools fail (which is common), use this reliable approach:

1. **Check structure**:

    ```
    mcp--filesystem--directory_tree path=resources/lang
    ```

2. **Create translation files**:

    ```
    mcp--filesystem--write_file path=resources/lang/{locale}/{file}.php content=<?php return [...]; ?>
    ```

3. **Read translation files**:

    ```
    mcp--filesystem--read_text_file path=resources/lang/{locale}/{file}.php
    ```

4. **Bulk update blade files** (use CLI `sed`):

    ```bash
    sed -i 's/__("app\./__("page-name./g' resources/views/**/*.blade.php
    ```

5. **Verify changes**:
    ```bash
    grep -r '__("app\.' resources/views/{category}/{page}/
    ```

## MCP Tools Quick Reference

```bash
# Check locale structure (filesystem MCP)
mcp--filesystem--directory_tree path=resources/lang

# Create translation file
mcp--filesystem--write_file path=resources/lang/{locale}/{file}.php content="<?php\n\nreturn [\n    // keys here\n];"

# Read translation file
mcp--filesystem--read_text_file path=resources/lang/{locale}/{file}.php

# Bulk update blade files (CLI - most reliable)
sed -i 's/__("app\./__("namespace./g' resources/views/**/*.blade.php

# Verify no old references remain
grep -r '__("app\.' resources/views/{category}/{page}/
```

# Example

**Task**: Add translations for the "laporan-peninggalan" page

**Steps**:

1. Read blade files in `resources/views/guest/laporan-peninggalan/`
2. Extract 23 translation keys from `__("app.*")` calls
3. Create `resources/lang/en/laporan-peninggalan.php` and `resources/lang/id/laporan-peninggalan.php`
4. Update blade files: `sed -i "s/__(\"app\./__(\"laporan-peninggalan./g" *.blade.php`
5. Remove duplicate keys from `app.php`
6. Verify with `grep` that no `__("app.` calls remain in the page folder

# Key Lessons Learned

1. **`the-i18n-mcp` is unreliable** - Always have `mcp--filesystem` as fallback
2. **Laravel uses `resources/lang/`** - Not `lang/` at project root
3. **`sed` CLI is most reliable** for bulk blade file replacements
4. **Create files before using `add_translations`** to avoid skip behavior
5. **Verify after every change** - use grep to confirm replacements
