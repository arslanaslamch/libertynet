<?php
register_asset("cookieconsent::css/cookieconsent.css");
register_asset("cookieconsent::js/cookieconsent.js");
register_asset("cookieconsent::js/cookie.js");

register_hook("footer", function() {
    echo view("cookieconsent::cookieconsent");
});
