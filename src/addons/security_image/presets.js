//load different presets - add new javascript to bottom of file.
//Remember to make sure function name is not already used.

/*
 * Overall Looks Section
 */
function loadInstallDefaults()
{
    //image size
    $('width').value = '125';
    $('height').value = '50';
    //character settings
    $('numChars').value = '4';
    if (!$('use_small_font_size')) {
        //only set font size if host has TTF fonts
        $('fontSize').value = '22';
    }
    $('useRandomColors').checked = 'checked';
    $('rmin').value = '0';
    $('rmax').value = '150';
    $('gmin').value = '0';
    $('gmax').value = '150';
    $('bmin').value = '0';
    $('bmax').value = '150';
    $('allowedChars').value = '2346789ABCDEFGHJKLMNPRTWXYZ';
    //overall image effects
    $('useDistort').checked = 'checked';
    $('distort').value = '.5';
    $('useBlur').checked = '';
    $('useEmboss').checked = '';
    $('useSketchy').checked = '';
    $('useNegative').checked = '';
    //add to image
    $('useRefresh').checked = '';
    $('refreshUrl').value = 'addons/security_image/reload.gif';
    $('useGrid').checked = '';
    $('numGrid').value = '8';
    $('useLines').checked = 'checked';
    $('lines').value = '3';
    $('useNoise').checked = 'checked';
    $('numNoise').value = '150';
}

function loadCleanLook()
{
    //image size
    $('width').value = '125';
    $('height').value = '50';
    //character settings
    $('numChars').value = '4';
    if (!$('use_small_font_size')) {
        //only set font size if host has TTF fonts
        $('fontSize').value = '16';
    }
    $('useRandomColors').checked = 'checked';
    $('rmin').value = '0';
    $('rmax').value = '150';
    $('gmin').value = '0';
    $('gmax').value = '150';
    $('bmin').value = '0';
    $('bmax').value = '150';
    $('allowedChars').value = '2346789ABCDEFGHJKLMNPRTWXYZ';
    //overall image effects
    $('useDistort').checked = '';
    $('distort').value = '.5';
    $('useBlur').checked = '';
    $('useEmboss').checked = '';
    $('useSketchy').checked = '';
    $('useNegative').checked = '';
    //add to image
    $('useRefresh').checked = '';
    $('refreshUrl').value = 'addons/security_image/reload.gif';
    $('useGrid').checked = 'checked';
    $('numGrid').value = '8';
    $('useLines').checked = '';
    $('lines').value = '3';
    $('useNoise').checked = '';
    $('numNoise').value = '250';
}

function loadIceyBlackLook()
{
    //image size
    $('width').value = '125';
    $('height').value = '50';
    //character settings
    $('numChars').value = '4';
    if (!$('use_small_font_size')) {
        //only set font size if host has TTF fonts
        $('fontSize').value = '20';
    }
    $('useRandomColors').checked = 'checked';
    $('rmin').value = '255';
    $('rmax').value = '255';
    $('gmin').value = '15';
    $('gmax').value = '120';
    $('bmin').value = '15';
    $('bmax').value = '20';
    $('allowedChars').value = '2346789ABCDEFGHJKLMNPRTWXYZ';
    //overall image effects
    $('useDistort').checked = '';
    $('distort').value = '.5';
    $('useBlur').checked = 'checked';
    $('useEmboss').checked = '';
    $('useSketchy').checked = '';
    $('useNegative').checked = 'checked';
    //add to image
    $('useRefresh').checked = 'checked';
    $('refreshUrl').value = 'addons/security_image/reload.gif';
    $('useGrid').checked = 'checked';
    $('numGrid').value = '8';
    $('useLines').checked = '';
    $('lines').value = '3';
    $('useNoise').checked = '';
    $('numNoise').value = '250';
}

function loadPlaidLook()
{
    //image size
    $('width').value = '125';
    $('height').value = '50';
    //character settings
    $('numChars').value = '4';
    if (!$('use_small_font_size')) {
        //only set font size if host has TTF fonts
        $('fontSize').value = '20';
    }
    $('useRandomColors').checked = 'checked';
    $('rmin').value = '255';
    $('rmax').value = '255';
    $('gmin').value = '15';
    $('gmax').value = '120';
    $('bmin').value = '15';
    $('bmax').value = '20';
    $('allowedChars').value = '2346789ABCDEFGHJKLMNPRTWXYZ';
    //overall image effects
    $('useDistort').checked = '';
    $('distort').value = '.5';
    $('useBlur').checked = '';
    $('useEmboss').checked = '';
    $('useSketchy').checked = '';
    $('useNegative').checked = '';
    //add to image
    $('useRefresh').checked = 'checked';
    $('refreshUrl').value = 'addons/security_image/reload.gif';
    $('useGrid').checked = 'checked';
    $('numGrid').value = '8';
    $('useLines').checked = '';
    $('lines').value = '3';
    $('useNoise').checked = '';
    $('numNoise').value = '250';
}

