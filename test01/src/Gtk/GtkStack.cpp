

#include "GtkStack.h"

GtkStack_::GtkStack_() = default;
GtkStack_::~GtkStack_() = default;

void GtkStack_::add_named(Php::Parameters &parameters)
{
	GtkWidget *child;
	Php::Value object_child = parameters[0];
	GtkWidget_ *phpgtk_child = (GtkWidget_ *)object_child.implementation();
	child = GTK_WIDGET(phpgtk_child->get_instance());

	std::string c_name = parameters[1];

	gchar *name = (gchar *)c_name.c_str();

	gtk_stack_add_named(GTK_STACK(instance), child, name);
}

void GtkStack_::add_titled(Php::Parameters &parameters)
{
	GtkWidget *child;
	Php::Value object_child = parameters[0];
	GtkWidget_ *phpgtk_child = (GtkWidget_ *)object_child.implementation();
	child = GTK_WIDGET(phpgtk_child->get_instance());

	std::string c_name = parameters[1];

	gchar *name = (gchar *)c_name.c_str();

	std::string c_title = parameters[2];

	gchar *title = (gchar *)c_title.c_str();

	gtk_stack_add_titled(GTK_STACK(instance), child, name, title);
}

Php::Value GtkStack_::get_child_by_name(Php::Parameters &parameters)
{
	std::string c_name = parameters[0];

	gchar *name = (gchar *)c_name.c_str();

	GtkWidget* ret = gtk_stack_get_child_by_name(GTK_STACK(instance), name);

	GtkWidget_ *phpgtk_ret = new GtkWidget_();
	phpgtk_ret->set_instance((gpointer *)ret);
	return Php::Object("GtkWidget", phpgtk_ret);
}

void GtkStack_::set_visible_child(Php::Parameters &parameters)
{
	GtkWidget *child;
	Php::Value object_child = parameters[0];
	GtkWidget_ *phpgtk_child = (GtkWidget_ *)object_child.implementation();
	child = GTK_WIDGET(phpgtk_child->get_instance());

	gtk_stack_set_visible_child(GTK_STACK(instance), child);
}

Php::Value GtkStack_::get_visible_child()
{
	GtkWidget* ret = gtk_stack_get_visible_child(GTK_STACK(instance));

	GtkWidget_ *phpgtk_ret = new GtkWidget_();
	phpgtk_ret->set_instance((gpointer *)ret);
	return Php::Object("GtkWidget", phpgtk_ret);
}

void GtkStack_::set_visible_child_name(Php::Parameters &parameters)
{
	std::string c_name = parameters[0];

	gchar *name = (gchar *)c_name.c_str();

	gtk_stack_set_visible_child_name(GTK_STACK(instance), name);
}

Php::Value GtkStack_::get_visible_child_name()
{
	const-gchar* ret = gtk_stack_get_visible_child_name(GTK_STACK(instance));

	return ret;
}

void GtkStack_::set_visible_child_full(Php::Parameters &parameters)
{
	std::string c_name = parameters[0];

	gchar *name = (gchar *)c_name.c_str();

	int int_transition = parameters[1];
	GtkStackTransitionType transition = (GtkStackTransitionType) int_transition;

	gtk_stack_set_visible_child_full(GTK_STACK(instance), name, transition);
}

void GtkStack_::set_homogeneous(Php::Parameters &parameters)
{
	gboolean homogeneous = (gboolean)parameters[0];

	gtk_stack_set_homogeneous(GTK_STACK(instance), homogeneous);
}

Php::Value GtkStack_::get_homogeneous()
{
	gboolean ret = gtk_stack_get_homogeneous(GTK_STACK(instance));

	return ret;
}

void GtkStack_::set_hhomogeneous(Php::Parameters &parameters)
{
	gboolean hhomogeneous = (gboolean)parameters[0];

	gtk_stack_set_hhomogeneous(GTK_STACK(instance), hhomogeneous);
}

Php::Value GtkStack_::get_hhomogeneous()
{
	gboolean ret = gtk_stack_get_hhomogeneous(GTK_STACK(instance));

	return ret;
}

void GtkStack_::set_vhomogeneous(Php::Parameters &parameters)
{
	gboolean vhomogeneous = (gboolean)parameters[0];

	gtk_stack_set_vhomogeneous(GTK_STACK(instance), vhomogeneous);
}

Php::Value GtkStack_::get_vhomogeneous()
{
	gboolean ret = gtk_stack_get_vhomogeneous(GTK_STACK(instance));

	return ret;
}

void GtkStack_::set_transition_duration(Php::Parameters &parameters)
{
	guint duration = (int)parameters[0];

	gtk_stack_set_transition_duration(GTK_STACK(instance), duration);
}

Php::Value GtkStack_::get_transition_duration()
{
	guint ret = gtk_stack_get_transition_duration(GTK_STACK(instance));

	return ret;
}

void GtkStack_::set_transition_type(Php::Parameters &parameters)
{
	int int_transition = parameters[0];
	GtkStackTransitionType transition = (GtkStackTransitionType) int_transition;

	gtk_stack_set_transition_type(GTK_STACK(instance), transition);
}

Php::Value GtkStack_::get_transition_type()
{
	GtkStackTransitionType ret = gtk_stack_get_transition_type(GTK_STACK(instance));

	GtkStackTransitionType_ *phpgtk_ret = new GtkStackTransitionType_();
	phpgtk_ret->set_instance((gpointer *)ret);
	return Php::Object("GtkStackTransitionType", phpgtk_ret);
}

Php::Value GtkStack_::get_transition_running()
{
	gboolean ret = gtk_stack_get_transition_running(GTK_STACK(instance));

	return ret;
}

void GtkStack_::set_interpolate_size(Php::Parameters &parameters)
{
	gboolean interpolate_size = (gboolean)parameters[0];

	gtk_stack_set_interpolate_size(GTK_STACK(instance), interpolate_size);
}

Php::Value GtkStack_::get_interpolate_size()
{
	gboolean ret = gtk_stack_get_interpolate_size(GTK_STACK(instance));

	return ret;
}

Php::Value GtkStack_::gtk_stack_get_type()
{
	GType ret = gtk_stack_get_type(GTK_STACK(instance));

	GType_ *phpgtk_ret = new GType_();
	phpgtk_ret->set_instance((gpointer *)ret);
	return Php::Object("GType", phpgtk_ret);
}

void GtkStack_::__construct()
{
	instance = (gpointer *)gtk_stack_new();
}

Php::Value GtkStack_::gtk_stack_sidebar_get_type()
{
	GType ret = gtk_stack_sidebar_get_type(GTK_STACK(instance));

	GType_ *phpgtk_ret = new GType_();
	phpgtk_ret->set_instance((gpointer *)ret);
	return Php::Object("GType", phpgtk_ret);
}

Php::Value GtkStack_::gtk_stack_switcher_get_type()
{
	GType ret = gtk_stack_switcher_get_type(GTK_STACK(instance));

	GType_ *phpgtk_ret = new GType_();
	phpgtk_ret->set_instance((gpointer *)ret);
	return Php::Object("GType", phpgtk_ret);
}

Php::Value GtkStack_::gtk_stack_transition_type_get_type()
{
	GType ret = gtk_stack_transition_type_get_type(GTK_STACK(instance));

	GType_ *phpgtk_ret = new GType_();
	phpgtk_ret->set_instance((gpointer *)ret);
	return Php::Object("GType", phpgtk_ret);
}



