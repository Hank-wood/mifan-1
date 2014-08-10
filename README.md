# Mifan是什么?

Mifan是一个快速, 简单, 可扩展的迷你PHP框架. 


```php
require "mifan/Mifan.php";

Mifan::route("/", function(){
    echo "Hello World!";
});

Mifan::start();
```


# PHP版本要求
`>= PHP 5.3`


# 安装
1. 下载Mifan框架并解压到您web目录下.
2. 配置您的web服务器.

对于*Apache*, 编辑您的 `.htaccess` 文件, 加上类似如下配置:
```php
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

对于*Nginx*, 加上类似如下配置:
```php
server {
    set $root /var/www/mifan-master;
    listen 0.0.0.0:80;
    server_name www.domain.com;
    root $root;

    location / { 
        try_files $uri $uri/ /index.php;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $root/index.php;
        include fastcgi_params;
    }   
} 
```

3\. 创建一个 `index.php` 文件.

首先, 引入Mifan框架.

```php
require "mifan/Mifan.php";
```

然后, 定义一个路由并绑定一个函数来处理HTTP请求.

```php
Mifan::route("/", function() {
   echo "Hello World!" ;
});
```

最后, 启动Mifan框架.
```php
Mifan::start();
```

#路由
Mifan的路由是由 URL+回调函数 组成的:
```php
Mifan::route("/", function(){
    echo "Hello World!";
});
```
或者:
```php
function hello() {
    echo "Hello World";
}
Mifan::route("/", "hello");
```
或者:
```php
class Geek {
    public static function say() {
        echo "Hello World";
    }
}
Mifan::route("/", array("Geek", "say"));
```
或者:
```php
class Geek {
    public function say() {
        echo "Hello World";
    }
}
Mifan::route("/", array(new Geek(), "say"));
```
路由是按顺序进行定义的, 所以第一个匹配的将会被执行.

## HTTP Method
默认情况下, 路由模式匹配所有HTTP Method(`GET`, `PUT`, `POST`, `DELETE`). 您可以在URL前面指定Method.
```php
Mifan::route("GET /", function() {
    echo "仅接受GET请求";
});

Mifan::route("POST /", function() {
    echo "仅接受POST请求";
});
```
您同样可以使用 `|` 映射多个Method到同一个回调函数.
```php
Mifan::route("GET|POST /", function() {
    echo "接受GET和POST其中之一的请求";
});
```

## 正则表达式
您可以在路由里使用正则表达式:
```php
Mifan::route("/user/[0-9]+", function() {
    #匹配 /user/1314
});
```

## 参数
您可以指定参数到路由里, 该参数将会作为回调函数的参数.
```php
Mifan::route("/@name/@id", function($name, $id) {
    echo "name:{$name}, id:{$id}";
});
```
同样还可以在参数里使用正则表达式:
```php
Mifan::route("/@name/@id:[0-9]{3}", function() {
    #匹配 /bob/123
    #不匹配 /bob/1234
});
```

## 可选参数
```php
Mifan::route("/blog/(/@year(/@month(/@day)))", function($year, $month, $day) {
    # 匹配下面的URL:
    # /blog/2014/08/11
    # /blog/2014/08
    # /blog/2014
    # /blog
});
```
任何没有匹配到的可选参数的默认值是 `NULL`

## 通配符
如果想匹配多段的URL, 您可以使用 `*`
```php
Mifan::route("/blog/*", function() {
    # 匹配如下
    # /blog/2012/02/22
    # /blog/any
    # ....
});
```
匹配所有的请求并绑定到一个回调函数, 您可以这么做:
```php
Mifan::route("*", function() {
    echo "站点维护中... 100年后开放";
});
```

## 传递
您可以让第一个被匹配到的回调函数执行后, 然后递到下一个被匹配的回调函数, 回调函数只需返回 `TRUE` 即可:
```php
Mifan::route("/user/@name", function($name) {
    if ($name != "bob") {
        //继续下一个路由
        return TRUE;
    } 
    else {
        echo "Hello bob";
    }
});

Mifan::route("/user/*", function() {
    //这将会被执行
});
```

# 可扩展
Mifan是可扩展的, 该框架自身带有一系列函数和组件, 但是允许您映射自己的函数和类到框架中, 甚至可以重载已有的类和函数.

## 映射函数
使用 `map` 方法, 可映射您的函数到Mifan中:
```php
Mifan::map("hello", function($name) {
    echo "hello {$name}";
});

