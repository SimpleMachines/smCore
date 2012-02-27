# For Developers - Code Workflow

## Overview

The smCore template engine uses a very simple lexing, parsing, and code building
system. The internal system uses events to make it easy to extend. This document
will give you a "high level" overview of how the system functions.

## Standard code flow

Here's a basic tree (looking at this alone may not make it clearer, read below):

	TemplateList
		Template
			Parser
				Source
					Token
						Exception
				Overlay
				Prebuilder
				Builder
					StandardElements
						Expression
					Errors

Compiling starts with Template. It primarily sets up other classes.

Template creates a Parser, which in turn creates a Source.

Source converts the template file into Tokens which it hands back to
the Parser.

Parser's job is to make sure these tokens are properly nested, take care
of some other parsing things, and also to fire off parsing events to the
other objects created by Template, such as Overlay, Builder, and
Prebuilder.

Overlay is created by Template as well. When the parser tells it
about something interesting, it "inserts" the alteration right into the
parsing stream.

The first pass goes to Prebuilder. This mainly collects information about
the templates used so the Builder already has this information from the
beginning.

After that, it's Builder's turn. This puts together the actual PHP code
for the template, and also embeds debugging info. It directly handles core
operations, such as using and declaring templates.

Builder has its own event system, and this fires off events to get any
elements used (such as tpl:foreach) processed. StandardElements handles
all the built in ones.

## The main event system

Both Parser and Builder have event systems at their center.

Overlay listens to the parser to insert the alterations at the appropriate
places in the parsing process. When a matching element is found, it pushes
extra data onto the Source stack the parser has. As far as the parser is
concerned, the alerations were part of the source file itself.

The Parser actually runs twice, first sending events to the Prebuilder,
then sending them to the Builder. It does this because the prebuilder
needs to know about all the templates before the first Builder starts its
work.

After sending these events, the Parser's job is done.

In the Builder, events are fired off to generate the actual code for the
final template. The only thing it manages itself is the code flow and debug
information for file/line numbers in errors.

## The tokenizing/lexing process

Source objects represent a source of template data, and process it into tokens
for Parser and Overlay to use.

When the Source has completely processed a token, it creates a Token
object, which holds information about the token (and parses attributes) as well
as file and line information.

The token itself is where most exceptions come from, because the token retains
all the necessary file and line information to generate quality error messages.

## Actually building the code

StandardElements hooks into the Builder, and is asked to provide the
Builder with code for any element it hits. It's in here that the actual
processing happens.

Whenever an attribute is an expression, or similar, Expression is used to
do any parsing that's necessary.
