# smCore Template Engine

## Version

Release 1.0 Alpha 1

## About

The smCore Template Engine is a fork of ToxG, a template engine created by [[Unknown]](http://www.unknownbrackets.com/).

## Overview

This is a template processing system built with these primary features in mind:

1. Good error reporting.
   If you make a mistake in a template, it should be easy to find, whether
   it's a syntax error or an undefined index.  It should point you to
   exactly what you need to change.

2. Cached PHP and performance.
   Efficiently generates PHP instead of parsing the templates over and
   over, using as little runtime overhead as possible.

3. Extensibility.
   When building templates, the controllers need to have good, strong
   integration with the templates, and the ability to extend the syntax.

4. Overlays for plugins.
   It should be easy and painless to provide hooks for plugins, and to make
   use of these hooks, so that templates don't stop you from using plugins.

## First steps

It's a great idea to take a look at the documentation, especially the ones named
Quick Start and Element Reference. The samples are good too.

## Supported PHP versions

The smCore Template Engine has been tested on and fully supports the following
PHP versions:

- 5.3.0 - 5.3.9

It has also been tested on a 2011-04-30 snapshot of PHP 6, and is believed to
be forwards-compatible. Versions prior to 5.3.0 are not supported.

## Known issues

Throughout the code and documentation, "!!!" is used to signify parts that need
more thought or changes, or represent minor bugs in the software.

Other than those, the following issues are somewhat major:

1. There need to be better samples.

2. The syntax, elements, and attributes are not final and may change.

As well, the following are important, but probably will not be changing:

1. Namespaces affect the remainder of the document, not just the element
   that they are defined on.

## Improvements and reporting bugs

Suggestions, comments, bugs, and code improvements are all very welcome.
