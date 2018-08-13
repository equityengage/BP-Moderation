# UCPT Labs Update
## Updating this older plug-in for use as a UCPT Labs project.

Please note, this is a plug-in that was previously available that is being updated for better compatibility with the UCPT framework. At this time, this is a UCPT Labs project, which means that it is NOT ready for production. Use at your own discretion, and understand that we have not fully reviewed this code.

NOTICE - OUTSIDE OF THE SCOPE OF PILOTS


-------------------------------------------------

=== BuddyPress Moderation ===
Contributors: francescolaffi
Donate link: http://flweb.it/
Tags: buddypress, moderation
Requires at least: WP 3.5, BP 1.7, PHP 5.3
Tested up to: WP 3.6, BP 1.8.1
Stable tag: 0.1.8-dev
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds links/buttons to flag inappropriate content and gives a convenient way to
moderators to view reports and take actions.

== Description ==

Site admins can already edit or delete every content in a BP community, but
analyzing every content posted could be a crazy/impossible work in big communities.
This plugin use crowdsourcing to help site admins finding contents to moderate.

It adds links/buttons to flag inappropriate user generated content in the site,
so members can easily flag contents as inappropriate. Admins can then see all the
reported contents in an organized table in the wp backend, order/filter them and
take actions (ignore, delete, mark/unmark the content author as spammer, ...).

Another table show members, how many posts from them have been reported/moderated,
how many posts have they reported and moderated from admin. Here find bad/good
members and take action on them.

Note on private messages:
* private message sender: reporting this will flag the sender, not the thread, but the admin is not able to see the messages, effective against bulk spammer
* private message: in this case a sender is reported in a specific thread, the admin can see the messages, more useful for moderation (eg k-12 communities)
the latter is a bit hackish and could be less solid on future bp upgrades, the first one is based on apis that should be more stable

