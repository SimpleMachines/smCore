# For Developers - Why Not X?

## Why not XSL?

XSL is a great transformation language written in XML. However, since it's an
XML application, it requires that it's well-formatted XML.

This leads to a few things that the authors of ToxG and the smCore template
engine find annoying:

-	Templates cannot contain partial elements, although this is uncommon.

-	Building an element with transformations requires using something like
	xsl:tag, xsl:attribute, which are very annoying to use in practice.

-	In practice, entity references are painful and external doctypes don't work.

-	Using it usually necessitates using XML as output, which causes extra layers
	of data output and buffering, which is less efficient.

-	Duplicate templates are not suported, which makes automatic additions from
	external modules difficult, which is the main purpose of this engine.


## Why not use PIs?

It's true that the tags used in this are really processing instructions,
especially when it comes to things like `<tpl:else />`. XML has a standard way
of dealing with these, PIs. They aren't used because:

-	PIs must start with an identifer, which makes open/close sections seem
	out of place, although they are important and key in templating.

-	They will confuse people with PHP, since `<?php ?>` is a PI, technically.

-	It's still not going to be valid XML, but will look even more like it.


## Why not use PHP?

PHP was originally built as a system for formatting HTML output, and is very
well documented. There's no doubt that it makes a good templating system
even used alone.

However, although PHP is primarily used for HTML, it does very little to help
the developer protect their website against XSS without thinking about it at
every step of the way. There's also no way to extensibly hook into files or
functions without, again, thinking about it every time they're used.

Templates should be simple and easy. Hooks for modules need to be common
place, and not involve tons of red tape. Moreover, security needs to be the
default, instead of an afterthought.


## Why not use Smarty or ...?

There are other templating systems out there. These systems are generally well
thought out and do a good job solving some of the same problems this does.

However, the authors are not aware of a system that provides as well for all
of the goals ToxG and the smCore template lanugage set out for, including good
error reporting, namespaced plugin support, and default XSS prevention. These
simply aren't goals of most of these other systems.
