这是一个使用 Laravel 框架的 Todo App，前端主要使用了 Vuejs ，CSS 样式使用了一些 bootstrap。

创建项目的目的是为了提升自己前后端的技术，以及框架使用能力，让自己对 web 开发更加熟练。

功能包括：
1. 对 Todo 项的 CURD 操作
2. 访客使用本 App 时，数据保存在客户端本地
3. 登录用户使用本 App 时，数据保存在服务器端


--- 

使用的技术和框架版本：

1. Laravel Framework version 5.2.45
2. Vuejs v1.0.26
3. jQuery v2.2.3

--- 

杂项：
1. 登录模块使用 Laravel 的 auth 组件， php artisan make:auth。该组件自带的一些样式和字体由于 CDN 在国外，可能需要代理才能正常显示。
2. CURD 操作使用了 Vuejs 的 Vue-resource plugin，简化了 Ajax 代码的编写
