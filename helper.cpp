
#include "helper.h"

/**
 * convert C++ object to Php::Object
 */
Php::Value cobject_to_phpobject(gpointer *cobject)
{
	if(cobject == NULL) {
		return NULL;
	}

	GtkWidget_ *return_parsed = new GtkWidget_();
	return_parsed->set_instance((gpointer *)cobject);
	return Php::Object(g_type_name(G_TYPE_FROM_INSTANCE((gpointer *)cobject)), return_parsed);
}

/**
 * convert C++ GList to Php::Array
 */
Php::Value glist_to_phparray(GList *glist);
{
	Php::Value ret_arr;

	for(int index=0; GList *item=g_list_nth(glist, index); index++) {

		// @todo verify the gpointer type, if char* if object, etc
		ret_arr[index] = (char *) item->data;
	}

	return ret_arr;
}