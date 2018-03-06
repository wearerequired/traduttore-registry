# Traduttore Registry

Allows loading translation files from a custom GlotPress site running Traduttore.

## Usage

You can install this library with `composer require wearerequired/traduttore-registry` after adding this repository to your project's `composer.json` file.

After that, you can use `Required\Traduttore_Registry\add_project()` in your theme or plugin. On a multisite install, it's recommend to use that in a must-use plugin.

Here's an example of you can use that function:

```php
Required\Traduttore_Registry\add_project(
	'plugin',
	'acme-plugin',
	'https://translate.acme.com/api/translations/acme/acme-plugin/'
);

Required\Traduttore_Registry\add_project(
	'theme',
	'acme-theme',
	'https://translate.acme.com/api/translations/acme/acme-theme/'
);
```
