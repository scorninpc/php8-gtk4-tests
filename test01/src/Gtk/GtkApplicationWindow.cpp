

#include "GtkApplicationWindow.h"

GtkApplicationWindow_::GtkApplicationWindow_() = default;
GtkApplicationWindow_::~GtkApplicationWindow_() = default;

void GtkApplicationWindow_::set_show_menubar(Php::Parameters &parameters)
{
	gboolean show_menubar = (gboolean)parameters[0];

	gtk_application_window_set_show_menubar(GTK_APPLICATION_WINDOW(instance), show_menubar);
}

Php::Value GtkApplicationWindow_::get_show_menubar()
{
	gboolean ret = gtk_application_window_get_show_menubar(GTK_APPLICATION_WINDOW(instance));

	return ret;
}

Php::Value GtkApplicationWindow_::get_id()
{
	guint ret = gtk_application_window_get_id(GTK_APPLICATION_WINDOW(instance));

	return ret;
}

void GtkApplicationWindow_::set_help_overlay(Php::Parameters &parameters)
{
	GtkShortcutsWindow *help_overlay;
	Php::Value object_help_overlay = parameters[0];
	GtkShortcutsWindow_ *phpgtk_help_overlay = (GtkShortcutsWindow_ *)object_help_overlay.implementation();
	help_overlay = GTK_SHORTCUTS_WINDOW(phpgtk_help_overlay->get_instance());

	gtk_application_window_set_help_overlay(GTK_APPLICATION_WINDOW(instance), help_overlay);
}

Php::Value GtkApplicationWindow_::get_help_overlay()
{
	gpointer *ret = (gpointer *)gtk_application_window_get_help_overlay(GTK_APPLICATION_WINDOW(instance));


	return cobject_to_phpobject(ret);
}



