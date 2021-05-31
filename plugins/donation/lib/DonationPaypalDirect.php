<?php

require_once(path('includes/libraries/paypal_class.php'));

class DonationPaypalDirect extends paypal_class{
    private $s_fields = array();

    public function add_field($field, $value) {
        $this->s_fields["$field"] = $value;
    }

    public function submit_paypal_post() {
        echo "<html>\n";
        echo "<head><title>Processing Payment...</title></head>\n";
        echo "<body onLoad=\"document.forms['paypal_form'].submit();\">\n";
        echo "<center><h2>Please wait, your request is being processed and you";
        echo " will be redirected to the paypal website.</h2></center>\n";
        echo "<form method=\"post\" name=\"paypal_form\" ";
        echo "action=\"".$this->getPaypalURL()."\">\n";

        if(isset($this->paypal_mail)) {
            echo "<input type=\"hidden\" name=\"business\" value=\"$this->paypal_mail\"/>\n";
        }

        foreach ($this->s_fields as $name => $value) {
            echo "<input type=\"hidden\" name=\"$name\" value=\"$value\"/>\n";
        }

        echo "<center><br/><br/>If you are not automatically redirected to ";
        echo "paypal within 5 seconds...<br/><br/>\n";
        echo "<input type=\"submit\" value=\"Click Here\"></center>\n";

        echo "</form>\n";
        echo "<script> window.onload = function(){document.forms['paypal_form'].submit();}</script>";
        echo "</body></html>\n";
        exit;
    }
}

//$paypal = new \paypal_class();