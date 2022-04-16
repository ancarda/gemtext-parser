# Contributing Guide

Thanks for your interest in contributing to gemtext-parser!

This guide assumes you are somewhat familiar with Git and have contributed to
open source software beforehand.

If you are struggling with anything -- Git, Go, C, Docker, or anything else --
please don't hesitate to send an email to the mailing list (public) or the
maintainer (private).

## Links and Information

* Source Code:   <https://git.sr.ht/~ancarda/gemtext-parser/>
* Issue Tracker: <https://todo.sr.ht/~ancarda/gemtext-parser/>
* Mailing List:  <https://lists.sr.ht/~ancarda/gemtext-parser/>
* GitHub Mirror: <https://github.com/ancarda/gemtext-parser/>
* Public Inbox:  <https://lists.sr.ht/~ancarda/public-inbox/>
* Maintainer: Mark Dain &lt;mark@markdain.net&gt; (_ancarda_)
  * PGP Fingerprint: 9612 F6C7 92C9 FB40 2EA6 1AC1 A440 BD5C DE5C A1F7
  * PGP URI: https://markdain.net/pgp.asc

## Code Quality

Before sending a patch, please run `composer check-everything` which will run
the same tools that are run in CI. You may submit patches that are failing some
checks if you are unsure how to fix them or believe they are false positives.

## Sending Patches

### Via Email

Email is the primary way to send patches to this project. If you are new to
`git send-email`, the website https://git-send-email.io/ has a good intro.
Once you have setup SMTP, you should run this command in the directory where
you checked out this project:

    git config sendemail.to "~ancarda/gemtext-parser@lists.sr.ht"

You don't need to subscribe to the mailing list to participate.

If you need to send a large volume of patches, please push your changes to a
software forge, ideally [git.sr.ht](https://git.sr.ht), and use
`git request-pull` to send an email.

### Via GitHub

If you are not able or willing to use `git send-email`, you can you can send
patches via GitHub's "Pull Request" feature. The mirror can be forked and is
monitored by the maintainer.

You will need a GitHub account and may need to run non-free JavaScript to send
patches this way.

## Discussions

To discuss features or report bugs, send an email to the mailing list. The
GitHub mirror cannot be used for issue tracking.

Please do not directly submit to the issue tracker.

Discussions that affect _all_ ancarda projects can be sent to the Public Inbox.

And of course, any discussion that is better off-the-record - such as security
reports - can be sent directly to the maintainer. Feel free to use PGP.
