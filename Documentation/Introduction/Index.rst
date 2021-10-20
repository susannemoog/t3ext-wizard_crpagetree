.. include:: ../Includes.txt

.. _introduction:

============
Introduction
============

This extension has been created, because the native "create multiple pages" wizard is too limiting.
When a full sitemap is already available, this extension enables you to import it in one go.

.. _what-it-does:

What does it do?
================

The extension allows to import an existing sitemap in a single run.
The wizard simply need the page tree structure, indented with space, tab or dots.
Moreover, each line may include additional fields to import, like a subtitle.
C-style comments (single and multi line) as well as empty lines are ignored.

Examples
========

A simple page tree:

.. code-block:: text

   Startpage
    Products
    Solutions
    Company
    Privacy


Pages with additional fields:

Additional fields: "keywords doktype"
Separation character: "!"

.. code-block:: text

   Startpage!productA, company name!1
    Products!!1
    Solutions!!1
    Company!!1
    Privacy!!1
    Customer!!199
