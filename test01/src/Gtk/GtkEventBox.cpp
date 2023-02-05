

#include "GtkEventBox.h"

GtkEventBox_::GtkEventBox_() = default;
GtkEventBox_::~GtkEventBox_() = default;

Php::Value GtkEventBox_::get_visible_window()
{
	gboolean ret = gtk_event_box_get_visible_window(GTK_EVENT_BOX(instance));

	return ret;
}

void GtkEventBox_::set_visible_window(Php::Parameters &parameters)
{
	gboolean visible_window = (gboolean)parameters[0];

	gtk_event_box_set_visible_window(GTK_EVENT_BOX(instance), visible_window);
}

Php::Value GtkEventBox_::get_above_child()
{
	gboolean ret = gtk_event_box_get_above_child(GTK_EVENT_BOX(instance));

	return ret;
}

void GtkEventBox_::set_above_child(Php::Parameters &parameters)
{
	gboolean above_child = (gboolean)parameters[0];

	gtk_event_box_set_above_child(GTK_EVENT_BOX(instance), above_child);
}

void GtkEventBox_::__construct()
{
	instance = (gpointer *)gtk_event_box_new();
}



