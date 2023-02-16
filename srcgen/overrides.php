<?php

return [

// gtk_tree_sortable_get_sort_column_id
'gtk_tree_sortable_get_sort_column_id' => "
Php::Value GtkTreeSortable_::get_sort_column_id(Php::Parameters &parameters)
{
	gint* sort_column_id;

	int int_order = parameters[0];
	GtkSortType *order = (GtkSortType*)int_order;

	gtk_tree_sortable_get_sort_column_id(GTK_TREE_SORTABLE(instance), sort_column_id, order);

	return sort_column_id;
}
",

// gtk_alignment_get_padding
'gtk_alignment_get_padding' => "
Php::Value GtkAlignment_::get_padding()
{
	guint* top;
	guint* bottom;
	guint* left;
	guint* right;

	gtk_alignment_get_padding(GTK_ALIGNMENT(instance), top, bottom, left, right);

	Php::Value arr;
	arr[\"top\"] = top;
	arr[\"bottom\"] = bottom;
	arr[\"left\"] = left;
	arr[\"right\"] = right;

	return arr;
}
",

// gtk_status_icon_position_menu
'gtk_status_icon_position_menu' => "
Php::Value GtkStatusIcon_::position_menu(Php::Parameters &parameters)
{
	GtkMenu *menu;
	if(parameters.size() > 0) {
		Php::Value object_menu = parameters[0];
		GtkMenu_ *phpgtk_menu = (GtkMenu_ *)object_menu.implementation();
		menu = GTK_WIDGET(phpgtk_menu->get_instance());
	}

	gint* x;
	gint* y;
	gboolean *push_in;

	gtk_status_icon_position_menu(GTK_MENU(menu), x, y, push_in, GTK_STATUS_ICON(instance));

	Php::Value arr;
	arr[\"x\"] = x;
	arr[\"y\"] = y;

	return arr;
}
",

// gtk_widget_size_request
'gtk_widget_size_request' => "
void GtkWidget_::size_request(Php::Parameters &parameters)
{
	GtkRequisition* requisition;

	gtk_widget_size_request(GTK_WIDGET(instance), requisition);

	Php::Value arr;
	arr[\"width\"] = requisition.width;
	arr[\"height\"] = requisition.height;

	return arr;
}
",


// gtk_widget_size_allocate
'gtk_widget_size_allocate' => "
void GtkWidget_::size_allocate(Php::Parameters &parameters)
{
	GtkAllocation* allocation;

	gtk_widget_size_allocate(GTK_WIDGET(instance), allocation);

	Php::Value arr;
	arr[\"width\"] = allocation.width;
	arr[\"height\"] = allocation.height;
	arr[\"x\"] = allocation.x;
	arr[\"y\"] = allocation.y;

	return arr;
}
",


// gtk_widget_size_allocate_with_baseline
'gtk_widget_size_allocate_with_baseline' => "
void GtkWidget_::size_allocate_with_baseline(Php::Parameters &parameters)
{
	GtkAllocation* allocation;
	gint baseline = (gint)parameters[0];

	gtk_widget_size_allocate_with_baseline(GTK_WIDGET(instance), allocation, baseline);

	Php::Value arr;
	arr[\"width\"] = allocation.width;
	arr[\"height\"] = allocation.height;
	arr[\"x\"] = allocation.x;
	arr[\"y\"] = allocation.y;

	return arr;
}
",


// gtk_widget_get_preferred_size
'gtk_widget_get_preferred_size' => "
void GtkWidget_::get_preferred_size(Php::Parameters &parameters)
{
	GtkRequisition* minimum_size;
	GtkRequisition* natural_size;

	gtk_widget_get_preferred_size(GTK_WIDGET(instance), minimum_size, natural_size);

	Php::Value arr;
	arr[\"minimum_width\"] = minimum_size.width;
	arr[\"minimum_height\"] = minimum_size.height;
	arr[\"natural_width\"] = natural_size.width;
	arr[\"natural_height\"] = natural_size.height;

	return arr;
}
",

// gtk_widget_get_child_requisition
'gtk_widget_get_child_requisition' => "
void GtkWidget_::get_child_requisition(Php::Parameters &parameters)
{
	GtkRequisition* requisition;

	gtk_widget_get_child_requisition(GTK_WIDGET(instance), requisition);

	Php::Value arr;
	arr[\"width\"] = requisition.width;
	arr[\"height\"] = requisition.height;

	return arr;
}
",

// gtk_widget_get_clipboard
'gtk_widget_get_clipboard' => "
void GtkWidget_::get_child_requisition(Php::Parameters &parameters)
{
	GtkClipboard* ret = gtk_widget_get_clipboard(GTK_WIDGET(instance), parameters[0]);

	return cobject_to_phpobject(ret);
}
",

// gtk_widget_translate_coordinates
'gtk_widget_translate_coordinates' => "
Php::Value GtkWidget_::translate_coordinates(Php::Parameters &parameters)
{
	GtkWidget *dest_widget;
	Php::Value object_dest_widget = parameters[0];
	GtkWidget_ *phpgtk_dest_widget = (GtkWidget_ *)object_dest_widget.implementation();
	dest_widget = GTK_WIDGET(phpgtk_dest_widget->get_instance());

	gint src_x = (gint)parameters[1];

	gint src_y = (gint)parameters[2];

	gint* dest_x;
	gint* dest_x

	gboolean ret = gtk_widget_translate_coordinates(GTK_WIDGET(instance), dest_widget, src_x, src_y, dest_x, dest_y);

	if(!ret) {
		return ret;
	}
	else {	
		Php::Value arr;
		arr[\"x\"] = x;
		arr[\"y\"] = y;
		return arr;
	}
}
",



// gtk_widget_style_get_property
'gtk_widget_style_get_property' => "
Php::Value GtkWidget_::style_get_property(Php::Parameters &parameters)
{
	
	GValue value;

	memset (&value, 0, sizeof (GValue));
	g_value_init( &value, G_TYPE_INT );

	std::string c_property_name = parameters[0];
	gchar *property_name = (gchar *)c_property_name.c_str();

	gtk_widget_style_get_property(GTK_WIDGET(instance), property_name, &value);

	return gvalue_to_phpvalue(&value);
}
",




];