MailLibrary
===========

A PHP library for downloading mails from server.

Documentation can be found at http://php-mail-client.github.io/Client/.

This is fork which differs from original in following:
- makes [less assumptions](https://github.com/grifart/MailLibrary/commit/daa0d1e16667b124b210ae329f16396117824c01) on server
- fixes [tons of encoding related bugs](https://github.com/grifart/MailLibrary/commit/ae4c5e894ab6cebc5813002ca9bb09ed35c3ceb1)
- cleans up API of Mail class by [removing magic getters](https://github.com/grifart/MailLibrary/commit/37c93ab5b70df57c5c6b8a253c9968aa100799f2)
- can [read arbitrary mime parts](https://github.com/grifart/MailLibrary/commit/505e37f8418c0ca947e8c169037df1f67559f8a7) from messages, which is useful for e-mail machine processing


Testing
-------

Install dependencies using composer and then run following in library root directory.

````cmd
# Unix
vendor\bin\tester -c tests\php-unix.ini tests

# Windows
vendor\bin\tester -c tests\php-windows.ini tests
````
