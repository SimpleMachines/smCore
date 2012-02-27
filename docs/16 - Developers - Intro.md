# For Developers - Introduction

## Hacking

This uses tests pretty heavily to ensure that things work like they should.

When making changes, make sure to run the tests to ensure you haven't made
changes that will break other parts of the code, or affect more areas than you
expected.

If tests fail, you should modify them so they pass, or change your code so
they pass. Ignoring failures could lead to confusing bugs.

## Coverage

The tests should already cover most of the codebase, but if you make changes,
make sure to add tests for what you add or fix, as well as testing the error
messages you return.