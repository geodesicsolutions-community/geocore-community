
function click_show_div(params)
{
    var element = CP.params('param1',params);
    element = CJAX.is_element(element);
    CJAX.show(element);
}
