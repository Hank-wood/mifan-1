<?php
require "mifan/Mifan.php";

Mifan::route("/", function(){
    echo "hello world!";
});

Mifan::start();