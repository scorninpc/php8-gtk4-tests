

#ifndef _PHPGTK_GTKSTATUSICON_H_
#define _PHPGTK_GTKSTATUSICON_H_

	#include <phpcpp.h>
    #include <gtk/gtk.h>

	

	class GtkStatusIcon_ : public GObject_
    {
		public:
			GtkStatusIcon_();
			~GtkStatusIcon_();

			void set_from_pixbuf(Php::Parameters &parameters);
			void set_from_file(Php::Parameters &parameters);
			void set_from_stock(Php::Parameters &parameters);
			void set_from_icon_name(Php::Parameters &parameters);
			Php::Value get_pixbuf();
			Php::Value get_stock();
			Php::Value get_icon_name();
			Php::Value get_size();
			Php::Value get_screen();
			void set_has_tooltip(Php::Parameters &parameters);
			void set_tooltip_text(Php::Parameters &parameters);
			void set_tooltip_markup(Php::Parameters &parameters);
			void set_title(Php::Parameters &parameters);
			Php::Value get_title();
			void set_name(Php::Parameters &parameters);
			void set_visible(Php::Parameters &parameters);
			Php::Value get_visible();
			Php::Value is_embedded();
			Php::Value get_has_tooltip();
			Php::Value get_tooltip_text();
			Php::Value get_tooltip_markup();
			void __construct();
			Php::Value gtk_status_icon_new_from_pixbuf(Php::Parameters &parameters);
			Php::Value gtk_status_icon_new_from_file(Php::Parameters &parameters);
			Php::Value gtk_status_icon_new_from_stock(Php::Parameters &parameters);
			Php::Value gtk_status_icon_new_from_icon_name(Php::Parameters &parameters);
			Php::Value position_menu(Php::Parameters &parameters);


	};

#endif