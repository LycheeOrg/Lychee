# Quick Reference for GitHub Copilot

> For comprehensive coding standards, see [docs/specs/3-reference/coding-conventions.md](../docs/specs/3-reference/coding-conventions.md)

This file provides quick hints for GitHub Copilot during real-time code completion.

---

# Conventions for PHP

- Any new file should contain the license header and has a single blank line after the opening PHP tag.
- The variable name should be in snake_case.
- We apply the PSR-4 coding standard.
- in_array() should be used with true as the third parameter.
- Only booleans should be used in if statements, not integers or strings.
- Use strict comparison (===) instead of loose comparison (==).
- Avoid code duplication in both if and else statements.
- Do not use empty() 

# Convention for the Application

- In Requests, if a user is provided by the query it is placed in the $this->user2.
- In Requests, $this->user is reserved for the user making the request.
- The Resource classes should extend from Spatie Data instead of JsonResource.
- We do not use blade view, we use Vue3 instead.

# Conventions for Vue3

- Use Typescript in composition API for Vue3. Use PrimeVue for UI components.
- Do not use await async calls in Vue3, use .then() instead.
- Do not use const function = () => {}, use function functionName() {} instead.
- the <template> comes first, then <script lang="ts">, then <style>.
- axios requests should be in the services/ directory and make use of `${Constants.getApiUrl()}` to specify the base URL.

# Documentation Conventions

- Use Markdown format for documentation.
- At the bottom of the file, add an hr line followed by "*Last updated: [date of the update]*" 

# Testing Conventions

- Tests in the tests/Unit directory should extend from AbstractTestCase.
- Tests in the tests/Feature_v2 directory should extend from BaseApiWithDataTest.
- No need to mock the database in tests, we use the in-memory SQLite database instead.

# Working with Money and Currency

When dealing with monetary values in PHP, it's crucial to handle them with precision to avoid rounding errors and inaccuracies. Here are how we handle money and currency in our application:
we use the `moneyphp/money` library, which provides a robust way to manage monetary values and currencies.

Never use floats or doubles to represent monetary values. Instead, use integers to represent the smallest currency unit (e.g., cents for USD). This means that $10.99 should be stored as 1099 (cents).

# Translations

Translation source files are the PHP arrays in `lang/<locale>/*.php` (e.g. `lang/en/gallery.php`).
When adding or editing translation keys, **only** modify these PHP files. Use snake_case for keys and group related translations in nested arrays.

**NEVER read, write, or modify `lang/php_*.json` files.** They are auto-generated from the PHP sources and not tracked in git.