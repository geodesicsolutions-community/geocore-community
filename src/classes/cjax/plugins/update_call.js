
var _execute;
function update_call(args)
{
    var element = CP.params('param1',args);
    _execute = CP.params('param2',args);
    var label = CP.params('param3',args);
    if (!element || !_execute) {
        return false;
    }
    element = CJAX.is_element(element,false);
    if (!element) {
        return false;
    }

    switch (element.type) {
        case 'submit':
        case 'button':
            CJAX.set.event(element,'click',function () {
                eval(_execute);

            });element.value = 'Save';
            //element.onclick =
        break;
    }
}