Mifan::hello("bob");
```

## 注册类
使用 `register` 方法, 可注册您的类到Mifan中:
```php
class User {
    public function getName() {
        return "bob";
    }
}
Mifan::register("user", "User");
$user = Mifan::user();
echo $user->getName(); #输出bob
```

`register` 方法允许将参数传递到类构造函数中, 也可以对该类的对象实例进行一系列的初始化操作:
```php
Mifan::register("db", "PDO", array("mysql:host=localhost;dbname=test","user","passwd")});
$db = Mifan::db();

//上述的代码就相当于如下代码:
$db = new PDO("mysql:host=localhost;dbname=test","user","passwd"); 
```

您还可以在 `register` 方法的第4个参数传递一个回调函数, 用于在创建对象时进行比如初始化的动作:
```php
Mifan::register("db", "PDO", array("mysql:host=localhost;dbname=test","user","passwd")}, function($db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
});
$db = Mifan::db();
```

默认情况下, 每当您加载您自己的类时, 使用的是共享的实例, 相当于单例模式. 要重新得到一个新实例时, 只需简单传递 `FALSE` 作为参数:
```php
//共享实例
$db = Mifan::db();

//重新创建实例
$db = Mifan::db(FALSE);
```

另外, 需要注意的是, 如果写了类似如下代码:
```php
function hello() {
    echo "function: hello";
}

class Hello { }

Mifan::map("hello", "hello");
Mifan::register("hello", "Hello");
```
那么, 当您调用:
```php
Mifan::hello();
```
时, 将会输出: `function: hello`

也就是说, 注册类, 映射函数, 出现了同名时, 映射的函数优先被调用.

## 重载
Mifan允许您根据自身需要, 重载默认的函数而不需要修改Mifan的代码.

举个例子, 当Mifan无法匹配一个URL到任何一个路由时, Mifan就会调用 `notFound` 方法来发送 `404` 状态码和错误信息. 您可以使用 `map` 来重载 `notFound`, 输出您自己的404错误页面:
```php
Mifan::map("notFound", function() {
    //显示自定义的404错误页面
    include "error404.html";
});
```

Mifan同样允许您替换框架中的核心组件. 举个例子, 如果您不满意默认的`Router`类, 想使用自己编写的`CustRouter`类, 那么只需如下这样:
```php
class CustRouter {
    // 您的代码
}
Mifan::register("router", "CustRouter");
```
当然, 前提条件是: 知道要实现哪些方法.

# 变量
Mifan允许您保存变量到框架中, 使其可在任何地方进行访问:
```php
//保存您的变量
Mifan::set("SITE_NAME", "我的站点");

//或者
Mifan:set(array(
    "SITE_NAME" => "我的站点", 
    "LIMIT" => 10
));

//其他任何地方
Mifan::get("SITE_NAME");
```

检测一个变量是否存在:
```php
if (Mifan::has("SITE_NAME")) {
    //code
}
```

清除指定变量
```php
Mifan::clear("SITE_NAME");
```

清除所有已保存的变量:
```php
Mifan::clear();
```

# 视图
Mifan默认提供了一个基本的模板引擎. 只需简单调用 `render` 方法, 传递模板文件名和可选数据即可:
```php
Mifan::render("hello.php", array(
    "name" => "bob",
));
```

`hello.php` 模板文件:
```php
hello, <?= $name; ?> !
```
将会输出:
`hello, bob !`

您还可以这样分配变量给模板:
```php
Mifan::view()->set("name", "bob");
Mifan::render("hello.php");
```

温馨提示: 您可以把模板文件的 `.php` 后缀名忽略掉.

默认情况下, Mifan是在 `view` 目录下寻找模板文件, 您可以修改模板文件所在目录:
```php
Mifan::set("mifan.views.path", "/path/to/views");
```

## 布局
通常情况下, 大部分网站都有共同的`头部区域` 和 `底部区域`, Mifan提供了简单的视图布局能力:
```php
Mifan::render("header.php", array(
        "header" => "头部内容",
    ),"header_content"
);
Mifan::render("footer.php", array(
        "footer" => "底部内容",
    ),"footer_content"
);

Mifan::render("layout.php", array(
    "title" => "主页内容",
));
```

`header.php` 视图文件:
```php
<div><?= $header; ?></div>
```

`footer.php` 视图文件:
```php
<div><?= $footer; ?></div>
```

`layout.php` 视图文件:
```php
<html>
<head>
    <title><?= $title; ?></title>
</head>
<body>
    <?= $header_content; ?>
    <?= $footer_content; ?>
