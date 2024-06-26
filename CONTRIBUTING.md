# Contributing to Baum

Thinking of contributing? Maybe you've found some nasty bug? That's great news!

## Reporting a Bug

1. Update to the most recent *master release* if possible. The bug you're experiencing may have already been fixed.
2. Search for similar issues. It's possible somebody has encountered the same bug already.
3. If you keep experiencing the issue and cannot find a similar bug report: Open a new issue and report the bug as specific as you can.
4. If possible, try to add a link to a gist with some failing code and/or stack traces or submit a Pull Request with a failing test. Better yet, take
a stab at fixing the bug yourself if you can!

The more information you provide, the easier it is to validate that there is a bug and the faster it is for someone to provide a fix.

## Requesting a Feature

1. Search [Issues](https://github.com/TopOnePercent/baum/issues) for similar feature requests. It's possible somebody has already asked for this feature or provided a pull request that's still in discussion.
2. Provide a clear and detailed explanation of the feature you want and why it's important to add.
3. If the feature is complex, consider writing some initial documentation for it. This will help everyone understand its use cases and if it is finally implemented it will serve as the base for its documentation.
4. Attempt a Pull Request. If you're at all able, start writing some code. Please understand that this is an open source side-project, so the time devoted to it is limited. If you can write some code then that will speed the process greatly.

## Pull Requests

1. Fork & clone the project: `git clone git@github.com:your-username/baum.git`.
2. Run the tests and coding standard checks to make sure that they pass with your setup: `./vendor/bin/phpcs -p -n src tests && phpunit`.
3. Create your bugfix/feature branch and code away! Add tests for your changes. If you're adding functionality or fixing a bug, tests make it easier for the merge to be possible. Also, they make sure we don't break your changes accidentally.
4. Make sure all the tests and coding standard checks still pass and manually update the Code Coverage % in README.md: `./vendor/bin/phpcs -p -n src tests && phpunit`.
5. Commit your changes. If your pull request fixes an specific issue make sure you say so in the commit message. For example: `git commit -m "Fix auto-imploding nasty bug. Fixes #90."`.
6. Push to your fork and submit a pull request. Please provide some explanation as to why you made the changes you made. For new features make sure to explain a standard use case.

At this point you're waiting on us to respond. Please, be patient. We may suggest some changes or improvements or even complete alternative implementations.
