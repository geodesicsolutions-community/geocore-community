
function click_close_div(params)
{
    var element = CP.params('param1',params);
    element = CJAX.is_element(element);
    element.onclick = function () {
        CJAX.hide(element);
        return false;
    }
}