Use [support forum on wordpress.org](http://wordpress.org/support/plugin/bp-moderation)
for support and discussion.

The default style uses one icon from http://www.famfamfam.com/lab/icons/silk/
(cc-by-2.5) and one from http://damieng.com/creative/icons/silk-companion-1-icons
(cc-by-3.0), so if you use default style give credit to them somewhere in your site.



== Installation ==
0. Install, activate and configure [BuddyPress](http://buddypress.org/download/).
1. Install BuddyPress Moderation either by:
 * using the [installer wizard](http://coveredwebservices.com/wp-plugin-install/?plugin=bp-moderation);
 * search bp-moderation in the built in WordPress plugin installer;
 * download it manually and upload it to `/wp-content/plugins/bp-moderation/`.
2. Activate BuddyPress Moderation in the "Plugins" admin panel using the "Activate" link.
3. Configure settings: go to "BuddyPress" > "Moderation" from the WordPress admin menu,
then select the "Settings" tab on the top.
4. For a quick start you can read the "Moderator panel" section of this readme/webpage.



== Frequently Asked Questions ==

= Where do flags show up? =

Flags show up in "BP Moderation" from the Wordpress admin menu.
Read the "Moderator panel" section of this guide for more information.

= How can I use keyboard shortcuts? =

Read the "Moderator panel > Hotkeys" section of this guide.



== Changelog ==

= 0.1.1 =
* first stable release

= 0.1.2 =
* bugfixes

= 0.1.4 =
* wp 3.1 compatibility

= 0.1.5 =
* bp 1.5 compatibility

= 0.1.7 =
* bp 1.7 and 1.8 compatibility
* requires PHP 5.3

= 0.1.8 =
* integration with buddypress-docs
* works with wp multisite with bp installed on secondary site
* fix activity comments url and doesn't use cached urls

== Upgrade Notice ==

= 0.1.1 =
First bp-moderation release

= 0.1.2 =
Fixes some bugs

= 0.1.3 =
Other fixes

= 0.1.4 =
wp 3.1 compatibility: tested with wp3.1+bp1.2.8, not tested with previous versions

= 0.1.5 =
bp 1.5 compatibility

= 0.1.7 =
Requires PHP 5.3
Moderation page has now a top-level menu item

== Screenshots ==

1. **Activity Loop Integration** — contents can be flagged directly from the activity that represent them
2. **Ajax Flagging** — the flagging animation



== Moderator panel ==
You can access the backend panel from the "BP Moderation" link in your
Wordpress admin menu.

There are three tabs on the top: "contents", "users", "settings".


= Contents view =
In this view you can see the reported contents.

Use the custom query filter/order contents.

The contents table has three columns:

1. info on the content author
2. info on the content itself ( and link to take actions on it on mouseover )
3. info on the flags on this content


= Users view =
In this view you can see users that reported a content or users whom contents
have been reported.

Use the custom query filter/order contents.

The contents table has three columns:

1. info on the user itself
2. info on the contents generated by the user and flagged by others
3. info on the contents generated by others and flagged by the user


= Hotkeys =
You can enable and disable hotkeys with the link displayed under contents and users tables.

I tried to make hotkeys similar wordpress comments table hotkeys, if you never
used them, give a look to [this codex page](http://codex.wordpress.org/Keyboard_Shortcuts).

When a row is selected with hotkeys the possible keys will be shown next to the actions links.

Hotkeys in both tables:

* j/k: moves down/up
* x: check current row for bulk actions
* shift+x: invert row selection
* c: direct contact selected user (or selected content author)
* s/u: mark as spammer selected user (or selected content author) / unmark him

Only in contents table:

* v: view content
* a: approve (ignore)
* e: edit
* m: mark as moderated
* d: delete

Only in user table:

* b: see the contents generated by the selected user and flagged by others in the content view
* g: see the contents generated by others and flagged by the selected user in the content view

Bulk hotkeys:

Some keys can be triggered on all selected rows if pressed with shift.
Those keys are s,u,a,m,d.



== Integration guide ==
= Introduction =
This guide aims to explain how integrate bp-moderation with custom content types,
wp/bp core content types are already covered by the plugin, but you can write your
own custom content type definitions also for them.

It's important to understand how bp-moderation differentiate/recognize contents:
each content have to be identified by an internal content type slug (you choose
it in your custom content type definition) and one or two bigint ids.

Decide a convention with 2 ids for your content type, is not something you can change
later. You have to call bp-moderation methods using always the same convention and
bp-moderation will use the same one when referring to your contents.
If your contents are tied with the activity stream you already have chosen a convention
for primary and secondary ids, using the same convention you use with activities
will make things easier. If you only have one id use 0 for the secondary id.


= Register a content type =
The main entry point in bp-moderation is bpModeration::register_content_type(), it
allows you to register a content type and has to be called at every page load.

You'll need to provide:

* a slug: used to differentiate between content types  (alfanumeric and underscore only)
* a label: human readable name of the content type (used in backend)
* some callbacks: called by bp-moderation to request information/operations on your contents
* activity types: the activity types that your content are posted on the activity stream with (if any)


= Code sample =
`examples/bpMod_ContentType_BlogPostExample.php` is a code sample that shows
how to integrate content types with bp-moderation taking blog posts as an example,
you can also modify and adapt it to your content type.

Other informations are in the doc of `bpModeration::register_content_type()` and
`bpModFrontend::get_link()`, those are most likely the only two bp-moderation
methods you need to use.

All core content types are in bpModDefaultContentTypes.php, but they are hardcoded
for speed reasons, so don't use them as an example.


= Advanced integration with activities =
If you use the same primary and secondary id convention for activities and bp-moderation
you only have to tell the activity types of your content when registering,
bp-moderation is already hooked in the activity loop and will print the links
for your contents too.

Instead if some reason you cat use the same convention or you want to customize
the activity loop flag button, you can use the filter
`
	bp_moderation_activity_loop_link_args_{the activity type}
`
where `{the activity type}` is the activity type you'd like to filter.
Look in `bpModFrontend::activity_loop_link()` to see what to filter.

= Contents generated by non members =
It's possible to have contents generated by not members (e.g. blog comments).
If you have to provide a user id to bp-moderation give 0 for it, but you'll also
need to filter author information for displaying them in backend table. Use the filter
`
	bp_moderation_author_details_for_{slug used in content type registration}
`
to add missing info, look in `bpModBackend::author_details()` to see info needed.

= OK I coded my custom content type, and now? =
If you have coded a custom content type and you think that could be widely useful,
contact me and it could easily get included in the bp-moderation plugin.

If you integrated your plugin and you prefer to keep the custom content type in
your plugin it's fine, I guess is more convenient and you can update it together
with your plugin.
Remember to check if bp-moderation is active before including unnecessary code
or calling non-existing functions: safest way is to use the action `bp_moderation_init`
for including/registering it.

If none of the above this is the easier way to get a custom content type loaded:

* place your custom content type php file in `wp-content/plugins/bp-moderation-content-types/`
* copy this line in wp-config.php `define('BPMOD_LOAD_CUSTOM_CONTENT_TYPES', true);`


= Possible future content type system features =
* differentiate between trash, untrash and delete, or maybe custom actions on content
* methods to be called when a content is edited/trashed/untrashed/deleted so bp-moderation
can display also what happen outside of it