function loadAsphaltLook()
{
    //image size
    $('width').value = '150';
    $('height').value = '50';
    //character settings
    $('numChars').value = '4';
    if (!$('use_small_font_size')) {
        //only set font size if host has TTF fonts
        $('fontSize').value = '25';
    }
    $('useRandomColors').checked = 'checked';
    $('rmin').value = '0';
    $('rmax').value = '150';
    $('gmin').value = '0';
    $('gmax').value = '150';
    $('bmin').value = '0';
    $('bmax').value = '150';
    $('allowedChars').value = '2346789ABCDEFGHJKLMNPRTWXYZ';
    //overall image effects
    $('useDistort').checked = '';
    $('distort').value = '.5';
    $('useBlur').checked = 'checked';
    $('useEmboss').checked = '';
    $('useSketchy').checked = 'checked';
    $('useNegative').checked = 'checked';
    //add to image
    $('useRefresh').checked = 'checked';
    $('refreshUrl').value = 'addons/security_image/reload.gif';
    $('useGrid').checked = '';
    $('numGrid').value = '8';
    $('useLines').checked = '';
    $('lines').value = '3';
    $('useNoise').checked = 'checked';
    $('numNoise').value = '2000';
}

function loadGrainyLook()
{
    //image size
    $('width').value = '150';
    $('height').value = '50';
    //character settings
    $('numChars').value = '4';
    if (!$('use_small_font_size')) {
        //only set font size if host has TTF fonts
        $('fontSize').value = '25';
    }
    $('useRandomColors').checked = 'checked';
    $('rmin').value = '0';
    $('rmax').value = '150';
    $('gmin').value = '0';
    $('gmax').value = '150';
    $('bmin').value = '0';
    $('bmax').value = '150';
    $('allowedChars').value = '2346789ABCDEFGHJKLMNPRTWXYZ';
    //overall image effects
    $('useDistort').checked = '';
    $('distort').value = '.5';
    $('useBlur').checked = 'checked';
    $('useEmboss').checked = '';
    $('useSketchy').checked = 'checked';
    $('useNegative').checked = '';
    //add to image
    $('useRefresh').checked = 'checked';
    $('refreshUrl').value = 'addons/security_image/reload.gif';
    $('useGrid').checked = '';
    $('numGrid').value = '8';
    $('useLines').checked = '';
    $('lines').value = '3';
    $('useNoise').checked = 'checked';
    $('numNoise').value = '2000';
}

/*
 * Color Preset Section
 */
//Only do colors and select the random color box
function loadFontColorRed()
{
    $('useRandomColors').checked = 'checked';
    $('rmin').value = '150';
    $('rmax').value = '255';
    $('gmin').value = '0';
    $('gmax').value = '0';
    $('bmin').value = '0';
    $('bmax').value = '0';
}
function loadFontColorGreen()
{
    $('useRandomColors').checked = 'checked';
    $('rmin').value = '0';
    $('rmax').value = '0';
    $('gmin').value = '150';
    $('gmax').value = '255';
    $('bmin').value = '0';
    $('bmax').value = '0';
}
function loadFontColorBlue()
{
    $('useRandomColors').checked = 'checked';
    $('rmin').value = '0';
    $('rmax').value = '0';
    $('gmin').value = '0';
    $('gmax').value = '0';
    $('bmin').value = '150';
    $('bmax').value = '255';
}
function loadFontColorLight()
{
    $('useRandomColors').checked = 'checked';
    $('rmin').value = '150';
    $('rmax').value = '200';
    $('gmin').value = '150';
    $('gmax').value = '200';
    $('bmin').value = '150';
    $('bmax').value = '200';
}
function loadFontColorDark()
{
    $('useRandomColors').checked = 'checked';
    $('rmin').value = '0';
    $('rmax').value = '100';
    $('gmin').value = '0';
    $('gmax').value = '100';
    $('bmin').value = '0';
    $('bmax').value = '100';
}
function loadFontColorBright()
{
    $('useRandomColors').checked = 'checked';
    $('rmin').value = '0';
    $('rmax').value = '255';
    $('gmin').value = '0';
    $('gmax').value = '255';
    $('bmin').value = '0';
    $('bmax').value = '255';
}

/*
 * Misc section
 */
function loadAlphaLowercase()
{
    //only load lowercase letters that are not easily confused with other letters (like don't use lowercase L)
    $('allowedChars').value = '2346789ABCDEFGHJKLMNPRTWXYZabcdefghjkmnpqrtwxyz';
}