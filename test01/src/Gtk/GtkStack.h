

#ifndef _PHPGTK_GTKSTACK_H_
#define _PHPGTK_GTKSTACK_H_

	#include <phpcpp.h>
    #include <gtk/gtk.h>

	

	class GtkStack_ : public GtkContainer_
    {
		public:
			GtkStack_();
			~GtkStack_();

			void add_named(Php::Parameters &parameters);
			void add_titled(Php::Parameters &parameters);
			Php::Value get_child_by_name(Php::Parameters &parameters);
			void set_visible_child(Php::Parameters &parameters);
			Php::Value get_visible_child();
			void set_visible_child_name(Php::Parameters &parameters);
			Php::Value get_visible_child_name();
			void set_visible_child_full(Php::Parameters &parameters);
			void set_homogeneous(Php::Parameters &parameters);
			Php::Value get_homogeneous();
			void set_hhomogeneous(Php::Parameters &parameters);
			Php::Value get_hhomogeneous();
			void set_vhomogeneous(Php::Parameters &parameters);
			Php::Value get_vhomogeneous();
			void set_transition_duration(Php::Parameters &parameters);
			Php::Value get_transition_duration();
			void set_transition_type(Php::Parameters &parameters);
			Php::Value get_transition_type();
			Php::Value get_transition_running();
			void set_interpolate_size(Php::Parameters &parameters);
			Php::Value get_interpolate_size();
			Php::Value gtk_stack_get_type();
			void __construct();
			Php::Value gtk_stack_sidebar_get_type();
			Php::Value gtk_stack_switcher_get_type();
			Php::Value gtk_stack_transition_type_get_type();


	};

#endif