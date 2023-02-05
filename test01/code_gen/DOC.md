# Create def files

- You will need checkout glibmm from git hub
- Use the tools/enum.pl to create enuns, like:
- [ ] Undestand why not internals.h and private.h

```
GLIBMM_SOURCE/tools/enum.pl /usr/include/gtk-3.0/gtk/gtk*.h /usr/include/gtk-3.0/gtk/deprecated/gtk*.h > PHPGTK_SOURCE/code_gen/defs/gtk_enums.defs
GLIBMM_SOURCE/tools/enum.pl /usr/include/gtk-3.0/gdk/gdk*.h /usr/include/gtk-3.0/gdk/deprecated/*.h /usr/include/gdk-pixbuf-2.0/gdk-pixbuf/*.h > PHPGTK_SOURCE/code_gen/defs/gdk_enums.defs


GLIBMM_SOURCE/tools/defs_gen/h2def.py /usr/include/gtk-3.0/gtk/gtk*.h /usr/include/gtk-3.0/gtk/deprecated/gtk*.h > PHPGTK_SOURCE/code_gen/defs/gtk_methods.defs
GLIBMM_SOURCE/tools/defs_gen/h2def.py  /usr/include/gtk-3.0/gdk/gdk*.h /usr/include/gtk-3.0/gdk/deprecated/*.h /usr/include/gdk-pixbuf-2.0/gdk-pixbuf/*.h > PHPGTK_SOURCE/code_gen/defs/gdk_methods.defs

```