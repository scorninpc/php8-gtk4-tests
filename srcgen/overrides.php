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

];