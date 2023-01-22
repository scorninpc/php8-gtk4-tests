<?php

return [

// gtk_tree_sortable_get_sort_column_id
'gtk_tree_sortable_get_sort_column_id' => "
	gint* sort_column_id;

	int int_order = parameters[0];
	GtkSortType *order = (GtkSortType*)int_order;

	gtk_tree_sortable_get_sort_column_id(GTK_TREE_SORTABLE(instance), sort_column_id, order);

	return sort_column_id;
",

];