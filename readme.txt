=== Contact Form ===
Contributors:      The WordPress Contributors
Tags:              block
Tested up to:      6.8
Stable tag:        0.1.0
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Example block scaffolded with Create Block tool.

== Description ==

This is the long description. No limit, and you can use Markdown (as well as in the following sections).

For backwards compatibility, if this section is missing, the full length of the short description will be used, and
Markdown parsed.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the plugin files to the `/wp-content/plugins/contact-form` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress


== Frequently Asked Questions ==

= A question that someone might have =

An answer to that question.

= What about foo bar? =

Answer to foo bar dilemma.

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
(or jpg, jpeg, gif).
2. This is the second screenshot

== Changelog ==

= 0.1.0 =
* Release

== Translations ==

Steps to generate/update translations:

1.(when new view keys) run `npm run build`, to generate a compiled view.js file with the new keys.
2. Generate the translation master key file/template with `npm run make-pot`
3. Update the existing language files with `wp i18n update-po ./languages`
4. For JS files, update the JSON translation files with `npm run make-json` (the files for build/view.js matter, the src files do not)
5. For PHP files, run `wp i18n make-mo`

Further details here: https://developer.wordpress.org/cli/commands/i18n/
And here: https://developer.wordpress.org/apis/internationalization/#internationalizing-javascript
