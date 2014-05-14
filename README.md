OpenPGP SignIn for Idno
=======================

This plugin provides a way for remote friends to be identified to your Idno installation
by their public PGP key, allowing you to write private posts that only they can read.

Roughly modelled on this: <https://www.marcus-povey.co.uk/2013/10/07/thoughts-private-posts-in-a-distributed-social-network/>

***NOTE: At time of writing, you need to be running my following2 fork <https://github.com/mapkyca/idno/tree/following2> for this to work ***

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
2. Look in the page metadata for _```<meta href="...." rel="key" />```_
3. Look on the page for an _```<a href="...." rel="key">```_
4. Look on the page for a ```<element class="key">....</element>```

If you want to make your non-Idno presence compatible, then add one of the above to your profile page.

The plugin then provides a mechanism for you to log in to a site using a public key, and so be able to see the post.

Authentication Protocol
-----------------------

* Two user profiles, Alice and Bob
* Alice and Bob's sites generate a private and public PGP key, and internally associate it with their profile. Their public key is made available using one of the above methods!
* Alice adds Bob as a friend, Alice's site looks at Bob's profile and saves his public key
* Alice repeats this for Clare, Dave, Emma, Fred etc...
* Alice writes a post, and only wants Bob to be able to read it, so lists Bob's profile as an approved viewer (we have saved his public key and fingerprint)
* Bob visits the private post, and is denied. Oh noes! He then signs his profile URL with his key, and then POSTs the ascii armoured signature as a ```signature``` variable.
* Alice verifies the signature, and compares the signing key's fingerprint against the fingerprint of the users who are permitted to view the post, and if they match, display it.

One thing to note, although I talk about sites and services here, there is nothing about the protocol that *requires* server scripting (for Bob's part at least), you could use command line tools.

The plugin makes a bookmarklet available to make the signin process easier, your non-Idno presences may want to do something similar.

Todo
----

* Would be nice if a user was notified that there is a post waiting.


See
---
 * Author: Marcus Povey <http://www.marcus-povey.co.uk> 
