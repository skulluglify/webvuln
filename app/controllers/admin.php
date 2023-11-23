<?php
namespace App\Controllers;

use Skfw\Tags\PathTag;

class AdminController {

    public function __construct() {


    }

    #[PathTag(name: "Home Based", value: "/")]
    public function home() {}
}
