<?php

return "

#ifndef _PHPGTK_%(classid)s_H_
#define _PHPGTK_%(classid)s_H_

	#include <phpcpp.h>
    #include <gtk/gtk.h>

	%(includes)s

	class %(classname)s_ : public %(parentname)s_
    {
		public:
			%(classname)s_();
			~%(classname)s_();

%(methodsdef)s

	};

#endif";