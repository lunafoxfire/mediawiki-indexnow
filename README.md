# IndexNowNotifier

## Overview

This is a MediaWiki extension for notifying search engines when pages are created, deleted, or updated. It uses the IndexNow API, which is an open, standardized protocol for pushing notifications to search engines. It is currently supported by several engines. More up-to-date info can be found on the official website: https://www.indexnow.org. In theory this greatly helps smaller wikis get discovered and crawled by search engines.

## Requirements

- **MediaWiki:** This extension requires MediaWiki 1.40.0 or greater.
- **cURL:** This extension sends requests using cURL. By default your host system probably has it unless you intentionally have a very minimal setup.

## Usage

### IndexNow

IndexNow requires you to generate and store a secret key at the root of your website to prove that your IndexNow request comes from a legitimate source.

1. Generate a secret key 8-128 characters in length. This key can be be generated however you like, but it should consist of only a-z, A-Z, 0-9, and dashes.
2. At the root directory of your site (same level as your `index.php`), host a file with the name `<your_key>.txt` with the contents `your_key`. For example if your key is `abc123`, you should create a file named `abc123.txt` with the contents `abc123`.

See the IndexNow documentation for more details: https://www.indexnow.org/documentation

### Extension

This extension is installed like a typical MediaWiki extension. See the MediaWiki docs if you need more information.

1. Clone or download this repository to your MediaWiki installation's extensions folder.
2. Add the following to your `LocalSettings.php` file, using the key you generated in the last section:
```php
wfLoadExtension( 'IndexNowNotifier' );
$wgIndexNowNotifier_SecretKey = '<YOUR_KEY_HERE>';
```


## Configuration

| Variable | Type | Description | Default |
| - | - | - | - |
| `$wgIndexNowNotifier_SecretKey` | string | Your IndexNow secret key. | `""` |
| `$wgIndexNowNotifier_IndexNowEndpoint` | string | The API endpoint to send requests to. | `"https://api.indexnow.org/indexnow"` |
| `$wgIndexNowNotifier_NotifyCreate` | bool | Notify when new pages are created. Includes page moves. | `true` |
| `$wgIndexNowNotifier_NotifyEdit` | bool | Notify when pages are edited. | `true` |
| `$wgIndexNowNotifier_NotifyDelete` | bool | Notify when pages are deleted. Includes page moves. | `true` |
| `$wgIndexNowNotifier_IgnoreMinor` | bool | Ignore minor edits. | `false` |
| `$wgIndexNowNotifier_IgnoreBot` | bool | Ignore changes made by bots | `true` |
| `$wgIndexNowNotifier_LogLevel` | string | Logging level. Valid values are `'off'`, `'error'`, `'warn'`, `'info'`, `'debug'`. See below.  | `"info"` |

### Logging

This extension uses WikiMedia's built in log channels with key `'IndexNowNotifier'`. You can direct the output of this channel by setting `$wgDebugLogGroups['IndexNowNotifier']`. For example you can use `$wgDebugLogGroups['IndexNowNotifier'] = '/var/www/html/logs/IndexNowNotifier.log'` if you want to direct log output to a file. This may be helpful if you wish to ensure that the extension is working.

## Hooks Used

- `PageSaveComplete`
- `PageDeleteComplete`
- `PageUndeleteComplete`
- `PageMoveComplete`

## License

Licensed under MIT. Copyright 2026 lunafox.
