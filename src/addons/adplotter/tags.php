<?php

//addons/adplotter/tags.php

# Adplotter Link addon

class addon_adplotter_tags extends addon_adplotter_info
{

    public function aff_id()
    {
        $reg = geoAddon::getRegistry($this->name);
        return $reg->affiliate_code;
    }
}
