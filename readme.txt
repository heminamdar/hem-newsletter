=== Newsletter by Hem ===
Contributors: heminamdar
Tags: newsletter, email, subscribers, smtp, subscription
Requires at least: 5.5
Tested up to: 7.0
Stable tag: 1.1.6
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A free forever newsletter plugin by Hem Inamdar. Collect subscribers, confirm via email, and broadcast posts — all from your dashboard.

== Description ==

**Newsletter by Hem** is a lightweight, privacy-friendly newsletter plugin that gives you everything you need and nothing you don't.

**Free Forever — by Hem Inamdar**

= Features =

* Simple `[newsletter_by_hem]` shortcode — place the subscribe form anywhere
* Double opt-in: subscribers receive a confirmation email before being added
* Subscriber dashboard: view all confirmed and pending subscribers
* Delete individual subscribers from the WordPress admin
* Settings page for sender name, sender email, wp_mail, Gmail, and SMTP configuration
* Test email tool to confirm whether sending works
* Post broadcast checkbox: tick a box before publishing to email all confirmed subscribers
* Clean, responsive HTML email templates
* Unsubscribe link included in broadcast emails
* Donation link shown only inside the WordPress dashboard
* No hidden ads in outgoing emails
* Zero tracking and no third-party JavaScript dependencies

= Privacy =

This plugin stores subscriber email addresses, subscription status, confirmation tokens, unsubscribe tokens, and subscription dates in the WordPress database. It does not track users and does not send subscriber data to third-party services.

Broadcast emails include an unsubscribe link so subscribers can remove themselves. Site owners can also delete subscribers from the dashboard.

= Usage =

1. Install and activate the plugin.
2. Go to **Newsletter by Hem → Settings** and configure your sender info and optionally your SMTP server.
3. Add `[newsletter_by_hem]` to any page, post, or widget area.
4. When you write a post, check **Send to confirmed subscribers** in the Newsletter Broadcast meta box before publishing.

== Installation ==

1. Upload the `Free_WP_Newsletter_by_Hem` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Configure settings under **Newsletter by Hem → Settings**.

== Frequently Asked Questions ==

= Does this plugin add ads to my emails? =

No. There are no footer ads or hidden promotional lines in outgoing emails.

= Does it support SMTP? =

Yes. You can use wp_mail, Gmail SMTP, or your domain SMTP mailbox.

= Does it include unsubscribe links? =

Yes. Broadcast emails include an unsubscribe link.

== Changelog ==

= 1.1.5 =
* Final WordPress.org hardening pass: verified nonces, sanitization, escaping, uninstall cleanup, readme, and direct-access guards.

= 1.1.4 =
* Added frontend style controls, live preview, customizable form text, and improved test email preview.

= 1.0.8 =
* Added unsubscribe handling for broadcast emails.
* Improved sanitization and escaping around public confirmation pages and admin actions.
* Added clearer privacy/readme information.

= 1.0.7 =
* Added final shortcode [newsletter_by_hem] and polished dashboard copy.

= 1.0.0 =
* Initial release.