</body>
</html>
```

将会输出如下:
```php
<html>
<head>
    <title>主页内容</title>
</head>
<body>
    <div>头部内容</div>
    <div>底部内容</div>
</body>
</html>
```

# 自定义模板引擎
如果您需要诸如[Twig](http://twig.sensiolabs.org), [Smarty](http://www.smarty.net)这样的模板引擎来替代默认的模板引擎. So easy! 用 `Smarty` 来举例:
```php
require "libs/Smarty.class.php";

Mifan::register("view", "Smarty", array(), function($smarty) {
    $smarty->template_dir = "./templates/";
    $smarty->compile_dir = './templates_c/';
    $smarty->config_dir = './config/';
    $smarty->cache_dir = './cache/';    
});
Mifan::view()->assign("name", "bob");
Mifan::view()->display();
```
换成其他模板引擎也如此, 对于那些需要比较复杂初始化配置的模板引擎, 您可以创建一个class, 来作为一个包裹器, 然后使用 `register` 来进行注册. 比如[Twig](http://twig.sensiolabs.org):
```php
require "/path/to/lib/Twig/Autoloader.php";
Twig_Autoloader::register();

class CustTwig {
    public function __construct() {
        $this->loader = new Twig_Loader_Filesystem("/path/to/templates");
        $this->twig = new Twig_Environment($this->loader, array(
            "cache" => "/path/to/compilation_cache",
        ));    
    }
    public function instance() {
        return $this->twig;
    }
}
Mifan::register("twig", "CustTwig");
echo Mifan::twig()->instance()->render("index.html", array("name" => "bob"));
```

# 错误处理

## 错误和异常
所有的错误和异常都会被Mifan所捕获, 然后调用 `error` 方法进行处理. 默认处理方式是发送 `HTTP 500 Internal Server Error` 和 一些错误信息.

您可以根据自身需要进行重载默认行为:
```php
Mifan::map("error", function(Exception $e) {
    echo $e->getTraceAsString();
});
```

默认情况下, 错误信息不会记录到web服务日志里. 您可以开启:
```php
Mifan::set("mifan.log_errors", TRUE);
```

## 页面不存在
当一个URL没有可匹配的路由时, 将会调用 `notFound` 方法. 默认行为是发送`HTTP 404 Not Found` 和 一些简单的信息.

您可以根据自身需要进行重载:
```php
Mifan::map("notFound", function() {
    //显示自定义的404错误页面
    include "error404.html";
});
```

# 重定向
您可以使用 `redirect` 方法将当前HTTP请求重定向到其他路由:
```php
Mifan::redirect("/new/location");
```
默认发送的HTTP状态码是 `303` , 您可指定其他状态码给第二参数:
```php
Mifan::redirect("/new/location", 301);
```


# 请求
Mifan将大部分请求信息以对象形式保存了起来, 您可以这么获取整个请求信息:
```php
$request = Mifan::request();
```

该 `request` 对象提供如下属性:
```
$url - 当前URL
$base - base URL
$method - HTTP method
$referrer - referrer URL
$ip - 来源IP
$ajax - 是否为ajax请求
$scheme - 服务器协议 (http, https)
$user_agent - 浏览器信息
$type - content type
$length - content length
$query - Query string 参数 ?name=xiaofan&age=23
$data - POST 或者 JSON 数据
$cookie - $_COOKIE 
$session - $_SESSION
$files - $_FILES
$accept - Accept
```

`query`, `data`, `cookie`, `session`, `files` 属性可以数组或对象的方式进行访问:
```php
$id = Mifan::request()->query["id"]
```
或者:
```php
$id = Mifan::request()->query->id;
```

其他属性就简单如下访问:
```php
$url = Mifan::request()->url;
```

# JSON和POST输入
```php
$id = Mifan::request()->data->id;
```

# JSON 输出
如果要以JSON的方式发送数据, 只需简单如下:
```php
Mifan::json(array("name"=>"bob"));
```

#配置
如前面所述, 修改某一配置, 只需简单调用 `Mifan::set` 方法即可:
```php
Mifan::set("mifan.log_errors", TRUE);
```

下面是所有可用配置参数:
```php
mifan.views.path - 模板文件所在目录. (默认值: views)
mifan.log_errors - 记录错误信息到 web服务器日志中. (默认值: FALSE)
mifan.handle_errors - 允许Mifan捕获并处理所有错误和异常. (默认值: TRUE)
```

# 其他
该框架并未经过严格测试, 请慎重应用于生产环境.