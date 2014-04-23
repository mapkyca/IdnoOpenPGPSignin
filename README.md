OpenPGP SignIn for Idno
=======================

This plugin provides a way for remote friends to be identified to your Idno installation
by their public PGP key, allowing you to write private posts that only they can read.

Roughly modelled on this: <https://www.marcus-povey.co.uk/2013/10/07/thoughts-private-posts-in-a-distributed-social-network/>

Requirements
------------

* OpenPGP.js plugin <https://github.com/mapkyca/IdnoOpenPGPJS>
* PECL gnupg extension <https://php.net/manual/en/book.gnupg.php> which must be initialised correctly 
  (*IMPORTANT*, the default location for .gnupg is web accessible, be sure to change this before using the plugin in production!)

Usage
-----

Install, then add your public and private keys in your settings. You can either use your email ones, or generate an Idno specific key pair
by clicking on the "Generate..." button (recommended).

You public key is then made available by using a rel="key" link in the page header.

When you follow a local or remote user, the plugin will attempt to fetch their public key by doing the following, in order:

1. Look in the HTTP header for a _Link: <....>; rel="key"_
2. Look in the page metadata for _<meta href="...." rel="key" />_
3. Look on the page for an _<a href="...." rel="key">_
4. Look on the page for a <element class="key">....</element>

If you want to make your non-Idno presence compatible, then add one of the above to your profile page.

Todo
----

* Would be nice if a user was notified that there is a post waiting.


See
---
 * Author: Marcus Povey <http://www.marcus-povey.co.uk> 