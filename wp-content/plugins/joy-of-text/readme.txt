=== Joy Of Text Lite - SMS messaging for Wordpress. ===
Contributors: wilsos6
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=J37CSTDJVLJTE
Tags:  twilio, message, sms, mms, text, mobile, sms scheduler, scheduler, woocommerce, membermouse, voice message, notification, subscribe, wordpres, gravity forms
Requires at least: 3.0.1
Tested up to: 4.4.2
Stable tag: 4.4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Send SMS and voice messages to your customers, subscribers, followers, members and friends.

== Description ==

**See [getcloudsms.com](http://www.getcloudsms.com/downloads/joy-of-text-pro-version-3/) for details of the Joy Of Text Pro plugin**

The Joy of Text (or JOT) plugin provides a great way of connecting with your customers, blog subscribers or club members. This plugin allows you to send both SMS and text-to-voice messages to a group or to individuals.

To use this plugin you will need a [Twilio](http://www.twilio.com "Twilio") account. If you don't have a Twilio account, you can sign up for a [free trial](https://www.twilio.com/try-twilio) account, then see what the JOT plugin can do for you.

This free version includes the following features:

* Allows SMS and text-to-voice messages to be sent a group or individuals.
* Creates a default group, into which subscriber names and phone numbers can be added.
* Allows administrators to add, delete and update members from the group.
* Creates a subscription form, allowing website visitors to subscribe to SMS updates.
* Provides merge tags that can be included into the message, allowing the subscriber's name, number or a link to your last blog post to be substituted into the message.
* Provides the option to automatically send an SMS messages to new subscribers. You can specify the content of this welcome message, which can include website and media links.
* Allows language preference to be selected for text-to-voice messaging.
* Checks that each phone number entered is valid.
* Has support for a number of extensions.


The extensions available for the JOT Lite plugin are: 
* [Woocommerce extension](http://www.getcloudsms.com/downloads/joy-of-text-woocommerce-integration-extension/)) - synchronising your Woo customers into JOT, allowing you to send SMS messages to your valued customer base.
* [Message Scheduling extension](http://www.getcloudsms.com/downloads/joy-text-message-scheduler-extension/) - allowing you to schedule messages to be sent at a future date and time.
* [Post and comment Notifier](http://www.getcloudsms.com/downloads/joy-of-text-post-and-comment-wordpress-sms-notifier/) - send SMS notifications to your customers, when a new post is added or updated.


As discussed in this [article](http://www.mobilemarketingwatch.com/sms-marketing-wallops-email-with-98-open-rate-and-only-1-spam-43866/), SMS messages have "open rate" of 98%, i.e. 98% of all SMS messages sent, are opened by the recipient. This compares to only 22% of emails sent, so give your marketing a boost, by sending SMS messages from the JOT plugin.

Here's a great [tutorial](https://needweb.help/blog/2016/05/14/create-a-form-that-will-send-a-coupon-by-textsms/) by Loren Nason. Thank you Loren.


**The JOT Lite Admin screens explained.**

The "Settings" tab

Within this tab your [Twilio](http://www.twilio.com "Twilio") authentication are entered. Once you have entered your Twilio details, you can select the 'from' number from which your messages will be sent.

You can also use this screen to choose your voice and language preference, which is used when sending text-to-voice messages.

The "Message" tab

Use this tab to construct your messages. First select the recipients of your message, then enter your message. The message can contain the special tags shown below:

* %name% - when the message is sent, this tag is replaced by the message recipients name.
* %number% - the tag is replaced by the recipients phone number.
* %lastpost% - the tag is replaced by the URL of your last blog post.

From this tab, you can also specify a message suffix, that will be appended to all messages.

You can also specify whether your message is sent as an SMS message or as a text-to-voice message. For text-to-voice messages, the text you enter will be converted (by Twilio) to speech, which is played to your chosen recipients when they answer their phones.

The "Group Manager" tab

The free version of the plugin provides one member group. 

From this tab you can change the name and description of the group. You can add new members to the group, delete existing members from the group or update their name and number details.

From the "Group Invite" you can construct a form which you can place on your website, to invite your website visitors to subscribe to SMS or text-to-voice message updates. You can also choose whether to send a new subscriber an SMS message containing a welcome message. The HTML for your form is provided on this tab or you can choose to use the shortcode `[jotform]`.

The [Pro version](http://www.getcloudsms.com/downloads/joy-of-text-pro-version-3/) allows an unlimited number of member groups to be created.

For requests, feedback and support please send an email to jotplugin@gmail.com. All feedback appreciated.

**Translations**

The Joy Of Text Lite is available in English or Vietnamese (thank you to Chuong Nguyen). If you are interested in translating the plugin into your own language, then the
default English translation file is available [here](http://www.getcloudsms.com/joy-of-text-lite-default-english-po-file/). Thank you.


**The Pro Version**

A [Pro version](http://www.getcloudsms.com/downloads/joy-of-text-pro-version-3/) is available for sale. 

Check out the [comparison](http://www.getcloudsms.com/jot-comparison/) of the JOT Lite and JOT Pro versions. 


The **Pro Version** has the following features:


* Allows SMS, MMS, text-to-voice or audio file messages to be sent a group or individuals.
* Receives inbound SMS messages.
* Allows multiple subscriber groups to be created, into which subscriber details, such as name,  phone number, email address, address can be added.
* Allows administrators to add, delete and update members from the groups.
* Allows a wide range of merge tags, such as  %firstname% and %lastname% to be included into your messages, providing more personalised messages.
* Create one or more subscription forms, using Gravity Forms or the built-in JOT forms , allowing website visitors to subscribe to SMS updates.
* Automatically sends out a "welcome" SMS message to new group subscribers, a great way to distribute your new media or your mobile app.
* Support for Twilio SMS and voice messaging.
* Routes inbound SMS messages to another cell phone number or email address.
* Shows the history of all successfully sent and received messages.
* Shows an Android/iPhone-like "threaded" view of messages sent and received from an individual member's cell number.
* Provides commands that can be issued from an administrator's cell phone, allowing group details to be retrieved and messages to be sent to groups remotely, without logging into the WordPress dashboard.
* Support for Twilio's new Sender ID capability on SMS and MMS messages. This replaces your Twilio number with some text, such as your company name, allowing you to add branding to your text messages. (Not supported by Twilio in the US).
* Integration with WooCommerce, allowing your Woo customers to synchronised with a Joy Of Text group, so you can send them messages.
* Allows "opt-out" keywords to be specified, allowing subscribers to opt-out of receiving SMS messages from individual or all groups.
* Includes an admin and user role and new capabilities for WordPress Multisite users or for making the plugin available to non-admin users.
* Allows a subscription keyword to be specified for each group. People can text the keyword to your Twilio number to be subscribed to the group.
* Twilio number verification and country codes – new numbers entered will be verified by Twilio, to ensure the number is valid.
* Provides a "bulk import" facility, allowing your existing customer's or member's details to be imported into the JOT plugin.
* Allows selection of all media types for MMS messages. Allowing all of the media file types that Twilio supports to be sent in MMS messages.
* Allows members lists to be downloaded into a CSV file
* Support for WordPress multisite networks and cross network activation of the plugin.
* Automatic updates from the WordPress dashboard and a licence key.
* Implementing auto-add groups. Allowing the numbers from inbound SMS messages to be automatically added to chosen groups.
* Support for unicode characters.
* Allows the gender and language to be chosen for text-to-voice messaging.
* Support for the JOT Scheduler extension plugin.
* Support for SQLlite.
* An SMS or email can be sent to an admin user, when new member subscribes to a group.


To download the pro version please visit [here](http://www.getcloudsms.com/)


== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `joy-of-text.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure the plugin from Settings-JOT Settings.

== Frequently Asked Questions ==

= Are other SMS providers supported? =

Currently only the excellent Twilio service is supported, however the plugin has been written so that other providers can be added. Please email jotplugin@gmail.com to discuss the addition of other providers.

= Do new subscribers need to enter both their name and number? =

By default the plugin expects new subscribers, to enter both their name and number into the form created either using the [jotform] shortcode or using the HTML supplied on the
"Group Invite" tab.

However, if you only want new subscribers to enter their phone number, you can alter you form as shown below, with a hidden name field or use the shortcode
[jotform id=1 name=no]

`<div>
  <form id="jot-subscriber-form-1" action="" method="post">
    <input type="hidden"  name="jot-group-id" value="1">
    <input type="hidden"  name="jot_form_id" value="jot-subscriber-form">
    <table>
       <tr><th colspan=2 class="jot-td-c">Please enter your number to get the app!</th></tr><tr><th></th><td><input id="jot-subscribe-name" name="jot-subscribe-name" maxlength="40" size="40" type="hidden" value="No name given"/></td></tr>
       <tr><th>Enter your phone number :</th><td><input id="jot-subscribe-num" name="jot-subscribe-num" maxlength="40" size="40" type="text"/></td></tr><tr><td><input type="button" id="jot-subscribegroup-1" class="button" value="Subscribe"/></td><td>
       <div id="jot-subscribemessage"></div></td></tr>
    </table>
  </form>
</div>`

= Can SMS messages be received by the plugin? =

Not with this free version. The [pro version](http://www.getcloudsms.com/) does allow inbound SMS messages to be accepted and forwarded onto another cell phone number. The pro version is available [here](http://www.getcloudsms.com).

= Will this plugin help me distribute my mobile app =

A number of users have used the plugin to distribute links to their mobile apps or media. The JOT plugin allows forms to be placed on your website, inviting visitors to subscribe
to SMS updates. When visitors enter their number successfully, the JOT plugin can be configured to send a "welcome message" in an SMS. 

Using this feature, you can use the "welcome message" to send new subscribers a link to your mobile app or any other digital media you'd like to distribute via SMS messages.

== Screenshots ==

1. The Getting Started screen, where your Twilio details are entered.
screenshot-1.jpg
2. The licence key section, for adding licence keys from purchased extensions. 
screenshot-2.jpg
3. General settings section.
screenshot-3.jpg
4. The Messages tab, where you SMS messages are constructed.
screenshot-4.jpg
5. The Group Details tab, where you can update the name and description of the group.
screenshot-5.jpg
6. The Group Members tab, where you can add, update or delete members.
screenshot-6.png
7. The Group Invite tab, allowing you to create a form for inviting new members (subscribers).
screenshot-7.png
8. **Purchased Extension** The group notification tab, where you can configure SMS notifications for new or updated posts and new comments.
screenshot-8.png
9. **Purchased Extension** The schedule manager tab, where you can see the status of your scheduled messages.
screenshot-9.png
10. **Purchased Extension** The Woocommerce integration tab, allowing you to synchronise your Woo customer into JOT.
screenshot-10.png
== Changelog ==

= 1.6 =
* Changed the layout of the settings pages, to match the Pro layout.
* Integrated the plugin with the SMS Post Notifications plugin extension.
* Improved documentation.

= 1.5 =
* Added support for the Woocommerce extension. Allowing customer details from Woocommerce to be pulled into JOT.

= 1.4 =
* Added support for the message scheduler extension

= 1.3 =
* Included number validation. A check is made with Twilio to ensure that each number added is a valid number.

= 1.2 =
* Included the option to change the voice and language of the text-to-voice messaging feature.

= 1.1 =
* Added support for localization and included a Vietnamese translation.
* Fixed problem with the inital activation of the plugin.

= 1.0.13 =
* Added support for the SMS Notifications extension which will send SMS messages to a group when new posts or comments are added.

= 1.0.12 =
* Added shortcode [jotform id=1 name={yes|no}], to create a number only form
* Removed default message suffix.

= 1.0.11 =
* Fixed a problem with php start tags, which was causing a problem on some sites.

= 1.0.10 =
* Added progress counter, useful when sending large amounts of messages.

= 1.0.9 =
* Group info tabs bug fix. Sorry about that folks.

= 1.0.8 =
* Fixed a bug related to tabs and Gravity forms
* Changes required to support the S2Member and Buddypress plugin

= 1.0.7 =
* Fixed problem with the form shortcode

= 1.0.6 =
* Fixed problem with checkbox on invite form
* Put some error handling on SMS provider form

= 1.0.5 =
* Changed Wordpress installation URL used by Twilio for voice messages
* Changed the method of saving voice messages

= 1.0.4 =
* Added voice call debug logging.

= 1.0.3 =
* Corrected path to javascript libraries.

= 1.0.2 =
* Further updates to screenshots and readme

= 1.0.1 =
* Updated readme.txt 

= 1.0 =
* First release.



== Upgrade Notice ==

= 1.5 =
* Added support for the Woocommerce extension. Allowing customer details from Woocommerce to be pulled into JOT.


