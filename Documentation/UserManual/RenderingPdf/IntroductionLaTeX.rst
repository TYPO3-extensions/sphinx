.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: Images.txt


Introduction to LaTeX
"""""""""""""""""""""

TeX and associated programs such as LaTeX (formatted as |LaTeX_logo|, and pronounced "Lah-tech") is a system for computer typesetting. It is well known for its skill with mathematical and scientific text (LaTeX is used as the primary method of displaying formulas on Wikipedia) and other difficult typesetting jobs such as long or intricate documents and multilingual works.

TeX systems produce output -- on paper or on the computer screen -- of the highest typographic quality. Even on simple documents, you get a better job than a word processor. Compare `these samples of plain text <http://www.ctan.org/tex/zen.pdf>`_ from Herigels' *Zen in the Art of Archery* done in the word processor *Word*, and *TeX*. These are short and the typographic differences are subtle but even a non-expert will have the sense that the TeX page looks better. For instance, the word processor's page has some lines with wide gaps between words and some lines with too many words stuffed in; contrast the second paragraph's second line with its third. TeX's output is better.

LaTeX is intended to provide a high-level language that accesses the power of TeX. LaTeX essentially comprises a collection of TeX macros and a program to process LaTeX documents. Because the TeX formatting commands are very low-level, it is usually much simpler for end-users to use LaTeX.

Similarly to reStructuredText, LaTeX is based on the idea that it is better to leave document design to document designers, and to let authors get on with writing documents. In reStructuredText you would input a simple document as:

.. code-block:: rest

	=================================================
	Cartesian closed categories and the price of eggs
	=================================================

	:author: Jane Doe
	:date: September 1994

	My First Chapter
	================

	Hello world!

and in LaTeX you would input this document as:

.. code-block:: latex

	\documentclass{article}
	\title{Cartesian closed categories and the price of eggs}
	\author{Jane Doe}
	\date{September 1994}
	\begin{document}
	\maketitle
	\section{My First Chapter}
	Hello world!
	\end{document}

History
~~~~~~~

LaTeX is based on `Donald E. Knuth`_'s TeX_ typesetting language or certain extensions. LaTeX was first developed in 1985 by `Leslie Lamport`_, and is now being maintained and developed by the `LaTeX3 Project`_. It is worth mentioning that first release of TeX dates back from 1978 and that the current stable release is version 3.1415926 from March 2008!

Here are a few really interesting interviews from Donald E. Knuth:

- `The importance of stability for TeX <http://www.webofstories.com/play/donald.knuth/68>`_ (and the fundamental difference between the GNU public license and TeX)
- `Deciding to make my own typesetting program <http://www.webofstories.com/play/donald.knuth/68>`_
- `Working on my own typesetting program (Part 1) <http://www.webofstories.com/play/donald.knuth/52>`_
- `Working on my own typesetting program (Part 2) <http://www.webofstories.com/play/donald.knuth/53>`_
- `Research into the history of typography <http://www.webofstories.com/play/donald.knuth/54>`_
