# JX Category Products

2.0.6
FIX:
 - allowed displaying of disabled categories in admin panel during blocks creating

2.0.5
FIX:
 - validation compliance is improved. Deprecated usage of __DIR__, use _PS_MODULE_DIR_ instead

2.0.4
FIX:
 - added checking if variable exists before count() in order to avoid an error on PHP 7.2

2.0.3
FIX:
 - fixed an issue during theme installation caused by wrong usage of count()

2.0.2
FIX:
 - removed a restriction of a table entry length that cause an error when a lot of product was added to some of a category block
 - fixed an issue with products duplicating from the previous block to the next blocks

2.0.1
Reworked an algorithm of the selected products parsing to avoid problems with page loading