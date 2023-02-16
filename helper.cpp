
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

/**
 * convert gvalue to Php::Value
 */
Php::Value gvalue_to_phpvalue(GValue *gvalue)
{
	switch (g_value_get_gtype(gvalue))
	{
	case G_TYPE_INVALID:
	case G_TYPE_NONE:
		throw Php::Exception("G_TYPE_INVALID not implemented");
		break;
	case G_TYPE_INT:
		return g_value_get_int(gvalue);
		break;
	case G_TYPE_BOOLEAN:
		return g_value_get_boolean(gvalue);
		break;
	case G_TYPE_DOUBLE:
		return g_value_get_double(gvalue);
		break;
	case G_TYPE_FLOAT:
		return g_value_get_float(gvalue);
		break;
	case G_TYPE_STRING:
		return g_value_get_string(gvalue);
		break;
	case G_TYPE_CHAR:
		return g_value_get_char(gvalue);
		break;
	case G_TYPE_LONG:
		return g_value_get_long(gvalue);
		break;
	case G_TYPE_ULONG:
		return (int64_t)g_value_get_int64(gvalue);
		break;
	case G_TYPE_UCHAR:
		return g_value_get_uchar(gvalue);
		break;
	case G_TYPE_OBJECT:
		return g_value_get_object(gvalue);
		break;
	case G_TYPE_INTERFACE:
		throw Php::Exception("G_TYPE_INTERFACE not implemented");
		break;
	case G_TYPE_PARAM:
		throw Php::Exception("G_TYPE_PARAM not implemented");
		break;
	case G_TYPE_BOXED:
		throw Php::Exception("G_TYPE_BOXED not implemented");
		break;
	case G_TYPE_POINTER:
		throw Php::Exception("G_TYPE_POINTER not implemented");
		break;
	case G_TYPE_FLAGS:
		throw Php::Exception("G_TYPE_FLAGS not implemented");
		break;
	case G_TYPE_ENUM:
		throw Php::Exception("G_TYPE_ENUM not implemented");
		break;
	}

	return -1;
}