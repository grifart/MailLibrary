MailLibrary
===========

A PHP library for downloading mails from server.

Documentation can be found at http://greeny.github.io/MailLibrary/.

This is fork which differs from original in following:
- makes [less assumptions](daa0d1e16667b124b210ae329f16396117824c01) on server
- fixes [tons of encoding related bugs](ae4c5e894ab6cebc5813002ca9bb09ed35c3ceb1)
- cleans up API of Mail class by [removing magic getters](37c93ab5b70df57c5c6b8a253c9968aa100799f2)
- can [read arbitrary mime parts](505e37f8418c0ca947e8c169037df1f67559f8a7) from messages, which is useful for e-mail machine processing
- 

Testing
-------

Install dependencies using composer and then run following in library root directory.

````cmd
# Unix
vendor\bin\tester -c tests\php-unix.ini tests

# Windows
vendor\bin\tester -c tests\php-windows.ini tests
````
