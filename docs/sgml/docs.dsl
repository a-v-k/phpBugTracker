<!DOCTYPE style-sheet PUBLIC "-//James Clark//DTD DSSSL Style 
Sheet//EN" [
<!ENTITY docbook.dsl PUBLIC "-//Norman Walsh//DOCUMENT DocBook HTML 
Stylesheet//EN" CDATA dsssl>
]>


<style-sheet>
<style-specification use="docbook">
<style-specification-body>

;; Copyright (c)  2001  phpBugTracker Group
;; Permission is granted to copy, distribute and/or modify this
;; document under the terms of the GNU Free Documentation License,
;; Version 1.1 or any later version published by the Free Software
;; Foundation; with no Invariant Sections, with no Front-Cover
;; Texts, and with no Back-Cover Texts. A copy of the license can
;; be found at http://www.fsf.org/copyleft/fdl.html.
;;
;; $Id: docs.dsl,v 1.3 2001/10/01 01:12:38 bcurtis Exp $


(define (toc-depth nd)
  (if (string=? (gi nd) (normalize "book"))
      2
      2))


(define %generate-book-titlepage%
  ;; Should an article title page be produced?
  #t)


(define %titlepage-in-info-order%
  ;; Place elements on title page in document order?
  #f)


(define %admon-graphics%
  ;; Use graphics in admonitions?
  #t)


(define %admon-graphics-path%
  "images/")

(define %callout-graphics%
  #t)

(define %callout-graphics-path%
  "images/callouts/")

(define %callout-graphics-number-limit%
  ;; If '%callout-graphics%' is true, graphics are used to represent
  ;; callout numbers. The value of '%callout-graphics-number-limit%' is
  ;; the largest number for which a graphic exists. If the callout number
  ;; exceeds this limit, the default presentation "(nnn)" will always
  ;; be used.
  10)

(define ($admon-graphic$ #!optional (nd (current-node)))
  ;; Admonition graphic file
  ;; Given an admonition node, returns the name of the
  ;; graphic that should be used for that admonition.
  (cond ((equal? (gi nd) (normalize "tip"))
         (string-append %admon-graphics-path% "tip.gif"))
        ((equal? (gi nd) (normalize "note"))
         (string-append %admon-graphics-path% "note.gif"))
        ((equal? (gi nd) (normalize "important"))
         (string-append %admon-graphics-path% "important.gif"))
        ((equal? (gi nd) (normalize "caution"))
         (string-append %admon-graphics-path% "caution.gif"))
        ((equal? (gi nd) (normalize "warning"))
         (string-append %admon-graphics-path% "warning.gif"))
        (else (error (string-append (gi nd) " is not an admonition.")))))


(define ($admon-graphic-width$ #!optional (nd (current-node)))
  "25")

(define %number-programlisting-lines%
  ;; Enumerate lines in a 'ProgramListing'?
  #f)

(define %linenumber-length%
  ;; Width of line numbers in enumerated environments
  ;; Line numbers will be padded to %linenumber-length% characters.
  0)


(define %linenumber-mod%
  ;; Controls line-number frequency in enumerated environments.
  ;; Every %linenumber-mod% line will be enumerated.
  1)


(define %linenumber-padchar%
  ;; Pad character in line numbers
  ;; Line numbers will be padded (on the left) with %linenumber-padchar%
  " ")


(define %shade-verbatim%
  ;; Should verbatim environments be shaded?
  #t)

(define ($shade-verbatim-attr$)
  ;; Attributes used to create a shaded verbatim environment.
  (list
   (list "BORDER" "0")
   (list "BGCOLOR" "#EEEEEE")
   (list "WIDTH" ($table-width$))))


(define %section-autolabel%
  ;; Are sections enumerated?
  #t)

(define %body-attr%
  ;; What attributes should be hung off of BODY?
  (list
   (list "BGCOLOR" "#FFFFFF")
   (list "TEXT" "#000000")
   (list "LINK" "#0000FF")
   (list "VLINK" "#800080")
   (list "ALINK" "#FF0000")))


(define %stylesheet%
  ;; Name of the stylesheet to use
  "docs.css")

(define %stylesheet-type%
  ;; The type of the stylesheet to use
  "text/css")

(define %html40%
  ;; Generate HTML 4.0
  #t)

(define %use-id-as-filename%
  ;; Use ID attributes as name for component HTML files?
  #t)

;;Default extension for filenames?
(define %html-ext%
  ".html")
	
(define %spacing-paras%
  ;; Block-element spacing hack
  #f)

</style-specification-body>
</style-specification>

<external-specification id="docbook" document="docbook.dsl">

</style-sheet>





















