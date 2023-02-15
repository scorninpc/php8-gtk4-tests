
# Please ignore this repo, its test only


### Create def file of GTK
- [x] Find a way to parse headers or get some def from gtkmm (for example)
- [x] def need to tell if the name of function
- [x] def need to tell if the name of class
- [x] def need to tell if the parameters and the type of
- [ ] def need to tell if function is deprecated from X version

### Parse def file
- [x] Read and organize the vector of objects
- [ ] Read and organize the vector of enums
- [ ] Read and organize the vector of functions

### Code gen
- [ ] Better parse of parameters, to verify if param is null for example
- [ ] Test throw exceptions on errors
- [ ] Create GObject manualy
- [x] Create a way to rewrite a method manualy
- [ ] Try implement of PHP-CPP
- [ ] Alguns parametros e retornos do tipo int gboolean, string, funcionam direto, sem precisar dar cast, verificar o que funciona direto `parameters[0]` como parametro na função C e o que da pra retornar direto `Note: verificara o metodo GtkEntry::set_text()` para compreender

### Compile
- [ ] Compile for linux
- [ ] Create .appimage for linux
- [ ] Compile for windows
- [ ] Create portable zip for windows
- [ ] Compile for mac
- [ ] Compile implement lib for mac integration
- [ ] Create portable package for mac