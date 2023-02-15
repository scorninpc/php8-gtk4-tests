#ifndef _PHPGTK_HELPER_H_
#define _PHPGTK_HELPER_H_

	#include <phpcpp.h>
    #include <iostream>
    #include <gtk/gtk.h>

	#include "main.h"

	Php::Value cobject_to_phpobject(gpointer *cobject);
	Php::Value glist_to_phparray(GList *glist);

#endif